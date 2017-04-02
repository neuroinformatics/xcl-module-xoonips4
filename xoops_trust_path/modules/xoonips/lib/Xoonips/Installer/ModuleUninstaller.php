<?php

namespace Xoonips\Installer;

use Xoonips\Core\LanguageManager;
use Xoonips\Core\XCubeUtils;

/**
 * generic module uninstaller class.
 */
class ModuleUninstaller
{
    /**
     * module install log.
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
     * custom pre-uninstall hooks.
     *
     * @var array
     */
    protected $mPreUninstallHooks = array();

    /**
     * custom post-uninstall hooks.
     *
     * @var array
     */
    protected $mPostUninstallHooks = array();

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
     * execute uninstall.
     *
     * @return bool
     */
    public function executeUninstall()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $this->mLangMan = new LanguageManager($dirname, 'install');
        $this->mLangMan->load();
        $this->_executePreUninstallHooks();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_uninstallTables();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        if ($this->mXoopsModule->get('mid') != null) {
            $this->_uninstallModule();
            if (!$this->mForceMode && $this->mLog->hasError()) {
                $this->_processReport();

                return false;
            }
            $this->_uninstallTemplates();
            if (!$this->mForceMode && $this->mLog->hasError()) {
                $this->_processReport();

                return false;
            }
            $this->_uninstallBlocks();
            if (!$this->mForceMode && $this->mLog->hasError()) {
                $this->_processReport();

                return false;
            }
            $this->_uninstallPreferences();
            if (!$this->mForceMode && $this->mLog->hasError()) {
                $this->_processReport();

                return false;
            }
            $this->_processScript();
            if (!$this->mForceMode && $this->mLog->hasError()) {
                $this->_processReport();

                return false;
            }
        }
        $this->_executePostUninstallHooks();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_processReport();

        return true;
    }

    /**
     * execute pre-uninstall hooks.
     */
    protected function _executePreUninstallHooks()
    {
        foreach ($this->mPreUninstallHooks as $func) {
            if (is_callable(array($this, $func))) {
                $this->$func();
                if (!$this->mForceMode && $this->mLog->hasError()) {
                    break;
                }
            }
        }
    }

    /**
     * execute post-uninstall hooks.
     */
    protected function _executePostUninstallHooks()
    {
        foreach ($this->mPostUninstallHooks as $func) {
            if (is_callable(array($this, $func))) {
                $this->$func();
                if (!$this->mForceMode && $this->mLog->hasError()) {
                    break;
                }
            }
        }
    }

    /**
     * uninstall tables.
     */
    protected function _uninstallTables()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        $tables = &$this->mXoopsModule->getInfo('tables');
        if (is_array($tables)) {
            foreach ($tables as $table) {
                $tableName = str_replace(array('{prefix}', '{dirname}'), array(XOOPS_DB_PREFIX, $dirname), $table);
                $sql = sprintf('DROP TABLE `%s`;', $tableName);
                if ($db->query($sql)) {
                    $this->mLog->addReport(XCubeUtils::formatString($this->mLangMan->get('INSTALL_MSG_TABLE_DOROPPED'), $tableName));
                } else {
                    $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_TABLE_DOROPPED'), $tableName));
                }
            }
        }
    }

    /**
     * uninstall module.
     */
    protected function _uninstallModule()
    {
        $moduleHandler = &xoops_gethandler('module');
        if ($moduleHandler->delete($this->mXoopsModule)) {
            $this->mLog->addReport($this->mLangMan->get('INSTALL_MSG_MODULE_INFORMATION_DELETED'));
        } else {
            $this->mLog->addError($this->mLangMan->get('INSTALL_ERROR_MODULE_INFORMATION_DELETED'));
        }
    }

    /**
     * uninstall templates.
     */
    protected function _uninstallTemplates()
    {
        ModuleInstallUtils::uninstallAllOfModuleTemplates($this->mXoopsModule, $this->mLog, false);
    }

    /**
     * uninstall blocks.
     */
    protected function _uninstallBlocks()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        ModuleInstallUtils::uninstallAllOfBlocks($this->mXoopsModule, $this->mLog);
        $tplHandler = &xoops_gethandler('tplfile');
        $cri = new \Criteria('tpl_module', $dirname);
        if (!$tplHandler->deleteAll($cri)) {
            $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_BLOCK_TPL_UNINSTALLED'), $tplHandler->db->error()));
        }
    }

    /**
     * uninstall preferences.
     */
    protected function _uninstallPreferences()
    {
        ModuleInstallUtils::uninstallAllOfConfigs($this->mXoopsModule, $this->mLog);
        ModuleInstallUtils::deleteAllOfNotifications($this->mXoopsModule, $this->mLog);
        ModuleInstallUtils::deleteAllOfComments($this->mXoopsModule, $this->mLog);
    }

    /**
     * process script.
     */
    protected function _processScript()
    {
        $installScript = trim($this->mXoopsModule->getInfo('onUninstall'));
        if ($installScript != false) {
            require_once XOOPS_MODULE_PATH.'/'.$this->mXoopsModule->get('dirname').'/'.$installScript;
            $funcName = 'xoops_module_uninstall_'.$this->mXoopsModule->get('dirname');
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $funcName)) {
                $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_FAILED_TO_EXECUTE_CALLBACK'), $funcName));

                return;
            }
            if (function_exists($funcName)) {
                $result = $funcName($this->mXoopsModule, new \XCube_Ref($this->mLog));
                if (!$result) {
                    $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_FAILED_TO_EXECUTE_CALLBACK'), $funcName));
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
            $this->mLog->addReport(XCubeUtils::formatString($this->mLangMan->get('INSTALL_MSG_MODULE_UNINSTALLED'), $this->mXoopsModule->get('name')));
        } elseif (is_object($this->mXoopsModule)) {
            $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_MODULE_UNINSTALLED'), $this->mXoopsModule->get('name')));
        } else {
            $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_MODULE_UNINSTALLED'), 'something'));
        }
    }
}
