<?php

require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/BeanBase.class.php';

/**
 * @brief operate xoonips_item_type table
 */
class Xoonips_ItemTypeBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('item_type', true);

        $this->typelinktable = $this->prefix($this->modulePrefix('item_type_field_group_link'));
        $this->grouptable = $this->prefix($this->modulePrefix('item_field_group'));
    }

    /**
     * get item type by id.
     *
     * @param item type id
     *
     * @return item type information
     */
    public function getItemType($id)
    {
        $ret = array();
        $id = Xoonips_Utils::convertSQLNum($id);
        $sql = "SELECT * FROM $this->table WHERE item_type_id=$id";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        if ($row = $this->fetchArray($result)) {
            $ret = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get item type by name.
     *
     * @param item type id
     *
     * @return item type information
     */
    public function getItemTypeByName($name)
    {
        $ret = array();
        $name = Xoonips_Utils::convertSQLStr($name);
        $sql = "SELECT * FROM $this->table WHERE name=".$name;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        if ($row = $this->fetchArray($result)) {
            $ret = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get item type information by id.
     *
     * @param item type id
     *
     * @return item type information
     */
    public function getItemTypeInfo($id)
    {
        $ret = array();
        $id = Xoonips_Utils::convertSQLNum($id);
        $sql = "SELECT * FROM $this->table WHERE released='1' AND item_type_id=$id";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get item type information.
     *
     * @param
     *
     * @return itemtype information
     */
    public function getItemTypeList()
    {
        $sql = "SELECT * FROM $this->table WHERE released='1' ORDER BY weight";
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
     * get item type name by id.
     *
     * @param  itemtype id
     *
     * @return itemtype name
     */
    public function getItemTypeName($id)
    {
        $id = Xoonips_Utils::convertSQLNum($id);
        $sql = "SELECT name FROM $this->table WHERE item_type_id=$id";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = '';
        while ($row = $this->fetchArray($result)) {
            $ret = $row['name'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get item type display information.
     *
     * @param
     *
     * @return itemtype display information
     */
    public function getItemTypeDisplayList()
    {
        $sql = "SELECT * FROM $this->table WHERE update_id is null order by weight";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }

        return $ret;
    }

    /**
     * update item type weight.
     *
     * @param long $id     item type id
     * @param int  $weight weight
     *
     * @return bool true:success,false:failed
     */
    public function updateItemTypeWeight($id, $weight)
    {
        $id = Xoonips_Utils::convertSQLNum($id);
        $weight = Xoonips_Utils::convertSQLNum($weight);
        $sql = "UPDATE $this->table set weight =$weight WHERE item_type_id=$id";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * check exist item_type name.
     *
     * @param  itemtype name
     *
     * @return bool true:success,false:failed
     */
    public function existItemTypeName($id, $name)
    {
        $ret = false;
        $id = Xoonips_Utils::convertSQLNum($id);
        $name = Xoonips_Utils::convertSQLStr($name);
        $sql = "SELECT name FROM $this->table WHERE item_type_id<>$id AND name=".$name;
        if ($id != 0) {
            $itemtypeInfo = $this->getItemType($id);
            $update_id = Xoonips_Utils::convertSQLNum($itemtypeInfo['update_id']);
            if (!empty($update_id)) {
                $sql .= " AND item_type_id<>$update_id";
            }
        }
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret = true;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * insert new itemtype.
     *
     * @param  $info: itemtype info
     *
     * @return bool true:success,false:failed
     */
    public function insert($info, &$insertId)
    {
        $sql = "INSERT INTO $this->table (preselect, released, weight, name, description, icon, mime_type, template, update_id)";
        $sql .= ' VALUES ('
            .Xoonips_Utils::convertSQLNum($info['preselect']).','
            .Xoonips_Utils::convertSQLNum($info['released']).','
            .Xoonips_Utils::convertSQLNum($info['weight']).','
            .Xoonips_Utils::convertSQLStr($info['name']).','
            .Xoonips_Utils::convertSQLStr($info['description']).','
            .Xoonips_Utils::convertSQLStr($info['icon']).','
            .Xoonips_Utils::convertSQLStr($info['mime_type']).','
            .Xoonips_Utils::convertSQLStr($info['template']).','
            .Xoonips_Utils::convertSQLNum($info['update_id']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $insertId = $this->getInsertId();

        return true;
    }

    /**
     * update itemtype.
     *
     * @param  $info: itemtype info
     *
     * @return bool true:success,false:failed
     */
    public function update($info, $itemtypeId, $hasIcon = true)
    {
        $itemtypeId = Xoonips_Utils::convertSQLNum($itemtypeId);
        $sql = "UPDATE $this->table SET "
            .'  weight = '.Xoonips_Utils::convertSQLNum($info['weight'])
            .', name = '.Xoonips_Utils::convertSQLStr($info['name'])
            .', released = '.Xoonips_Utils::convertSQLStr($info['released'])
            .', description = '.Xoonips_Utils::convertSQLStr($info['description'])
            .', template = '.Xoonips_Utils::convertSQLStr($info['template']);
        if ($hasIcon) {
            $sql .= ', icon = '.Xoonips_Utils::convertSQLStr($info['icon'])
                .', mime_type = '.Xoonips_Utils::convertSQLStr($info['mime_type']);
        }
        $sql .= " WHERE item_type_id=$itemtypeId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get itemtype edit info.
     *
     * @param  $itemtypeId: itemtype id
     *
     * @return item type information
     */
    public function getItemtypeEditInfo($itemtypeId)
    {
        $itemtypeId = Xoonips_Utils::convertSQLNum($itemtypeId);
        $sql = 'SELECT a.preselect as a_preselect, b.preselect as b_preselect, '
            .' a.released as a_released, b.released as b_released, '
            .' a.weight as a_weight, b.weight as b_weight, '
            .' a.name as a_name, b.name as b_name, '
            .' a.description as a_description, b.description as b_description, '
            .' a.icon as a_icon, b.icon as b_icon, '
            .' a.mime_type as a_mime_type, b.mime_type as b_mime_type, '
            .' a.template as a_template, b.template as b_template, '
            .' a.update_id as a_update_id, b.update_id as b_update_id, '
            .' a.item_type_id as a_item_type_id, b.item_type_id as b_item_type_id '
            ." FROM $this->table a LEFT JOIN $this->table b ON a.item_type_id=b.update_id "
            ." WHERE a.item_type_id=$itemtypeId "
            .' and a.update_id IS NULL ';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);
        $this->freeRecordSet($result);

        return $row;
    }

    /**
     * update itemtype.
     *
     * @param  $itemtypeId: itemtype id
     *
     * @return bool true:success,false:failed
     */
    public function updateCopyToBase($itemtypeId)
    {
        $itemtypeId = Xoonips_Utils::convertSQLNum($itemtypeId);
        $sql = "UPDATE $this->table t1, $this->table t2 SET "
            .' t1.weight = t2.weight '
            .', t1.name = t2.name '
            .', t1.description = t2.description '
            .', t1.icon = t2.icon '
            .', t1.mime_type = t2.mime_type '
            .', t1.template = t2.template ';
        $sql .= " WHERE t1.item_type_id=t2.update_id AND t2.item_type_id=$itemtypeId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete itemtype.
     *
     * @param  $itemtypeId: itemtype id
     *
     * @return bool true:success,false:failed
     */
    public function deleteCopyItemtype($itemtypeId)
    {
        $itemtypeId = Xoonips_Utils::convertSQLNum($itemtypeId);
        $sql = "DELETE FROM $this->table WHERE item_type_id=$itemtypeId AND update_id IS NOT NULL";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        $sql = "DELETE FROM $this->table WHERE update_id=$itemtypeId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * do copy by id.
     *
     * @param  $itemtypeId, $map
     *
     * @return bool true:success,false:failed
     */
    public function copyById($itemtypeId, &$map, $update = false, $import = false, $copy = false)
    {
        // get copy information
        $itemtypeObj = $this->getItemType($itemtypeId);
        if (!$itemtypeObj) {
            return false;
        }

        // do copy by obj
        return $this->copyByObj($itemtypeObj, $map, $update, $import, $copy);
    }

    /**
     * do copy by obj.
     *
     * @param  $itemtypeObj, $map
     *
     * @return bool true:success,false:failed
     */
    public function copyByObj($itemtypeObj, &$map, $update, $import, $copy)
    {
        // insert copy
        $itemtypeObj['name'] = $copy ? $itemtypeObj['name'].'_'._AM_XOONIPS_LABEL_COPY : $itemtypeObj['name'];
        $itemtypeObj['released'] = $import ? $itemtypeObj['released'] : 0;
        $itemtypeObj['preselect'] = $import ? $itemtypeObj['preselect'] : 0;
        $itemtypeObj['update_id'] = $update ? $itemtypeObj['item_type_id'] : null;
        $insertId = null;
        if (!$this->insert($itemtypeObj, $insertId)) {
            return false;
        }

        $map['itemtype'][$itemtypeObj['item_type_id']] = $insertId;

        return true;
    }

    // delete item_type
    public function delete($itemtypeId)
    {
        $itemtypeId = Xoonips_Utils::convertSQLNum($itemtypeId);
        $sql = "delete from $this->table where item_type_id=$itemtypeId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    // get itemtype objs
    public function itemtypeGetItemtypelist($limit = 0, $start = 0)
    {
        $ret = array();
        $limit = Xoonips_Utils::convertSQLNum($limit);
        $start = Xoonips_Utils::convertSQLNum($start);
        $sql = "SELECT at.*, bt.update_id as upid FROM $this->table at LEFT JOIN $this->table bt "
            .' ON at.item_type_id=bt.update_id WHERE at.update_id IS NULL ORDER BY at.weight '
            ." LIMIT $start, $limit";
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

    // get count itemtypes
    public function countItemtypes()
    {
        $sql = 'SELECT at.item_type_id FROM '.$this->table.' at LEFT JOIN '.$this->table.' bt '
            .' ON at.item_type_id=bt.update_id WHERE at.update_id IS NULL ';
        $result = $this->execute($sql);
        $ret = 0;
        if (!$result) {
            return $ret;
        }
        $ret = $this->getRowsNum($result);
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get type group list.
     *
     * @param $typeId
     *
     * @return itemtype detail list
     */
    public function getTypeGroups($typeId)
    {
        $ret = array();
        $typeId = Xoonips_Utils::convertSQLNum($typeId);
        $sql = 'SELECT g.group_id,g.name,g.xml,t.weight,t.edit_weight '
        ." ,t.edit,t.released as link_release FROM $this->grouptable AS g "
        ." LEFT JOIN $this->typelinktable AS t ON g.group_id=t.group_id "
        ." WHERE t.item_type_id=$typeId ORDER BY t.edit_weight";
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
     * get type by group id.
     *
     * @param $groupId
     *
     * @return itemtype detail list
     */
    public function getTypeByGroupId($groupId)
    {
        $ret = array();
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $sql = "SELECT t.* FROM $this->table AS t"
        ." LEFT JOIN $this->typelinktable AS l ON t.item_type_id=l.item_type_id"
        ." WHERE l.group_id=$groupId AND l.edit=1";
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
     * insert type link.
     *
     * @param  $info: default detail info
     *
     * @return bool true:success,false:failed
     */
    public function insertLink($info, &$insertId)
    {
        $sql = "INSERT INTO $this->typelinktable (item_type_id, group_id,  weight, edit, edit_weight, released)"
            .' VALUES ('.Xoonips_Utils::convertSQLStr($info['item_type_id']).','
            .Xoonips_Utils::convertSQLStr($info['group_id']).','
            .Xoonips_Utils::convertSQLNum($info['weight']).','
            .Xoonips_Utils::convertSQLNum($info['edit']).','
            .Xoonips_Utils::convertSQLNum($info['edit_weight']).','
            .Xoonips_Utils::convertSQLNum($info['released']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $insertId = $this->getInsertId();

        return true;
    }

    /**
     * update type link weight.
     *
     * @param  $typeId, $groupId, $weight
     *
     * @return bool true:success,false:failed
     */
    public function updateWeightForLink($typeId, $groupId, $weight)
    {
        $typeId = Xoonips_Utils::convertSQLNum($typeId);
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $weight = Xoonips_Utils::convertSQLNum($weight);
        $sql = "UPDATE $this->typelinktable SET edit_weight=$weight"
        ." WHERE item_type_id=$typeId AND group_id=$groupId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update type link edit.
     *
     * @param  $typeId, $groupId, $edit
     *
     * @return bool true:success,false:failed
     */
    public function updateLinkEdit($typeId, $groupId, $edit = 0)
    {
        $typeId = Xoonips_Utils::convertSQLNum($typeId);
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $edit = Xoonips_Utils::convertSQLNum($edit);
        $sql = "UPDATE $this->typelinktable SET edit=$edit"
        ." WHERE item_type_id=$typeId AND group_id=$groupId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update type link sync.
     *
     * @param  $typeId, $release
     *
     * @return bool true:success,false:failed
     */
    public function updateLinkSync($typeId, $release = false)
    {
        $typeId = Xoonips_Utils::convertSQLNum($typeId);
        $sql2 = 'edit=released,edit_weight=weight';
        if ($release) {
            $sql2 = 'released=edit,weight=edit_weight';
        }
        $sql = "UPDATE $this->typelinktable SET $sql2"
        ." WHERE item_type_id=$typeId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * do copy link by id.
     *
     * @param  $base_id, $map
     *
     * @return bool true:success,false:failed
     */
    public function copyLinkById($base_id, &$map)
    {
        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);

        $itemtypeId = $map['itemtype'][$base_id];
        $groups = $this->getTypeGroups($base_id);
        foreach ($groups as $group) {
            $gid = $group['group_id'];
            $gxml = $group['xml'];
            $group['item_type_id'] = $itemtypeId;
            $group['released'] = $group['link_release'];

            $map['group'][$gxml] = $gid;

            $details = $detailBean->getGroupDetails($gid);
            foreach ($details as $detail) {
                $gdxml = $gxml.':'.$detail['xml'];
                $map['detail'][$gdxml] = $detail['item_field_detail_id'];
            }

            if (!$this->insertLink($group, $insertId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * get all item_type_id.
     *
     * @param
     *
     * @return array item_type_id
     **/
    public function getAllItemTypeId()
    {
        $sql = "SELECT item_type_id FROM $this->table";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row['item_type_id'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }
}
