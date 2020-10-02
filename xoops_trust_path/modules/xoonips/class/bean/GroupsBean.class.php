<?php

use Xoonips\Core\Functions;

/**
 * @brief operate groups table
 */
class Xoonips_GroupsBean extends Xoonips_BeanBase
{
    private $linkTable = null;

    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('groups');
        $this->linkTable = $this->prefix('groups_users_link');
    }

    /**
     * get group.
     *
     * @param int $groupId group id
     *
     * @return array
     */
    public function getGroup($groupId)
    {
        $ret = [];
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `groupid`='.intval($groupId);

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
     * get group by name.
     *
     * @param int $gname group name
     *
     * @return array
     */
    public function getGroupByName($gname)
    {
        $ret = [];
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `name`='.Xoonips_Utils::convertSQLStr($gname);

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
     * insert group.
     *
     * @param array $group
     *
     * @return int
     */
    public function insert($group)
    {
        $ret = true;
        $sql = 'INSERT INTO `'.$this->table.'` (`activate`,`name`,`description`,`icon`,`mime_type`,`is_public`,';
        $sql .= '`can_join`,`is_hidden`,`member_accept`,`item_accept`,`item_number_limit`,`index_number_limit`,`item_storage_limit`,`group_type`)';
        $sql .= ' VALUES ('.intval($group['activate']).','.Xoonips_Utils::convertSQLStr($group['name']).',';
        $sql .= Xoonips_Utils::convertSQLStr($group['description']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($group['icon']).','.Xoonips_Utils::convertSQLStr($group['mime_type']);
        $sql .= ','.intval($group['is_public']).','.intval($group['can_join']);
        $sql .= ','.intval($group['is_hidden']).','.intval($group['member_accept']);
        $sql .= ','.intval($group['item_accept']).','.intval($group['item_number_limit']);
        $sql .= ','.intval($group['index_number_limit']).','.intval($group['item_storage_limit']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($group['group_type']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = $this->getInsertId();

        return $ret;
    }

    /**
     * delete group.
     *
     * @param int $groupId group id
     *
     * @return bool true:success,false:failed
     */
    public function delete($groupId)
    {
        $ret = true;
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `groupid`='.intval($groupId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * update group.
     *
     * @param array $group
     *
     * @return bool true:success,false:failed
     */
    public function update($group)
    {
        $ret = true;
        $sql = 'UPDATE `'.$this->table.'` SET `activate`='.intval($group['activate']).',`name`='.Xoonips_Utils::convertSQLStr($group['name']);
        $sql .= ',`description`='.Xoonips_Utils::convertSQLStr($group['description']).',`icon`='.Xoonips_Utils::convertSQLStr($group['icon']);
        $sql .= ',`mime_type`='.Xoonips_Utils::convertSQLStr($group['mime_type']).',`is_public`='.intval($group['is_public']);
        $sql .= ',`can_join`='.intval($group['can_join']).',`is_hidden`='.intval($group['is_hidden']);
        $sql .= ',`member_accept`='.intval($group['member_accept']).',`item_accept`='.intval($group['item_accept']);
        $sql .= ',`item_number_limit`='.intval($group['item_number_limit']);
        $sql .= ',`index_number_limit`='.intval($group['index_number_limit']);
        $sql .= ',`item_storage_limit`='.intval($group['item_storage_limit']);
        $sql .= ' WHERE `groupid`='.intval($group['groupid']);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * get admin group id.
     *
     * @param int $uid:uid
     *
     * @return array
     */
    public function getAdminGroupIds($uid)
    {
        $ret = [];

        $sql = 'SELECT `a`.`groupid` FROM `'.$this->linkTable.'` `a`,`'.$this->table.'` `b`';
        $sql .= ' WHERE `a`.`groupid`=`b`.`groupid` AND `a`.`uid`='.intval($uid).' AND `a`.`is_admin`='.Xoonips_Enum::GRP_ADMINISTRATOR;
        $sql .= ' AND `b`.`activate`<>'.Xoonips_Enum::GRP_NOT_CERTIFIED.' AND `a`.`activate`<>'.Xoonips_Enum::GRP_US_JOIN_REQUIRED;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row['groupid'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get my group id.
     *
     * @param int $uid:uid
     *
     * @return array
     */
    public function getMyGroupIds($uid)
    {
        $ret = [];

        $sql = 'SELECT `groupid` FROM `'.$this->linkTable.'` `a`,`'.$this->table.'` `b`';
        $sql .= ' WHERE `a`.`groupid`=`b`.`groupid` AND `a`.`uid`='.intval($uid);
        $sql .= ' AND `b`.`activate`<>'.Xoonips_Enum::GRP_NOT_CERTIFIED.' AND `a`.`activate`<>'.Xoonips_Enum::GRP_US_JOIN_REQUIRED;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row['groupid'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get moderator group id.
     *
     * @return int group id
     */
    public function getModeratorGroupId()
    {
        $moderator_gid = Functions::getXoonipsConfig($this->dirname, 'moderator_gid');
        $group = $this->getGroup($moderator_gid);
        if (false === $group) {
            return 1;
        } // fail safe

        return $group['groupid'];
    }

    /**
     * get admin group id.
     *
     * @param int $uid:uid
     *
     * @return bool true:isPublic,false:is't Public
     */
    public function isPublic($groupId)
    {
        $ret = false;
        $sql = 'SELECT `groupid` FROM `'.$this->table.'` WHERE `groupid`='.intval($groupId);
        $sql .= ' AND (`activate`='.Xoonips_Enum::GRP_PUBLIC.' OR `activate`='.Xoonips_Enum::GRP_CLOSE_REQUIRED.')';
        if (($result = $this->execute($sql)) && $this->getRowsNum($result) > 0) {
            $ret = true;
        } else {
            $ret = false;
        }

        return $ret;
    }

    /**
     * retrieve groups for a user.
     *
     * @param int $uid:id of the user
     *
     * @return array
     */
    public function getGroupIdsByUser($uid)
    {
        $ret = [];
        $sql = 'SELECT `groupid` FROM '.$this->linkTable.' WHERE `uid`='.intval($uid);
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row['groupid'];
        }

        return $ret;
    }

    /**
     * get all groups.
     *
     * @param
     *
     * @return array
     */
    public function getGroups($groupType, $operation = '=')
    {
        $ret = [];
        //$operation is AYASHII
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `group_type`'.$operation.Xoonips_Utils::convertSQLStr($groupType).' ORDER BY `name`';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get all groups.
     *
     * @param
     *
     * @return array
     */
    public function getAllGroups($activate)
    {
        $ret = [];
        $sql = 'SELECT `groupid`,`name` FROM `'.$this->table.'` WHERE';
        $sql .= ' `activate`>='.intval($activate).' OR `group_type`<>'.Xoonips_Utils::convertSQLStr(Xoonips_Enum::GROUP_TYPE).' ORDER BY `name`';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[$row['groupid']] = $row['name'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * groups certify.
     *
     * @param int $groupId group id
     *
     * @return bool true:success,false:failed
     */
    public function groupsCertify($groupId)
    {
        $sql = 'UPDATE `'.$this->table.'` SET `activate`='.Xoonips_Enum::GRP_CERTIFIED;
        $sql .= ' WHERE `groupid`='.intval($groupId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * set group status to delete request.
     *
     * @param int $groupId group id
     *
     * @return bool true:success,false:failed
     */
    public function groupsDeleteRequest($groupId)
    {
        $ret = true;
        $sql = 'UPDATE `'.$this->table.'` SET `activate`='.Xoonips_Enum::GRP_DELETE_REQUIRED;
        $sql .= ' WHERE `groupid`='.intval($groupId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * groups open.
     *
     * @param int $groupId group id
     *
     * @return bool true:success,false:failed
     */
    public function groupsOpen($groupId)
    {
        $sql = 'UPDATE `'.$this->table.'` SET `activate`='.Xoonips_Enum::GRP_PUBLIC.',`is_public`=1';
        $sql .= ' WHERE `groupid`='.intval($groupId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * groups close.
     *
     * @param int $groupId group id
     *
     * @return bool true:success,false:failed
     */
    public function groupsClose($groupId)
    {
        $sql = 'UPDATE `'.$this->table.'` SET `activate`='.Xoonips_Enum::GRP_CERTIFIED.',`is_public`=0';
        $sql .= ' WHERE `groupid`='.intval($groupId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * judge if the group is exist.
     *
     * @param string $gname: name
     *
     * @return bool true:exist,false:not exist
     */
    public function existsGroup($gname)
    {
        $ret = false;
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `name`='.Xoonips_Utils::convertSQLStr($gname);
        if (($result = $this->execute($sql)) && $this->getRowsNum($result) > 0) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * update group index.
     *
     * @param int $groupId group id
     * @param int $indexId index id
     *
     * @return bool true:success,false:failed
     */
    public function updateGroupIndex($groupId, $indexId)
    {
        $ret = true;
        $sql = 'UPDATE `'.$this->table.'` SET `index_id`='.intval($indexId);
        $sql .= ' WHERE `groupid`='.intval($groupId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * get maximum resource limits.
     *
     * @param int $groupId group id
     *
     * @return arary resources
     */
    public function getGroupMaximumResources($groupId)
    {
        $groupInfo = $this->getGroup($groupId);

        return [
            'itemNumberLimit' => (!isset($groupInfo['item_number_limit'])) ? Functions::getXoonipsConfig($this->dirname, 'group_item_number_limit') : $groupInfo['item_number_limit'],
            'indexNumberLimit' => (!isset($groupInfo['index_number_limit'])) ? Functions::getXoonipsConfig($this->dirname, 'group_index_number_limit') : $groupInfo['index_number_limit'],
            'itemStorageLimit' => (!isset($groupInfo['item_storage_limit'])) ? Functions::getXoonipsConfig($this->dirname, 'group_item_storage_limit') : $groupInfo['item_storage_limit'],
        ];
    }

    /**
     * get amount of current used resources.
     *
     * @param int $groupId group id
     *
     * @return arary resources
     */
    public function getGroupUsedResources($groupId)
    {
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);

        return [
            'itemNum' => $itemBean->countGroupItems($groupId),
            'indexNum' => $indexBean->countGroupIndexes($groupId),
            'fileSize' => $fileBean->countGroupFileSizes($groupId),
        ];
    }

    /**
     * alter check.
     *
     * @param void
     *
     * @return bool true:exist false:not exist
     **/
    public function alterCheck()
    {
        //FIXME
        $ret = [];
        $sql = 'SELECT * FROM `'.$this->table;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            if (isset($row['activate'])) {
                return true;
            }
        }

        return false;
    }
}
