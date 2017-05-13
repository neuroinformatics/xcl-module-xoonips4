<?php

use Xoonips\Core\Functions;
use Xoonips\Core\SqlUtils;
use Xoonips\Core\XCubeUtils;
use Xoonips\Installer\ModuleUpdater;

/**
 * updater class.
 */
class Xoonips_Updater extends ModuleUpdater
{
    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mTimeLimit = 240;
        $this->mPostUpdateHooks[410] = 'onUpdateToVersion410';
        $this->mPostUpdateHooks[420] = 'onUpdateToVersion420';
    }

    /**
     * update version to 4.10.
     *
     * @return bool
     */
    public function onUpdateToVersion410()
    {
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        $this->mLog->addReport('Start to apply changes since verion 4.10.');
        $dirname = $this->mCurrentXoopsModule->get('dirname');
        // update creative commons version 3.0 to 4.0
        //  - From 3.0: /^\d\d\d.+,.*/ - {USE_CC}{COMMERCIAL_USE}{MODIFICATION}{REGION},{TEXT}
        //  - To   4.0: /^\d\d\d,.*/   - {USE_CC}{COMMERCIAL_USE}{MODIFICATION},{TEXT}
        $ifdHandler = &Functions::getXoonipsHandler('ItemFieldDetailObject', $dirname);
        $ifdObj = $ifdHandler->getByXml('rights');
        if (is_object($ifdObj)) {
            $table = $dirname.'_'.$ifdObj->get('table_name');
            if (SqlUtils::tableExists($table)) {
                $tableName = $db->prefix($table);
                $sql = sprintf('SELECT * FROM `%s`', $tableName);
                if (($res = $db->query($sql)) === false) {
                    $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_SQL_FAILURE'), 'on '.__CLASS__.'::'.__METHOD__.' at line '.__LINE__));

                    return;
                } else {
                    while ($row = $db->fetchArray($res)) {
                        $item_id = $row['item_id'];
                        $group_id = $row['group_id'];
                        $value = $row['value'];
                        $occurrence_number = $row['occurrence_number'];
                        if (!preg_match('/^\d\d\d,/', $value)) {
                            $value = preg_replace('/(\d\d\d).+,(.*)$/', '$1,$2', $value);
                            $sql = sprintf('UPDATE `%s` SET `value`=%s WHERE `item_id`=%u AND `group_id`=%u AND `occurrence_number`=%u', $tableName, $db->quoteString($value), $item_id, $group_id, $occurrence_number);
                            if (!$db->query($sql)) {
                                $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_SQL_FAILURE'), 'on '.__CLASS__.'::'.__METHOD__.' at line '.__LINE__));

                                return;
                            }
                        }
                    }
                    $db->freeRecordSet($res);
                }
            }
        }
    }

    /**
     * update version to 4.20.
     *
     * @return bool
     */
    public function onUpdateToVersion420()
    {
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        $this->mLog->addReport('Start to apply changes since verion 4.20.');
        $dirname = $this->mCurrentXoopsModule->get('dirname');
        $ifdHandler = &Functions::getXoonipsHandler('ItemFieldDetailObject', $dirname);
        // item_extend : dataType varchar(255) => text
        $xmls = array('kana', 'romaji', 'sub_title_title', 'sub_title_kana', 'sub_title_romaji', 'name', 'jalc_doi', 'naid', 'ichushi', 'grant_id', 'date_of_granted', 'degree_name', 'grantor', 'type_of_resource', 'textversion');
        $xmlsSql = array_map(array($db, 'quoteString'), $xmls);
        $criteria = new Criteria('xml', $xmlsSql, 'IN');
        $ifdObjs = $ifdHandler->getObjects($criteria);
        foreach ($ifdObjs as $ifdObj) {
            $table = $ifdObj->get('table_name');
            if (SqlUtils::tableExists($table)) {
                $cinfo = SqlUtils::getColumnInfo($table, 'value');
                if ($cinfo !== false && $cinfo['Type'] == 'varchar(255)') {
                    $sqls = <<<'SQL'
ALTER TABLE `{prefix}_{table_name}` DROP INDEX `value`;
ALTER TABLE `{prefix}_{table_name}` MODIFY `value` TEXT;
ALTER TABLE `{prefix}_{table_name}` ADD INDEX `value`(`value`(255));
SQL;
                    $sqls = str_replace('{table_name}', $table, $sqls);
                    if (SqlUtils::execute($sqls)) {
                        $this->mLog->addReport(XCubeUtils::formatString($this->mLangMan->get('INSTALL_MSG_TABLE_ALTERED'), $table));
                    } else {
                        $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_TABLE_ALTERED'), $table));

                        return;
                    }
                    // NOTE: require to update data_type_id in item_field_detail
                }
            }
        }
        // item_extend : dataType varchar(255) => varchar(1000)
        $xmls = array('physical_description', 'uri');
        $xmlsSql = array_map(array($db, 'quoteString'), $xmls);
        $criteria = new Criteria('xml', $xmlsSql, 'IN');
        $ifdObjs = $ifdHandler->getObjects($criteria);
        foreach ($ifdObjs as $ifdObj) {
            $table = $ifdObj->get('table_name');
            if (SqlUtils::tableExists($table)) {
                $cinfo = SqlUtils::getColumnInfo($table, 'value');
                if ($cinfo !== false && $cinfo['Type'] == 'varchar(255)') {
                    $sqls = <<<'SQL'
ALTER TABLE `{prefix}_{table_name}` DROP INDEX `value`;
ALTER TABLE `{prefix}_{table_name}` MODIFY `value` VARCHAR(1000);
ALTER TABLE `{prefix}_{table_name}` ADD INDEX `value`(`value`(255));
SQL;
                    $sqls = str_replace('{table_name}', $table, $sqls);
                    if (SqlUtils::execute($sqls)) {
                        $this->mLog->addReport(XCubeUtils::formatString($this->mLangMan->get('INSTALL_MSG_TABLE_ALTERED'), $table));
                    } else {
                        $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_TABLE_ALTERED'), $table));

                        return;
                    }
                }
            }
        }
        // index : add index detail information
        $table = $dirname.'_index';
        if (!SqlUtils::columnExists($table, 'detailed_title')) {
            $sqls = <<<'SQL'
ALTER TABLE `{prefix}_{table_name}`
  ADD `detailed_title` VARCHAR(255) DEFAULT NULL AFTER `description`, 
  ADD `icon` VARCHAR(255) DEFAULT NULL AFTER `detailed_title`, 
  ADD `mime_type` VARCHAR(255) DEFAULT NULL AFTER `icon`, 
  ADD `detailed_description` TEXT DEFAULT NULL AFTER `mime_type`;
UPDATE `{prefix}_{table_name}` SET `detailed_description`=`description`;
ALTER TABLE `{prefix}_{table_name}` DROP COLUMN `description`;
SQL;
            $sqls = str_replace('{table_name}', $table, $sqls);
            if (SqlUtils::execute($sqls)) {
                $this->mLog->addReport(XCubeUtils::formatString($this->mLangMan->get('INSTALL_MSG_TABLE_ALTERED'), $table));
            } else {
                $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_TABLE_ALTERED'), $table));

                return;
            }
        }
        // config : add 'index_upload_dir' entry
        $table = $dirname.'_config';
        $cHandler = &Functions::getXoonipsHandler('ConfigObject', $dirname);
        $configArr = array(
            array('name' => 'index_upload_dir', 'value' => XOOPS_ROOT_PATH.'/uploads'),
        );
        foreach ($configArr as $config) {
            $value = $cHandler->getConfig($config['name']);
            if ($value === null) {
                $cObj = $cHandler->create();
                $cObj->set('name', $config['name']);
                $cObj->set('value', $config['value']);
                if (!$cHandler->insert($cObj)) {
                    $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_DATA_INSERTED'), $table));

                    return;
                }
            }
        }
        $this->mLog->addReport(XCubeUtils::formatString($this->mLangMan->get('INSTALL_MSG_DATA_INSERTED'), $table));
        // item_import_log : expand `log` field size
        $table = $dirname.'_item_import_log';
        $cinfo = SqlUtils::getColumnInfo($table, 'log');
        if ($cinfo['Type'] == 'text') {
            $sql = sprintf('ALTER TABLE `{prefix}_%s` MODIFY `log` LONGTEXT', $table);
            if (SqlUtils::execute($sql)) {
                $this->mLog->addReport(XCubeUtils::formatString($this->mLangMan->get('INSTALL_MSG_TABLE_ALTERED'), $table));
            } else {
                $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_TABLE_ALTERED'), $table));

                return;
            }
        }
    }
}
