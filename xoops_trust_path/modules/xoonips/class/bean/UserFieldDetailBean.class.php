<?php

require_once dirname(__DIR__).'/core/ViewTypeFactory.class.php';
require_once dirname(__DIR__).'/core/DataTypeFactory.class.php';
require_once dirname(__DIR__).'/core/UserField.class.php';

/**
 * @brief operate xoonips_user_field_detail table
 */
class Xoonips_UserFieldDetailBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName(XCUBE_CORE_USER_MODULE_NAME.'_field_detail', false);

        $this->typelinktable = $this->prefix(XCUBE_CORE_USER_MODULE_NAME.'_type_field_group_link');
        $this->grouplinktable = $this->prefix(XCUBE_CORE_USER_MODULE_NAME.'_field_group_field_detail_link');
        $this->complementlink = $this->prefix(XCUBE_CORE_USER_MODULE_NAME.'_field_detail_complement_link');
    }

    /**
     * get user type detail by id.
     *
     * @param int  $id      user detail id
     * @param bool $release
     *
     * @return array user type detail
     */
    public function getUserTypeDetailById($id, $release = true)
    {
        $id = Xoonips_Utils::convertSQLNum($id);
        $sql = "SELECT * FROM $this->table"
            ." WHERE user_detail_id=$id";
        if ($release) {
            $sql .= " AND released='1'";
        }
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);
        $this->freeRecordSet($result);

        return $row;
    }

    /**
     * get default user type detail list.
     *
     * @param int $groupId
     *
     * @return array default usertype detail list
     */
    public function getDefaultUserTypeDetail($groupId)
    {
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $table = $this->prefix($this->modulePrefix('default_user_field_detail'));
        $sql = "SELECT * FROM $table"
            ." WHERE group_id=$groupId"
            .' ORDER BY weight';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get user type detail list.
     *
     * @param int $groupId
     *
     * @return array usertype detail list
     */
    public function getUserTypeGroupDetail($groupId)
    {
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $sql = "SELECT * FROM $this->table"
            ." WHERE group_id=$groupId"
            .' ORDER BY user_detail_id';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * insert new usertype detail.
     *
     * @param array $info     usertype detail info
     * @param int   $insertId reference of insert id
     *
     * @return bool true:success,false:failed
     */
    public function insert($info, &$insertId)
    {
        $sql = "INSERT INTO $this->table ("
            .' released,'
            .' preselect,'
            .' table_name,'
            .' column_name,'
            .' group_id,'
            .' weight,'
            .' name, '
            .' xml,'
            .' view_type_id,'
            .' data_type_id,'
            .' data_length,'
            .' data_decimal_places,'
            .' default_value,'
            .' list,'
            //. ' list_display,'
            //. ' list_sort_key,'
            //. ' list_width,'
            .' essential,'
            .' detail_display,'
            .' registry_user,'
            .' edit_user,'
            .' target_user,'
            .' scope_search,'
            .' nondisplay,'
            .' update_id )';
        $sql .= ' VALUES ('
            .Xoonips_Utils::convertSQLNum($info['released']).','
            .Xoonips_Utils::convertSQLNum($info['preselect']).','
            .Xoonips_Utils::convertSQLStr($info['table_name']).','
            .Xoonips_Utils::convertSQLStr($info['column_name']).','
            .Xoonips_Utils::convertSQLNum($info['group_id']).','
            .Xoonips_Utils::convertSQLNum($info['weight']).','
            .Xoonips_Utils::convertSQLStr($info['name']).','
            .Xoonips_Utils::convertSQLStr($info['xml']).','
            .Xoonips_Utils::convertSQLNum($info['view_type_id']).','
            .Xoonips_Utils::convertSQLNum($info['data_type_id']).','
            .Xoonips_Utils::convertSQLNum($info['data_length']).','
            .Xoonips_Utils::convertSQLNum($info['data_decimal_places']).','
            .Xoonips_Utils::convertSQLStr($info['default_value']).','
            .Xoonips_Utils::convertSQLStr($info['list']).','
            //. Xoonips_Utils::convertSQLStr($info['list_display']) . ','
            //. Xoonips_Utils::convertSQLStr($info['list_sort_key']) . ','
            //. Xoonips_Utils::convertSQLStr($info['list_width']) . ','
            .Xoonips_Utils::convertSQLNum($info['essential']).','
            .Xoonips_Utils::convertSQLNum($info['detail_display']).','
            .Xoonips_Utils::convertSQLNum($info['registry_user']).','
            .Xoonips_Utils::convertSQLNum($info['edit_user']).','
            .Xoonips_Utils::convertSQLNum($info['target_user']).','
            .Xoonips_Utils::convertSQLNum($info['scope_search']).','
            .Xoonips_Utils::convertSQLNum($info['nondisplay']).','
            .Xoonips_Utils::convertSQLNum($info['update_id']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $insertId = $this->getInsertId();

        return true;
    }

    /**
     * insert default detail.
     *
     * @param array $info     default detail info
     * @param int   $insertId reference of insert id
     *
     * @return bool true:success,false:failed
     */
    public function insertDefault($info, &$insertId)
    {
        $table = $this->prefix($this->modulePrefix('default_user_field_detail'));
        $sql = "INSERT INTO $table ("
            .' table_name,'
            .' column_name,'
            .' group_id,'
            .' weight,'
            .' name,'
            .' xml,'
            .' view_type_id,'
            .' data_type_id,'
            .' data_length,'
            .' data_decimal_places,'
            .' default_value,'
            .' list,'
            .' essential,'
            .' detail_display,'
            .' target_user,'
            .' scope_search,'
            .' nondisplay )';
        $sql .= ' VALUES ('
            .Xoonips_Utils::convertSQLStr($info['table_name']).','
            .Xoonips_Utils::convertSQLStr($info['column_name']).','
            .Xoonips_Utils::convertSQLNum($info['group_id']).','
            .Xoonips_Utils::convertSQLNum($info['weight']).','
            .Xoonips_Utils::convertSQLStr($info['name']).','
            .Xoonips_Utils::convertSQLStr($info['xml']).','
            .Xoonips_Utils::convertSQLNum($info['view_type_id']).','
            .Xoonips_Utils::convertSQLNum($info['data_type_id']).','
            .Xoonips_Utils::convertSQLNum($info['data_length']).','
            .Xoonips_Utils::convertSQLNum($info['data_decimal_places']).','
            .Xoonips_Utils::convertSQLStr($info['default_value']).','
            .Xoonips_Utils::convertSQLStr($info['list']).','
            .Xoonips_Utils::convertSQLNum($info['essential']).','
            .Xoonips_Utils::convertSQLNum($info['detail_display']).','
            .Xoonips_Utils::convertSQLNum($info['target_user']).','
            .Xoonips_Utils::convertSQLNum($info['scope_search']).','
            .Xoonips_Utils::convertSQLNum($info['nondisplay']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $insertId = $this->getInsertId();

        return true;
    }

    /**
     * delete by group id.
     *
     * @param int $group_id
     *
     * @return bool true:success,false:failed
     */
    public function deleteByGroupId($groupId)
    {
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $sql = "DELETE FROM $this->table"
            ." WHERE group_id=$groupId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get new user type detail list for ng.
     *
     * @param int $detailid
     *
     * @return array usertype detail list
     */
    public function getReleasedDetailByDetailId($detailId)
    {
        $detailId = Xoonips_Utils::convertSQLNum($detailId);
        $sql = "SELECT * FROM $this->table"
            .' WHERE update_id IS NULL'
            ." AND user_detail_id=$detailId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get group detail list.
     *
     * @param int $groupId
     *
     * @return array usertype detail list
     */
    public function getGroupDetails($groupId)
    {
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $sql = "SELECT * FROM $this->table"
            ." WHERE group_id=$groupId"
            .' ORDER BY weight';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * update weight.
     *
     * @param int $detail_id
     * @param int $weight
     *
     * @return bool true:success,false:failed
     */
    public function updateWeight($detailId, $weight)
    {
        $detailId = Xoonips_Utils::convertSQLNum($detailId);
        $weight = Xoonips_Utils::convertSQLNum($weight);
        $sql = "UPDATE $this->table"
            ." SET weight=$weight"
            ." WHERE user_detail_id=$detailId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get max detail weight.
     *
     * @param  int group_id
     *
     * @return max group weight
     */
    public function getMaxDetailWeight($groupId)
    {
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $sql = "SELECT MAX(weight) AS maxWeight FROM $this->table"
            ." WHERE group_id=$groupId";
        $result = $this->execute($sql);
        if (!$result) {
            return 0;
        }
        while ($row = $this->fetchArray($result)) {
            return $row['maxWeight'];
        }

        return 0;
    }

    /**
     * update table_name.
     *
     * @param int $detailId
     *
     * @return bool true:success,false:failed
     */
    public function updateTableName($detailId)
    {
        $detailId = Xoonips_Utils::convertSQLNum($detailId);
        $table_name = 'user_extend'.$detailId;
        $sql = "UPDATE $this->table"
            ." SET table_name=$table_name"
            ." WHERE user_detail_id=$detailId"
            ." AND table_name LIKE '$this->dirname"."_user_extend%' ";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete by detail id.
     *
     * @param int $detail_id
     *
     * @return bool true:success,false:failed
     */
    public function delete($detailId)
    {
        $detailId = Xoonips_Utils::convertSQLNum($detailId);
        $sql = "DELETE FROM $this->table"
            ." WHERE user_detail_id=$detailId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $sql = "DELETE FROM $this->table"
            ." WHERE update_id=$detailId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update detail.
     *
     * @param array $detail_info detail info
     * @param int   $detailId    detail id
     *
     * @return bool true:success,false:failed
     */
    public function update($detail_info, $detailId)
    {
        $detailId = Xoonips_Utils::convertSQLNum($detailId);
        $sql = "UPDATE $this->table SET "
            .'  name = '.Xoonips_Utils::convertSQLStr($detail_info['name'])
            .', xml = '.Xoonips_Utils::convertSQLStr($detail_info['xml'])
            .', view_type_id = '.Xoonips_Utils::convertSQLNum($detail_info['view_type_id'])
            .', data_type_id = '.Xoonips_Utils::convertSQLNum($detail_info['data_type_id'])
            .', data_length = '.Xoonips_Utils::convertSQLNum($detail_info['data_length'])
            .', data_decimal_places = '.Xoonips_Utils::convertSQLNum($detail_info['data_decimal_places'])
            .', default_value = '.Xoonips_Utils::convertSQLStr($detail_info['default_value'])
            .', list = '.Xoonips_Utils::convertSQLStr($detail_info['list'])
            .', essential = '.Xoonips_Utils::convertSQLNum($detail_info['essential'])
            .', detail_display = '.Xoonips_Utils::convertSQLNum($detail_info['detail_display'])
            .', target_user = '.Xoonips_Utils::convertSQLNum($detail_info['target_user'])
            .', scope_search = '.Xoonips_Utils::convertSQLNum($detail_info['scope_search'])
            .', nondisplay = '.Xoonips_Utils::convertSQLNum($detail_info['nondisplay']);
        $sql .= " WHERE user_detail_id=$detailId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * create extend table.
     *
     * @param array $detailObj
     *
     * @return bool true:success,false:failed
     */
    public function createExtendTable($detailObj)
    {
        foreach ($detailObj as $detail) {
            // view type to confirm create user extend table, except (id, title, keyword, file upload, create date,
            // last update, create user, change log, index, relation user, preview)
            $viewtypeObj = Xoonips_ViewTypeFactory::getInstance($this->dirname, $this->trustDirname)->getViewType($detail['view_type_id']);
            if (!$viewtypeObj->mustCreateUserExtendTable() || strpos($detail['table_name'], 'user_extend') === false) {
                continue;
            }

            // check table exsit
            $tableName = $this->prefix('user_extend'.$detail['user_detail_id']);
            $result = $this->execute("SHOW TABLES LIKE '$tableName'");
            if (!$result) {
                return false;
            }
            while ($row = $this->fetchArray($result)) {
                continue;
            }

            // data type to confirm create 'vaule' column
            $user = new User_UserField();
            $user->setLen($detail['data_length']);
            $user->setDecimalPlaces($detail['data_decimal_places']);
            $user->setDefault($detail['default_value']);
            $user->setEssential($detail['essential']);
            $datatypeObj = Xoonips_DataTypeFactory::getInstance($this->dirname, $this->trustDirname)->getDataType($detail['data_type_id']);
            $valueSql = $datatypeObj->getValueSql($user);

            $createSql = " CREATE TABLE $tableName ("
                ." uid int(10) unsigned NOT NULL default '0',"
                ." group_id int(10) unsigned default '0',"
                .' value '.$valueSql[0].','
                ." occurrence_number smallint(3) unsigned NOT NULL default '1',"
                .' PRIMARY KEY (uid, group_id, occurrence_number),'
                .' KEY value (value'.$valueSql[1].')'
                .') ENGINE=InnoDB;';
            $createRes = $this->execute($createSql);
            if (!$createRes) {
                return false;
            }
        }

        return true;
    }

    /**
     * get count userfields.
     *
     * @return int count userfields
     */
    public function countUserfields()
    {
        $sql = "SELECT user_detail_id FROM $this->table"
            .' WHERE update_id IS NULL';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = $this->getRowsNum($result);
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get userfield objs.
     *
     * @param int $limit
     * @param int $start
     *
     * @return array userfield objs
     */
    public function getUserfieldlist($limit = 0, $start = 0)
    {
        $limit = Xoonips_Utils::convertSQLNum($limit);
        $start = Xoonips_Utils::convertSQLNum($start);
        $sql = "SELECT at.*, bt.update_id AS upid FROM $this->table at"
            ." LEFT JOIN $this->table bt"
            .' ON at.user_detail_id=bt.update_id'
            .' WHERE at.update_id IS NULL '
            ." LIMIT $start , $limit";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * 	get userfield complement link.
     *
     * @return array userfield complement link
     */
    public function getUserfieldComplementLink()
    {
        $sql = "SELECT * FROM $this->complementlink"
            .' WHERE complement_id != 0';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * check exist user_type detail name.
     *
     * @param int    $detailId
     * @param string $name
     * @param int    $base_detailId
     *
     * @return bool true:success,false:failed
     */
    public function existDetailName($detailId, $name, $base_detailId)
    {
        $detailId = Xoonips_Utils::convertSQLNum($detailId);
        $name = Xoonips_Utils::convertSQLStr($name);
        $base_detailId = Xoonips_Utils::convertSQLNum($base_detailId);
        $sql = "SELECT name FROM $this->table"
            ." WHERE user_detail_id<>$detailId"
            ." AND user_detail_id<>$base_detailId"
            ." AND name=$name";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            return true;
        }

        return false;
    }

    /**
     * check exist user_type detail xml.
     *
     * @param int    $detailId
     * @param string $xml
     * @param int    $base_detailId
     *
     * @return bool true:success,false:failed
     */
    public function existDetailXml($detailId, $xml, $base_detailId)
    {
        $detailId = Xoonips_Utils::convertSQLNum($detailId);
        $xml = Xoonips_Utils::convertSQLStr($xml);
        $base_detailId = Xoonips_Utils::convertSQLNum($base_detailId);
        $sql = "SELECT xml FROM $this->table"
            ." WHERE user_detail_id<>$detailId"
            ." AND user_detail_id<>$base_detailId"
            ." AND xml=$xml";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            return true;
        }

        return false;
    }

    /**
     * check exist view type.
     *
     * @param int $detailId
     * @param int $viewtypeId
     * @param int $base_detailId
     *
     * @return bool true:success,false:failed
     */
    public function existViewtype($detailId, $viewtypeId, $base_detailId)
    {
        $detailId = Xoonips_Utils::convertSQLNum($detailId);
        $viewtypeId = Xoonips_Utils::convertSQLNum($viewtypeId);
        $base_detailId = Xoonips_Utils::convertSQLNum($base_detailId);
        $sql = "SELECT view_type_id FROM $this->table"
            ." WHERE user_detail_id<>$detailId"
            ." AND user_detail_id<>$base_detailId"
            ." AND view_type_id=$viewtypeId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            return true;
        }

        return false;
    }

    /**
     * do copy by id.
     *
     * @param int   $usertypeId
     * @param array $map
     * @param bool  $update
     * @param bool  $import
     *
     * @return bool true:success,false:failed
     */
    public function copyById($userfieldId, &$map, $update = false, $import = false)
    {
        $detailObj = array();

        // get copy information
        $detailObj[] = $this->getUserTypeDetailById($userfieldId, false);
        if (!$detailObj) {
            return false;
        }

        // do copy by obj
        return $this->copyByObj($detailObj, $map, $update, $import);
    }

    /**
     * do copy by obj.
     *
     * @param array $detailObj
     * @param array $map
     * @param bool  $update
     * @param bool  $import
     *
     * @return bool true:success,false:failed
     */
    public function copyByObj($detailObj, &$map, $update, $import)
    {
        // insert copy
        foreach ($detailObj as $detail) {
            $detail['released'] = $import ? $detail['released'] : 0;
            $detail['update_id'] = $update ? $detail['user_detail_id'] : null;

            $insertId = null;
            if (!$this->insert($detail, $insertId)) {
                return false;
            }
            $map['detail'][$detail['user_detail_id']] = $insertId;

            if ($update) {
                continue;
            }
            if (!$this->updateTableName($insertId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * get usertype detail edit info.
     *
     * @param int  $detailId user type detail id (released)
     * @param bool $flg      flg
     *
     * @return array user type detail information
     */
    public function getDetailEditInfo($detailId, $flg = false)
    {
        $detailId = Xoonips_Utils::convertSQLNum($detailId);
        $sql = 'SELECT a.released as a_released, b.released as b_released, '
            .' a.name as a_name, b.name as b_name, '
            .' a.xml as a_xml, b.xml as b_xml, '
            .' a.view_type_id as a_view_type_id, b.view_type_id as b_view_type_id, '
            .' a.data_type_id as a_data_type_id, b.data_type_id as b_data_type_id, '
            .' a.data_length as a_data_length, b.data_length as b_data_length, '
            .' a.data_decimal_places as a_data_decimal_places, b.data_decimal_places as b_data_decimal_places, '
            .' a.default_value as a_default_value, b.default_value as b_default_value, '
            .' a.list as a_list, b.list as b_list, '
            .' a.essential as a_essential, b.essential as b_essential, '
            .' a.detail_display as a_detail_display, b.detail_display as b_detail_display, '
            .' a.registry_user as a_registry_user, b.registry_user as b_registry_user, '
            .' a.edit_user as a_edit_user, b.edit_user as b_edit_user, '
            .' a.target_user as a_target_user, b.target_user as b_target_user, '
            .' a.scope_search as a_scope_search, b.scope_search as b_scope_search, '
            .' a.nondisplay as a_nondisplay, b.nondisplay as b_nondisplay, '
            .' a.weight as a_weight, b.weight as b_weight,'
            .' b.user_detail_id as b_user_detail_id ';
        if ($flg) {
            $sql .= " FROM $this->table a"
                ." LEFT JOIN $this->table b"
                .' ON a.update_id=b.user_detail_id'
                ." WHERE a.user_detail_id=$detailId";
        } else {
            $sql .= ', b.update_id as b_update_id'
                ." FROM $this->table a"
                ." LEFT JOIN $this->table b"
                .' ON a.user_detail_id=b.update_id'
                ." WHERE a.user_detail_id=$detailId";
        }
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);
        $this->freeRecordSet($result);

        return $row;
    }

    /**
     * release.
     *
     * @param int $detailId
     * @param int $base_detailId
     *
     * @return bool true:success,false:failed
     */
    public function release($detailId, $base_detailId)
    {
        $detailId = Xoonips_Utils::convertSQLNum($detailId);
        $base_detailId = Xoonips_Utils::convertSQLNum($base_detailId);

        // create extend table
        if (!$this->createExtendTable($this->getReleasedDetailByDetailId($detailId))) {
            return false;
        }

        if ($detailId == $base_detailId) {
            $sql = "UPDATE $this->table"
                .' SET released=1'
                ." WHERE user_detail_id=$detailId";
        } else {
            $sql = "UPDATE $this->table t1, $this->table t2 SET "
            .' t1.table_name = t2.table_name ,'
            .' t1.column_name = t2.column_name ,'
            .' t1.weight = t2.weight ,'
            .' t1.name = t2.name ,'
            .' t1.xml = t2.xml ,'
            .' t1.view_type_id = t2.view_type_id ,'
            .' t1.data_type_id = t2.data_type_id ,'
            .' t1.data_length = t2.data_length ,'
            .' t1.data_decimal_places = t2.data_decimal_places ,'
            .' t1.default_value = t2.default_value ,'
            .' t1.list = t2.list ,'
            .' t1.essential = t2.essential ,'
            .' t1.detail_display = t2.detail_display ,'
            .' t1.target_user = t2.target_user ,'
            .' t1.scope_search = t2.scope_search ,'
            .' t1.nondisplay = t2.nondisplay ,'
            .' t1.released = 1 '
            .' WHERE t1.user_detail_id=t2.update_id'
            ." AND t2.user_detail_id=$detailId";
        }
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }
}
