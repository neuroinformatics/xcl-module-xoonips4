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
     * @var Installer\InstallLog
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
     * custom hooks.
     *
     * @var array
     */
    protected $mHooks = array();

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->mLog = new InstallLog();
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
        $this->_executeHooks();
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
        }
        $this->_processReport();

        return true;
    }

    /**
     * execute hooks.
     */
    protected function _executeHooks()
    {
        foreach ($this->mHooks as $func) {
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
        InstallUtils::uninstallAllOfModuleTemplates($this->mXoopsModule, $this->mLog, false);
    }

    /**
     * uninstall blocks.
     */
    protected function _uninstallBlocks()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        InstallUtils::uninstallAllOfBlocks($this->mXoopsModule, $this->mLog);
        $tplHandler = &xoops_gethandler('tplfile');
        $cri = new \Criteria('tpl_module', $dirname);
        if (!$tplHandler->deleteAll($cri)) {
            $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_BLOCK_TPL_DELETED'), $tplHandler->db->error()));
        }
    }

    /**
     * uninstall preferences.
     */
    protected function _uninstallPreferences()
    {
        InstallUtils::uninstallAllOfConfigs($this->mXoopsModule, $this->mLog);
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
