<?php

/**
 * @brief operate groups_users_link table
 */
class Xoonips_GroupsUsersLinkBean extends Xoonips_BeanBase
{
    private static $cache = array();

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
        $ret = array();
        if (isset(self::$cache[$groupId][$userId])) {
            return self::$cache[$groupId][$userId];
        }
        $sql = "SELECT * FROM $this->table";
        $sql = $sql." WHERE groupid=$groupId";
        $sql = $sql." AND uid=$userId";
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
        $ret = array();
        $sql = "SELECT * FROM $this->table";
        $sql = $sql." WHERE groupid=$groupId";
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
        $ret = array();
        $sql = "SELECT * FROM $this->table";
        $sql = $sql." WHERE uid=$uid";
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
        $ret = array();
        $sql = "SELECT uid,groupid FROM $this->table";
        $sql = $sql." WHERE groupid=$groupId";
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
        $ret = array();
        $sql = "SELECT * FROM $this->table";
        $sql = $sql." WHERE groupid=$groupId AND is_admin=".Xoonips_Enum::GRP_ADMINISTRATOR;
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
        $sql = "INSERT INTO $this->table (activate,groupid,uid,is_admin)";
        $sql = $sql.' VALUES('.Xoonips_Utils::convertSQLNum($groupUserLink['activate']).','.Xoonips_Utils::convertSQLNum($groupUserLink['groupid']);
        $sql = $sql.','.Xoonips_Utils::convertSQLNum($groupUserLink['uid']).','.Xoonips_Utils::convertSQLNum($groupUserLink['is_admin']).')';
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
        $sql = "DELETE FROM $this->table WHERE groupid=$groupId and uid=$uid";
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
        $sql = "DELETE FROM $this->table WHERE uid=$uid";
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
        $sql = "UPDATE $this->table set activate=".Xoonips_Enum::GRP_US_CERTIFIED.',is_admin='.Xoonips_Enum::GRP_ADMINISTRATOR;
        $sql = $sql.' WHERE groupid='.$groupId.' and uid='.$uid;
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
        $sql = "UPDATE $this->table set is_admin=".Xoonips_Enum::GRP_USER;
        $sql = $sql.' WHERE groupid='.$groupId.' and uid='.$uid;
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
        $sql = "UPDATE $this->table set activate=".Xoonips_Enum::GRP_US_LEAVE_REQUIRED;
        $sql = $sql.' WHERE groupid='.$groupId.' and uid='.$uid;
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
        $sql = "UPDATE $this->table set activate=".Xoonips_Enum::GRP_US_CERTIFIED;
        $sql = $sql." WHERE groupid=$groupId and uid=$uid";
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
        $ret = array();
        $sql = "SELECT * FROM $this->table";
        $sql = $sql." WHERE linkid=$linkId";
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
        $sql = "DELETE FROM $this->table WHERE groupid=$groupId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }
}
