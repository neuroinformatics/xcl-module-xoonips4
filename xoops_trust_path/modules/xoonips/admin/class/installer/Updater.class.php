<?php

require_once dirname(__FILE__) . '/InstallUtils.class.php';

require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/BeanFactory.class.php';

/**
 * updater class
 */
class Xoonips_Updater
{

    /**
     * module install log
     * @var Legacy_ModuleInstallLog
     */
    public $mLog = null;

    /**
     * milestone
     * @var string[]
     */
    private $_mMileStone = array();

    /**
     * current xoops module
     * @var XoopsModule
     */
    private $_mCurrentXoopsModule = null;

    /**
     * target xoops module
     * @var XoopsModule
     */
    private $_mTargetXoopsModule = null;

    /**
     * current module version
     * @var int
     */
    private $_mCurrentVersion = 0;

    /**
     * target module version
     * @var int
     */
    private $_mTargetVersion = 0;

    /**
     * flag for force mode
     * @var bool
     */
    private $_mForceMode = false;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->mLog = new Legacy_ModuleInstallLog();
    }

    /**
     * set force mode
     *
     * @param bool $isForceMode
     */
    public function setForceMode($isForceMode)
    {
        $this->_mForceMode = $isForceMode;
    }

    /**
     * set current xoops module
     *
     * @param XoopsModule &$module
     */
    public function setCurrentXoopsModule(&$module)
    {
        $moduleHandler =& Xoonips_Utils::getXoopsHandler('module');
        $cloneModule =& $moduleHandler->create();
        $cloneModule->unsetNew();
        $cloneModule->set('mid', $module->get('mid'));
        $cloneModule->set('name', $module->get('name'));
        $cloneModule->set('version', $module->get('version'));
        $cloneModule->set('last_update', $module->get('last_update'));
        $cloneModule->set('weight', $module->get('weight'));
        $cloneModule->set('isactive', $module->get('isactive'));
        $cloneModule->set('dirname', $module->get('dirname'));
        // $cloneModule->set('trust_dirname', $module->get('trust_dirname'));
        $cloneModule->set('hasmain', $module->get('hasmain'));
        $cloneModule->set('hasadmin', $module->get('hasadmin'));
        $cloneModule->set('hasconfig', $module->get('hasconfig'));
        $this->_mCurrentXoopsModule =& $cloneModule;
        $this->_mCurrentVersion = $cloneModule->get('version');
    }

    /**
     * set target xoops module
     *
     * @param XoopsModule &$module
     */
    public function setTargetXoopsModule(&$module)
    {
        $this->_mTargetXoopsModule =& $module;
        $this->_mTargetVersion = $this->getTargetPhase();
    }

    /**
     * get current version
     *
     * @return int
     */
    public function getCurrentVersion()
    {
        return intval($this->_mCurrentVersion);
    }

    /**
     * get target phase
     *
     * @return int
     */
    public function getTargetPhase()
    {
        ksort($this->_mMileStone);
        foreach ($this->_mMileStone as $tVer => $tMethod) {
            if ($tVer >= $this->getCurrentVersion())
                return intval($tVer);
        }
        return $this->_mTargetXoopsModule->get('version');
    }

    /**
     * check whether updater has phase update method
     *
     * @return bool
     */
    public function hasUpgradeMethod()
    {
        ksort($this->_mMileStone);
        foreach ($this->_mMileStone as $tVer => $tMethod) {
            if ($tVer >= $this->getCurrentVersion() && is_callable(array($this, $tMethod)))
                return true;
        }
        return false;
    }

    /**
     * check whether it is latest update now
     *
     * @return bool
     */
    public function isLatestUpgrade()
    {
        return ($this->_mTargetXoopsModule->get('version') == $this->getTargetPhase());
    }

    /**
     * update module templates
     */
    private function _updateModuleTemplates()
    {
        Xoonips_InstallUtils::uninstallAllOfModuleTemplates($this->_mTargetXoopsModule, $this->mLog);
        Xoonips_InstallUtils::installAllOfModuleTemplates($this->_mTargetXoopsModule, $this->mLog);
    }

    /**
     * update blocks
     */
    private function _updateBlocks()
    {
        Xoonips_InstallUtils::smartUpdateAllOfBlocks($this->_mTargetXoopsModule, $this->mLog);
    }

    /**
     * update preferences
     */
    private function _updatePreferences()
    {
        Xoonips_InstallUtils::smartUpdateAllOfConfigs($this->_mTargetXoopsModule, $this->mLog);
    }

    /**
     * execute upgrade
     *
     * @return bool
     */
    public function executeUpgrade()
    {
        set_time_limit(240);
        return ($this->hasUpgradeMethod() ? $this->_callUpgradeMethod() : $this->executeAutomaticUpgrade());
    }

    /**
     * call upgrade method
     *
     * @return bool
     */
    private function _callUpgradeMethod()
    {
        ksort($this->_mMileStone);
        foreach ($this->_mMileStone as $tVer => $tMethod) {
            if ($tVer >= $this->getCurrentVersion() && is_callable(array($this, $tMethod)))
                return $this->$tMethod();
        }
        return false;
    }

    /**
     * execute automatic upgrade
     *
     * @return bool
     */
    public function executeAutomaticUpgrade()
    {
        $dirname = $this->_mCurrentXoopsModule->get('dirname');
        $constpref = '_MI_' . strtoupper($dirname);
        $this->mLog->addReport(constant($constpref . '_INSTALL_MSG_UPDATE_STARTED'));
        $this->_updateModuleTemplates();
        if (!$this->_mForceMode && $this->mLog->hasError()) {
            $this->_processReport();
            return false;
        }
        $this->_updateBlocks();
        if (!$this->_mForceMode && $this->mLog->hasError()) {
            $this->_processReport();
            return false;
        }
        $this->_updatePreferences();
        if (!$this->_mForceMode && $this->mLog->hasError()) {
            $this->_processReport();
            return false;
        }
        $this->saveXoopsModule($this->_mTargetXoopsModule);
        if (!$this->_mForceMode && $this->mLog->hasError()) {
            $this->_processReport();
            return false;
        }
        $this->_updateScript($this->_mTargetXoopsModule, $this->_mCurrentXoopsModule->get('version'));
        if (!$this->_mForceMode && $this->mLog->hasError()) {
            $this->_processReport();
            return false;
        }
        $this->_processReport();
        return true;
    }

    /**
     * save xoops module
     *
     * @param XoopsModule &$module
     */
    public function saveXoopsModule(&$module)
    {
        $dirname = $module->get('dirname');
        $constpref = '_MI_' . strtoupper($dirname);
        $moduleHandler =& Xoonips_Utils::getXoopsHandler('module');
        if ($moduleHandler->insert($module)) {
            $this->mLog->addReport(constant($constpref . '_INSTALL_MSG_UPDATE_FINISHED'));
        } else {
            $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
        }
    }

    /**
     * process report
     */
    private function _processReport()
    {
        $dirname = $this->_mCurrentXoopsModule->get('dirname');
        $constpref = '_MI_' . strtoupper($dirname);
        if (!$this->mLog->hasError()) {
            $this->mLog->add(XCube_Utils::formatString(
                constant($constpref . '_INSTALL_MSG_MODULE_UPDATED'), $this->_mCurrentXoopsModule->get('name'))
            );
        } else {
            $this->mLog->add(XCube_Utils::formatString(
                constant($constpref . '_INSTALL_ERROR_MODULE_UPDATED'), $this->_mCurrentXoopsModule->get('name'))
            );
        }
    }

    /**
     * update script
    */
    private function _updateScript(&$module, $currentVersion)
    {
        $dirname = $module->get('dirname');
        $trust_dirname = $module->get('trust_dirname');
        $version = $module->get('version');
        $constpref = '_MI_' . strtoupper($dirname);

        $root =& XCube_Root::getSingleton();
        $db =& $root->mController->getDB();
        if ($currentVersion < 410 && $version >= 410) {
            // update Creative Commons 4.0
            $itemFieldDetailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $dirname, $trust_dirname);
            $rightsInfo = $itemFieldDetailBean->getDetailByXml('rights');
            $tableName = XOOPS_DB_PREFIX.'_'.$rightsInfo['table_name'];
            $sql = sprintf("SELECT * FROM `%s`", $tableName);
            $result = $db->query($sql);
            if (!$result) {
                $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
            }
            while ($row = $db->fetchArray($result)) {
                $item_id = $row['item_id'];
                $group_id = $row['group_id'];
                $value = $row['value'];
                $occurrence_number = $row['occurrence_number'];
                if (!preg_match("/^\d\d\d,/", $value)) {
                    $pattern = '/(\d\d\d).+,(.*)$/';
                    $replacement = '$1,$2';
                    $updateValue = preg_replace($pattern, $replacement, $value);
                    $sql = sprintf("UPDATE `%s` SET `value` = \"%s\" WHERE 
                    `item_id` = %d AND `group_id` = %d AND `occurrence_number` = %d", 
                    $tableName, $updateValue, $item_id, $group_id, $occurrence_number);
                    $result = $db->queryF($sql);
                    if (!$result)
                        $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
                }
            }
            $db->freeRecordSet($result);
        }
        if ($currentVersion < 420 && $version >= 420) {
            $detailTable = XOOPS_DB_PREFIX . "_" . $dirname . "_item_field_detail";
            // varchar(255) => text
            $xmls = array(
                          'kana',
                          'romaji',
                          'sub_title_title',
                          'sub_title_kana',
                          'sub_title_romaji',
                          'name',
                          'jalc_doi',
                          'naid',
                          'ichushi',
                          'grant_id',
                          'date_of_granted',
                          'degree_name',
                          'grantor',
                          'type_of_resource',
                          'textversion',
            );
            $tables = array();
            $sql = sprintf("SELECT * FROM `%s` WHERE", $detailTable);
            $first = true;
            foreach($xmls as $xml) {
                if ($first) {
                    $sql .= sprintf(" `xml` = \"%s\"", $xml);
                    $first = false;
                } else {
                    $sql .= sprintf(" OR `xml` = \"%s\"", $xml);
                }
            }
            $result = $db->query($sql);
            if (!$result) {
                $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
            }
            while ($table = $db->fetchArray($result)) {
                $tables[] = XOOPS_DB_PREFIX . "_" . $table['table_name'];
            }
            $db->freeRecordSet($result);
            foreach ($tables as $table) {
                // delete index
                $sql = sprintf("ALTER TABLE `%s` DROP INDEX `value`", $table);
                $execute = $db->queryF($sql);
                if (!$execute)
                    $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
                // modify value
                $sql = sprintf("ALTER TABLE `%s` MODIFY `value` TEXT", $table);
                $execute = $db->queryF($sql);
                if (!$execute)
                    $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
                // add index
                $sql = sprintf("ALTER TABLE `%s` ADD INDEX `value`(`value`(255))", $table);
                $execute = $db->queryF($sql);
                if (!$execute)
                    $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
            }

            //varchar(255) => varchar(1000)
            $xmls = array(
                          'physical_description',
                          'uri',
            );
            $tables = array();
            $sql = sprintf("SELECT * FROM `%s` WHERE", $detailTable);
            $first = true;
            foreach ($xmls as $xml) {
                if ($first) {
                    $sql .= sprintf(" `xml` = \"%s\"", $xml);
                    $first = false;
                } else {
                    $sql .= sprintf(" OR `xml` = \"%s\"", $xml);
                }
            }
            $result = $db->query($sql);
            if (!$result) {
                $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
            }
            while ($table = $db->fetchArray($result)) {
                $tables[] = XOOPS_DB_PREFIX . "_" . $table['table_name'];
            }
            $db->freeRecordSet($result);
            foreach($tables as $table) {
                // delete index
                $sql = sprintf("ALTER TABLE `%s` DROP INDEX `value`", $table);
                $execute = $db->queryF($sql);
                if (!$execute) {
                    $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
                }
                // modify value
                $sql = sprintf("ALTER TABLE `%s` MODIFY `value` VARCHAR(1000)", $table);
                $execute = $db->queryF($sql);
                if (!$execute) {
                    $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
                }
                // add index
                $sql = sprintf("ALTER TABLE `%s` ADD INDEX `value`(`value`(255))", $table);
                $execute = $db->queryF($sql);
                if (!$execute) {
                    $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
                }
            }

            // modify index table
            $indexTable = XOOPS_DB_PREFIX . "_" . $dirname . "_index";
            $sql = sprintf("ALTER TABLE `%s` ADD `detailed_title` varchar(255) default NULL AFTER `description`, 
            ADD `icon` varchar(255) default NULL AFTER `detailed_title`, 
            ADD `mime_type` varchar(255) default NULL AFTER `icon`, 
            ADD `detailed_description` text default NULL AFTER `mime_type`", $indexTable);
            $execute = $db->queryF($sql);
            if (!$execute) {
                $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
            }

            // migrate description
            $indexes = array();
            $sql = sprintf("SELECT * FROM `%s`", $indexTable);
            $result = $db->query($sql);
            if (!$result) {
                $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
            }
            while ($row = $db->fetchArray($result)) {
                $indexes[] = $row;
            }
            foreach ($indexes as $index) {
                $sql = sprintf("UPDATE `%s` SET `detailed_description` = %s WHERE `index_id` = %d", $indexTable, Xoonips_Utils::convertSQLStr($index['description']), $index['index_id']);
                $result = $db->queryF($sql);
                if (!$result) {
                    $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
                }
            }           

            // drop description column
            $sql = sprintf("ALTER TABLE `%s` DROP COLUMN `description`", $indexTable);
            $execute = $db->queryF($sql);
            if (!$execute) {
                $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
            }

            // modify item_import_log table
            $importLogTable = XOOPS_DB_PREFIX . "_" . $dirname . "_item_import_log";
            $sql = sprintf("ALTER TABLE `%s` MODIFY `log` LONGTEXT", $importLogTable);
            $execute = $db->queryF($sql);
            if (!$execute) {
                $this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
            }

            // add config index_upload_dir
            $configlist = array(
                 array('name' => 'index_upload_dir', 'value' => XOOPS_ROOT_PATH . '/uploads'),
            );
            $handler = Xoonips_Utils::getTrustModuleHandler('config', $dirname, $trust_dirname);
            return $handler->insertConfigs($configlist); 
        }
    }
}

