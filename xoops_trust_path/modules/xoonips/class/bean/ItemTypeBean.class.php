<?php

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
        $ret = [];
        $id = Xoonips_Utils::convertSQLNum($id);
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `item_type_id`='.intval($id);
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
        $ret = [];
        $name = Xoonips_Utils::convertSQLStr($name);
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `name`='.$name;
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
        $ret = [];
        $id = Xoonips_Utils::convertSQLNum($id);
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `released`=1 AND `item_type_id`='.intval($id);
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
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `released`=1 ORDER BY `weight`';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = [];
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
        $sql = 'SELECT `name` FROM `'.$this->table.'` WHERE `item_type_id`='.intval($id);
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
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `update_id` IS NULL ORDER BY `weight`';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = [];
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
        $sql = 'UPDATE `'.$this->table.'` SET `weight`='.intval($weight).' WHERE `item_type_id`='.intval($id);
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
        $sql = 'SELECT `name` FROM `'.$this->table.'` WHERE `item_type_id`<>'.intval($id).' AND `name`='.$name;
        if (0 != $id) {
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
        $sql = 'INSERT INTO `'.$this->table.'` (`preselect`, `released`, `weight`, `name`, ';
        $sql .= ' `description`, `icon`, `mime_type`, `template`, `update_id`) VALUES (';
        $sql .= intval($info['preselect']).', '.intval($info['released']).', '.intval($info['weight']).', ';
        $sql .= Xoonips_Utils::convertSQLStr($info['name']).', ';
        $sql .= Xoonips_Utils::convertSQLStr($info['description']).', ';
        $sql .= Xoonips_Utils::convertSQLStr($info['icon']).', ';
        $sql .= Xoonips_Utils::convertSQLStr($info['mime_type']).', ';
        $sql .= Xoonips_Utils::convertSQLStr($info['template']).', ';
        $sql .= intval($info['update_id']).')';
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
        $sql = 'UPDATE `'.$this->table.'` SET ';
        $sql .= ' `weight`='.intval($info['weight']);
        $sql .= ', `name`='.Xoonips_Utils::convertSQLStr($info['name']);
        $sql .= ', `released`='.Xoonips_Utils::convertSQLStr($info['released']);
        $sql .= ', `description`='.Xoonips_Utils::convertSQLStr($info['description']);
        $sql .= ', `template`='.Xoonips_Utils::convertSQLStr($info['template']);
        if ($hasIcon) {
            $sql .= ', `icon`='.Xoonips_Utils::convertSQLStr($info['icon']);
            $sql .= ', `mime_type`='.Xoonips_Utils::convertSQLStr($info['mime_type']);
        }
        $sql .= ' WHERE `item_type_id`='.intval($itemtypeId);
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
        $sql = 'SELECT `a`.`preselect` AS `a_preselect`, `b`.`preselect` AS `b_preselect`, ';
        $sql .= ' `a`.`released` AS `a_released`, `b`.`released` AS `b_released`, ';
        $sql .= ' `a`.`weight` AS `a_weight`, `b`.`weight` AS `b_weight`, ';
        $sql .= ' `a`.`name` AS `a_name`, `b`.`name` AS `b_name`, ';
        $sql .= ' `a`.`description` AS `a_description`, `b`.`description` AS `b_description`, ';
        $sql .= ' `a`.`icon` AS `a_icon`, `b`.`icon` AS `b_icon`, ';
        $sql .= ' `a`.`mime_type` AS `a_mime_type`, `b`.`mime_type` AS `b_mime_type`, ';
        $sql .= ' `a`.`template` AS `a_template`, `b`.`template` AS `b_template`, ';
        $sql .= ' `a`.`update_id` AS `a_update_id`, `b`.`update_id` AS `b_update_id`, ';
        $sql .= ' `a`.`item_type_id` AS `a_item_type_id`, `b`.`item_type_id` AS `b_item_type_id` ';
        $sql .= ' FROM `'.$this->table.'` `a` LEFT JOIN `'.$this->table.'` `b` ON `a`.`item_type_id`=`b`.`update_id` ';
        $sql .= ' WHERE `a`.`item_type_id`='.intval($itemtypeId);
        $sql .= ' AND `a`.`update_id` IS NULL';
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
        $sql = 'UPDATE `'.$this->table.'` `t1`, `'.$this->table.'` `t2` ';
        $sql .= ' SET `t1`.`weight`=`t2`.`weight`, ';
        $sql .= ' `t1`.`name`=`t2`.`name`, ';
        $sql .= ' `t1`.`description`=`t2`.`description`, ';
        $sql .= ' `t1`.`icon`=`t2`.`icon`, ';
        $sql .= ' `t1`.`mime_type`=`t2`.`mime_type`, ';
        $sql .= ' `t1`.`template`=`t2`.`template` ';
        $sql .= ' WHERE `t1`.`item_type_id`=`t2`.`update_id` AND `t2`.`item_type_id`='.$itemtypeId;
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
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `item_type_id`='.$itemtypeId.' AND `update_id` IS NOT NULL';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        $sql = "DELETE FROM `' .$this->table. '` WHERE `update_id`=' .$itemtypeId";
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
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `item_type_id`='.$itemtypeId;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    // get itemtype objs
    public function itemtypeGetItemtypelist($limit = 0, $start = 0)
    {
        $ret = [];
        $limit = Xoonips_Utils::convertSQLNum($limit);
        $start = Xoonips_Utils::convertSQLNum($start);
        $sql = 'SELECT `at`.*, `bt`.`update_id` AS `upid` FROM `'.$this->table.'` `at` LEFT JOIN `'.$this->table.'` `bt` ';
        $sql .= ' ON `at`.`item_type_id`=`bt`.`update_id` WHERE `at`.`update_id` IS NULL ORDER BY `at`.`weight`';
        $sql .= ' LIMIT '.intval($start).', '.intval($limit);
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
        $sql = 'SELECT `at`.`item_type_id` FROM `'.$this->table.'` `at` LEFT JOIN `'.$this->table.'` `bt` ';
        $sql .= ' ON `at`.`item_type_id`=`bt`.`update_id` WHERE `at`.`update_id` IS NULL';
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
        $ret = [];
        $typeId = Xoonips_Utils::convertSQLNum($typeId);
        $sql = 'SELECT `g`.`group_id`,`g`.`name`,`g`.`xml`,`t`.`weight`,`t`.`edit_weight` ';
        $sql .= ' ,`t`.`edit`,`t`.`released` AS `link_release` FROM `'.$this->grouptable.'` AS `g` ';
        $sql .= ' LEFT JOIN `'.$this->typelinktable.'` AS `t` ON `g`.`group_id`=`t`.`group_id` ';
        $sql .= ' WHERE `t`.`item_type_id`='.$typeId.' ORDER BY `t`.`edit_weight`';
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
        $ret = [];
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $sql = 'SELECT `t`.* FROM `'.$this->table.'` AS `t`';
        $sql .= ' LEFT JOIN `'.$this->typelinktable.'` AS `l` ON `t`.`item_type_id`=`l`.`item_type_id`';
        $sql .= ' WHERE `l`.`group_id`='.$groupId.' AND `l`.`edit`=1';
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
        $sql = 'INSERT INTO `'.$this->typelinktable.'`';
        $sql .= ' (`item_type_id`, `group_id`, `weight`, `edit`, `edit_weight`, `released`)';
        $sql .= ' VALUES ('.Xoonips_Utils::convertSQLStr($info['item_type_id']).',';
        $sql .= Xoonips_Utils::convertSQLStr($info['group_id']).',';
        $sql .= intval($info['weight']).','.intval($info['edit']).',';
        $sql .= intval($info['edit_weight']).','.intval($info['released']).')';
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
        $sql = 'UPDATE `'.$this->typelinktable.'` SET `edit_weight`='.intval($weight);
        $sql .= ' WHERE `item_type_id`='.intval($typeId).' AND `group_id`='.intval($groupId);
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
        $sql = 'UPDATE `'.$this->typelinktable.'` SET `edit`='.intval($edit);
        $sql .= ' WHERE `item_type_id`='.intval($typeId).' AND `group_id`='.intval($groupId);
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
        $sql2 = '`edit`=`released`,`edit_weight`=`weight`';
        if ($release) {
            $sql2 = '`released`=`edit`,`weight`=`edit_weight`';
        }
        $sql = 'UPDATE `'.$this->typelinktable.'` SET '.$sql2.' WHERE `item_type_id`='.intval($typeId);
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
        $sql = 'SELECT `item_type_id` FROM `'.$this->table.'`';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = [];
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row['item_type_id'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }
}
