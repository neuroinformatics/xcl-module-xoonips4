<?php

/**
 * @brief operate xoonips_user_field_group table
 */
class Xoonips_UserFieldGroupBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('user_field_group', false);

        $this->grouplinktable = $this->prefix('user_field_group_field_detail_link');
        $this->detailtable = $this->prefix('user_field_detail');
        $this->defaulttable = $this->prefix('default_user_field_group');
    }

    /**
     * get user type groups by usertypeid.
     *
     * @param int $usertypeId user type id
     *
     * @return array user field group
     */
    public function getUserTypeGroups($usertypeId)
    {
        $usertypeId = Xoonips_Utils::convertSQLNum($usertypeId);
        $sql = "SELECT * FROM $this->table"
            ." WHERE user_type_id=$usertypeId"
            .' ORDER BY group_id';
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
     * get default user type group list.
     *
     * @return array default usertype group list
     */
    public function getDefaultUserTypeGroup()
    {
        $sql = "SELECT * FROM $this->defaulttable"
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
     * insert new usertype group.
     *
     * @param array $info     usertype group info
     * @param int   $insertId reference of insert id
     *
     * @return bool true:success,false:failed
     */
    public function insert($info, &$insertId)
    {
        $sql = "INSERT INTO $this->table ("
            .' released,'
            .' preselect,'
            .' name,'
            .' xml,'
            .' weight,'
            .' occurrence,'
            .' update_id )';
        $sql .= ' VALUES ('
            .Xoonips_Utils::convertSQLNum($info['released']).','
            .Xoonips_Utils::convertSQLNum($info['preselect']).','
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
     * insert default usertype group.
     *
     * @param array $info     default group info
     * @param int   $insertId reference of insert id
     *
     * @return bool true:success,false:failed
     */
    public function insertDefault($info, &$insertId)
    {
        $sql = "INSERT INTO $this->defaulttable ("
            .' name,'
            .' xml,'
            .' weight,'
            .' occurrence )';
        $sql .= ' VALUES ('
            .Xoonips_Utils::convertSQLStr($info['name']).','
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
     * get usertype group edit info.
     *
     * @param int $usertypeId usertype id
     *
     * @return array user type group information
     */
    public function getUsertypeGroupEditInfo($usertypeId)
    {
        $usertypeId = Xoonips_Utils::convertSQLNum($usertypeId);
        $sql = "SELECT a.* FROM $this->table a"
            ." LEFT JOIN $this->table b"
            .' ON a.update_id=b.group_id'
            ." WHERE a.user_type_id=$usertypeId"
            .' ORDER BY a.weight';
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
     * @param int $groupId
     * @param int $weight
     *
     * @return bool true:success,false:failed
     */
    public function updateWeight($groupId, $weight)
    {
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $weight = Xoonips_Utils::convertSQLNum($weight);
        $sql = "UPDATE $this->table"
            ." SET weight=$weight"
            ." WHERE group_id=$groupId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete user field group by group_id.
     *
     * @param int $groupId
     *
     * @return bool true:success,false:failed
     */
    public function delete($groupId)
    {
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $sql = "DELETE FROM $this->table"
            ." WHERE group_id=$groupId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        $sql = "DELETE FROM $this->table"
            ." WHERE update_id=$groupId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete user field group by usertpye_id.
     *
     * @param int $usertypeId
     *
     * @return bool true:success,false:failed
     */
    public function deleteByUsertypeId($usertypeId)
    {
        $usertypeId = Xoonips_Utils::convertSQLNum($usertypeId);
        $sql = "DELETE FROM $this->table"
            ." WHERE user_type_id=$usertypeId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update usertype group.
     *
     * @param int $usertypeId usertype id
     *
     * @return bool true:success,false:failed
     */
    public function updateNewGroup($base_usertypeId, $copy_usertypeId)
    {
        $base_usertypeId = Xoonips_Utils::convertSQLNum($base_usertypeId);
        $copy_usertypeId = Xoonips_Utils::convertSQLNum($copy_usertypeId);
        $sql = "UPDATE $this->table"
            .' SET released = 1 ,'
            ." user_type_id = $base_usertypeId"
            ." WHERE user_type_id=$copy_usertypeId"
            .' AND released=0'
            .' AND update_id IS NULL';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update usertype group.
     *
     * @param int $usertypeId usertype id
     *
     * @return bool true:success,false:failed
     */
    public function updateCopyToBaseGroup($usertypeId)
    {
        $usertypeId = Xoonips_Utils::convertSQLNum($usertypeId);
        $sql = "UPDATE $this->table t1, $this->table t2 SET "
            .' t1.weight = t2.weight ,'
            .' t1.name = t2.name ,'
            .' t1.xml = t2.xml ,'
            .' t1.occurrence = t2.occurrence '
            .' WHERE t1.group_id=t2.update_id'
            ." AND t2.user_type_id=$usertypeId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete usertype group.
     *
     * @param int $usertypeId usertype id
     *
     * @return bool true:success,false:failed
     */
    public function deleteCopyUsertypeGroup($usertypeId)
    {
        $usertypeId = Xoonips_Utils::convertSQLNum($usertypeId);
        $sql = "DELETE FROM $this->table"
            ." WHERE user_type_id=$usertypeId"
            .' AND update_id IS NOT NULL';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get max group weight.
     *
     * @return int max group weight
     */
    public function getMaxGroupWeight()
    {
        $sql = "SELECT MAX(weight) AS maxWeight FROM $this->table";
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
     * @param array $info group info
     *
     * @return bool true:success,false:failed
     */
    public function update($info, $gid)
    {
        $name = Xoonips_Utils::convertSQLStr($info['name']);
        $xml = Xoonips_Utils::convertSQLStr($info['xml']);
        $occurrence = Xoonips_Utils::convertSQLNum($info['occurrence']);
        $gid = Xoonips_Utils::convertSQLNum($gid);
        $sql = "UPDATE $this->table SET "
            ." name = $name , "
            ." xml = $xml , "
            ." occurrence = $occurrence "
            ." WHERE group_id=$gid";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get detail list.
     *
     * @param int $usertypeId
     * @param int $baseId
     *
     * @return array
     */
    public function getDetailList($usertypeId, $baseId)
    {
        $usertypeId = Xoonips_Utils::convertSQLNum($usertypeId);
        $baseId = Xoonips_Utils::convertSQLNum($baseId);
        $sql = 'SELECT dt.user_detail_id, dt.name AS detail_name, gt.name AS group_name'
            ." FROM $this->detailtable dt, $this->table gt"
            .' WHERE dt.user_type_id=gt.user_type_id'
            .' AND dt.group_id=gt.group_id'
            ." AND dt.nondisplay='0'"
            ." AND dt.user_detail_id<>$baseId"
            ." AND dt.user_type_id=$usertypeId"
            .' ORDER BY dt.weight';
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
     * get count user groups.
     *
     * @return int
     */
    public function countUsergroups()
    {
        $sql = "SELECT group_id FROM $this->table"
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
     * 	get usergroup list.
     *
     *	@param int $limit
     *	@param int $start
     *
     * @return array
     */
    public function getUsergrouplist($limit = 0, $start = 0)
    {
        $limit = Xoonips_Utils::convertSQLNum($limit);
        $start = Xoonips_Utils::convertSQLNum($start);
        $sql = "SELECT at.*, bt.update_id AS upid FROM $this->table at"
            ." LEFT JOIN $this->table bt"
            .' ON at.group_id=bt.update_id'
            .' WHERE at.update_id IS NULL'
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
     * check exist user_type group name.
     *
     * @param int    $gid
     * @param string $gname
     * @param int    $base_gid
     *
     * @return bool true:success,false:failed
     */
    public function existGroupName($gid, $gname, $base_gid = 0)
    {
        $gid = Xoonips_Utils::convertSQLNum($gid);
        $gname = Xoonips_Utils::convertSQLStr($gname);
        $base_gid = Xoonips_Utils::convertSQLNum($base_gid);
        $sql = "SELECT name FROM $this->table"
            ." WHERE group_id<>$gid"
            ." AND group_id<>$base_gid"
            ." AND name=$gname";
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
     * check exist user_type group xml.
     *
     * @param int    $gid
     * @param string $gxml
     * @param int    $base_gid
     *
     * @return bool true:success,false:failed
     */
    public function existGroupXml($gid, $gxml, $base_gid = 0)
    {
        $gid = Xoonips_Utils::convertSQLNum($gid);
        $gxml = Xoonips_Utils::convertSQLStr($gxml);
        $base_gid = Xoonips_Utils::convertSQLNum($base_gid);
        $sql = "SELECT xml FROM $this->table"
            ." WHERE group_id<>$gid"
            ." AND group_id<>$base_gid"
            ." AND xml=$gxml";
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
    public function copyById($usergroupId, &$map, $update = false, $import = false)
    {
        // get copy information
        $groupObj = $this->getUserGroup($usergroupId);
        if (!$groupObj) {
            return false;
        }

        // do copy by obj
        return $this->copyByObj($groupObj, $map, $update, $import);
    }

    /**
     * do copy by obj.
     *
     * @param array $groupObj
     * @param array $map
     * @param bool  $update
     * @param bool  $import
     *
     * @return bool true:success,false:failed
     */
    public function copyByObj($groupObj, &$map, $update, $import)
    {
        // insert copy
        foreach ($groupObj as $group) {
            $group['released'] = $import ? $group['released'] : 0;
            $group['update_id'] = $update ? $group['group_id'] : null;

            $insertId = null;
            if (!$this->insert($group, $insertId)) {
                return false;
            }

            $map['group'][$group['group_id']] = $insertId;
        }

        return true;
    }

    /**
     * get user type groups by usergroupId.
     *
     * @param int $usergroupId user type id
     *
     * @return array
     */
    public function getUserGroup($usergroupId)
    {
        $usergroupId = Xoonips_Utils::convertSQLNum($usergroupId);
        $sql = "SELECT * FROM $this->table"
            ." WHERE group_id=$usergroupId";
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
     * get usertype group edit info.
     *
     * @param int  $gid group id (released)
     * @param bool $flg
     *
     * @return array user type group information
     */
    public function getGroupEditInfo($gid, $flg = false)
    {
        $gid = Xoonips_Utils::convertSQLNum($gid);
        $sql = 'SELECT a.preselect AS a_preselect, b.preselect AS b_preselect, '
            .' a.released AS a_released, b.released AS b_released, '
            .' a.name AS a_name, b.name AS b_name, '
            .' a.xml AS a_xml, b.xml AS b_xml, '
            .' a.occurrence AS a_occurrence, b.occurrence AS b_occurrence, '
            .' a.weight AS a_weight, b.weight AS b_weight, '
            .' a.update_id AS a_update_id, b.update_id AS b_update_id, '
            .' a.group_id AS a_group_id, b.group_id AS b_group_id ';
        if ($flg) {
            $sql .= " FROM $this->table a"
                ." LEFT JOIN $this->table b"
                .' ON a.update_id=b.group_id'
                ." WHERE a.group_id=$gid";
        } else {
            $sql .= " FROM $this->table a"
                ." LEFT JOIN $this->table b"
                .' ON a.group_id=b.update_id'
                ." WHERE a.group_id=$gid";
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
     * @param int $group_id
     * @param int $base_groupid
     *
     * @return bool true:success,false:failed
     */
    public function release($group_id, $base_groupid)
    {
        $group_id = Xoonips_Utils::convertSQLNum($group_id);
        $base_groupid = Xoonips_Utils::convertSQLNum($base_groupid);
        if ($group_id == $base_groupid) {
            $sql = "UPDATE $this->table SET"
                .' released = 1'
                ." WHERE group_id=$group_id";
        } else {
            $sql = "UPDATE $this->table t1, $this->table t2 SET "
            .' t1.weight = t2.weight ,'
            .' t1.name = t2.name ,'
            .' t1.xml = t2.xml ,'
            .' t1.occurrence = t2.occurrence '
            .' WHERE t1.group_id=t2.update_id'
            ." AND t2.group_id=$group_id";
        }
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        // release detail link
        if (!$this->updateLinkSync($base_groupid, true)) {
            return false;
        }

        return true;
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
        $sql = 'SELECT d.user_detail_id,d.name,d.xml,g.weight,g.edit_weight,g.edit,g.released AS link_release'
            ." FROM $this->detailtable AS d"
            ." LEFT JOIN $this->grouplinktable AS g"
            .' ON d.user_detail_id=g.user_detail_id'
            ." WHERE g.group_id=$groupId"
            .' ORDER BY g.edit_weight';
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
     * insert group link.
     *
     * @param array $info     default detail info
     * @param int   $insertId reference of insert id
     *
     * @return bool true:success,false:failed
     */
    public function insertLink($info, &$insertId)
    {
        $sql = "INSERT INTO $this->grouplinktable ("
            .' group_id,'
            .' user_detail_id,'
            .' weight,'
            .' edit,'
            .' edit_weight,'
            .' released )';
        $sql .= ' VALUES ('
            .Xoonips_Utils::convertSQLNum($info['group_id']).','
            .Xoonips_Utils::convertSQLNum($info['user_detail_id']).','
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
     * @param int  $groupId
     * @param int  $detailId
     * @param bool $weight
     *
     * @return bool true:success,false:failed
     */
    public function updateWeightForLink($groupId, $detailId, $weight)
    {
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $detailId = Xoonips_Utils::convertSQLNum($detailId);
        $weight = Xoonips_Utils::convertSQLNum($weight);
        $sql = "UPDATE $this->grouplinktable"
            ." SET edit_weight=$weight"
            ." WHERE group_id=$groupId"
            ." AND user_detail_id=$detailId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update group link released.
     *
     * @param int  $groupId
     * @param bool $release
     *
     * @return bool true:success,false:failed
     */
    public function updateLinkRelease($groupId, $release = 0)
    {
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $release = Xoonips_Utils::convertSQLNum($release);
        $sql = "UPDATE $this->grouplinktable"
            ." SET released=$release"
            ." WHERE group_id=$groupId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update group link edit.
     *
     * @param int  $groupId
     * @param int  $detailId
     * @param bool $edit
     *
     * @return bool true:success,false:failed
     */
    public function updateLinkEdit($groupId, $detailId, $edit = 0)
    {
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $detailId = Xoonips_Utils::convertSQLNum($detailId);
        $edit = Xoonips_Utils::convertSQLNum($edit);
        $sql = "UPDATE $this->grouplinktable"
            ." SET edit=$edit"
            ." WHERE group_id=$groupId"
            ." AND user_detail_id=$detailId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update group link sync.
     *
     * @param int  $groupId
     * @param bool $release
     *
     * @return bool true:success,false:failed
     */
    public function updateLinkSync($groupId, $release = false)
    {
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $sql = "UPDATE $this->grouplinktable";
        if ($release) {
            $sql .= ' SET released=edit, weight=edit_weight';
        } else {
            $sql .= ' SET edit=released, edit_weight=weight';
        }
        $sql .= " WHERE group_id=$groupId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete group link.
     *
     * @param int $groupId
     *
     * @return bool true:success,false:failed
     */
    public function deleteLink($groupId)
    {
        $groupId = Xoonips_Utils::convertSQLNum($groupId);
        $sql = "DELETE FROM $this->grouplinktable"
            ." WHERE group_id=$groupId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get group by detail id.
     *
     * @param int $detailId
     *
     * @return array usertype detail list
     */
    public function getGroupByDetailId($detailId)
    {
        $detailId = Xoonips_Utils::convertSQLNum($detailId);
        $sql = "SELECT g.* FROM $this->table AS g"
        ." LEFT JOIN $this->grouplinktable AS l"
        .' ON g.group_id=l.group_id'
        ." WHERE l.user_detail_id=$detailId"
        .' AND l.edit=1';
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
     * get detail id by Xml.
     *
     * @param string $xml
     *
     * @return array user_detail_id list
     */
    public function getDetailIdbyXml($xml)
    {
        $xml = Xoonips_Utils::convertSQLStr($xml);
        $sql = "SELECT user_detail_id FROM $this->table AS g , $this->grouplinktable AS l"
            .' WHERE g.released = 1 AND l.released = 1'
            .' AND g.group_id = l.group_id'
            ." AND xml=$xml";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row['user_detail_id'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }
}
