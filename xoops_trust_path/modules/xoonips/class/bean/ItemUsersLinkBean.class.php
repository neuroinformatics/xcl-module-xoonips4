<?php

require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/BeanBase.class.php';

/**
 * @brief operate xoonips_item_users_link table
 */
class Xoonips_ItemUsersLinkBean extends Xoonips_BeanBase
{
    private static $cache = array();

    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('item_users_link', true);
    }

    /**
     * get item users information by id.
     *
     * @param int $id:item id
     *
     * @return item users information
     */
    public function getItemUsersInfo($id)
    {
        $sql = 'SELECT * FROM '.$this->table.' WHERE item_id='.$id;
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

    public function getLinkItemIds($uid)
    {
        $ret = array();
        $sql = "SELECT item_id FROM $this->table WHERE uid=$uid";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row['item_id'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * is link.
     *
     * @param int $itemid:item id
     *                         int $uid:user id
     *
     * @return bool true:link,false:not link
     */
    public function isLink($itemId, $uid)
    {
        $ret = false;
        if (!isset(self::$cache[$uid])) {
            self::$cache[$uid] = $this->getLinkItemIds($uid);
        }
        if (is_array(self::$cache[$uid]) && in_array($itemId, self::$cache[$uid])) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * delete by id.
     *
     * @param  item id
     *
     * @return bool
     */
    public function delete($id)
    {
        $sql = "DELETE FROM $this->table WHERE item_id=$id";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get user change info.
     *
     * @param int $item_id:item id
     *                          array $selectedUids:selected user ids
     *
     * @return array
     *               [0]:add user ids
     *               [1]:delete user ids
     *               [2]:can not delete user ids
     */
    public function getUserChangeInfo($item_id, $selectedUids)
    {
        $ret = array();
        $sql = "SELECT uid FROM $this->table WHERE item_id=$item_id";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $uids = array();
        while ($row = $this->fetchArray($result)) {
            $uids[] = $row['uid'];
        }
        $this->freeRecordSet($result);

        // add user
        foreach ($selectedUids as $uid) {
            if (!in_array($uid, $uids)) {
                $ret[0][] = $uid;
            }
        }

        // delete user
        foreach ($uids as $uid) {
            if (!in_array($uid, $selectedUids)) {
                if ($this->canDeleteUser($item_id, $uid)) {
                    $ret[1][] = $uid;
                // can not delete user
                } else {
                    $ret[2][] = $uid;
                }
            }
        }

        return $ret;
    }

    /**
     * can delete user.
     *
     * @param int $item_id:item id
     *                          int uid:user id
     *
     * @return boolean:true-can,false-can not
     */
    public function canDeleteUser($item_id, $uid)
    {
        $tblGroupLink = $this->prefix('groups');
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $groupIndexes = $indexBean->getGroupIndexes($uid);
        $linkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        foreach ($groupIndexes as $index) {
            if (count($linkBean->getInfo($item_id, $index['index_id'])) != 0) {
                $sql = "SELECT a.uid FROM $this->table a,$tblGroupLink b";
                $sql = $sql."WHERE a.item_id=$item_id AND b.groupid=".$index['groupid'];
                $sql = $sql." AND a.uid=b.uid AND b.activate<>1 AND a.uid<>$uid";
                $result = $this->execute($sql);
                if ($result && $this->fetchArray($result)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * get items with owner.
     *
     * @param int $uid:uid
     *
     * @return array
     */
    public function getItemsWithOwner($uid)
    {
        $ret = array();
        $sql = 'SELECT a.item_id,COUNT(a.item_id) AS cnt '
            ."FROM $this->table a "
            ."WHERE a.item_id in (SELECT item_id FROM $this->table where uid=$uid) "
            .'GROUP BY a.item_id HAVING cnt=1 '
            .'ORDER BY a.item_id';
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
     * get item with owners;.
     *
     * @param int $uid:uid
     *
     * @return array
     */
    public function getItemsWithOwners($uid)
    {
        $ret = array();
        $sql = 'SELECT a.item_id,COUNT(a.item_id) AS cnt '
            ."FROM $this->table a "
            ."WHERE a.item_id in (SELECT item_id FROM $this->table where uid=$uid) "
            .'GROUP BY a.item_id HAVING cnt>1 '
            .'ORDER BY a.item_id';
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
     * is itemuser.
     *
     * @param int $uid:uid
     *
     * @return bool
     */
    public function isItemUser($uid)
    {
        $ret = false;
        $tblindex = $this->prefix($this->modulePrefix('index'));
        $tbllink = $this->prefix($this->modulePrefix('index_item_link'));
        $sql = "SELECT COUNT(a.uid) AS count
    		FROM $this->table a,
    		$tbllink b,
    		$tblindex c 
    		WHERE a.uid=$uid 
    		AND a.item_id=b.item_id
    		AND b.index_id=c.index_id
    		AND (c.open_level=1 or c.open_level=2)";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);
        if ($row && $row['count'] != 0) {
            $ret = true;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * delete by uid.
     *
     * @param int $uid:uid
     *
     * @return array
     */
    public function deleteAllByUid($uid)
    {
        $sql = "DELETE FROM $this->table WHERE uid=$uid";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete by uid.
     *
     * @param  $itemId item id, $uid user id
     *
     * @return bool true/false
     */
    public function deleteByUid($itemId, $uid)
    {
        $sql = "DELETE FROM $this->table WHERE item_id=$itemId AND uid=$uid";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * insert new item user.
     *
     * @param  $info: item user info
     *
     * @return bool true:success,false:failed
     */
    public function insert($info)
    {
        $sql = "INSERT INTO $this->table (item_id, uid, weight)";
        $sql .= ' VALUES ('.Xoonips_Utils::convertSQLNum($info['item_id']).','
            .Xoonips_Utils::convertSQLNum($info['uid']).','
            .Xoonips_Utils::convertSQLNum($info['weight']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get max weight.
     *
     * @param  $itemId item id
     *
     * @return max weight
     */
    public function getMaxWeight($itemId)
    {
        $sql = 'SELECT MAX(weight) AS maxWeight FROM '.$this->table." WHERE item_id=$itemId";
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
     * update new item user.
     *
     * @param  $info: item user info
     *
     * @return bool true:success,false:failed
     */
    public function update($info)
    {
        $item_id = $info['item_id'];
        $uid = $info['uid'];
        if ($this->deleteByUid($item_id, $uid) == true) {
            return $this->insert($info);
        }

        return false;
    }
}
