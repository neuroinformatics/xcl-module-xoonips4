<?php

use Xoonips\Core\FileUtils;
use Xoonips\Core\Functions;
use Xoonips\Core\SqlUtils;
use Xoonips\Core\XCubeUtils;
use Xoonips\Core\XoopsSystemUtils;
use Xoonips\Installer\ModuleInstaller;

require_once dirname(dirname(dirname(__DIR__))).'/class/core/ImportItemtype.class.php';

/**
 * installer class.
 */
class Xoonips_Installer extends ModuleInstaller
{
    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mTimeLimit = 240;
        $this->mPreInstallHooks[] = 'onInstallFixXoopsTable';
        $this->mPreInstallHooks[] = 'onInstallHideSystemBlocks';
        $this->mPostInstallHooks[] = 'onInstallAlterTables';
        $this->mPostInstallHooks[] = 'onInstallInsertDataConfig';
        $this->mPostInstallHooks[] = 'onInstallInsertDataDataType';
        $this->mPostInstallHooks[] = 'onInstallInsertDataViewType';
        $this->mPostInstallHooks[] = 'onInstallInsertDataViewDataRelation';
        $this->mPostInstallHooks[] = 'onInstallInsertDataComplement';
        $this->mPostInstallHooks[] = 'onInstallInsertDataComplementDetail';
        $this->mPostInstallHooks[] = 'onInstallInsertDataItemFieldValueSet';
        $this->mPostInstallHooks[] = 'onInstallInsertDataOaipmhSchema';
        $this->mPostInstallHooks[] = 'onInstallInsertDataOaipmhSchemaValueSet';
        $this->mPostInstallHooks[] = 'onInstallInsertDataOaipmhSchemaLink';
        $this->mPostInstallHooks[] = 'onInstallInsertDataItemTypeSort';
        $this->mPostInstallHooks[] = 'onInstallInsertDataItemTypes';
        $this->mPostInstallHooks[] = 'onInstallInsertDataItemTypeSearchCondition';
        $this->mPostInstallHooks[] = 'onInstallInsertDataIndex';
        $this->mPostInstallHooks[] = 'onInstallSetupUploadsDirectory';
        $this->mPostInstallHooks[] = 'onInstallSetupPermissions';
        $this->mPostInstallHooks[] = 'onInstallSetupUsers';
        $this->mPostInstallHooks[] = 'onInstallSetupNotifications';
    }

    /**
     * fix xoops table.
     */
    protected function onInstallFixXoopsTable()
    {
        $this->mLog->addReport('Fix Xoops Tables.');
        XoopsSystemUtils::fixGroupPermissions();
        XoopsSystemUtils::fixModuleConfigs();
    }

    /**
     * hide system blocks.
     */
    protected function onInstallHideSystemBlocks()
    {
        $this->mLog->addReport('Hide "login" and "usermenu" Xoops System Blocks.');
        // hide 'user' and 'login' blocks
        $blocks = [
            // for XOOPS 2.0
            ['system', 'b_system_user_show'],
            ['system', 'b_system_login_show'],
            // for XOOPS Cube Legacy 2.2
            ['legacy', 'b_legacy_usermenu_show'],
            ['user', 'b_user_login_show'],
            // for cubeUtils
            ['cubeUtils', 'b_cubeUtils_login_show'],
        ];
        foreach ($blocks as $block) {
            list($dirname, $show_func) = $block;
            $bid = XoopsSystemUtils::getBlockId($dirname, $show_func);
            if (null !== $bid) {
                XoopsSystemUtils::setBlockInfo($bid, XoopsSystemUtils::BLOCK_SIDE_HIDE, false, false);
            }
        }
    }

    /**
     * alter tables.
     */
    protected function onInstallAlterTables()
    {
        $this->mLog->addReport('Alter Existing Tables.');
        // add xoonips extended columns to `groups` table
        if (!SqlUtils::columnExists('groups', 'activate')) {
            $sql = <<<'SQL'
ALTER TABLE `{prefix}_groups`
    ADD `activate` tinyint(1) unsigned NOT NULL default '0' AFTER `groupid`,
    ADD `icon` varchar(255) default NULL AFTER `description`,
    ADD `mime_type` varchar(255) default NULL AFTER `icon`,
    ADD `is_public` tinyint(1) unsigned NOT NULL default '0' AFTER `mime_type`,
    ADD `can_join` tinyint(1) unsigned NOT NULL default '0' AFTER `is_public`,
    ADD `is_hidden` tinyint(1) unsigned NOT NULL default '0' AFTER `can_join`,
    ADD `member_accept` tinyint(1) unsigned NOT NULL default '0' AFTER `is_hidden`,
    ADD `item_accept` tinyint(1) unsigned NOT NULL default '0' AFTER `member_accept`,
    ADD `item_number_limit` int(10) unsigned default NULL AFTER `item_accept`,
    ADD `index_number_limit` int(10) unsigned default NULL AFTER `item_number_limit`,
    ADD `item_storage_limit` int(10) default NULL AFTER `index_number_limit`,
    ADD `index_id` int(10) unsigned NOT NULL default '0' AFTER `item_storage_limit`;
SQL;
            if (SqlUtils::execute($sql)) {
                $this->mLog->addReport(XCubeUtils::formatString($this->mLangMan->get('INSTALL_MSG_TABLE_ALTERED'), 'groups'));
            } else {
                $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_TABLE_ALTERED'), 'groups'));
            }
        }
        // add xoonips extended columns to `groups_users_link` table
        if (!SqlUtils::columnExists('groups_users_link', 'activate')) {
            $sql = <<<'SQL'
ALTER TABLE `{prefix}_groups_users_link`
    ADD `activate` tinyint(1) unsigned NOT NULL default '0' AFTER `linkid`,
    ADD `is_admin` tinyint(1) unsigned NOT NULL default '0';
SQL;
            if (SqlUtils::execute($sql)) {
                $this->mLog->addReport(XCubeUtils::formatString($this->mLangMan->get('INSTALL_MSG_TABLE_ALTERED'), 'groups_users_link'));
            } else {
                $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_TABLE_ALTERED'), 'groups_users_link'));
            }
        }
    }

    /**
     * insert data - config.
     */
    protected function onInstallInsertDataConfig()
    {
        $configArr = [
            ['name' => 'moderator_gid', 'value' => '1'],
            ['name' => 'upload_dir', 'value' => XOOPS_TRUST_PATH.'/uploads/xoonips'],
            ['name' => 'repository_name', 'value' => ''],
            ['name' => 'repository_nijc_code', 'value' => ''],
            ['name' => 'repository_deletion_track', 'value' => '30'],
            ['name' => 'proxy_host', 'value' => ''],
            ['name' => 'proxy_port', 'value' => '80'],
            ['name' => 'proxy_user', 'value' => ''],
            ['name' => 'proxy_pass', 'value' => ''],
            ['name' => 'certify_user', 'value' => 'on'],
            ['name' => 'user_certify_date', 'value' => '0'],
            ['name' => 'private_item_number_limit', 'value' => '0'],
            ['name' => 'private_index_number_limit', 'value' => '0'],
            ['name' => 'private_item_storage_limit', 'value' => '0'],
            ['name' => 'group_making', 'value' => 'off'],
            ['name' => 'group_making_certify', 'value' => 'on'],
            ['name' => 'group_publish_certify', 'value' => 'on'],
            ['name' => 'group_item_number_limit', 'value' => '0'],
            ['name' => 'group_index_number_limit', 'value' => '0'],
            ['name' => 'group_item_storage_limit', 'value' => '0'],
            ['name' => 'certify_item', 'value' => 'on'],
            ['name' => 'download_file_compression', 'value' => 'on'],
            ['name' => 'export_enabled', 'value' => 'off'],
            ['name' => 'export_attachment', 'value' => 'off'],
            ['name' => 'private_import_enabled', 'value' => 'off'],
            ['name' => 'message_sign', 'value' => '{X_SITENAME}({X_SITEURL})'.$this->mLangMan->get('DATA_CONFIG_MESSAGE_SIGN').':{X_ADMINMAIL}'],
            ['name' => 'access_key', 'value' => ''],
            ['name' => 'secret_access_key', 'value' => ''],
            ['name' => 'index_upload_dir', 'value' => XOOPS_ROOT_PATH.'/uploads'],
            ['name' => 'url_compatible', 'value' => 'off'],
            ['name' => 'ranking_last_update', 'value' => time()],
            ['name' => 'ranking_lock_timeout', 'value' => '0'],
        ];
        $this->_insertData('config', $configArr);
    }

    /**
     * insert data - data_type.
     */
    protected function onInstallInsertDataDataType()
    {
        $dataTypeArr = [
            ['name' => 'int', 'module' => 'DataTypeInt'],
            ['name' => 'float', 'module' => 'DataTypeFloat'],
            ['name' => 'double', 'module' => 'DataTypeDouble'],
            ['name' => 'char', 'module' => 'DataTypeChar'],
            ['name' => 'varchar', 'module' => 'DataTypeVarchar'],
            ['name' => 'text', 'module' => 'DataTypeText'],
            ['name' => 'date', 'module' => 'DataTypeDate'],
            ['name' => 'datetime', 'module' => 'DataTypeDatetime'],
            ['name' => 'blob', 'module' => 'DataTypeBlob'],
        ];
        $this->_insertData('data_type', $dataTypeArr);
    }

    /**
     * insert data - view_type.
     */
    protected function onInstallInsertDataViewType()
    {
        $viewTypeArr = [
            ['preselect' => 0, 'multi' => 1, 'name' => 'hidden', 'module' => 'ViewTypeHidden'],
            ['preselect' => 0, 'multi' => 1, 'name' => 'text', 'module' => 'ViewTypeText'],
            ['preselect' => 0, 'multi' => 1, 'name' => 'textarea', 'module' => 'ViewTypeTextArea'],
            ['preselect' => 0, 'multi' => 1, 'name' => 'radio', 'module' => 'ViewTypeRadioBox'],
            ['preselect' => 0, 'multi' => 1, 'name' => 'checkbox', 'module' => 'ViewTypeCheckBox'],
            ['preselect' => 0, 'multi' => 1, 'name' => 'select', 'module' => 'ViewTypeComboBox'],
            ['preselect' => 1, 'multi' => 0, 'name' => 'id', 'module' => 'ViewTypeId'],
            ['preselect' => 1, 'multi' => 1, 'name' => 'title', 'module' => 'ViewTypeTitle'],
            ['preselect' => 1, 'multi' => 1, 'name' => 'keyword', 'module' => 'ViewTypeKeyword'],
            ['preselect' => 1, 'multi' => 0, 'name' => 'last update', 'module' => 'ViewTypeLastUpdate'],
            ['preselect' => 1, 'multi' => 0, 'name' => 'create date', 'module' => 'ViewTypeCreateDate'],
            ['preselect' => 1, 'multi' => 0, 'name' => 'create user', 'module' => 'ViewTypeCreateUser'],
            ['preselect' => 1, 'multi' => 0, 'name' => 'change log', 'module' => 'ViewTypeChangeLog'],
            ['preselect' => 1, 'multi' => 0, 'name' => 'index', 'module' => 'ViewTypeIndex'],
            ['preselect' => 1, 'multi' => 0, 'name' => 'relation item', 'module' => 'ViewTypeRelatedTo'],
            ['preselect' => 0, 'multi' => 1, 'name' => 'date(yyyy mm dd)', 'module' => 'ViewTypeDate'],
            ['preselect' => 0, 'multi' => 0, 'name' => 'preview', 'module' => 'ViewTypePreview'],
            ['preselect' => 0, 'multi' => 1, 'name' => 'file upload', 'module' => 'ViewTypeFileUpload'],
            ['preselect' => 0, 'multi' => 1, 'name' => 'file type', 'module' => 'ViewTypeFileType'],
            ['preselect' => 0, 'multi' => 0, 'name' => 'download limit', 'module' => 'ViewTypeDownloadLimit'],
            ['preselect' => 0, 'multi' => 0, 'name' => 'download notify', 'module' => 'ViewTypeDownloadNotify'],
            ['preselect' => 0, 'multi' => 0, 'name' => 'readme', 'module' => 'ViewTypeReadme'],
            ['preselect' => 0, 'multi' => 0, 'name' => 'rights', 'module' => 'ViewTypeRights'],
            ['preselect' => 0, 'multi' => 1, 'name' => 'url', 'module' => 'ViewTypeUrl'],
            ['preselect' => 0, 'multi' => 1, 'name' => 'pubmed id', 'module' => 'ViewTypePubmedId'],
            ['preselect' => 0, 'multi' => 1, 'name' => 'isbn', 'module' => 'ViewTypeIsbn'],
            ['preselect' => 0, 'multi' => 1, 'name' => 'kana', 'module' => 'ViewTypeKana'],
        ];
        $this->_insertData('view_type', $viewTypeArr);
    }

    /**
     * insert data - view_data_relation.
     */
    protected function onInstallInsertDataViewDataRelation()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $dataTypeIds = [];
        $dataTypeHandler = Functions::getXoonipsHandler('DataTypeObject', $dirname);
        $dataTypeObjs = $dataTypeHandler->getObjects();
        foreach ($dataTypeObjs as $dataTypeObj) {
            $id = $dataTypeObj->get($dataTypeHandler->getPrimaryKey());
            $name = $dataTypeObj->get('name');
            $dataTypeIds[$name] = $id;
        }
        $viewTypeIds = [];
        $viewTypeHandler = Functions::getXoonipsHandler('ViewTypeObject', $dirname);
        $viewTypeObjs = $viewTypeHandler->getObjects();
        foreach ($viewTypeObjs as $viewTypeObj) {
            $id = $viewTypeObj->get($viewTypeHandler->getPrimaryKey());
            $name = $viewTypeObj->get('name');
            $viewTypeIds[$name] = $id;
        }
        $relationArr = [
            ['view_type_id' => $viewTypeIds['hidden'], 'data_type_id' => $dataTypeIds['int'], 'data_length' => 11, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['hidden'], 'data_type_id' => $dataTypeIds['float'], 'data_length' => 24, 'data_decimal_places' => 1],
            ['view_type_id' => $viewTypeIds['hidden'], 'data_type_id' => $dataTypeIds['double'], 'data_length' => 53, 'data_decimal_places' => 1],
            ['view_type_id' => $viewTypeIds['hidden'], 'data_type_id' => $dataTypeIds['char'], 'data_length' => 10, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['hidden'], 'data_type_id' => $dataTypeIds['varchar'], 'data_length' => 255, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['hidden'], 'data_type_id' => $dataTypeIds['text'], 'data_length' => -1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['hidden'], 'data_type_id' => $dataTypeIds['date'], 'data_length' => -1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['hidden'], 'data_type_id' => $dataTypeIds['datetime'], 'data_length' => -1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['hidden'], 'data_type_id' => $dataTypeIds['blob'], 'data_length' => -1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['text'], 'data_type_id' => $dataTypeIds['int'], 'data_length' => 11, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['text'], 'data_type_id' => $dataTypeIds['float'], 'data_length' => 24, 'data_decimal_places' => 1],
            ['view_type_id' => $viewTypeIds['text'], 'data_type_id' => $dataTypeIds['double'], 'data_length' => 53, 'data_decimal_places' => 1],
            ['view_type_id' => $viewTypeIds['text'], 'data_type_id' => $dataTypeIds['char'], 'data_length' => 10, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['text'], 'data_type_id' => $dataTypeIds['varchar'], 'data_length' => 255, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['text'], 'data_type_id' => $dataTypeIds['text'], 'data_length' => -1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['text'], 'data_type_id' => $dataTypeIds['date'], 'data_length' => -1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['text'], 'data_type_id' => $dataTypeIds['datetime'], 'data_length' => -1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['text'], 'data_type_id' => $dataTypeIds['blob'], 'data_length' => -1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['textarea'], 'data_type_id' => $dataTypeIds['varchar'], 'data_length' => 255, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['textarea'], 'data_type_id' => $dataTypeIds['text'], 'data_length' => -1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['radio'], 'data_type_id' => $dataTypeIds['int'], 'data_length' => 11, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['radio'], 'data_type_id' => $dataTypeIds['float'], 'data_length' => 24, 'data_decimal_places' => 1],
            ['view_type_id' => $viewTypeIds['radio'], 'data_type_id' => $dataTypeIds['double'], 'data_length' => 53, 'data_decimal_places' => 1],
            ['view_type_id' => $viewTypeIds['radio'], 'data_type_id' => $dataTypeIds['char'], 'data_length' => 10, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['radio'], 'data_type_id' => $dataTypeIds['varchar'], 'data_length' => 255, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['checkbox'], 'data_type_id' => $dataTypeIds['int'], 'data_length' => 11, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['checkbox'], 'data_type_id' => $dataTypeIds['float'], 'data_length' => 24, 'data_decimal_places' => 1],
            ['view_type_id' => $viewTypeIds['checkbox'], 'data_type_id' => $dataTypeIds['double'], 'data_length' => 53, 'data_decimal_places' => 1],
            ['view_type_id' => $viewTypeIds['checkbox'], 'data_type_id' => $dataTypeIds['char'], 'data_length' => 10, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['checkbox'], 'data_type_id' => $dataTypeIds['varchar'], 'data_length' => 255, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['select'], 'data_type_id' => $dataTypeIds['int'], 'data_length' => 11, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['select'], 'data_type_id' => $dataTypeIds['float'], 'data_length' => 24, 'data_decimal_places' => 1],
            ['view_type_id' => $viewTypeIds['select'], 'data_type_id' => $dataTypeIds['double'], 'data_length' => 53, 'data_decimal_places' => 1],
            ['view_type_id' => $viewTypeIds['select'], 'data_type_id' => $dataTypeIds['char'], 'data_length' => 10, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['select'], 'data_type_id' => $dataTypeIds['varchar'], 'data_length' => 255, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['id'], 'data_type_id' => $dataTypeIds['blob'], 'data_length' => -1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['title'], 'data_type_id' => $dataTypeIds['text'], 'data_length' => -1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['keyword'], 'data_type_id' => $dataTypeIds['varchar'], 'data_length' => 255, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['last update'], 'data_type_id' => $dataTypeIds['int'], 'data_length' => 10, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['create date'], 'data_type_id' => $dataTypeIds['int'], 'data_length' => 10, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['create user'], 'data_type_id' => $dataTypeIds['int'], 'data_length' => 10, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['change log'], 'data_type_id' => $dataTypeIds['text'], 'data_length' => -1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['index'], 'data_type_id' => $dataTypeIds['int'], 'data_length' => 10, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['relation item'], 'data_type_id' => $dataTypeIds['int'], 'data_length' => 10, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['date(yyyy mm dd)'], 'data_type_id' => $dataTypeIds['date'], 'data_length' => -1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['preview'], 'data_type_id' => $dataTypeIds['varchar'], 'data_length' => 255, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['file upload'], 'data_type_id' => $dataTypeIds['varchar'], 'data_length' => 255, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['file type'], 'data_type_id' => $dataTypeIds['varchar'], 'data_length' => 30, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['download limit'], 'data_type_id' => $dataTypeIds['int'], 'data_length' => 1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['download notify'], 'data_type_id' => $dataTypeIds['int'], 'data_length' => 1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['readme'], 'data_type_id' => $dataTypeIds['text'], 'data_length' => -1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['rights'], 'data_type_id' => $dataTypeIds['text'], 'data_length' => -1, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['url'], 'data_type_id' => $dataTypeIds['varchar'], 'data_length' => 255, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['pubmed id'], 'data_type_id' => $dataTypeIds['varchar'], 'data_length' => 30, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['isbn'], 'data_type_id' => $dataTypeIds['char'], 'data_length' => 13, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['kana'], 'data_type_id' => $dataTypeIds['varchar'], 'data_length' => 255, 'data_decimal_places' => -1],
            ['view_type_id' => $viewTypeIds['kana'], 'data_type_id' => $dataTypeIds['text'], 'data_length' => -1, 'data_decimal_places' => -1],
        ];
        $this->_insertData('view_data_relation', $relationArr);
    }

    /**
     * insert data - complement.
     */
    protected function onInstallInsertDataComplement()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $viewTypeIds = [];
        $viewTypeHandler = Functions::getXoonipsHandler('ViewTypeObject', $dirname);
        $viewTypeObjs = $viewTypeHandler->getObjects();
        foreach ($viewTypeObjs as $viewTypeObj) {
            $id = $viewTypeObj->get($viewTypeHandler->getPrimaryKey());
            $name = $viewTypeObj->get('name');
            $viewTypeIds[$name] = $id;
        }
        $complementArr = [
            ['view_type_id' => $viewTypeIds['preview'], 'title' => 'Preview', 'module' => null],
            ['view_type_id' => $viewTypeIds['url'], 'title' => 'URL', 'module' => null],
            ['view_type_id' => $viewTypeIds['pubmed id'], 'title' => 'Pubmed ID', 'module' => 'ComplementPubmedId'],
            ['view_type_id' => $viewTypeIds['isbn'], 'title' => 'ISBN', 'module' => 'ComplementIsbn'],
            ['view_type_id' => $viewTypeIds['kana'], 'title' => 'KANA', 'module' => 'ComplementKana'],
        ];
        $this->_insertData('complement', $complementArr);
    }

    /**
     * insert data - complement_detail.
     */
    protected function onInstallInsertDataComplementDetail()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $complementIds = [];
        $complementHandler = Functions::getXoonipsHandler('ComplementObject', $dirname);
        $complementObjs = $complementHandler->getObjects();
        foreach ($complementObjs as $complementObj) {
            $id = $complementObj->get($complementHandler->getPrimaryKey());
            $title = $complementObj->get('title');
            $complementIds[$title] = $id;
        }
        $complementDetailArr = [
            ['complement_id' => $complementIds['Preview'], 'code' => 'caption', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_CAPTION')],
            ['complement_id' => $complementIds['URL'], 'code' => 'hits', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_HITS')],
            ['complement_id' => $complementIds['Pubmed ID'], 'code' => 'title', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_TITLE')],
            ['complement_id' => $complementIds['Pubmed ID'], 'code' => 'keyword', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_KEYWORD')],
            ['complement_id' => $complementIds['Pubmed ID'], 'code' => 'author', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_AUTHOR')],
            ['complement_id' => $complementIds['Pubmed ID'], 'code' => 'journal', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_JOURNAL')],
            ['complement_id' => $complementIds['Pubmed ID'], 'code' => 'publicationyear', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_PUBLICATION_YEAR')],
            ['complement_id' => $complementIds['Pubmed ID'], 'code' => 'volume', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_VOLUME')],
            ['complement_id' => $complementIds['Pubmed ID'], 'code' => 'number', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_NUMBER')],
            ['complement_id' => $complementIds['Pubmed ID'], 'code' => 'page', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_PAGE')],
            ['complement_id' => $complementIds['Pubmed ID'], 'code' => 'abstract', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_ABSTRACT')],
            ['complement_id' => $complementIds['ISBN'], 'code' => 'title', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_TITLE')],
            ['complement_id' => $complementIds['ISBN'], 'code' => 'author', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_AUTHOR')],
            ['complement_id' => $complementIds['ISBN'], 'code' => 'publisher', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_PUBLISHER')],
            ['complement_id' => $complementIds['ISBN'], 'code' => 'publicationyear', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_PUBLICATION_YEAR')],
            ['complement_id' => $complementIds['ISBN'], 'code' => 'url', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_URL')],
            ['complement_id' => $complementIds['KANA'], 'code' => 'romaji', 'title' => $this->mLangMan->get('DATA_COMPLEMENT_DETAIL_ROMAJI')],
        ];
        $this->_insertData('complement_detail', $complementDetailArr);
    }

    /**
     * insert data - item_field_value_set.
     */
    protected function onInstallInsertDataItemFieldValueSet()
    {
        $itemFieldValueSetArr = [
            ['select_name' => 'Language', 'title_id' => 'eng', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_ENGLISH')],
            ['select_name' => 'Language', 'title_id' => 'jpn', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_JAPANES')],
            ['select_name' => 'Language', 'title_id' => 'fra', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_FRENCH')],
            ['select_name' => 'Language', 'title_id' => 'deu', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_GERMAN')],
            ['select_name' => 'Language', 'title_id' => 'esl', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_SPANISH')],
            ['select_name' => 'Language', 'title_id' => 'ita', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_ITALIAN')],
            ['select_name' => 'Language', 'title_id' => 'dut', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_DUTCH')],
            ['select_name' => 'Language', 'title_id' => 'sve', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_SWEDISH')],
            ['select_name' => 'Language', 'title_id' => 'nor', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_NORWEGIAN')],
            ['select_name' => 'Language', 'title_id' => 'dan', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_DANISH')],
            ['select_name' => 'Language', 'title_id' => 'fin', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_FINNISH')],
            ['select_name' => 'Language', 'title_id' => 'por', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_PORTUGUESE')],
            ['select_name' => 'Language', 'title_id' => 'chi', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_CHINESE')],
            ['select_name' => 'Language', 'title_id' => 'kor', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_LANGUAGE_KOREAN')],
            ['select_name' => 'Conference file type', 'title_id' => 'powerpoint', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_CONFERENCE_FILE_TYPE_POWERPOINT')],
            ['select_name' => 'Conference file type', 'title_id' => 'pdf', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_CONFERENCE_FILE_TYPE_PDF')],
            ['select_name' => 'Conference file type', 'title_id' => 'illustrator', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_CONFERENCE_FILE_TYPE_ILLUSTRATOR')],
            ['select_name' => 'Conference file type', 'title_id' => 'other', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_CONFERENCE_FILE_TYPE_OTHER')],
            ['select_name' => 'Data type', 'title_id' => 'excel', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_DATA_TYPE_EXCEL')],
            ['select_name' => 'Data type', 'title_id' => 'movie', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_DATA_TYPE_MOVIE')],
            ['select_name' => 'Data type', 'title_id' => 'text', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_DATA_TYPE_TEXT')],
            ['select_name' => 'Data type', 'title_id' => 'picture', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_DATA_TYPE_PICTURE')],
            ['select_name' => 'Data type', 'title_id' => 'other', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_DATA_TYPE_OTHER')],
            ['select_name' => 'Model type', 'title_id' => 'matlab', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_MODAL_TYPE_MATLAB')],
            ['select_name' => 'Model type', 'title_id' => 'neuron', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_MODAL_TYPE_NEURON')],
            ['select_name' => 'Model type', 'title_id' => 'originalprogram', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_MODAL_TYPE_ORIGINALPROGRAM')],
            ['select_name' => 'Model type', 'title_id' => 'satellite', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_MODAL_TYPE_SATELLITE')],
            ['select_name' => 'Model type', 'title_id' => 'genesis', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_MODAL_TYPE_Genesis')],
            ['select_name' => 'Model type', 'title_id' => 'a-cell', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_MODAL_TYPE_ACELL')],
            ['select_name' => 'Model type', 'title_id' => 'other', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_MODAL_TYPE_OTHER')],
            ['select_name' => 'Presentation file type', 'title_id' => 'powerpoint', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_PRESENTATION_FILE_TYPE_POWERPOINT')],
            ['select_name' => 'Presentation file type', 'title_id' => 'illustrator', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_CONFERENCE_FILE_TYPE_ILLUSTRATOR')],
            ['select_name' => 'Presentation file type', 'title_id' => 'lotus', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_PRESENTATION_FILE_TYPE_LOTUS')],
            ['select_name' => 'Presentation file type', 'title_id' => 'justsystem', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_PRESENTATION_FILE_TYPE_JUSTSYSTEM')],
            ['select_name' => 'Presentation file type', 'title_id' => 'html', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_PRESENTATION_FILE_TYPE_HTML')],
            ['select_name' => 'Presentation file type', 'title_id' => 'pdf', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_PRESENTATION_FILE_TYPE_PDF')],
            ['select_name' => 'Presentation file type', 'title_id' => 'other', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_PRESENTATION_FILE_TYPE_OTHER')],
            ['select_name' => 'Simulator file type', 'title_id' => 'matlab', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_SIMULATOR_FILE_TYPE_MATLAB')],
            ['select_name' => 'Simulator file type', 'title_id' => 'mathematica', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_SIMULATOR_FILE_TYPE_MATHEMATICA')],
            ['select_name' => 'Simulator file type', 'title_id' => 'program', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_SIMULATOR_FILE_TYPE_PROGRAM')],
            ['select_name' => 'Simulator file type', 'title_id' => 'other', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_SIMULATOR_FILE_TYPE_OTHER')],
            ['select_name' => 'Stimulus type', 'title_id' => 'picture', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_STIMULUS_TYPE_PICTURE')],
            ['select_name' => 'Stimulus type', 'title_id' => 'movie', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_STIMULUS_TYPE_MOVIE')],
            ['select_name' => 'Stimulus type', 'title_id' => 'program', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_STIMULUS_TYPE_PROGRAM')],
            ['select_name' => 'Stimulus type', 'title_id' => 'other', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_STIMULUS_TYPE_OTHER')],
            ['select_name' => 'Tool file type', 'title_id' => 'matlab', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_TOOL_FILE_TYPE_MATLAB')],
            ['select_name' => 'Tool file type', 'title_id' => 'mathematica', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_TOOL_FILE_TYPE_MATHEMATICA')],
            ['select_name' => 'Tool file type', 'title_id' => 'program', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_TOOL_FILE_TYPE_PROGRAM')],
            ['select_name' => 'Tool file type', 'title_id' => 'other', 'title' => $this->mLangMan->get('DATA_ITEM_FIELD_VALUE_SET_TOOL_FILE_TYPE_OTHER')],
        ];
        $name = '';
        $weight = 0;
        foreach ($itemFieldValueSetArr as &$itemFieldValueSet) {
            if ($name != $itemFieldValueSet['select_name']) {
                $weight = 1;
                $name = $itemFieldValueSet['select_name'];
            } else {
                ++$weight;
            }
            $itemFieldValueSet['weight'] = $weight;
        }
        unset($itemFieldValueSet);
        $this->_insertData('item_field_value_set', $itemFieldValueSetArr);
    }

    /**
     * insert data - oaipmh_schema.
     */
    protected function onInstallInsertDataOaipmhSchema()
    {
        $oaipmhSchemaArr = [
            ['metadata_prefix' => 'junii2', 'name' => 'title', 'min_occurences' => 1, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'alternative', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'creator', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'subject', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'NDC', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'NDLC', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'NDLSH', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'BSH', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'UDC', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'MeSH', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'DDC', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'LCC', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'LCSH', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'NIIsubject', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'description', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'publisher', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'contributor', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'date', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'type', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'NIItype', 'min_occurences' => 1, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'format', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'identifier', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'URI', 'min_occurences' => 1, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'fullTextURL', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'selfDOI', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'isbn', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'issn', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'NCID', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'jtitle', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'volume', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'issue', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'spage', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'epage', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'dateofissued', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'source', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'language', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'relation', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'pmid', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'doi', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'NAID', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'ichushi', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'isVersionOf', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'hasVersion', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'isReplacedBy', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'replaces', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'isRequiredBy', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'requires', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'isPartOf', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'hasPart', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'isReferencedBy', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'references', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'isFormatOf', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'hasFormat', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'coverage', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'spatial', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'NIIspatial', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'temporal', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'NIItemporal', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'rights', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'junii2', 'name' => 'textversion', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'grantid', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'dateofgranted', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'degreename', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'junii2', 'name' => 'grantor', 'min_occurences' => 0, 'max_occurences' => 1],
            ['metadata_prefix' => 'oai_dc', 'name' => 'title', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'oai_dc', 'name' => 'creator', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'oai_dc', 'name' => 'subject', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'oai_dc', 'name' => 'description', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'oai_dc', 'name' => 'publisher', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'oai_dc', 'name' => 'contributor', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'oai_dc', 'name' => 'date', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'oai_dc', 'name' => 'type', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'oai_dc', 'name' => 'type:NIItype', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'oai_dc', 'name' => 'format', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'oai_dc', 'name' => 'identifier', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'oai_dc', 'name' => 'source', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'oai_dc', 'name' => 'language', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'oai_dc', 'name' => 'relation', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'oai_dc', 'name' => 'coverage', 'min_occurences' => 0, 'max_occurences' => 0],
            ['metadata_prefix' => 'oai_dc', 'name' => 'rights', 'min_occurences' => 0, 'max_occurences' => 0],
        ];
        $name = '';
        $weight = 0;
        foreach ($oaipmhSchemaArr as &$oaipmhSchema) {
            if ($name != $oaipmhSchema['metadata_prefix']) {
                $weight = 1;
                $name = $oaipmhSchema['metadata_prefix'];
            } else {
                ++$weight;
            }
            $oaipmhSchema['weight'] = $weight;
        }
        unset($oaipmhSchema);
        $this->_insertData('oaipmh_schema', $oaipmhSchemaArr);
    }

    /**
     * insert data - oaipmh_schema_value_set.
     */
    protected function onInstallInsertDataOaipmhSchemaValueSet()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $oaipmhSchemaIds = [];
        $oaipmhSchemaHandler = Functions::getXoonipsHandler('OaipmhSchemaObject', $dirname);
        $oaipmhSchemaObjs = $oaipmhSchemaHandler->getObjects();
        foreach ($oaipmhSchemaObjs as $oaipmhSchemaObj) {
            $id = $oaipmhSchemaObj->get($oaipmhSchemaHandler->getPrimaryKey());
            $label = $oaipmhSchemaObj->get('metadata_prefix').':'.$oaipmhSchemaObj->get('name');
            $oaipmhSchemaIds[$label] = $id;
        }
        $oaipmhSchemaValueSetArr = [
            ['schema_id' => $oaipmhSchemaIds['junii2:NIItype'], 'value' => 'Journal Article'],
            ['schema_id' => $oaipmhSchemaIds['junii2:NIItype'], 'value' => 'Thesis or Dissertation'],
            ['schema_id' => $oaipmhSchemaIds['junii2:NIItype'], 'value' => 'Departmental Bulletin Paper'],
            ['schema_id' => $oaipmhSchemaIds['junii2:NIItype'], 'value' => 'Conference Paper'],
            ['schema_id' => $oaipmhSchemaIds['junii2:NIItype'], 'value' => 'Presentation'],
            ['schema_id' => $oaipmhSchemaIds['junii2:NIItype'], 'value' => 'Book'],
            ['schema_id' => $oaipmhSchemaIds['junii2:NIItype'], 'value' => 'Technical Report'],
            ['schema_id' => $oaipmhSchemaIds['junii2:NIItype'], 'value' => 'Research Paper'],
            ['schema_id' => $oaipmhSchemaIds['junii2:NIItype'], 'value' => 'Article'],
            ['schema_id' => $oaipmhSchemaIds['junii2:NIItype'], 'value' => 'Preprint'],
            ['schema_id' => $oaipmhSchemaIds['junii2:NIItype'], 'value' => 'Learning Material'],
            ['schema_id' => $oaipmhSchemaIds['junii2:NIItype'], 'value' => 'Data or Dataset'],
            ['schema_id' => $oaipmhSchemaIds['junii2:NIItype'], 'value' => 'Software'],
            ['schema_id' => $oaipmhSchemaIds['junii2:NIItype'], 'value' => 'Others'],
            ['schema_id' => $oaipmhSchemaIds['junii2:textversion'], 'value' => 'author'],
            ['schema_id' => $oaipmhSchemaIds['junii2:textversion'], 'value' => 'publisher'],
            ['schema_id' => $oaipmhSchemaIds['junii2:textversion'], 'value' => 'ETD'],
            ['schema_id' => $oaipmhSchemaIds['junii2:textversion'], 'value' => 'none'],
            ['schema_id' => $oaipmhSchemaIds['oai_dc:type:NIItype'], 'value' => 'Journal Article'],
            ['schema_id' => $oaipmhSchemaIds['oai_dc:type:NIItype'], 'value' => 'Thesis or Dissertation'],
            ['schema_id' => $oaipmhSchemaIds['oai_dc:type:NIItype'], 'value' => 'Departmental Bulletin Paper'],
            ['schema_id' => $oaipmhSchemaIds['oai_dc:type:NIItype'], 'value' => 'Conference Paper'],
            ['schema_id' => $oaipmhSchemaIds['oai_dc:type:NIItype'], 'value' => 'Presentation'],
            ['schema_id' => $oaipmhSchemaIds['oai_dc:type:NIItype'], 'value' => 'Book'],
            ['schema_id' => $oaipmhSchemaIds['oai_dc:type:NIItype'], 'value' => 'Technical Report'],
            ['schema_id' => $oaipmhSchemaIds['oai_dc:type:NIItype'], 'value' => 'Research Paper'],
            ['schema_id' => $oaipmhSchemaIds['oai_dc:type:NIItype'], 'value' => 'Article'],
            ['schema_id' => $oaipmhSchemaIds['oai_dc:type:NIItype'], 'value' => 'Preprint'],
            ['schema_id' => $oaipmhSchemaIds['oai_dc:type:NIItype'], 'value' => 'Learning Material'],
            ['schema_id' => $oaipmhSchemaIds['oai_dc:type:NIItype'], 'value' => 'Data or Dataset'],
            ['schema_id' => $oaipmhSchemaIds['oai_dc:type:NIItype'], 'value' => 'Software'],
            ['schema_id' => $oaipmhSchemaIds['oai_dc:type:NIItype'], 'value' => 'Others'],
        ];
        $this->_insertData('oaipmh_schema_value_set', $oaipmhSchemaValueSetArr);
    }

    /**
     * insert data - oaipmh_schema_link.
     */
    protected function onInstallInsertDataOaipmhSchemaLink()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $oaipmhSchemaIds = [];
        $oaipmhSchemaHandler = Functions::getXoonipsHandler('OaipmhSchemaObject', $dirname);
        $oaipmhSchemaObjs = $oaipmhSchemaHandler->getObjects();
        foreach ($oaipmhSchemaObjs as $oaipmhSchemaObj) {
            $id = $oaipmhSchemaObj->get($oaipmhSchemaHandler->getPrimaryKey());
            $label = $oaipmhSchemaObj->get('metadata_prefix').':'.$oaipmhSchemaObj->get('name');
            $oaipmhSchemaIds[$label] = $id;
        }
        $oaipmhSchemaLinkArr = [
            ['schema_id1' => $oaipmhSchemaIds['junii2:title'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:title'], 'number' => 1],
            ['schema_id1' => $oaipmhSchemaIds['junii2:alternative'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:title'], 'number' => 2],
            ['schema_id1' => $oaipmhSchemaIds['junii2:creator'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:creator'], 'number' => 1],
            ['schema_id1' => $oaipmhSchemaIds['junii2:subject'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:subject'], 'number' => 1],
            ['schema_id1' => $oaipmhSchemaIds['junii2:NIIsubject'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:subject'], 'number' => 2],
            ['schema_id1' => $oaipmhSchemaIds['junii2:NDC'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:subject'], 'number' => 3],
            ['schema_id1' => $oaipmhSchemaIds['junii2:NDLC'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:subject'], 'number' => 4],
            ['schema_id1' => $oaipmhSchemaIds['junii2:BSH'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:subject'], 'number' => 5],
            ['schema_id1' => $oaipmhSchemaIds['junii2:NDLSH'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:subject'], 'number' => 6],
            ['schema_id1' => $oaipmhSchemaIds['junii2:MeSH'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:subject'], 'number' => 7],
            ['schema_id1' => $oaipmhSchemaIds['junii2:DDC'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:subject'], 'number' => 8],
            ['schema_id1' => $oaipmhSchemaIds['junii2:LCC'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:subject'], 'number' => 9],
            ['schema_id1' => $oaipmhSchemaIds['junii2:UDC'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:subject'], 'number' => 10],
            ['schema_id1' => $oaipmhSchemaIds['junii2:LCSH'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:subject'], 'number' => 11],
            ['schema_id1' => $oaipmhSchemaIds['junii2:description'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:description'], 'number' => 1],
            ['schema_id1' => $oaipmhSchemaIds['junii2:publisher'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:publisher'], 'number' => 1],
            ['schema_id1' => $oaipmhSchemaIds['junii2:contributor'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:contributor'], 'number' => 1],
            ['schema_id1' => $oaipmhSchemaIds['junii2:date'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:date'], 'number' => 1],
            ['schema_id1' => $oaipmhSchemaIds['junii2:type'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:type'], 'number' => 1],
            ['schema_id1' => $oaipmhSchemaIds['junii2:NIItype'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:type:NIItype'], 'number' => 2],
            ['schema_id1' => $oaipmhSchemaIds['junii2:format'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:format'], 'number' => 1],
            ['schema_id1' => $oaipmhSchemaIds['junii2:identifier'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:identifier'], 'number' => 1],
            ['schema_id1' => $oaipmhSchemaIds['junii2:URI'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:identifier'], 'number' => 2],
            ['schema_id1' => $oaipmhSchemaIds['junii2:fullTextURL'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:identifier'], 'number' => 3],
            ['schema_id1' => $oaipmhSchemaIds['junii2:selfDOI'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:identifier'], 'number' => 4],
            ['schema_id1' => $oaipmhSchemaIds['junii2:isbn'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:identifier'], 'number' => 5],
            ['schema_id1' => $oaipmhSchemaIds['junii2:issn'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:identifier'], 'number' => 6],
            ['schema_id1' => $oaipmhSchemaIds['junii2:NCID'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:identifier'], 'number' => 7],
            ['schema_id1' => $oaipmhSchemaIds['junii2:jtitle'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:identifier'], 'number' => 8],
            ['schema_id1' => $oaipmhSchemaIds['junii2:volume'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:identifier'], 'number' => 8],
            ['schema_id1' => $oaipmhSchemaIds['junii2:issue'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:identifier'], 'number' => 8],
            ['schema_id1' => $oaipmhSchemaIds['junii2:spage'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:identifier'], 'number' => 8],
            ['schema_id1' => $oaipmhSchemaIds['junii2:epage'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:identifier'], 'number' => 8],
            ['schema_id1' => $oaipmhSchemaIds['junii2:dateofissued'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:identifier'], 'number' => 8],
            ['schema_id1' => $oaipmhSchemaIds['junii2:dateofissued'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:date'], 'number' => 2],
            ['schema_id1' => $oaipmhSchemaIds['junii2:source'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:source'], 'number' => 1],
            ['schema_id1' => $oaipmhSchemaIds['junii2:language'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:language'], 'number' => 1],
            ['schema_id1' => $oaipmhSchemaIds['junii2:relation'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 1],
            ['schema_id1' => $oaipmhSchemaIds['junii2:pmid'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 2],
            ['schema_id1' => $oaipmhSchemaIds['junii2:doi'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 3],
            ['schema_id1' => $oaipmhSchemaIds['junii2:NAID'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 4],
            ['schema_id1' => $oaipmhSchemaIds['junii2:ichushi'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 5],
            ['schema_id1' => $oaipmhSchemaIds['junii2:isVersionOf'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 6],
            ['schema_id1' => $oaipmhSchemaIds['junii2:hasVersion'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 7],
            ['schema_id1' => $oaipmhSchemaIds['junii2:isReplacedBy'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 8],
            ['schema_id1' => $oaipmhSchemaIds['junii2:replaces'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 9],
            ['schema_id1' => $oaipmhSchemaIds['junii2:isRequiredBy'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 10],
            ['schema_id1' => $oaipmhSchemaIds['junii2:requires'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 11],
            ['schema_id1' => $oaipmhSchemaIds['junii2:isPartOf'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 12],
            ['schema_id1' => $oaipmhSchemaIds['junii2:hasPart'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 13],
            ['schema_id1' => $oaipmhSchemaIds['junii2:isReferencedBy'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 14],
            ['schema_id1' => $oaipmhSchemaIds['junii2:references'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 15],
            ['schema_id1' => $oaipmhSchemaIds['junii2:isFormatOf'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 16],
            ['schema_id1' => $oaipmhSchemaIds['junii2:hasFormat'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:relation'], 'number' => 17],
            ['schema_id1' => $oaipmhSchemaIds['junii2:coverage'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:coverage'], 'number' => 1],
            ['schema_id1' => $oaipmhSchemaIds['junii2:spatial'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:coverage'], 'number' => 2],
            ['schema_id1' => $oaipmhSchemaIds['junii2:NIIspatial'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:coverage'], 'number' => 3],
            ['schema_id1' => $oaipmhSchemaIds['junii2:temporal'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:coverage'], 'number' => 4],
            ['schema_id1' => $oaipmhSchemaIds['junii2:NIItemporal'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:coverage'], 'number' => 5],
            ['schema_id1' => $oaipmhSchemaIds['junii2:rights'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:rights'], 'number' => 1],
            ['schema_id1' => $oaipmhSchemaIds['junii2:grantid'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:identifier'], 'number' => 9],
            ['schema_id1' => $oaipmhSchemaIds['junii2:dateofgranted'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:date'], 'number' => 3],
            ['schema_id1' => $oaipmhSchemaIds['junii2:degreename'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:description'], 'number' => 2],
            ['schema_id1' => $oaipmhSchemaIds['junii2:grantor'], 'schema_id2' => $oaipmhSchemaIds['oai_dc:description'], 'number' => 3],
        ];
        $this->_insertData('oaipmh_schema_link', $oaipmhSchemaLinkArr);
    }

    /**
     * insert data - item_type_sort.
     */
    protected function onInstallInsertDataItemTypeSort()
    {
        $itemTypeSortArr = [
            ['title' => $this->mLangMan->get('DATA_ITEM_TYPE_SORT_TITLE')],
            ['title' => $this->mLangMan->get('DATA_ITEM_TYPE_SORT_ID')],
            ['title' => $this->mLangMan->get('DATA_ITEM_TYPE_SORT_LAST_UPDATE')],
            ['title' => $this->mLangMan->get('DATA_ITEM_TYPE_SORT_CREATION_DATE')],
        ];
        $this->_insertData('item_type_sort', $itemTypeSortArr);
    }

    /**
     * insert data - item types.
     */
    protected function onInstallInsertDataItemTypes()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $trustDirname = Functions::getTrustDirname();
        $importItemType = new Xoonips_ImportItemType($dirname, $trustDirname);
        $dpath = dirname(dirname(dirname(__DIR__))).'/itemtype';
        // for Default.xml
        $fname = 'Default/Default.xml';
        $fpath = $dpath.'/'.$fname;
        $xmlObj = $importItemType->getSimpleXMLElement($fpath);
        if (false == $xmlObj) {
            $this->mLog->addError('Failed to load Item Type XML : '.$fname);

            return;
        }
        $id = '';
        if (false === $importItemType->installDefaultItemtype($xmlObj, $id)) {
            $this->mLog->addError('Failed to install Item Type XML : '.$fname);

            return;
        }
        // for All item types
        $itemTypes = [];
        if (false !== ($dh = opendir($dpath))) {
            while (false !== ($fname = readdir($dh))) {
                if (in_array($fname, ['.', '..', 'Default'])) {
                    continue;
                }
                $itemTypes[] = $fname;
            }
            closedir($dh);
        }
        asort($itemTypes);
        foreach ($itemTypes as $itemType) {
            $fname = $itemType.'/'.$itemType.'.xml';
            if (false === $importItemType->installItemType($dpath, $itemType)) {
                $this->mLog->addError('Failed to install Item Type XML : '.$fname);

                return;
            }
        }
        $this->mLog->addReport('Install Predefined Item Types');
    }

    /**
     * insert data - item type search condition.
     */
    protected function onInstallInsertDataItemTypeSearchCondition()
    {
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        $dirname = $this->mXoopsModule->get('dirname');
        $searchCondArr = [
            [
                'title' => $this->mLangMan->get('DATA_ITEM_TYPE_SEARCH_CONDITION_ALL'),
                'field_xmls' => [],
            ],
            [
                'title' => $this->mLangMan->get('DATA_ITEM_TYPE_SEARCH_CONDITION_TITLE_KEYWORD'),
                'field_xmls' => ['title', 'keyword'],
            ],
        ];
        $itscHandler = Functions::getXoonipsHandler('ItemTypeSearchConditionObject', $dirname);
        $ifdHandler = Functions::getXoonipsHandler('ItemFieldDetailObject', $dirname);
        foreach ($searchCondArr as $searchCond) {
            // install search condtion
            $itscObj = $itscHandler->create();
            $itscObj->set('title', $searchCond['title']);
            if (!$itscHandler->insert($itscObj)) {
                $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_DATA_INSERTED'), 'item_type_search_condition'));

                return;
            }
            // install search conditon details
            if (!empty($searchCond['field_xmls'])) {
                $criteria = new Criteria('xml', $searchCond['field_xmls'], 'IN');
                $ifdObjs = $ifdHandler->getObjects($criteria, null, null, true);
                if (count($ifdObjs) > 0) {
                    $fids = array_keys($ifdObjs);
                    if (!$itscObj->updateItemFieldDetailIds($fids)) {
                        $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_DATA_INSERTED'), 'item_type_search_condition_detail'));

                        return;
                    }
                }
            }
        }
        $this->mLog->addReport(XCubeUtils::formatString($this->mLangMan->get('INSTALL_MSG_DATA_INSERTED'), 'item_type_search_condtion, item_type_search_condtion_detail'));
    }

    /**
     * insert data - index.
     */
    protected function onInstallInsertDataIndex()
    {
        $indexArr = [
            ['parent_index_id' => 0, 'open_level' => XOONIPS_OL_PUBLIC, 'weight' => 1, 'title' => 'Root'],
            ['parent_index_id' => 1, 'open_level' => XOONIPS_OL_PUBLIC, 'weight' => 1, 'title' => 'Public'],
        ];
        $this->_insertData('index', $indexArr);
    }

    /**
     * setup uploads directory.
     */
    protected function onInstallSetupUploadsDirectory()
    {
        $fpath = 'uploads/xoonips/group';
        if (false === FileUtils::makeDirectory(XOOPS_ROOT_PATH.'/'.$fpath)) {
            $this->mLog->addError('Failed to create file upload direcotry "XOOPS_ROOT_PATH/'.$fpath.'".');
        }
        $this->mLog->addReport('Create file upload directory "XOOPS_ROOT_PATH/'.$fpath.'".');
    }

    /**
     * setup permissions.
     */
    protected function onInstallSetupPermissions()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        // set gourps permission
        $this->mLog->addReport('Setup Groups Permission.');
        $perms = [
            'b_xoonips_quick_search_show' => [
                'side' => XoopsSystemUtils::BLOCK_SIDE_LEFT,
                'weight' => 10,
                'pages' => [XoopsSystemUtils::BLOCK_PAGE_ALL],
                'gids' => [XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS],
            ],
            'b_xoonips_tree_show' => [
                'side' => XoopsSystemUtils::BLOCK_SIDE_LEFT,
                'weight' => 20,
                'pages' => [XoopsSystemUtils::BLOCK_PAGE_ALL],
                'gids' => [XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS],
            ],
            'b_xoonips_login_show' => [
                'side' => XoopsSystemUtils::BLOCK_SIDE_LEFT,
                'weight' => 0,
                'pages' => [XoopsSystemUtils::BLOCK_PAGE_ALL],
                'gids' => [XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS],
            ],
            'b_xoonips_user_show' => [
                'side' => XoopsSystemUtils::BLOCK_SIDE_RIGHT,
                'weight' => 0,
                'pages' => [XoopsSystemUtils::BLOCK_PAGE_ALL],
                'gids' => [XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS],
            ],
            'b_xoonips_itemtypes_show' => [
                'side' => XoopsSystemUtils::BLOCK_SIDE_CENTER_CENTER,
                'weight' => 20,
                'pages' => [XoopsSystemUtils::BLOCK_PAGE_TOP],
                'gids' => [XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS],
            ],
        ];
        foreach ($perms as $show_func => $perm) {
            $bid = XoopsSystemUtils::getBlockId($dirname, $show_func);
            XoopsSystemUtils::setBlockInfo($bid, $perm['side'], $perm['weight'], $perm['pages']);
            XoopsSystemUtils::setBlockReadRights($bid, $perm['gids']);
        }
    }

    /**
     * setup users.
     */
    protected function onInstallSetupUsers()
    {
        $this->mLog->addReport('Setup Additional Users Information.');
        $dirname = $this->mXoopsModule->get('dirname');
        $memberHandler = xoops_gethandler('member');
        $numUsers = $memberHandler->getUserCount();
        $limit = 100;
        for ($start = 0; $start < $numUsers; $start += $limit) {
            $criteria = new CriteriaElement();
            $criteria->setSort('uid');
            $criteria->setOrder('ASC');
            $criteria->setLimit($limit);
            $criteria->setStart($start);
            $userObjs = &$memberHandler->getUsers($criteria, true);
            foreach ($userObjs as $userObj) {
                if ($userObj->isActive()) {
                    if (false === $this->_pickupUser($userObj, $dirname)) {
                        $this->mLog->addError('Failed to pickup user : '.$userObj->get('name').'.');

                        return;
                    }
                }
            }
        }
    }

    /**
     * setup notifications.
     */
    protected function onInstallSetupNotifications()
    {
        $this->mLog->addReport('Setup Notificaitons.');
        $mid = $this->mXoopsModule->get('mid');
        $dirname = $this->mXoopsModule->get('dirname');
        $notification = $this->mXoopsModule->getInfo('notification');
        $memberHandler = xoops_gethandler('member');
        $numUsers = $memberHandler->getUserCount();
        $limit = 100;
        foreach ($notification['event'] as $event) {
            $name = $event['name'];
            $category = $event['category'];
            if (false === XoopsSystemUtils::enableNotification($mid, $category, $name)) {
                $this->mLog->addError('Failed to enable notification "'.$category.'-'.$name.'".');

                return;
            }
            for ($start = 0; $start < $numUsers; $start += $limit) {
                $criteria = new CriteriaElement();
                $criteria->setSort('uid');
                $criteria->setOrder('ASC');
                $criteria->setLimit($limit);
                $criteria->setStart($start);
                $userObjs = &$memberHandler->getUsers($criteria, true);
                foreach ($userObjs as $userObj) {
                    XoopsSystemUtils::subscribeNotification($mid, $userObj->get('uid'), $category, $name);
                }
            }
        }
    }

    /**
     * insert data.
     *
     * @param string $name
     * @param array  $data
     */
    private function _insertData($name, $data)
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $tableName = $dirname.'_'.$name;
        $objectName = str_replace(' ', '', ucwords(str_replace('_', ' ', $name))).'Object';
        $handler = Functions::getXoonipsHandler($objectName, $dirname);
        foreach ($data as $datum) {
            $obj = $handler->create();
            foreach ($datum as $key => $value) {
                $obj->set($key, $value);
            }
            if (!$handler->insert($obj)) {
                $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_DATA_INSERTED'), $tableName));

                return;
            }
        }
        $this->mLog->addReport(XCubeUtils::formatString($this->mLangMan->get('INSTALL_MSG_DATA_INSERTED'), $tableName));
    }

    /**
     * pickup user.
     *  - todo:this function should be implement in user model class.
     *
     * @param object $userObj
     * @param string $dirname
     *
     * @return bool
     */
    private function _pickupUser($userObj, $dirname)
    {
        $uid = $userObj->get('uid');
        $memberHandler = xoops_gethandler('member');
        if ($userObj->get('level') <= Xoonips_Enum::USER_NOT_CERTIFIED) {
            $userObj->set('level', Xoonips_Enum::USER_CERTIFIED);
            if (!$memberHandler->insertUser($userObj)) {
                return false;
            }
        }
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $dirname);
        if (false === $indexBean->insertPrivateIndex($uid)) {
            return false;
        }
        $eventLogBean = Xoonips_BeanFactory::getBean('EventLogBean', $dirname);
        $eventLogBean->recordRequestInsertAccountEvent($uid);
        $eventLogBean->recordCertifyAccountEvent($uid);

        return true;
    }
}
