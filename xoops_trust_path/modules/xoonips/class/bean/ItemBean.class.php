<?php

/**
 * @brief operate xoonips_item table
 */
class Xoonips_ItemBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('item', true);
        $this->typelinktable = $this->prefix($this->modulePrefix('item_type_field_group_link'));
        $this->grouplinktable = $this->prefix($this->modulePrefix('item_field_group_field_detail_link'));
        $this->detailtable = $this->prefix($this->modulePrefix('item_field_detail'));
        $this->grouptable = $this->prefix($this->modulePrefix('item_field_group'));
    }

    /**
     * get item basic information by id.
     *
     * @param int $id:item_id
     *
     * @return array
     **/
    public function getItemBasicInfo($id)
    {
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `item_id`='.intval($id);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);
        $this->freeRecordSet($result);

        return $row;
    }

    /**
     * get item basic information by doi.
     *
     * @param string $doi:doi
     *
     * @return array
     **/
    public function getBydoi($doi)
    {
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `doi`='.Xoonips_Utils::convertSQLStr($doi);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);
        $this->freeRecordSet($result);

        return $row;
    }

    /**
     * getBydoi method has bug!
     * if fetch empty record set ,then retunrn false.
     * But upper function expect array().
     *
     * @param type $doi
     *
     * @return type
     */
    public function getBydoi2($doi)
    {
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `doi`='.Xoonips_Utils::convertSQLStr($doi);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);
        $this->freeRecordSet($result);
        if (is_bool($row) && false === $row) {
            return [];
        }

        return $row;
    }

    /**
     * get doi information by item id.
     *
     * @param string $doi:doi
     *
     * @return item id
     **/
    public function getItemIdBydoi($doi)
    {
        $row = $this->getBydoi2($doi);
        if (empty($row)) {
            return false;
        }

        return $row['item_id'];
    }

    /**
     * check exist doi.
     *
     * @param blob $doi:doi
     *
     * @return bool true:success, false:failed
     **/
    public function checkExistdoi($itemid, $doi)
    {
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `item_id`<>'.intval($itemid).' AND `doi`='.Xoonips_Utils::convertSQLStr($doi);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = false;
        while ($row = $this->fetchArray($result)) {
            $ret = true;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * update the View Count by id.
     *
     * @param int $id:item_id
     *
     * @return bool true:success,false:failed
     */
    public function updateViewCount($id)
    {
        $sql = 'UPDATE `'.$this->table.'` SET `view_count`=`view_count`+1 WHERE `item_id`='.intval($id);
        $result = $this->execute($sql);

        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete by id.
     *
     * @param int $id:item_id
     *
     * @return bool true:success,false:failed
     */
    public function delete($id)
    {
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `item_id`='.intval($id);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    public function groupby($item_ids)
    {
        $ret = [];
        $imploded_ids = '';
        for ($i = 0; $i < count($item_ids); ++$i) {
            $imploded_ids .= intval($item_ids[$i]);
            if ($i < count($item_ids) - 1) {
                $imploded_ids .= ',';
            }
        }
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `item_id` IN('.$imploded_ids.')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[$row['item_type_id']][] = $row['item_id'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * check title.
     *
     * @param var $name:name
     *                       var $title:title
     *
     * @return bool
     */
    public function checkItemtype($itemtypeid)
    {
        $sql = 'SELECT COUNT(`item_id`) AS `cnt` FROM `'.$this->table.'` WHERE `item_type_id`='.intval($itemtypeid);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = $this->fetchArray($result);
        $this->freeRecordSet($result);

        return $ret['cnt'];
    }

    /**
     * check item field.
     *
     * @param var $name:name
     *                       var $title:title
     *
     * @return bool
     */
    public function checkItemfield($itemfieldid)
    {
        $sql = 'SELECT COUNT(`i`.`item_id`) AS `cnt` FROM `'.$this->table.'` AS `i`';
        $sql .= ' LEFT JOIN `'.$this->typelinktable.'` AS `g` ON `i`.`item_type_id`=`g`.`item_type_id`';
        $sql .= ' LEFT JOIN `'.$this->grouplinktable.'` AS `d` ON `g`.`group_id`=`d`.`group_id`';
        $sql .= ' WHERE `d`.`item_field_detail_id`='.intval($itemfieldid);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = $this->fetchArray($result);

        $this->freeRecordSet($result);

        return $ret['cnt'];
    }

    /**
     * check item group.
     *
     * @param var $name:name
     *                       var $title:title
     *
     * @return bool
     */
    public function checkItemgroup($itemgroupid)
    {
        $sql = 'SELECT COUNT(`i`.`item_id`) AS `cnt` FROM `'.$this->table.'` AS `i`';
        $sql .= ' LEFT JOIN `'.$this->typelinktable.'` AS `g` ON `i`.`item_type_id`=`g`.`item_type_id`';
        $sql .= ' WHERE `g`.`group_id`='.intval($itemgroupid);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = $this->fetchArray($result);

        $this->freeRecordSet($result);

        return $ret['cnt'];
    }

    /**
     * get item_field_detail values from item_id.
     *
     * @param int $item_id
     *
     * @return more than 1 array:success, 0 array:fail
     */
    public function getItemTypeDetails($item_id)
    {
        $sql = 'SELECT `detail`.* FROM `'.$this->typelinktable.'` AS `link`,';
        $sql .= '`'.$this->table.'` AS `item`,`'.$this->grouplinktable.'` AS `grp`,';
        $sql .= '`'.$this->detailtable.'` AS `detail`';
        $sql .= ' WHERE `item`.`item_type_id`=`link`.`item_type_id`';
        $sql .= ' AND `link`.`group_id`=`grp`.`group_id`';
        $sql .= ' AND `detail`.`item_field_detail_id`=`grp`.`item_field_detail_id`';
        $sql .= ' AND `item_id`='.intval($item_id);

        $result = $this->execute($sql);
        $ret = [];
        if (!$result) {
            return [];
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * Get ItemGroup by item_id.
     *
     * @param int $item_id
     *
     * @return array item_file
     */
    public function getItemFieldGroup($item_id)
    {
        $sql = 'SELECT `grp`.*,`item_field_detail_id`';
        $sql .= ' FROM `'.$this->typelinktable.'` AS `link`,`'.$this->table.'` AS `item`,';
        $sql .= '`'.$this->grouplinktable.'` AS `grplnk`,`'.$this->grouptable.'` AS `grp`';
        $sql .= ' WHERE `item`.`item_type_id`=`link`.`item_type_id` AND `link`.`group_id`=`grp`.`group_id`';
        $sql .= ' AND `link`.`group_id`=`grplnk`.`group_id` AND `grplnk`.`released`=1 AND `grp`.`released`=1';
        $sql .= ' AND `link`.`released`=1 AND `item_id`='.intval($item_id);
        $result = $this->execute($sql);
        $ret = [];
        if (!$result) {
            return [];
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }
}
