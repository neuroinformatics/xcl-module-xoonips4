<?php

namespace Xoonips\Installer;

use Xoonips\Core\LanguageManager;
use Xoonips\Core\XCubeUtils;

/**
 * generic module updater class.
 */
class ModuleUpdater
{
    /**
     * module install log.
     *
     * @var Installer\ModuleInstallLog
     */
    public $mLog = null;

    /**
     * milestone.
     *
     * @var array
     */
    protected $mMilestone = [];

    /**
     * current xoops module.
     *
     * @var XoopsModule
     */
    protected $mCurrentXoopsModule = null;

    /**
     * target xoops module.
     *
     * @var XoopsModule
     */
    protected $mTargetXoopsModule = null;

    /**
     * current module version.
     *
     * @var int
     */
    protected $mCurrentVersion = 0;

    /**
     * target module version.
     *
     * @var int
     */
    protected $mTargetVersion = 0;

    /**
     * flag for force mode.
     *
     * @var bool
     */
    protected $mForceMode = false;

    /**
     * language manager.
     *
     * @var Core\LanguageManager
     */
    protected $mLangMan = null;

    /**
     * time limit.
     *
     * @var int
     */
    protected $mTimeLimit = -1;

    /**
     * custom pre-update hooks.
     *
     * @var array
     */
    protected $mPreUpdateHooks = [];

    /**
     * custom post-update hooks.
     *
     * @var array
     */
    protected $mPostUpdateHooks = [];

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->mLog = new ModuleInstallLog();
    }

    /**
     * set force mode.
     *
     * @param bool $isForceMode
     */
    public function setForceMode($isForceMode)
    {
        $this->mForceMode = $isForceMode;
    }

    /**
     * set current xoops module.
     *
     * @param XoopsModule &$module
     */
    public function setCurrentXoopsModule(&$module)
    {
        $dirname = $module->get('dirname');
        $moduleHandler = &xoops_gethandler('module');
        $cloneModule = &$moduleHandler->create();
        $cloneModule->unsetNew();
        $cloneModule->set('mid', $module->get('mid'));
        $cloneModule->set('name', $module->get('name'));
        $cloneModule->set('version', $module->get('version'));
        $cloneModule->set('last_update', $module->get('last_update'));
        $cloneModule->set('weight', $module->get('weight'));
        $cloneModule->set('isactive', $module->get('isactive'));
        $cloneModule->set('dirname', $dirname);
        // $cloneModule->set('trust_dirname', $module->get('trust_dirname'));
        $cloneModule->set('hasmain', $module->get('hasmain'));
        $cloneModule->set('hasadmin', $module->get('hasadmin'));
        $cloneModule->set('hasconfig', $module->get('hasconfig'));
        $this->mCurrentXoopsModule = &$cloneModule;
        $this->mCurrentVersion = $cloneModule->get('version');
    }

    /**
     * set target xoops module.
     *
     * @param XoopsModule &$module
     */
    public function setTargetXoopsModule(&$module)
    {
        $this->mTargetXoopsModule = &$module;
        $this->mTargetVersion = $this->getTargetPhase();
    }

    /**
     * get current version.
     *
     * @return int
     */
    public function getCurrentVersion()
    {
        return intval($this->mCurrentVersion);
    }

    /**
     * get target phase.
     *
     * @return int
     */
    public function getTargetPhase()
    {
        ksort($this->mMilestone);
        foreach ($this->mMilestone as $tVer => $tMethod) {
            if ($tVer > $this->getCurrentVersion()) {
                return intval($tVer);
            }
        }

        return $this->mTargetXoopsModule->get('version');
    }

    /**
     * check whether updater has phase update method.
     *
     * @return bool
     */
    public function hasUpgradeMethod()
    {
        ksort($this->mMilestone);
        foreach ($this->mMilestone as $tVer => $tMethod) {
            if ($tVer > $this->getCurrentVersion() && is_callable([$this, $tMethod])) {
                return true;
            }
        }

        return false;
    }

    /**
     * check whether it is latest update now.
     *
     * @return bool
     */
    public function isLatestUpgrade()
    {
        return $this->mTargetXoopsModule->get('version') == $this->getTargetPhase();
    }

    /**
     * execute upgrade.
     *
     * @return bool
     */
    public function executeUpgrade()
    {
        $dirname = $this->mCurrentXoopsModule->get('dirname');
        $this->mLangMan = new LanguageManager($dirname, 'install');
        $this->mLangMan->load();
        if ($this->mTimeLimit >= 0) {
            set_time_limit($this->mTimeLimit);
        }

        return $this->hasUpgradeMethod() ? $this->_callUpgradeMethod() : $this->_executeAutomaticUpgrade();
    }

    /**
     * call upgrade method.
     *
     * @return bool
     */
    protected function _callUpgradeMethod()
    {
        ksort($this->mMilestone);
        foreach ($this->mMilestone as $tVer => $tMethod) {
            if ($tVer > $this->getCurrentVersion() && is_callable([$this, $tMethod])) {
                return $this->$tMethod();
            }
        }

        return false;
    }

    /**
     * execute automatic upgrade.
     *
     * @return bool
     */
    protected function _executeAutomaticUpgrade()
    {
        $this->mLog->addReport($this->mLangMan->get('INSTALL_MSG_UPDATE_STARTED'));
        $this->_executePreUpdateHooks();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_updateModuleTemplates();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_updateBlocks();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_updatePreferences();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_saveXoopsModule($this->mTargetXoopsModule);
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_executePostUpdateHooks();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_processReport();

        return true;
    }

    /**
     * execute pre-update hooks.
     */
    protected function _executePreUpdateHooks()
    {
        if (!empty($this->mPreUpdateHooks)) {
            $currentVersion = $this->getCurrentVersion();
            ksort($this->mPreUpdateHooks);
            foreach ($this->mPreUpdateHooks as $version => $func) {
                if ($version > $currentVersion) {
                    if (is_callable([$this, $func])) {
                        $this->$func();
                        if (!$this->mForceMode && $this->mLog->hasError()) {
                            break;
                        }
                        $currentVersion = $version;
                    } else {
                        $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_EXECUTE_CALLBACK'), get_class($this).'::'.$func));
                        if (!$this->mForceMode) {
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * execute post-update hooks.
     */
    protected function _executePostUpdateHooks()
    {
        if (!empty($this->mPostUpdateHooks)) {
            $currentVersion = $this->getCurrentVersion();
            ksort($this->mPostUpdateHooks);
            foreach ($this->mPostUpdateHooks as $version => $func) {
                if ($version > $currentVersion) {
                    if (is_callable([$this, $func])) {
                        $this->$func();
                        if (!$this->mForceMode && $this->mLog->hasError()) {
                            break;
                        }
                        $currentVersion = $version;
                    } else {
                        $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_EXECUTE_CALLBACK'), get_class($this).'::'.$func));
                        if (!$this->mForceMode) {
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * update module templates.
     */
    protected function _updateModuleTemplates()
    {
        ModuleInstallUtils::uninstallAllOfModuleTemplates($this->mTargetXoopsModule, $this->mLog);
        ModuleInstallUtils::installAllOfModuleTemplates($this->mTargetXoopsModule, $this->mLog);
    }

    /**
     * update blocks.
     */
    protected function _updateBlocks()
    {
        ModuleInstallUtils::smartUpdateAllOfBlocks($this->mTargetXoopsModule, $this->mLog);
    }

    /**
     * update preferences.
     */
    protected function _updatePreferences()
    {
        ModuleInstallUtils::smartUpdateAllOfConfigs($this->mTargetXoopsModule, $this->mLog);
    }

    /**
     * save xoops module.
     *
     * @param XoopsModule &$module
     */
    protected function _saveXoopsModule(&$module)
    {
        $moduleHandler = &xoops_gethandler('module');
        if ($moduleHandler->insert($module)) {
            $this->mLog->addReport($this->mLangMan->get('INSTALL_MSG_UPDATE_FINISHED'));
        } else {
            $this->mLog->addError($this->mLangMan->get('INSTALL_ERROR_UPDATE_FINISHED'));
        }
    }

    /**
     * process report.
     */
    protected function _processReport()
    {
        if (!$this->mLog->hasError()) {
            $this->mLog->addReport(XCubeUtils::formatString($this->mLangMan->get('INSTALL_MSG_MODULE_UPDATED'), $this->mCurrentXoopsModule->get('name')));
        } else {
            $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_MODULE_UPDATED'), $this->mCurrentXoopsModule->get('name')));
        }
    }
}
