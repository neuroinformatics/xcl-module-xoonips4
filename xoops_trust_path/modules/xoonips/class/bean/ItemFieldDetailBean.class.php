<?php

require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/BeanBase.class.php';
require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/ViewTypeFactory.class.php';
require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/DataTypeFactory.class.php';
require_once dirname(dirname(__FILE__)).'/core/ItemField.class.php';

/**
 * @brief operate xoonips_item_field_detail table
 */
class Xoonips_ItemFieldDetailBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('item_field_detail', true);

        $this->typelinktable = $this->prefix($this->modulePrefix('item_type_field_group_link'));
        $this->grouplinktable = $this->prefix($this->modulePrefix('item_field_group_field_detail_link'));
    }

    /**
     * get item type detail by id.
     *
     * @param item type id
     *
     * @return item type detail
     */
    public function getItemTypeDetails($itemTypeId)
    {
        $sql = 'SELECT * FROM '.$this->table.' WHERE item_type_id='.$itemTypeId.' ORDER BY item_field_detail_id';
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
     * get item type detail by id.
     *
     * @param item detail id, release
     *
     * @return item type detail
     */
    public function getItemTypeDetailById($id, $release = true)
    {
        $sql_release = '';
        if ($release) {
            $sql_release = ' and released=1';
        }
        $sql = 'SELECT * FROM '.$this->table.' WHERE item_field_detail_id='.$id.$sql_release;

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);
        $this->freeRecordSet($result);

        return $row;
    }

    /**
     * get create user detail by itemtype id.
     *
     * @param item type id
     *
     * @return item type detail
     */
    public function getCreateUserDetail($itemtypeId)
    {
        $sql = 'SELECT * FROM '.$this->table." WHERE released='1' AND item_type_id=0 AND view_type_id='12'";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);
        $this->freeRecordSet($result);

        return $row;
    }

    /**
     * get file type list by id.
     *
     * @param item type id
     *
     * @return file type list
     */
    public function getFileTypeList($id)
    {
        $viewTypeBean = Xoonips_BeanFactory::getBean('ViewTypeBean', $this->dirname, $this->trustDirname);
        $tv = $this->prefix($this->modulePrefix('item_field_value_set'));

        $sql = 'SELECT tv.title_id, tv.title FROM '.$this->table.' td'
            .' LEFT JOIN '.$tv.' AS tv ON td.list=tv.select_name'
            .' LEFT JOIN '.$this->grouplinktable.' AS lg ON td.item_field_detail_id=lg.item_field_detail_id'
            .' LEFT JOIN '.$this->typelinktable.' AS lt ON lg.group_id=lt.group_id'
            .' WHERE td.view_type_id='.$viewTypeBean->selectByName('file type')
            ." AND lt.released='1'"
            .' AND lt.item_type_id='.$id;

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
     * get default item type detail list.
     *
     * @param
     *
     * @return default itemtype detail list
     */
    public function getDefaultItemTypeDetail($groupId)
    {
        $ret = array();
        $table = $this->prefix($this->modulePrefix('default_item_field_detail'));
        $sql = 'SELECT * FROM '.$table." WHERE group_id=$groupId ORDER BY weight";
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get item type detail list.
     *
     * @param $groupId
     *
     * @return itemtype detail list
     */
    public function getItemTypeGroupDetail($groupId)
    {
        $ret = array();
        $sql = 'SELECT * FROM '.$this->table." WHERE group_id=$groupId ORDER BY item_field_detail_id";
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * insert new itemtype detail.
     *
     * @param  $info: itemtype detail info
     *
     * @return bool true:success,false:failed
     */
    public function insert($info, &$insertId)
    {
        $sql = "INSERT INTO $this->table (released, preselect, table_name, column_name, item_type_id, group_id, weight, name, "
            .' xml, view_type_id, data_type_id, data_length, data_decimal_places, default_value, list, essential, '
            .' detail_display, detail_target, scope_search, nondisplay, update_id)';
        $sql .= ' VALUES ('.Xoonips_Utils::convertSQLNum($info['released']).','
            .Xoonips_Utils::convertSQLNum($info['preselect']).','
            .Xoonips_Utils::convertSQLStr($info['table_name']).','
            .Xoonips_Utils::convertSQLStr($info['column_name']).','
            .Xoonips_Utils::convertSQLNum($info['item_type_id']).','
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
            .Xoonips_Utils::convertSQLNum($info['detail_target']).','
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
     * @param  $info: default detail info
     *
     * @return bool true:success,false:failed
     */
    public function insertDefault($info, &$insertId)
    {
        $table = $this->prefix($this->modulePrefix('default_item_field_detail'));
        $sql = "INSERT INTO $table (table_name, column_name, group_id, weight, name, xml, view_type_id, "
            .' data_type_id, data_length, data_decimal_places, default_value, list, essential, '
            .' detail_display, detail_target, scope_search, nondisplay)';
        $sql .= ' VALUES ('.Xoonips_Utils::convertSQLStr($info['table_name']).','
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
            .Xoonips_Utils::convertSQLNum($info['detail_target']).','
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
     * update itemtype detail.
     *
     * @param  $itemtypeId: itemtype id
     *
     * @return bool true:success,false:failed
     */
    public function updateNewDetail($base_itemtypeid, $copy_itemtypeid)
    {
        $gt = $this->prefix($this->modulePrefix('item_field_group'));
        $sql = "UPDATE $this->table t1, (SELECT if (update_id IS NULL, group_id, update_id) as upd_group_id, group_id FROM $gt) t2 SET "
            .' t1.released = 1 '.", t1.item_type_id = $base_itemtypeid "
            .', t1.group_id = t2.upd_group_id ';
        $sql .= " WHERE t1.group_id=t2.group_id AND t1.item_type_id=$copy_itemtypeid AND t1.released=0 AND t1.update_id IS NULL";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update itemtype detail.
     *
     * @param  $itemtypeId: itemtype id
     *
     * @return bool true:success,false:failed
     */
    public function updateCopyToBaseDetail($itemtypeId)
    {
        $sql = "UPDATE $this->table t1, $this->table t2 SET "
            .' t1.table_name = t2.table_name '
            .', t1.column_name = t2.column_name '
            .', t1.weight = t2.weight '
            .', t1.name = t2.name '
            .', t1.xml = t2.xml '
            .', t1.view_type_id = t2.view_type_id '
            .', t1.data_type_id = t2.data_type_id '
            .', t1.data_length = t2.data_length '
            .', t1.data_decimal_places = t2.data_decimal_places '
            .', t1.default_value = t2.default_value '
            .', t1.list = t2.list '
            .', t1.essential = t2.essential '
            .', t1.detail_display = t2.detail_display '
            .', t1.detail_target = t2.detail_target '
            .', t1.scope_search = t2.scope_search '
            .', t1.nondisplay = t2.nondisplay ';
        $sql .= " WHERE t1.item_field_detail_id=t2.update_id AND t2.item_type_id=$itemtypeId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete itemtype detail.
     *
     * @param  $itemtypeId: itemtype id
     *
     * @return bool true:success,false:failed
     */
    public function deleteCopyItemtypeDetail($itemtypeId)
    {
        $sql = "DELETE FROM $this->table WHERE item_type_id=$itemtypeId AND update_id IS NOT NULL";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete by group id.
     *
     * @param  $group_id,
     *
     * @return bool true:success,false:failed
     */
    public function deleteByGroupId($groupId)
    {
        $sql = "DELETE FROM $this->table WHERE group_id=$groupId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete by itemtypeid.
     *
     * @param  $itemtypeid,
     *
     * @return bool true:success,false:failed
     */
    public function deleteByItemtypeId($itemtypeid)
    {
        $sql = "DELETE FROM $this->table WHERE item_type_id=$itemtypeid";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get new item type detail list.
     *
     * @param $groupId
     *
     * @return itemtype detail list
     */
    public function getNewItemTypeDetail($itemtypeId)
    {
        $ret = array();
        $sql = 'SELECT * FROM '.$this->table." WHERE update_id IS NULL AND released='0' AND item_type_id=$itemtypeId";
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get new item type detail list.
     *
     * @param $itemtypeId
     *
     * @return itemtype detail list
     */
    public function getReleasedDetail($itemtypeId)
    {
        $ret = array();
        $sql = 'SELECT d.* FROM '.$this->table.' AS d'
        .' LEFT JOIN '.$this->grouplinktable.' AS lg ON d.item_field_detail_id=lg.item_field_detail_id'
        .' LEFT JOIN '.$this->typelinktable.' AS lt ON lg.group_id=lt.group_id'
        ." WHERE d.update_id IS NULL AND d.released='1' AND lt.item_type_id=$itemtypeId";

        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $exist = false;
            $id = $row['item_field_detail_id'];
            foreach ($ret as $detail) {
                if ($id == $detail['item_field_detail_id']) {
                    $exist = true;
                    break;
                }
            }
            if (!$exist) {
                $ret[] = $row;
            }
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get new item type detail list for ng.
     *
     * @param $detailid
     *
     * @return itemtype detail list
     */
    public function getReleasedDetailByDetailId($detailid)
    {
        $ret = array();
        $sql = 'SELECT * FROM '.$this->table." WHERE update_id IS NULL AND item_field_detail_id=$detailid";
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get group detail list.
     *
     * @param $groupId
     *
     * @return itemtype detail list
     */
    public function getGroupDetails($groupId)
    {
        $ret = array();
        $sql = 'SELECT d.* FROM '.$this->table.' AS d'
        .' INNER JOIN '.$this->grouplinktable.' AS l ON d.item_field_detail_id=l.item_field_detail_id'
        ." WHERE l.group_id=$groupId ORDER BY weight";
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * update weight.
     *
     * @param  $detail_id, $weight
     *
     * @return bool true:success,false:failed
     */
    public function updateWeight($detailId, $weight)
    {
        $sql = "UPDATE $this->table SET weight=$weight WHERE item_field_detail_id=$detailId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get max detail weight.
     *
     * @param  group_id
     *
     * @return max group weight
     */
    public function getMaxDetailWeight($gid)
    {
        $sql = 'SELECT MAX(weight) AS maxWeight FROM '.$this->table." WHERE group_id=$gid";
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
     * @param  $detail_id
     *
     * @return bool true:success,false:failed
     */
    public function updateTableName($detailId)
    {
        $table_name = $this->modulePrefix('item_extend').$detailId;
        $sql = "UPDATE $this->table SET table_name='$table_name' WHERE item_field_detail_id=$detailId"
            ." AND table_name LIKE '$this->dirname"."_item_extend%' ";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete by detail id.
     *
     * @param  $detail_id,
     *
     * @return bool true:success,false:failed
     */
    public function delete($detailId)
    {
        $sql = "DELETE FROM $this->table WHERE item_field_detail_id=$detailId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        $sql = "DELETE FROM $this->table WHERE update_id=$detailId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update detail.
     *
     * @param  $detail_info: detail info,$detail_id: detail id
     *
     * @return bool true:success,false:failed
     */
    public function update($detail_info, $detail_id)
    {
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
            .', detail_target = '.Xoonips_Utils::convertSQLNum($detail_info['detail_target'])
            .', scope_search = '.Xoonips_Utils::convertSQLNum($detail_info['scope_search'])
            .', nondisplay = '.Xoonips_Utils::convertSQLNum($detail_info['nondisplay']);
        $sql .= " WHERE item_field_detail_id=$detail_id";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * create extend table.
     *
     * @param  $detail
     *
     * @return bool true:success,false:failed
     */
    public function createExtendTable($detailObj)
    {
        foreach ($detailObj as $detail) {
            // view type to confirm create item extend table, except (id, title, keyword, file upload, create date,
            // last update, create user, change log, index, relation item, preview)
            $viewtypeObj = Xoonips_ViewTypeFactory::getInstance($this->dirname, $this->trustDirname)->getViewType($detail['view_type_id']);
            if (!$viewtypeObj->mustCreateItemExtendTable() || strpos($detail['table_name'], $this->dirname.'_item_extend') === false) {
                continue;
            }

            // check table exsit
            $tableName = $this->prefix($this->modulePrefix('item_extend').$detail['item_field_detail_id']);
            $exist = false;
            $result = $this->execute("SHOW TABLES LIKE '%${tableName}'");
            while ($row = $this->fetchArray($result)) {
                $exist = true;
            }
            if ($exist) {
                continue;
            }

            // data type to confirm create 'vaule' column
            $item = new Xoonips_ItemField();
            $item->setLen($detail['data_length']);
            $item->setDecimalPlaces($detail['data_decimal_places']);
            $item->setDefault($detail['default_value']);
            $item->setEssential($detail['essential']);
            $datatypeObj = Xoonips_DataTypeFactory::getInstance($this->dirname, $this->trustDirname)->getDataType($detail['data_type_id']);
            $valueSql = $datatypeObj->getValueSql($item);

            $createSql = " CREATE TABLE $tableName ("
                ." item_id int(10) unsigned NOT NULL default '0',"
                ." group_id int(10) unsigned default '0',"
                .' value '.$valueSql[0].','
                ." occurrence_number smallint(3) unsigned NOT NULL default '1',"
                .' PRIMARY KEY (item_id, group_id, occurrence_number),'
                .' KEY value (value'.$valueSql[1].')'
                .') ENGINE=InnoDB;';

            $createRes = $this->execute($createSql);

            if (!$createRes) {
                return false;
            }
        }

        return true;
    }

    // get count itemfields
    public function countItemfields()
    {
        $sql = 'SELECT item_field_detail_id FROM '.$this->table.' WHERE update_id IS NULL';
        $result = $this->execute($sql);
        $ret = 0;
        if (!$result) {
            return $ret;
        }
        $ret = $this->getRowsNum($result);
        $this->freeRecordSet($result);

        return $ret;
    }

    // get itemfield objs
    public function getItemfieldlist($limit = 0, $start = 0)
    {
        $ret = array();
        $sql = 'SELECT at.*, bt.update_id as upid FROM '.$this->table.' at LEFT JOIN '.$this->table.' bt'
        .' ON at.item_field_detail_id=bt.update_id WHERE at.update_id IS NULL '
        .' LIMIT '.(int) $start.', '.(int) $limit;

        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * check exist item_type detail name.
     *
     * @param detail_id, name, $base_did
     *
     * @return bool true:success,false:failed
     */
    public function existDetailName($did, $name, $base_did)
    {
        $sql = 'SELECT name FROM '.$this->table
            ." WHERE item_field_detail_id<>$did AND item_field_detail_id<>$base_did"
            .' AND name='.Xoonips_Utils::convertSQLStr($name);
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
     * check exist item_type detail xml.
     *
     * @param detail_id, xml, $base_did
     *
     * @return bool true:success,false:failed
     */
    public function existDetailXml($did, $xml, $base_did)
    {
        $sql = 'SELECT xml FROM '.$this->table.' WHERE '
            ." item_field_detail_id<>$did AND item_field_detail_id<>$base_did"
            .' AND xml='.Xoonips_Utils::convertSQLStr($xml);
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
     * @param  $detailId, $viewtypeId: view type id, $base_did
     *
     * @return bool true:success,false:failed
     */
    public function existViewtype($detailId, $viewtypeId, $base_did)
    {
        $sql = 'SELECT view_type_id FROM '.$this->table.' WHERE '
            ." item_field_detail_id<>$detailId AND item_field_detail_id<>$base_did"
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
     * @param  $itemtypeId, $map
     *
     * @return bool true:success,false:failed
     */
    public function copyById($itemfieldId, &$map, $update = false, $import = false)
    {
        $detailObj = array();

        // get copy information
        $detailObj[] = $this->getItemTypeDetailById($itemfieldId, false);
        if (!$detailObj) {
            return false;
        }

        // do copy by obj
        return $this->copyByObj($detailObj, $map, $update, $import);
    }

    /**
     * do copy by obj.
     *
     * @param  $detailObj, $map
     *
     * @return bool true:success,false:failed
     */
    public function copyByObj($detailObj, &$map, $update, $import)
    {
        // insert copy
        foreach ($detailObj as $detail) {
            $detail['released'] = $import ? $detail['released'] : 0;
            $detail['item_type_id'] = (is_numeric($detail['item_type_id'])) ? $detail['item_type_id'] : 0;
            $detail['group_id'] = (is_numeric($detail['group_id'])) ? $detail['group_id'] : 0;
            $detail['update_id'] = $update ? $detail['item_field_detail_id'] : null;

            $insertId = null;
            $detail_info = $this->getDetailByXml($detail['xml']);
            if (!$update && count($detail_info) > 0) {
                $insertId = $detail_info['item_field_detail_id'];
            } else {
                if (!$this->insert($detail, $insertId)) {
                    return false;
                }
            }

            $map['detail'][$detail['item_field_detail_id']] = $insertId;

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
     * get itemtype detail edit info.
     *
     * @param  $detailId: item type detail id (released)
     * @param  $type_flg: item type flg
     *
     * @return item type detail information
     */
    public function getDetailEditInfo($detailId, $type_flg = false)
    {
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
            .' a.detail_target as a_detail_target, b.detail_target as b_detail_target, '
            .' a.scope_search as a_scope_search, b.scope_search as b_scope_search, '
            .' a.nondisplay as a_nondisplay, b.nondisplay as b_nondisplay, '
            .' a.weight as a_weight, b.weight as b_weight, b.item_field_detail_id as b_item_field_detail_id ';
        if ($type_flg) {
            $sql .= " FROM $this->table a LEFT JOIN $this->table b ON a.update_id=b.item_field_detail_id WHERE a.item_field_detail_id=$detailId";
        } else {
            $sql .= ', b.update_id as b_update_id'
                    ." FROM $this->table a LEFT JOIN $this->table b ON a.item_field_detail_id=b.update_id WHERE a.item_field_detail_id=$detailId";
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
     * @param  $detail_id, $base_detailid
     *
     * @return bool true:success,false:failed
     */
    public function release($detail_id, $base_detailid)
    {
        if ($detail_id == $base_detailid) {
            // create extend table
            if (!$this->createExtendTable($this->getReleasedDetailByDetailId($detail_id))) {
                return false;
            }

            $sql = "UPDATE $this->table SET released=1 WHERE item_field_detail_id=$detail_id";
        } else {
            $sql = "UPDATE $this->table t1, $this->table t2 SET "
            .' t1.table_name = t2.table_name '
            .', t1.column_name = t2.column_name '
            .', t1.weight = t2.weight '
            .', t1.name = t2.name '
            .', t1.xml = t2.xml '
            .', t1.view_type_id = t2.view_type_id '
            .', t1.data_type_id = t2.data_type_id '
            .', t1.data_length = t2.data_length '
            .', t1.data_decimal_places = t2.data_decimal_places '
            .', t1.default_value = t2.default_value '
            .', t1.list = t2.list '
            .', t1.essential = t2.essential '
            .', t1.detail_display = t2.detail_display '
            .', t1.detail_target = t2.detail_target '
            .', t1.scope_search = t2.scope_search '
            .', t1.nondisplay = t2.nondisplay '
            .', t1.released = 1 ';
            $sql .= " WHERE t1.item_field_detail_id=t2.update_id AND t2.item_field_detail_id=$detail_id";
        }
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get detail by xml.
     *
     * @param  string xml
     *
     * @return array ret
     */
    public function getDetailByXml($xml)
    {
        $ret = array();
        $sql = 'SELECT * FROM '.$this->table
        .' WHERE xml='.Xoonips_Utils::convertSQLStr($xml).' AND update_id IS NULL';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret = $row;
        }

        return $ret;
    }

    /**
     *  get item detail data for Export Item Type XML Element.
     *
     * @param   string group_id
     *
     * @return array ret
     **/
    public function getExportItemTypeDetail($group_id)
    {
        $groupTable = $this->prefix($this->modulePrefix('item_field_group'));
        $viewTable = $this->prefix($this->modulePrefix('view_type'));
        $dataTable = $this->prefix($this->modulePrefix('data_type'));
        $sql = 'SELECT '.$this->table.'.item_field_detail_id, ';
        $sql .= $this->table.'.table_name, ';
        $sql .= $this->table.'.column_name, ';
        $sql .= $groupTable.'.xml, ';
        $sql .= $this->table.'.name, ';
        $sql .= $this->table.'.xml, ';
        $sql .= $viewTable.'.name, ';
        //$sql .= $this->table . ".data_type_id, ";
        $sql .= $dataTable.'.name, ';
        $sql .= $this->table.'.data_length, ';
        $sql .= $this->table.'.data_decimal_places, ';
        $sql .= $this->table.'.default_value, ';
        $sql .= $this->table.'.list, ';
        $sql .= $this->table.'.essential, ';
        $sql .= $this->table.'.detail_target, ';
        $sql .= $this->table.'.scope_search ';
        $sql .= 'FROM '.$this->table.', ';
        $sql .= $this->grouplinktable.', ';
        $sql .= $groupTable.', ';
        $sql .= $viewTable.', ';
        $sql .= $dataTable.' ';

        $sql .= 'WHERE '.$this->table.'.item_field_detail_id='.$this->grouplinktable.'.item_field_detail_id ';
        $sql .= 'AND '.$groupTable.'.group_id='.$this->grouplinktable.'.group_id ';
        $sql .= 'AND '.$this->table.'.view_type_id='.$viewTable.'.view_type_id ';
        $sql .= 'AND '.$this->table.'.data_type_id='.$dataTable.'.data_type_id ';
        $sql .= 'AND '.$this->grouplinktable.'.group_id='.$group_id;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchRow($result)) {
            $detail = array(
            'item_field_detail_id' => '',
            'table_name' => '',
            'column_name' => '',
            'group_id' => '',
            'name' => '',
            'xml' => '',
            'view_type_id' => '',
            'data_type_id' => '',
            'data_length' => '',
            'data_decimal_places' => '',
            'default_value' => '',
            'list' => '',
            'essential' => '',
            'detail_target' => '',
            'scope_search' => '',
            );
            $detail['item_field_detail_id'] = $row[0];
            $pattern = '/'.$this->dirname.'_(.+)/';
            if (preg_match($pattern, $row[1], $matches)) {
                $row[1] = $matches[1];
            }
            if (preg_match("/item_extend\d+/", $row[1])) {
                $row[1] = 'item_extend';
            }
            $detail['table_name'] = $row[1];
            $detail['column_name'] = $row[2];
            $detail['group_id'] = $row[3];
            $detail['name'] = $row[4];
            $detail['xml'] = $row[5];
            $detail['view_type_id'] = $row[6];
            $detail['data_type_id'] = $row[7];
            $detail['data_length'] = $row[8];
            $detail['data_decimal_places'] = $row[9];
            $detail['default_value'] = $row[10];
            $detail['list'] = $row[11];
            $detail['essential'] = $row[12];
            $detail['detail_target'] = $row[13];
            $detail['scope_search'] = $row[14];
            $ret[] = $detail;
        }
        $this->freeRecordSet($result);

        return $ret;
    }
}
