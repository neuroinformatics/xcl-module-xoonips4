<?php

require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/BeanBase.class.php';

/**
 * @brief operate xoonips_item_field_group table
 */
class Xoonips_ItemFieldGroupBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('item_field_group', true);

        $this->grouplinktable = $this->prefix($this->modulePrefix('item_field_group_field_detail_link'));
        $this->detailtable = $this->prefix($this->modulePrefix('item_field_detail'));
    }

    /**
     * get item type groups by itemtypeid.
     *
     * @param item type id
     *
     * @return array
     */
    public function getItemTypeGroups($itemtypeId)
    {
        $sql = 'SELECT * FROM '.$this->table." WHERE item_type_id=$itemtypeId ORDER BY group_id";
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
         * get item group data for Export Item Type XML Element.
         *
         * @param item type id
         *
         * @return array
         */
        public function getExportItemTypeGroup($itemtypeId)
        {
            $sql = 'SELECT '.$this->prefix($this->modulePrefix('item_type_field_group_link')).'.group_id, ';
            $sql .= $this->table.'.name, ';
            $sql .= $this->table.'.xml, ';
            $sql .= $this->table.'.occurrence ';
            $sql .= 'FROM '.$this->prefix($this->modulePrefix('item_type_field_group_link')).', '.$this->table.' ';
            $sql .= 'WHERE '.$this->prefix($this->modulePrefix('item_type_field_group_link')).'.item_type_id='.$itemtypeId.' ';
            $sql .= 'AND '.$this->prefix($this->modulePrefix('item_type_field_group_link')).'.group_id='.$this->table.'.group_id';
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
     * get default item type group list for ng.
     *
     * @param bool $preselect_flg
     *
     * @return default itemtype group list
     */
    public function getDefaultItemTypeGroup($preselect_flg = true)
    {
        $ret = array();
        $sql = 'SELECT * FROM '.$this->table;
        if ($preselect_flg) {
            $sql .= ' WHERE preselect=1';
        }
        $sql .= ' ORDER BY weight';
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
     * insert new itemtype group.
     *
     * @param  $info: itemtype group info
     *
     * @return bool true:success,false:failed
     */
    public function insert($info, &$insertId)
    {
        $sql = "INSERT INTO $this->table (released, preselect, item_type_id, name, xml, weight, occurrence, update_id)";
        $sql .= ' VALUES ('.Xoonips_Utils::convertSQLNum($info['released']).','
            .Xoonips_Utils::convertSQLNum($info['preselect']).','
            .Xoonips_Utils::convertSQLNum($info['item_type_id']).','
            .Xoonips_Utils::convertSQLStr($info['name']).','
            .Xoonips_Utils::convertSQLStr($info['xml']).','
            .Xoonips_Utils::convertSQLNum($info['weight']).','
            .Xoonips_Utils::convertSQLNum($info['occurrence']).','
            .Xoonips_Utils::convertSQLNum($info['update_id']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $insertId = $this->getInsertId();

        return true;
    }

    /**
     * insert default group.
     *
     * @param  $info: default group info
     *
     * @return bool true:success,false:failed
     */
    public function insertDefault($info, &$insertId)
    {
        $table = $this->prefix($this->modulePrefix('default_item_field_group'));
        $sql = "INSERT INTO $table (name, xml, weight, occurrence)";
        $sql .= ' VALUES ('.Xoonips_Utils::convertSQLStr($info['name']).','
            .Xoonips_Utils::convertSQLStr($info['xml']).','
            .Xoonips_Utils::convertSQLNum($info['weight']).','
            .Xoonips_Utils::convertSQLNum($info['occurrence']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $insertId = $this->getInsertId();

        return true;
    }

    /**
     * get itemtype group edit info.
     *
     * @param  $itemtypeId: itemtype id
     *
     * @return item type group information
     */
    public function getItemtypeGroupEditInfo($itemtypeId)
    {
        $ret = array();
        $sql = "SELECT a.* FROM $this->table a LEFT JOIN $this->table b ON a.update_id=b.group_id WHERE a.item_type_id=$itemtypeId ORDER BY a.weight";
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
     * @param  $group_id, $weight
     *
     * @return bool true:success,false:failed
     */
    public function updateWeight($groupId, $weight)
    {
        $sql = "UPDATE $this->table SET weight=$weight WHERE group_id=$groupId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete.
     *
     * @param  $group_id,
     *
     * @return bool true:success,false:failed
     */
    public function delete($groupId)
    {
        $sql = "DELETE FROM $this->table WHERE group_id=$groupId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        $sql = "DELETE FROM $this->table WHERE update_id=$groupId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete by itemtpye_id.
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
     * update itemtype group.
     *
     * @param  $itemtypeId: itemtype id
     *
     * @return bool true:success,false:failed
     */
    public function updateNewGroup($base_itemtypeid, $copy_itemtypeid)
    {
        $sql = "UPDATE $this->table SET "
            .' released = 1 '
            .", item_type_id = $base_itemtypeid ";
        $sql .= " WHERE item_type_id=$copy_itemtypeid AND released=0 AND update_id IS NULL";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update itemtype group.
     *
     * @param  $itemtypeId: itemtype id
     *
     * @return bool true:success,false:failed
     */
    public function updateCopyToBaseGroup($itemtypeId)
    {
        $sql = "UPDATE $this->table t1, $this->table t2 SET "
            .' t1.weight = t2.weight '
            .', t1.name = t2.name '
            .', t1.xml = t2.xml '
            .', t1.occurrence = t2.occurrence ';
        $sql .= " WHERE t1.group_id=t2.update_id AND t2.item_type_id=$itemtypeId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete itemtype group.
     *
     * @param  $itemtypeId: itemtype id
     *
     * @return bool true:success,false:failed
     */
    public function deleteCopyItemtypeGroup($itemtypeId)
    {
        $sql = "DELETE FROM $this->table WHERE item_type_id=$itemtypeId AND update_id IS NOT NULL";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get max group weight.
     *
     * @param  itemtype_id
     *
     * @return max group weight
     */
    public function getMaxGroupWeight($itid)
    {
        $sql = 'SELECT MAX(weight) AS maxWeight FROM '.$this->table." WHERE item_type_id=$itid";
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
     * update group.
     *
     * @param  $info: group info
     *
     * @return bool true:success,false:failed
     */
    public function update($info, $gid)
    {
        $sql = "UPDATE $this->table SET "
            .'  name = '.Xoonips_Utils::convertSQLStr($info['name'])
            .', xml = '.Xoonips_Utils::convertSQLStr($info['xml'])
            .', occurrence = '.Xoonips_Utils::convertSQLNum($info['occurrence']);
        $sql .= " WHERE group_id=$gid";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    // get detail list
    public function getDetailList($itemtypeid, $baseId, $groupId = 0)
    {
        $detailTable = $this->prefix($this->modulePrefix('item_field_detail'));

        $groupTable = $this->prefix($this->modulePrefix('item_field_group'));
        $glinkTable = $this->prefix($this->modulePrefix('item_field_group_field_detail_link'));
        $tlinkTable = $this->prefix($this->modulePrefix('item_type_field_group_link'));

        $sql_group = '';
        if ($groupId > 0) {
            $sql_group = " AND lg.group_id=$groupId";
        }

        $sql = 'SELECT dt.item_field_detail_id, dt.name as detail_name, gt.name as group_name'
        .", lg.group_id FROM $detailTable dt"
        ." LEFT JOIN $glinkTable lg"
        .' ON  dt.item_field_detail_id=lg.item_field_detail_id'
        ." LEFT JOIN $groupTable gt"
        .' ON lg.group_id=gt.group_id'
        ." LEFT JOIN $tlinkTable lt"
        .' ON  lg.group_id=lt.group_id'
        ." WHERE lt.item_type_id=${itemtypeid} AND dt.nondisplay=0"
        .$sql_group
        ." AND dt.item_field_detail_id<>${baseId} ORDER BY dt.weight";

        $result = $this->execute($sql);
        $ret = array();
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    // get countItemgroups
    public function countItemgroups()
    {
        $sql = 'SELECT group_id FROM '.$this->table.' WHERE update_id IS NULL';
        $result = $this->execute($sql);
        $ret = 0;
        if (!$result) {
            return $ret;
        }
        $ret = $this->getRowsNum($result);
        $this->freeRecordSet($result);

        return $ret;
    }

    // getItemgrouplist
    public function getItemgrouplist($limit = 0, $start = 0)
    {
        $ret = array();
        $sql = 'SELECT at.*, bt.update_id as upid FROM '.$this->table.' at LEFT JOIN '.$this->table.' bt'
        .' ON at.group_id=bt.update_id WHERE at.update_id IS NULL '
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
     * check exist item_type group name.
     *
     * @param  group_id, name, base_gid
     *
     * @return bool true:success,false:failed
     */
    public function existGroupName($gid, $gname, $base_gid = 0)
    {
        $sql = 'SELECT name FROM '.$this->table." WHERE group_id<>$gid AND group_id<>$base_gid"
        .' AND name='.Xoonips_Utils::convertSQLStr($gname);
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
     * check exist item_type group xml.
     *
     * @param  group_id, xml, base_gid
     *
     * @return bool true:success,false:failed
     */
    public function existGroupXml($gid, $gxml, $base_gid = 0)
    {
        $sql = 'SELECT xml FROM '.$this->table." WHERE group_id<>$gid AND group_id<>$base_gid"
        .' AND xml='.Xoonips_Utils::convertSQLStr($gxml);
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
    public function copyById($itemgroupId, &$map, $update = false, $import = false)
    {

        // get copy information
        $groupObj = $this->getItemGroup($itemgroupId);
        if (!$groupObj) {
            return false;
        }

        // do copy by obj
        return $this->copyByObj($groupObj, $map, $update, $import);
    }

    /**
     * do copy by obj.
     *
     * @param  $groupObj, $map
     *
     * @return bool true:success,false:failed
     */
    public function copyByObj($groupObj, &$map, $update, $import)
    {

        // insert copy
        foreach ($groupObj as $group) {
            $group['released'] = $import ? $group['released'] : 0;
            $group['item_type_id'] = (is_numeric($group['item_type_id'])) ? $group['item_type_id'] : 0;
            $group['update_id'] = $update ? $group['group_id'] : null;

            $insertId = null;
            $group_info = $this->getGroupByXml($group['xml']);
            if (!$update && count($group_info) > 0) {
                $insertId = $group_info['group_id'];
            } else {
                if (!$this->insert($group, $insertId)) {
                    return false;
                }
            }

            $map['group'][$group['group_id']] = $insertId;
        }

        return true;
    }

    /**
     * get item type groups by itemtypeid.
     *
     * @param item type id
     *
     * @return array
     */
    public function getItemGroup($itemgroupId)
    {
        $sql = 'SELECT * FROM '.$this->table." WHERE group_id=$itemgroupId";
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
     * get itemtype group edit info.
     *
     * @param  $gid: group id (released)
     * @param  $type_flg: item type flg
     *
     * @return item type group information
     */
    public function getGroupEditInfo($gid, $type_flg = false)
    {
        $sql = 'SELECT a.preselect as a_preselect, b.preselect as b_preselect, '
            .' a.released as a_released, b.released as b_released, '
            .' a.name as a_name, b.name as b_name, '
            .' a.xml as a_xml, b.xml as b_xml, '
            .' a.occurrence as a_occurrence, b.occurrence as b_occurrence, '
            .' a.weight as a_weight, b.weight as b_weight, '
            .' a.update_id as a_update_id, b.update_id as b_update_id, '
            .' a.group_id as a_group_id, b.group_id as b_group_id ';
        if ($type_flg) {
            $sql .= " FROM $this->table a LEFT JOIN $this->table b ON a.update_id=b.group_id WHERE a.group_id=$gid";
        } else {
            $sql .= " FROM $this->table a LEFT JOIN $this->table b ON a.group_id=b.update_id WHERE a.group_id=$gid";
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
     * @param  $group_id, $base_groupid
     *
     * @return bool true:success,false:failed
     */
    public function release($group_id, $base_groupid)
    {
        if ($group_id == $base_groupid) {
            $sql = "UPDATE $this->table SET released = 1 WHERE group_id=$group_id";
        } else {
            $sql = "UPDATE $this->table t1, $this->table t2 SET "
            .' t1.weight = t2.weight '
            .', t1.name = t2.name '
            .', t1.xml = t2.xml '
            .', t1.occurrence = t2.occurrence ';
            $sql .= " WHERE t1.group_id=t2.update_id AND t2.group_id=$group_id";
        }
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        // release detail link
        if (!self::updateLinkSync($base_groupid, true)) {
            return false;
        }

        return true;
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
        $sql = 'SELECT d.item_field_detail_id,d.name,d.xml,g.weight,g.edit_weight'
        .' ,g.edit,g.released as link_release FROM '.$this->detailtable.' AS d'
        .' LEFT JOIN '.$this->grouplinktable.' AS g ON d.item_field_detail_id=g.item_field_detail_id'
        ." WHERE g.group_id=$groupId ORDER BY g.edit_weight";
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
     * insert group link.
     *
     * @param  $info: default detail info
     *
     * @return bool true:success,false:failed
     */
    public function insertLink($info, &$insertId)
    {
        $sql = "INSERT INTO $this->grouplinktable (group_id, item_field_detail_id, weight, edit, edit_weight, released)"
            .' VALUES ('.Xoonips_Utils::convertSQLStr($info['group_id']).','
            .Xoonips_Utils::convertSQLStr($info['item_field_detail_id']).','
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
     * update group link weight.
     *
     * @param  $groupid, $detail_id, $weight
     *
     * @return bool true:success,false:failed
     */
    public function updateWeightForLink($groupid, $detailId, $weight)
    {
        $sql = "UPDATE $this->grouplinktable SET edit_weight=$weight"
        ." WHERE group_id=$groupid AND item_field_detail_id=$detailId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update group link released.
     *
     * @param  $groupid, $release
     *
     * @return bool true:success,false:failed
     */
    public function updateLinkRelease($groupid, $release = 0)
    {
        $sql = "UPDATE $this->grouplinktable SET released=$release"
        ." WHERE group_id=$groupid";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update group link edit.
     *
     * @param  $groupid, $detail_id, $edit
     *
     * @return bool true:success,false:failed
     */
    public function updateLinkEdit($groupid, $detailId, $edit = 0)
    {
        $sql = "UPDATE $this->grouplinktable SET edit=$edit"
        ." WHERE group_id=$groupid AND item_field_detail_id=$detailId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update group link sync.
     *
     * @param  $groupid, $release
     *
     * @return bool true:success,false:failed
     */
    public function updateLinkSync($groupid, $release = false)
    {
        $sql2 = 'edit=released,edit_weight=weight';
        if ($release) {
            $sql2 = 'released=edit,weight=edit_weight';
        }
        $sql = "UPDATE $this->grouplinktable SET $sql2"
        ." WHERE group_id=$groupid";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete group link.
     *
     * @param  $groupid
     *
     * @return bool true:success,false:failed
     */
    public function deleteLink($groupid)
    {
        $sql = "DELETE FROM $this->grouplinktable WHERE group_id=$groupid";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get group by detail id.
     *
     * @param $groupId
     *
     * @return itemtype detail list
     */
    public function getGroupByDetailId($detailid)
    {
        $ret = array();
        $sql = "SELECT g.* FROM $this->table AS g"
        ." LEFT JOIN $this->grouplinktable AS l ON g.group_id=l.group_id"
        ." WHERE l.item_field_detail_id=$detailid AND l.edit=1";
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

    public function getDetailIdbyXml($xml)
    {
        $ret = array();
        $sql = 'select item_field_detail_id from '.$this->table.' AS g,';
        $sql .= $this->grouplinktable.' AS l where ';
        $sql .= ' g.released = 1 and l.released = 1 and';
        $sql .= ' g.group_id = l.group_id and xml='.Xoonips_Utils::convertSQLStr($xml);
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row['item_field_detail_id'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get group by xml.
     *
     * @param  string xml
     *
     * @return array ret
     */
    public function getGroupByXml($xml)
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
     * get group detail by id.
     *
     * @param int groupId, int detailId
     *
     * @return array ret
     */
    public function getGroupDetailById($groupId, $detailId)
    {
        $ret = array();
        $sql = 'SELECT * FROM '.$this->grouplinktable
        ." WHERE group_id=$groupId AND item_field_detail_id=$detailId";
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }
}
