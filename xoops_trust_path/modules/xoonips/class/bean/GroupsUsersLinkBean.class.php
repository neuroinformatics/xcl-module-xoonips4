<?php

/**
 * @brief operate groups_users_link table
 */
class Xoonips_GroupsUsersLinkBean extends Xoonips_BeanBase
{
    private static $cache = [];

    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('groups_users_link');
    }

    /**
     * get group_user_link info.
     *
     * @param int $groupId : groupid
     *                     int $userId : uid
     *
     * @return array
     */
    public function getGroupUserLinkInfo($groupId, $userId)
    {
        $ret = [];
        if (isset(self::$cache[$groupId][$userId])) {
            return self::$cache[$groupId][$userId];
        }
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `groupid`='.intval($groupId);
        $sql .= ' AND `uid`='.intval($userId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret = $row;
            self::$cache[$groupId][$userId] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get group_user_link info.
     *
     * @param int $groupId : groupid
     *                     int $userId : uid
     *
     * @return array
     */
    public function getGroupUserLinkInfoByGroupId($groupId)
    {
        $ret = [];
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `groupid`='.intval($groupId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[$row['uid']] = $row;
            self::$cache[$groupId][$row['uid']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get group_user_link info.
     *
     * @param int $groupId : groupid
     *                     int $userId : uid
     *
     * @return array
     */
    public function getGroupUserLinkInfoByUserId($uid)
    {
        $ret = [];
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `uid`='.intval($uid);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[$row['groupid']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get group's user ids.
     *
     * @param int $groupId : groupid
     *
     * @return array
     */
    public function getUserIds($groupId)
    {
        $ret = [];
        $sql = 'SELECT `uid`,`groupid` FROM `'.$this->table.'` WHERE `groupid`='.intval($groupId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row['uid'];
            self::$cache[$row['groupid']][$row['uid']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get group's administrator user ids.
     *
     * @param int $groupId : groupid
     *
     * @return array
     */
    public function getAdminUserIds($groupId)
    {
        $ret = [];
        $sql = 'SELECT * FROM `'.$this->table.'`';
        $sql .= ' WHERE `groupid`='.intval($groupId).' AND `is_admin`='.Xoonips_Enum::GRP_ADMINISTRATOR;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row['uid'];
            self::$cache[$row['groupid']][$row['uid']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get moderator user ids.
     *
     * @return array
     */
    public function getModeratorUserIds()
    {
        $groupsBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        $moderator_gid = $groupsBean->getModeratorGroupId();

        return $this->getUserIds($moderator_gid);
    }

    /**
     * insert group_user_link.
     *
     * @param array $groupUserLink
     *
     * @return bool true:success,false:failed
     */
    public function insert($groupUserLink)
    {
        $ret = true;
        $sql = 'INSERT INTO `'.$this->table.'` (`activate`,`groupid`,`uid`,`is_admin`) VALUES (';
        $sql .= intval($groupUserLink['activate']).','.intval($groupUserLink['groupid']).',';
        $sql .= intval($groupUserLink['uid']).','.intval($groupUserLink['is_admin']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * delete group_user_link.
     *
     * @param int $groupId : groupid
     *                     int $uid : uid
     *
     * @return bool true:success,false:failed
     */
    public function delete($groupId, $uid)
    {
        $ret = true;
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `groupid`='.intval($groupId).' AND `uid`='.intval($uid);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * delete group_user_link.
     *
     * @param int $uid : uid
     *
     * @return bool true:success,false:failed
     */
    public function deleteByUid($uid)
    {
        $ret = true;
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `uid`='.intval($uid);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * update user as group manager.
     *
     * @param int $groupId : groupid
     *                     int $uid : uid
     *
     * @return bool true:success,false:failed
     */
    public function updateManager($groupId, $uid)
    {
        $ret = true;
        $sql = 'UPDATE `'.$this->table.'` SET `activate`='.Xoonips_Enum::GRP_US_CERTIFIED.',`is_admin`='.Xoonips_Enum::GRP_ADMINISTRATOR;
        $sql .= ' WHERE `groupid`='.intval($groupId).' AND `uid`='.intval($uid);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * update user as group member.
     *
     * @param int $groupId : groupid
     *                     int $uid : uid
     *
     * @return bool true:success,false:failed
     */
    public function updateMember($groupId, $uid)
    {
        $ret = true;
        $sql = 'UPDATE `'.$this->table.'` SET `is_admin`='.Xoonips_Enum::GRP_USER;
        $sql .= ' WHERE `groupid`='.intval($groupId).' AND `uid`='.intval($uid);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * request to leave.
     *
     * @param int $groupId : groupid
     *                     int $uid : uid
     *
     * @return bool true:success,false:failed
     */
    public function leaveRequest($groupId, $uid)
    {
        $ret = true;
        $sql = 'UPDATE `'.$this->table.'` SET `activate`='.Xoonips_Enum::GRP_US_LEAVE_REQUIRED;
        $sql .= ' WHERE `groupid`='.intval($groupId).' AND `uid`='.intval($uid);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * group certify.
     *
     * @param int $groupId : groupid
     *
     * @return bool true:success,false:failed
     */
    public function certify($groupId, $uid)
    {
        $ret = true;
        $sql = 'UPDATE `'.$this->table.'` SET `activate`='.Xoonips_Enum::GRP_US_CERTIFIED;
        $sql .= ' WHERE `groupid`='.intval($groupId).' AND `uid`='.intval($uid);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * get group_user_link info by link id.
     *
     * @param int $groupId : groupid
     *                     int $userId : uid
     *
     * @return array
     */
    public function getGroupUserLinkInfoByLinkId($linkId)
    {
        $ret = [];
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `linkid`='.intval($linkId);
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
     * delete group users.
     *
     * @param int $groupId : groupid
     *
     * @return bool true:success,false:failed
     */
    public function deleteGroupUsers($groupId)
    {
        $ret = true;
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `groupid`='.intval($groupId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }
}
