<?php

/**
 * @brief operate xoonips_item_users_link table
 */
class Xoonips_ItemUsersLinkBean extends Xoonips_BeanBase
{
    private static $cache = [];

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
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `item_id`='.intval($id);
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

    public function getLinkItemIds($uid)
    {
        $ret = [];
        $sql = 'SELECT `item_id` FROM `'.$this->table.'` WHERE `uid`='.intval($uid);
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
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `item_id`='.intval($id);
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
        $ret = [];
        $sql = 'SELECT `uid` FROM `'.$this->table.'` WHERE `item_id`='.intval($item_id);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $uids = [];
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
            if (0 != count($linkBean->getInfo($item_id, $index['index_id']))) {
                $sql = 'SELECT `a`.`uid` FROM `'.$this->table.'` `a`,`'.$tblGroupLink.'` `b`';
                $sql .= ' WHERE `a`.`item_id`='.intval($item_id).' AND `b`.`groupid`='.$index['groupid'];
                $sql .= ' AND `a`.`uid`=`b`.`uid` AND `b`.`activate`<>1 AND `a`.`uid`<>'.intval($uid);
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
        $ret = [];
        $sql = 'SELECT `a`.`item_id`,COUNT(`a`.`item_id`) AS `cnt` ';
        $sql .= ' FROM `'.$this->table.'` `a` ';
        $sql .= ' WHERE `a`.`item_id` IN (SELECT `item_id` FROM `'.$this->table.'` WHERE `uid`='.intval($uid).')';
        $sql .= ' GROUP BY `a`.`item_id` HAVING `cnt`=1 ';
        $sql .= ' ORDER BY `a`.`item_id`';
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
        $ret = [];
        $sql = 'SELECT `a`.`item_id`,COUNT(`a`.`item_id`) AS `cnt` ';
        $sql .= ' FROM `'.$this->table.'` `a` ';
        $sql .= ' WHERE `a`.`item_id` IN (SELECT `item_id` FROM `'.$this->table.'` WHERE `uid`='.intval($uid).')';
        $sql .= ' GROUP BY `a`.`item_id` HAVING `cnt`>1 ';
        $sql .= ' ORDER BY `a`.`item_id`';
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
        $sql = 'SELECT COUNT(`a`.`uid`) AS `count` FROM `'.$this->table.'` `a`, `'.$tbllink.'` `b`, `'.$tblindex.'` `c`';
        $sql .= ' WHERE `a`.`uid`='.intval($uid);
        $sql .= ' AND `a`.`item_id`=`b`.`item_id` AND `b`.`index_id`=`c`.`index_id` AND (`c`.`open_level`=1 OR `c`.`open_level`=2)';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);
        if ($row && 0 != $row['count']) {
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
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `uid`='.intval($uid);
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
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `item_id`='.intval($itemId).' AND `uid`='.intval($uid);
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
        $sql = 'INSERT INTO `'.$this->table.'` (`item_id`, `uid`, `weight`)';
        $sql .= ' VALUES ('.intval($info['item_id']).',';
        $sql .= intval($info['uid']).','.intval($info['weight']).')';
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
        $sql = 'SELECT MAX(`weight`) AS `maxWeight` FROM `'.$this->table.'` WHERE `item_id`='.intval($itemId);
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
        if (true == $this->deleteByUid($item_id, $uid)) {
            return $this->insert($info);
        }

        return false;
    }
}
