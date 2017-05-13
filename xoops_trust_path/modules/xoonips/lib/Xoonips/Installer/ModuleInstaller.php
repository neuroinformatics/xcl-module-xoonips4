<?php

namespace Xoonips\Installer;

use Xoonips\Core\LanguageManager;
use Xoonips\Core\XCubeUtils;

/**
 * generic module installer class.
 */
class ModuleInstaller
{
    /**
     * module log.
     *
     * @var Installer\ModuleInstallLog
     */
    public $mLog = null;

    /**
     * flag for force mode.
     *
     * @var bool
     */
    protected $mForceMode = false;

    /**
     * xoops module.
     *
     * @var \XoopsModule
     */
    protected $mXoopsModule = null;

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
     * custom pre-install hooks.
     *
     * @var array
     */
    protected $mPreInstallHooks = array();

    /**
     * custom post-install hooks.
     *
     * @var array
     */
    protected $mPostInstallHooks = array();

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->mLog = new ModuleInstallLog();
    }

    /**
     * set current xoops module.
     *
     * @param \XoopsModule &$xoopsModule
     */
    public function setCurrentXoopsModule(&$xoopsModule)
    {
        $this->mXoopsModule = &$xoopsModule;
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
     * execute install.
     *
     * @return bool
     */
    public function executeInstall()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $this->mLangMan = new LanguageManager($dirname, 'install');
        $this->mLangMan->load();
        if ($this->mTimeLimit >= 0) {
            set_time_limit($this->mTimeLimit);
        }
        $this->_executePreInstallHooks();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_installTables();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_installModule();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_installTemplates();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_installBlocks();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_installPreferences();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_executePostInstallHooks();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_processReport();

        return true;
    }

    /**
     * install tables information.
     */
    protected function _installTables()
    {
        ModuleInstallUtils::installSQLAutomatically($this->mXoopsModule, $this->mLog);
    }

    /**
     * install module information.
     */
    protected function _installModule()
    {
        $moduleHandler = &xoops_gethandler('module');
        if (!$moduleHandler->insert($this->mXoopsModule)) {
            $this->mLog->addError($this->mLangMan->get('INSTALL_ERROR_MODULE_INFORMATION_INSTALLED'));

            return;
        }
        $gpermHandler = &xoops_gethandler('groupperm');
        if ($this->mXoopsModule->getInfo('hasAdmin')) {
            // grant administrator privilages to XOOPS_GROUP_ADMIN group.
            $adminPerm = $this->_createPermission(XOOPS_GROUP_ADMIN);
            $adminPerm->set('gperm_name', 'module_admin');
            if (!$gpermHandler->insert($adminPerm)) {
                $this->mLog->addError($this->mLangMan->get('INSTALL_ERROR_PERM_ADMIN_SET'));
            }
        }
        if ($this->mXoopsModule->getInfo('hasMain')) {
            if ($this->mXoopsModule->getInfo('read_any')) {
                // grant module read privilages to all groups.
                $memberHandler = &xoops_gethandler('member');
                $groupObjects = $memberHandler->getGroups();
                foreach ($groupObjects as $group) {
                    $readPerm = $this->_createPermission($group->get('groupid'));
                    $readPerm->set('gperm_name', 'module_read');
                    if (!$gpermHandler->insert($readPerm)) {
                        $this->mLog->addError($this->mLangMan->get('INSTALL_ERROR_PERM_READ_SET'));
                    }
                }
            } else {
                // grant module read privilages to XOOPS_GROUP_ADMIN and XOOPS_GROUP_USERS groups.
                foreach (array(XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS) as $group) {
                    $readPerm = $this->_createPermission($group);
                    $readPerm->set('gperm_name', 'module_read');
                    if (!$gpermHandler->insert($readPerm)) {
                        $this->mLog->addError($this->mLangMan->get('INSTALL_ERROR_PERM_READ_SET'));
                    }
                }
            }
        }
        $this->mLog->addReport($this->mLangMan->get('INSTALL_MSG_MODULE_INFORMATION_INSTALLED'));
    }

    /**
     * create permission.
     *
     * @param int $gid
     *
     * @return \XoopsGroupPerm&
     */
    protected function _createPermission($gid)
    {
        $gpermHandler = &xoops_gethandler('groupperm');
        $perm = $gpermHandler->create();
        $perm->set('gperm_groupid', $gid);
        $perm->set('gperm_itemid', $this->mXoopsModule->get('mid'));
        $perm->set('gperm_modid', 1);

        return $perm;
    }

    /**
     * install templates.
     */
    protected function _installTemplates()
    {
        ModuleInstallUtils::installAllOfModuleTemplates($this->mXoopsModule, $this->mLog);
    }

    /**
     * install blocks.
     */
    protected function _installBlocks()
    {
        ModuleInstallUtils::installAllOfBlocks($this->mXoopsModule, $this->mLog);
    }

    /**
     * install preferences.
     */
    protected function _installPreferences()
    {
        ModuleInstallUtils::installAllOfConfigs($this->mXoopsModule, $this->mLog);
    }

    /**
     * execute pre-install hooks.
     */
    protected function _executePreInstallHooks()
    {
        foreach ($this->mPreInstallHooks as $func) {
            if (is_callable(array($this, $func))) {
                $this->$func();
                if (!$this->mForceMode && $this->mLog->hasError()) {
                    break;
                }
            } else {
                $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_EXECUTE_CALLBACK'), get_class($this).'::'.$func));
                if (!$this->mForceMode) {
                    break;
                }
            }
        }
    }

    /**
     * execute post-install hooks.
     */
    protected function _executePostInstallHooks()
    {
        foreach ($this->mPostInstallHooks as $func) {
            if (is_callable(array($this, $func))) {
                $this->$func();
                if (!$this->mForceMode && $this->mLog->hasError()) {
                    break;
                }
            } else {
                $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_EXECUTE_CALLBACK'), get_class($this).'::'.$func));
                if (!$this->mForceMode) {
                    break;
                }
            }
        }
    }

    /**
     * process report.
     */
    protected function _processReport()
    {
        if (!$this->mLog->hasError()) {
            $this->mLog->addReport(XCubeUtils::formatString($this->mLangMan->get('INSTALL_MSG_MODULE_INSTALLED'), $this->mXoopsModule->getInfo('name')));
        } elseif (is_object($this->mXoopsModule)) {
            $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_MODULE_INSTALLED'), $this->mXoopsModule->getInfo('name')));
        } else {
            $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_MODULE_INSTALLED'), '(unknown)'));
        }
    }
}
