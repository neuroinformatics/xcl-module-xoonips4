<?php

require_once dirname(__DIR__).'/core/BeanBase.class.php';

/**
 * @brief operate xoonips_index table
 */
class Xoonips_IndexBean extends Xoonips_BeanBase
{
    private static $index_cache = array();

    private $groupBean;
    private $indexItemLinkBean;
    private $usersBean;
    private $itemUsersLinkBean;

    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('index', true);

        $this->groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname);
        $this->indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $this->usersBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $this->itemUsersLinkBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
    }

    /**
     * get root public index.
     *
     * @param
     *
     * @return array
     */
    public function getPublicIndex()
    {
        $ret = array();

        $sql = "SELECT a.* FROM $this->table a";
        $sql = $sql.' WHERE a.open_level='.XOONIPS_OL_PUBLIC.' AND a.parent_index_id='.XOONIPS_IID_ROOT;

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret = $row;
            self::$index_cache[$row['index_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get root public group index.
     *
     * @param
     *
     * @return array
     */
    public function getPublicGroupIndex()
    {
        $ret = array();

        $tblGroup = $this->prefix('groups');

        $sql = "SELECT a.* FROM $this->table a LEFT JOIN $tblGroup b ON(a.groupid=b.groupid)";
        $sql = $sql.' WHERE a.open_level='.XOONIPS_OL_GROUP_ONLY.' AND (b.activate='.Xoonips_Enum::GRP_PUBLIC;
        $sql = $sql.' OR b.activate='.Xoonips_Enum::GRP_CLOSE_REQUIRED.') AND a.parent_index_id='.XOONIPS_IID_ROOT;
        $sql = $sql.' ORDER BY a.index_id';

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
            self::$index_cache[$row['index_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get root user's group index.
     *
     * @param int $uid:user id
     *
     * @return array
     */
    public function getGroupIndex($uid)
    {
        $ret = array();
        $tblGroupUser = $this->prefix('groups_users_link');
        $tblGroup = $this->prefix('groups');
        $sql = "SELECT a.* FROM $this->table a";
        $sql = $sql.' WHERE a.open_level='.XOONIPS_OL_GROUP_ONLY.' AND a.parent_index_id='.XOONIPS_IID_ROOT.' AND EXISTS';
        $sql = $sql." (SELECT b.linkid FROM $tblGroupUser b,$tblGroup c";
        $sql = $sql." WHERE b.uid=$uid AND b.activate<>".Xoonips_Enum::GRP_US_JOIN_REQUIRED;
        $sql = $sql.' AND b.groupid=c.groupid AND c.activate<>';
        $sql = $sql.Xoonips_Enum::GRP_NOT_CERTIFIED.' AND b.groupid=a.groupid)';
        $sql = $sql.' ORDER BY a.index_id';

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
            self::$index_cache[$row['index_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get root user's group index.
     *
     * @param int $uid:user id
     *
     * @return array
     */
    public function getGroupIndex2($uids)
    {
        $ret = array();
        $tblGroupUser = $this->prefix('groups_users_link');
        $tblGroup = $this->prefix('groups');
        $sql = "SELECT a.* FROM $this->table a";
        $sql = $sql.' WHERE a.open_level='.XOONIPS_OL_GROUP_ONLY.' AND a.parent_index_id='.XOONIPS_IID_ROOT.' AND EXISTS';
        $sql = $sql." (SELECT b.linkid FROM $tblGroupUser b,$tblGroup c";
        $sql = $sql.' WHERE b.uid IN('.implode(',', $uids).') AND b.activate<>';
        $sql = $sql.Xoonips_Enum::GRP_US_JOIN_REQUIRED.' AND b.groupid=c.groupid AND c.activate<>';
        $sql = $sql.Xoonips_Enum::GRP_NOT_CERTIFIED.' AND b.groupid=a.groupid)';
        $sql = $sql.' ORDER BY a.index_id';

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
            self::$index_cache[$row['index_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get root user's private index.
     *
     * @param int $uid:user id
     *
     * @return array
     */
    public function getPrivateIndex($uid)
    {
        $ret = array();

        $sql = "SELECT a.* FROM $this->table a";
        $sql = $sql." WHERE a.uid=$uid AND a.open_level=".XOONIPS_OL_PRIVATE.' AND a.parent_index_id='.XOONIPS_IID_ROOT;

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        if ($row = $this->fetchArray($result)) {
            $ret = &$row;
            self::$index_cache[$row['index_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get root user's private index.
     *
     * @param int $uid:user ids
     *
     * @return array
     */
    public function getPrivateIndex2($uids)
    {
        $ret = array();

        $sql = "SELECT a.* FROM $this->table a";
        $sql = $sql.' WHERE a.uid IN('.implode(',', $uids);
        $sql = $sql.') AND a.open_level='.XOONIPS_OL_PRIVATE.' AND a.parent_index_id='.XOONIPS_IID_ROOT;

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
            self::$index_cache[$row['index_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * merge index array data.
     *
     * @param array $idxs1:index array data1,
     *                           array $idxs2:index array data2
     *
     * @return array
     */
    public function mergeIndexes($idxs1, $idxs2)
    {
        if ($idxs1 == false) {
            return $idxs2;
        } else {
            $ret = $idxs1;
        }
        if ($idxs2 != false) {
            foreach ($idxs2 as $idx2) {
                $flg = false;
                foreach ($idxs1 as $idx1) {
                    if ($idx1['index_id'] == $idx2['index_id']) {
                        $flg = true;
                        break;
                    }
                }
                if ($flg == false) {
                    $ret[] = $idx2;
                }
            }
        }

        return $ret;
    }

    /**
     * get all public index.
     *
     * @param
     *
     * @return array
     */
    public function getPublicIndexes()
    {
        $ret = array();

        $sql = "SELECT a.* FROM $this->table a";
        $sql = $sql.' WHERE a.open_level='.XOONIPS_OL_PUBLIC.' AND a.index_id<>'.XOONIPS_IID_ROOT;
        $sql = $sql.' ORDER BY a.index_id';

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
            self::$index_cache[$row['index_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get all public group index.
     *
     * @param
     *
     * @return array
     */
    public function getPublicGroupIndexes()
    {
        $ret = array();
        $tblGroup = $this->prefix('groups');

        $sql = "SELECT a.* FROM $this->table a LEFT JOIN $tblGroup b ON(a.groupid=b.groupid)";
        $sql = $sql.' WHERE a.open_level='.XOONIPS_OL_GROUP_ONLY.' AND (b.activate='.Xoonips_Enum::GRP_PUBLIC;
        $sql = $sql.' OR b.activate='.Xoonips_Enum::GRP_CLOSE_REQUIRED.')';
        $sql = $sql.' ORDER BY a.index_id';

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
            self::$index_cache[$row['index_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get all user's group index.
     *
     * @param int $uid:user id
     *
     * @return array
     */
    public function getGroupIndexes($uid)
    {
        $ret = array();
        $tblGroupUser = $this->prefix('groups_users_link');
        $tblGroup = $this->prefix('groups');

        $sql = "SELECT a.* FROM $this->table a";
        $sql = $sql.' WHERE a.open_level='.XOONIPS_OL_GROUP_ONLY.' AND EXISTS';
        $sql = $sql." (SELECT b.linkid FROM $tblGroupUser b,$tblGroup c";
        $sql = $sql." WHERE b.uid=$uid AND b.activate<>".Xoonips_Enum::GRP_US_JOIN_REQUIRED;
        $sql = $sql.' AND b.groupid=c.groupid AND c.activate<>';
        $sql = $sql.Xoonips_Enum::GRP_NOT_CERTIFIED.' AND b.groupid=a.groupid)';
        $sql = $sql.' ORDER BY a.index_id';

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
            self::$index_cache[$row['index_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get all user's private index.
     *
     * @param int $uid:user id
     *
     * @return array
     */
    public function getPrivateIndexes($uid)
    {
        $ret = array();
        $sql = "SELECT a.* FROM $this->table a";
        $sql = $sql." WHERE a.uid=$uid AND a.open_level=".XOONIPS_OL_PRIVATE;
        $sql = $sql.' ORDER BY a.index_id';

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
            self::$index_cache[$row['index_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * filter child index.
     *
     * @param array $indexes:all index
     *                           int $indexId:index id
     *
     * @return array
     */
    public function getChilds(&$indexes, $indexId)
    {
        $ret = array();
        foreach ($indexes as $index) {
            if ($index['parent_index_id'] == $indexId) {
                $ret[] = $index['index_id'];
            }
        }

        return $ret;
    }

    /**
     * get index data by index id.
     *
     * @param int $indexId:index id
     *
     * @return array
     */
    public function getIndex($indexId, $force = false)
    {
        $ret = false;
        if ($force == false && isset(self::$index_cache[$indexId])) {
            return self::$index_cache[$indexId];
        }
        $sql = "SELECT a.* FROM $this->table a";
        $sql = $sql." WHERE a.index_id=$indexId";

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret = $row;
            self::$index_cache[$indexId] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get index's full path.
     *
     * @param int $indexId:index id
     *
     * @return array
     */
    public function getFullPathIndexes($indexId, $force = false)
    {
        $ret = array();
        while ($indexId != 1) {
            $index = $this->getIndex($indexId, $force);
            if ($index == false) {
                return false;
            }
            $index['html_title'] = $index['title'];
            $ret[] = $index;
            $indexId = $index['parent_index_id'];
        }
        $ret = array_reverse($ret);

        return $ret;
    }

    /**
     * get child index.
     *
     * @param int $indexId:index id
     *
     * @return array
     */
    public function getChildIndexes($indexId)
    {
        $ret = array();
        $sql = "SELECT a.* FROM $this->table a";
        $sql = $sql." WHERE a.parent_index_id=$indexId ORDER BY weight";

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
            self::$index_cache[$row['index_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * insert index.
     *
     * @param int $indexId:index id
     *
     * @return bool true:success,false:failed
     */
    public function insertIndex($index)
    {
        $ret = true;
        $weight = $this->getMaxWeight($index['parent_index_id']) + 1;
        $sql = "INSERT INTO $this->table (parent_index_id,uid,groupid,open_level,weight,title,last_update_date,creation_date)";
        $sql = $sql.' VALUES('.$index['parent_index_id'].','.Xoonips_Utils::convertSQLNum($index['uid']).','.Xoonips_Utils::convertSQLNum($index['groupid']).','.$index['open_level'];
        $sql = $sql.','.$weight.','.Xoonips_Utils::convertSQLStr($index['title']).','.time().','.time().')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * get max weight.
     *
     * @param int $parentIndexId:parent index id
     *
     * @return int
     */
    private function getMaxWeight($parentIndexId)
    {
        $ret = 0;
        $sql = "SELECT MAX(weight) as weight FROM $this->table WHERE parent_index_id=$parentIndexId";
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        if ($row = $this->fetchArray($result)) {
            if ($row['weight'] != null) {
                $ret = $row['weight'];
            }
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * delete index.
     *
     * @param array $indexIds:index ids
     *
     * @return bool true:success,false:failed
     */
    public function deleteIndex($indexIds)
    {
        $ret = true;
        $sql = "DELETE FROM $this->table WHERE index_id IN (".implode(',', $indexIds).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * delete index item link.
     *
     * @param array $indexIds:index ids
     *
     * @return bool true:success,false:failed
     */
    public function deleteIndexItemLink($indexIds)
    {
        $ret = true;
        $tblIndexItem = $this->prefix($this->modulePrefix('index_item_link'));
        $sql = "DELETE FROM $tblIndexItem WHERE index_id IN (".implode(',', $indexIds).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * update index.
     *
     * @param array $index:index data
     *
     * @return bool true:success,false:failed
     */
    public function updateIndex($index)
    {
        $ret = true;
        $sql = "UPDATE $this->table set title=".Xoonips_Utils::convertSQLStr($index['title']);
        $sql = $sql.',last_update_date='.time();
        $sql = $sql.' WHERE index_id='.$index['index_id'];
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * update index detailed.
     *
     * @param array $index:index data
     *
     * @return bool true:success,false:failed
     */
    public function updateIndexDetailed($index)
    {
        $ret = true;
        $sql = "UPDATE $this->table set `title` = ".Xoonips_Utils::convertSQLStr($index['title']);
        $sql = $sql.', `detailed_title` = '.Xoonips_Utils::convertSQLStr($index['detailed_title']);
        $sql = $sql.', `icon` = '.Xoonips_Utils::convertSQLStr($index['icon']);
        $sql = $sql.', `mime_type` = '.Xoonips_Utils::convertSQLStr($index['mime_type']);
        $sql = $sql.', `detailed_description` = '.Xoonips_Utils::convertSQLStr($index['detailed_description']);
        $sql = $sql.', `last_update_date` = '.time();
        $sql = $sql.' WHERE `index_id` = '.$index['index_id'];
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * count child index.
     *
     * @param int $indexId:index id
     *
     * @return int
     */
    public function countIndexes($indexId)
    {
        $ret = 0;
        $sql = "SELECT index_id FROM $this->table";
        $sql = $sql." WHERE parent_index_id=$indexId";

        $result = $this->execute($sql);
        if ($this->getRowsNum($result) > 0) {
            $ret = $ret + $this->getRowsNum($result);
            while ($row = $this->fetchArray($result)) {
                $ret = $ret + $this->countIndexes($row['index_id']);
            }
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get root index.
     *
     * @param int $indexId:index id
     *
     * @return array
     */
    public function getRootIndex($indexId)
    {
        $ret = false;
        do {
            $ret = $this->getIndex($indexId);
            $indexId = $ret['parent_index_id'];
        } while ($indexId != 1);

        return $ret;
    }

    /**
     * filter editable group index.
     *
     * @param array $indexes:indexes
     *                               int $uid:user id
     *
     * @return array
     */
    public function filteEditableGroupIndex(&$indexes, $uid)
    {
        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname);
        $admin_gids = $groupBean->getAdminGroupIds($uid);
        $len = count($indexes);
        for ($i = 0; $i < $len; ++$i) {
            $index = &$indexes[$i];
            if ($index === false) {
                continue;
            }
            if (!is_array($admin_gids) || !in_array($index['groupid'], $admin_gids)) {
                unset($indexes[$i]);
            }
        }
    }

    /**
     * get all child index.
     *
     * @param int $indexId:index id
     *
     * @return array
     */
    public function getAllChildIndexes($indexId)
    {
        $ret = array();
        $sql = "SELECT * FROM $this->table";
        $sql = $sql." WHERE parent_index_id=$indexId";

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
            self::$index_cache[$row['index_id']] = $row;
            $childs = $this->getAllChildIndexes($row['index_id']);
            if ($childs) {
                $ret = array_merge($ret, $childs);
            }
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get item ids by index ids.
     *
     * @param array $indexIds:index ids
     *
     * @return array
     */
    public function getItemIds($indexIds)
    {
        $ret = array();
        $tblIndexItem = $this->prefix($this->modulePrefix('index_item_link'));
        $sql = "SELECT item_id FROM $tblIndexItem";
        $sql = $sql.' WHERE index_id IN ('.implode(',', $indexIds).')';

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
     * is linked item.
     *
     * @param int $indexId:index id
     *                           int $itemId:item id
     *
     * @return bool true:linked,false:not linked
     */
    public function isLinkedItem($indexId, $itemId)
    {
        $ret = false;
        $tblIndexItem = $this->prefix($this->modulePrefix('index_item_link'));
        $sql = "SELECT item_id FROM $tblIndexItem";
        $sql = $sql." WHERE index_id=$indexId AND item_id=$itemId";

        if (($result = $this->execute($sql)) && $this->getRowsNum($result) > 0) {
            $ret = true;
        } else {
            $indexes = $this->getChildIndexes($indexId);
            foreach ($indexes as $index) {
                if ($this->isLinkedItem($index['index_id'], $itemId)) {
                    $ret = true;
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     * insert index item link.
     *
     * @param int $indexId:index id
     *                           int $itemId:item id
     *
     * @return bool true:success,false:failed
     */
    public function insertIndexItemLink($indexId, $itemId)
    {
        $ret = true;
        $tblIndexItem = $this->prefix($this->modulePrefix('index_item_link'));
        $sql = "INSERT INTO $tblIndexItem (index_id,item_id,certify_state)";
        $sql = $sql.' VALUES('.$indexId.','.$itemId.','.XOONIPS_NOT_CERTIFIED.')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * is linked to public index.
     *
     * @param int $itemId:item id
     *
     * @return bool true:linked,false:not linked
     */
    public function isLinked2PublicItem($itemId)
    {
        $ret = false;
        $publicIndex = $this->getPublicIndex();
        $publicGroupIndex = $this->getPublicGroupIndex();
        if ($this->isLinkedItem($publicIndex['index_id'], $itemId)) {
            $ret = true;
        } else {
            foreach ($publicGroupIndex as $index) {
                if ($this->isLinkedItem($index['index_id'], $itemId)) {
                    $ret = true;
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     * update weight.
     *
     * @param int $indexId:index id
     *                           int $weight:weight
     *
     * @return bool true:success,false:failed
     */
    public function updateWeight($indexId, $weight)
    {
        $ret = true;
        $sql = "UPDATE $this->table set weight=".$weight;
        $sql = $sql.',last_update_date='.time();
        $sql = $sql.' WHERE index_id='.$indexId;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * get index's path.
     *
     * @param int $rootIndexId:root index id
     *                              int $indexId:index id
     *
     * @return string
     */
    public function getIndexPath($rootIndexId, $indexId)
    {
        $ret = array();
        static $level = -1;
        if ($rootIndexId == $indexId) {
            return $ret;
        }
        // dir level+1 when start
        ++$level;
        $sql = "SELECT index_id,parent_index_id,title,open_level FROM $this->table";
        $sql = $sql." WHERE index_id=$rootIndexId";

        $result = $this->execute($sql);
        if ($row = $this->fetchArray($result)) {
            if ($row['open_level'] == XOONIPS_OL_PRIVATE && $row['parent_index_id'] == XOONIPS_IID_ROOT) {
                $row['title'] = 'Private';
            }
            $row['title'] = str_repeat('&nbsp;&nbsp;', $level).$row['title'];
            $cnt = count($this->getChildIndexes($rootIndexId));
            if ($cnt > 0) {
                $row['title'] = $row['title']."($cnt)";
            }
            $ret[] = $row;
        }

        $sql = "SELECT index_id FROM $this->table";
        $sql = $sql." WHERE parent_index_id=$rootIndexId";

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret = array_merge($ret, $this->getIndexPath($row['index_id'], $indexId));
        }
        // dir level+1 when end
        --$level;

        return $ret;
    }

    /**
     * is locked.
     *
     * @param int $indexId:index id
     *
     * @return bool true:locked,false:not locked
     */
    public function isLocked($indexId)
    {
        $tblIndexItem = $this->prefix($this->dirname.'_index_item_link');
        $index = $this->getIndex($indexId);
        if ($index['open_level'] == XOONIPS_OL_PRIVATE) {
            return false;
        }

        $childs = $this->getAllChildIndexes($indexId);
        $indexIds = $indexId;
        foreach ($childs as $value) {
            $indexIds = $indexIds.','.$value['index_id'];
        }
        $sql = "SELECT item_id FROM $tblIndexItem";
        $sql = $sql." WHERE index_id IN ($indexIds) AND (certify_state=";
        $sql = $sql.XOONIPS_CERTIFY_REQUIRED.' OR certify_state='.XOONIPS_WITHDRAW_REQUIRED.')';

        if (($result = $this->execute($sql)) && $this->getRowsNum($result) > 0) {
            return true;
        }

        return false;
    }

    /**
     * is child index.
     *
     * @param int $parentIndexId:parent index id
     *                                  int $childIndexId:child index id
     *
     * @return bool true:yes,false:no
     */
    public function isChild($parentIndexId, $childIndexId)
    {
        $parent = $this->getAllChildIndexes($parentIndexId);
        foreach ($parent as $index) {
            if ($index['index_id'] == $childIndexId) {
                return true;
            }
        }

        return false;
    }

    /**
     * move index.
     *
     * @param int $indexId:index id
     *                           int $moveto:move to index id
     *
     * @return bool true:success,false:failed
     */
    public function moveto($indexId, $moveto)
    {
        $weight = $this->getMaxWeight($moveto) + 1;
        $sql = "UPDATE $this->table set parent_index_id=".$moveto;
        $sql = $sql.',weight='.$weight;
        $sql = $sql.',last_update_date='.time();
        $sql = $sql.' WHERE index_id='.$indexId;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * has same title index.
     *
     * @param int $indexId:index id
     *                           int $title:index title
     *                           bool $self:self index id
     *
     * @return bool true:has,false:has not
     */
    public function hasSameNameIndex($indexId, $title, $self = false)
    {
        $childs = $this->getChildIndexes($indexId);
        foreach ($childs as $index) {
            if ($index['title'] == $title && $index['index_id'] != $self) {
                return true;
            }
        }

        return false;
    }

    /**
     * get item list link index id.
     *
     * @param int $uid:user id
     *
     * @return string
     */
    public function getItemlistLinkIndex($uid)
    {
        $ret = '';
        $sql = "SELECT index_id FROM $this->table WHERE parent_index_id=".XOONIPS_IID_ROOT;
        if ($uid == XOONIPS_UID_GUEST) {
            $sql .= ' AND open_level='.XOONIPS_OL_PUBLIC;
        } else {
            $sql .= ' AND open_level='.XOONIPS_OL_PRIVATE." AND uid=$uid";
        }
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        if ($row = $this->fetchArray($result)) {
            $ret = $row['index_id'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * delete group index.
     *
     * @param int $groupid:group id
     *
     * @return bool true:success,false:failed
     */
    public function deleteGroupIndex($groupid)
    {
        $ret = true;
        $sql = "DELETE FROM $this->table WHERE groupid= $groupid and open_level=".XOONIPS_OL_GROUP_ONLY;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * delete group index.
     *
     * @param int $groupid:group id
     *
     * @return bool true:success,false:failed
     */
    public function deleteIndexByGid($groupid)
    {
        $ret = true;
        $sql = "DELETE FROM $this->table WHERE groupid= $groupid";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * delete user index.
     *
     * @param int $uid:uid
     *
     * @return bool
     */
    public function deleteIndexByUid($uid)
    {
        $sql = "DELETE FROM $this->table WHERE uid=$uid";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * count group index.
     *
     * @param int $groupid:group id
     *
     * @return int
     */
    public function countGroupIndexes($groupId)
    {
        $ret = 0;
        $sql = "SELECT COUNT(a.index_id) AS count FROM $this->table a WHERE a.groupid=$groupId AND a.open_level=".XOONIPS_OL_GROUP_ONLY;
        $result = $this->execute($sql);
        if (!$result) {
            return 0;
        }
        if ($row = $this->fetchArray($result)) {
            if ($row['count'] != 0) {
                $ret = $row['count'];
            }
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * count user index.
     *
     * @param int $uid:user id
     *
     * @return int
     */
    public function countUserIndexes($uid)
    {
        $ret = 0;
        $sql = "SELECT COUNT(a.index_id) AS count FROM $this->table a WHERE a.uid=$uid AND a.open_level=".XOONIPS_OL_PRIVATE;
        $result = $this->execute($sql);
        if (!$result) {
            return 0;
        }
        if ($row = $this->fetchArray($result)) {
            if ($row['count'] != 0) {
                $ret = $row['count'];
            }
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * insert group index.
     *
     * @param array $index
     *
     * @return bool true:success,false:failed
     */
    public function insertGroupIndex($index)
    {
        $ret = true;
        $weight = $this->getWeight($index['parent_index_id']) + 1;
        $sql = "INSERT INTO $this->table (parent_index_id,groupid,open_level,weight,title,last_update_date,creation_date)";
        $sql = $sql.' VALUES('.$index['parent_index_id'].','.Xoonips_Utils::convertSQLNum($index['groupid']).','.$index['open_level'];
        $sql = $sql.','.$weight.','.Xoonips_Utils::convertSQLStr($index['title']).','.time().','.time().')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = $this->getInsertId();

        return $ret;
    }

    /**
     * get group weight.
     *
     * @param int $parentIndexId:parent index id
     *
     * @return int
     */
    private function getWeight($parentIndexId)
    {
        $ret = 0;
        $sql = "SELECT MAX(weight) as weight FROM $this->table";
        $sql = $sql.' WHERE open_level<>'.XOONIPS_OL_PRIVATE." AND parent_index_id=$parentIndexId";
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        if ($row = $this->fetchArray($result)) {
            if ($row['weight'] != null) {
                $ret = $row['weight'];
            }
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get exist indexes by item_id.
     *
     * @param int $itemId: item id
     *
     * @return array
     */
    public function getIndexWithState($itemId, $indexIds)
    {
        $ret = array();
        $tblIndexItem = $this->prefix($this->modulePrefix('index_item_link'));
        $sql = 'SELECT t1.index_id, t1.certify_state, t2.open_level, t2.title, t2.parent_index_id, t2.uid '
            ." FROM $tblIndexItem t1, $this->table t2 WHERE t1.index_id=t2.index_id "
            ." AND t1.item_id=$itemId AND t1.index_id IN(".implode(',', $indexIds).') ORDER BY t2.open_level';
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[$row['index_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get indexes by checked index.
     *
     * @param int $indexIds: checked index id
     *
     * @return array
     */
    public function getIndexesByCheckedIndex($indexIds)
    {
        $ret = array();
        $sql = "SELECT DISTINCT index_id, open_level, title, parent_index_id, uid FROM $this->table "
            ." WHERE index_id in ($indexIds) ORDER BY open_level";
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[$row['index_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * update root group index.
     *
     * @param array $index:index data
     *
     * @return bool true:success,false:failed
     */
    public function updateRootGroupIndex($index)
    {
        $ret = true;
        $sql = "UPDATE $this->table set title=".Xoonips_Utils::convertSQLStr($index['title']);
        $sql = $sql.',last_update_date='.time();
        $sql = $sql.' WHERE groupid='.$index['groupid'];
        $sql = $sql.' AND open_level='.$index['open_level'];
        $sql = $sql.' AND parent_index_id='.$index['parent_index_id'];
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * check write right.
     *
     * @param int $indexId:index id
     *                           int $uid:user id
     *
     * @return bool true:yes,false:no
     */
    public function checkWriteRight($indexId, $uid)
    {
        if ($uid == XOONIPS_UID_GUEST) {
            return false;
        }
        $index = $this->getIndex($indexId);
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        // if public index
        if ($index['open_level'] == XOONIPS_OL_PUBLIC) {
            if (!$userBean->isModerator($uid)) {
                return false;
            }
        // if group index
        } elseif ($index['open_level'] == XOONIPS_OL_GROUP_ONLY) {
            if (!$userBean->isGroupManager($index['groupid'], $uid)) {
                return false;
            }
        // if private index
        } else {
            if ($index['uid'] != $uid) {
                return false;
            }
        }

        return true;
    }

    /**
     * can view.
     *
     * @param int $indexId:index id
     *                           int $uid:user id
     *
     * @return bool true:yes,false:no
     */
    public function canView($indexId, $uid)
    {
        $index = $this->getIndex($indexId);
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname);

        // moderator
        if ($userBean->isModerator($uid)) {
            return true;
        }

        // if public index
        if ($index['open_level'] == XOONIPS_OL_PUBLIC) {
            return true;
        // if group index
        } elseif ($index['open_level'] == XOONIPS_OL_GROUP_ONLY) {
            // if group public
            if ($groupBean->isPublic($index['groupid'])) {
                return true;
            } else {
                if ($userBean->isGroupManager($index['groupid'], $uid)
                        || $userBean->isGroupMember($index['groupid'], $uid)) {
                    return true;
                }
            }
        // if private index
        } else {
            if ($index['uid'] == $uid) {
                return true;
            }
        }

        return false;
    }

    /**
     * insert private index.
     *
     * @param array $index
     *
     * @return bool true:success,false:failed
     */
    public function insertPrivateIndex($uid)
    {
        $ret = true;
        $usersBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $user = $usersBean->getUserBasicInfo($uid);
        $weight = 16777215 - $this->countPrivateIndex();
        $sql = "INSERT INTO $this->table (parent_index_id,uid,open_level,weight,title,last_update_date,creation_date)";
        $sql = $sql.' VALUES(1,'.Xoonips_Utils::convertSQLNum($user['uid']);
        $sql = $sql.','.XOONIPS_OL_PRIVATE.','.$weight.','.Xoonips_Utils::convertSQLStr($user['uname']).','.time().','.time().')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = $this->getInsertId();

        return $ret;
    }

    /**
     * count of private index.
     *
     * @param
     *
     * @return int
     */
    private function countPrivateIndex()
    {
        $ret = 0;
        $sql = "SELECT count(index_id) FROM $this->table WHERE open_level=";
        $sql = $sql.XOONIPS_OL_PRIVATE.' AND parent_index_id='.XOONIPS_IID_ROOT;
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        if ($row = $this->fetchRow($result)) {
            if ($row[0] != null) {
                $ret = $row[0];
            }
        }

        return $ret;
    }

    /**
     * get public index's full path.
     *
     * @param
     *
     * @return array
     */
    public function getPublicFullPath($assoc_array_mode = false)
    {
        $publicIndex = $this->getPublicIndexes();
        $publicGroupIndexes = $this->getPublicGroupIndexes();
        $publicIndex = $this->mergeIndexes($publicIndex, $publicGroupIndexes);
        foreach ($publicIndex as $index) {
            $indexes = $this->getFullPathIndexes($index['index_id']);
            if ($indexes != false) {
                $fullpath = '';
                $ids = array();
                foreach ($indexes as $idx) {
                    $fullpath = $fullpath.'/'.$idx['title'];
                    $ids[] = $idx['index_id'];
                }
                $id_fullpath = implode(',', $ids);
                if ($assoc_array_mode) {
                    $ret[$index['index_id']] = array('id' => $index['index_id'], 'fullpath' => $fullpath, 'id_fullpath' => $id_fullpath);
                } else {
                    $ret[] = array('id' => $index['index_id'], 'fullpath' => $fullpath, 'id_fullpath' => $id_fullpath);
                }
            }
        }

        return $ret;
    }

    /**
     * filter valid index.
     *
     * @param int $item_id:item id
     *                          array $index_ids
     *
     * @return
     */
    public function filterValidIndex($item_id, &$index_ids)
    {
        $itemUserBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        foreach ($index_ids as $key => $index_id) {
            $index = $this->getIndex($index_id);
            if ($index['open_level'] == XOONIPS_OL_PRIVATE) {
                if (!$itemUserBean->isLink($item_id, $index['uid'])) {
                    unset($index_ids[$key]);
                }
            }
        }
    }

    /**
     * get can veiw indexes.
     *
     * @param int $item_id:item id
     *                          int $uid:user id
     *                          bool $isEdit
     *
     * @return array
     */
    public function getCanVeiwIndexes($item_id, $uid)
    {
        $ret = array();
        /*
         $authen_list[index_level][index_state][group_state]=user_type
            index_level
            1:public
            2:group
            3:private
            index_sate
            0:no public no share
            1:public share request
            2:public share
            3:public share withdraw request
            group_state
            3:public
            0:other
            user_type
            1:guest
            2:login user
            3:item user
            4:index group member
            5:index group member and item user
            6:index group admin
            7:moderator
            8:self
            */
        //---------------------- detail--------------------------
        // public request
        $authen_list[1][1][0] = '3,7';
        // public withdraw request
        $authen_list[1][3][0] = '1,2,3,4,5,6,7,8';
        // public
        $authen_list[1][2][0] = '1,2,3,4,5,6,7,8';

        // public group share request
        $authen_list[2][1][3] = '5,6,7';

        // public group share withdraw request
        $authen_list[2][3][3] = '1,2,3,4,5,6,7,8';

        // public group share
        $authen_list[2][2][3] = '1,2,3,4,5,6,7,8';

        // group share request
        $authen_list[2][1][0] = '5,6,7';

        // group share withdraw request
        $authen_list[2][3][0] = '4,5,6,7';

        // group share
        $authen_list[2][2][0] = '4,5,6,7';

        // private
        $authen_list[3][0][0] = '7,8';

        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname);

        // get index item link info
        $linkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $linkInfo = $linkBean->getIndexItemLinkInfo($item_id);

        // get can view indexes
        foreach ($linkInfo as $obj) {
            $index = $this->getIndex($obj['index_id']);
            $index_level = $index['open_level'];
            $index_state = $obj['certify_state'];
            if ($index['open_level'] == XOONIPS_OL_PRIVATE) {
                $index_state = 0;
                $group_state = 0;
            } elseif ($index['open_level'] == XOONIPS_OL_GROUP_ONLY) {
                if ($groupBean->isPublic($index['groupid'])) {
                    $group_state = 3;
                } else {
                    $group_state = 0;
                }
            } elseif ($index['open_level'] == XOONIPS_OL_PUBLIC) {
                $group_state = 0;
            }
            if (isset($authen_list[$index_level][$index_state][$group_state])) {
                $authen = $authen_list[$index_level][$index_state][$group_state];
                $user_type = $this->parseUser($item_id, $index['index_id'], $uid);
                if (count(array_intersect($user_type, explode(',', $authen))) > 0) {
                    $ret[] = $index['index_id'];
                }
            }
        }

        return $ret;
    }

    /**
     * get can veiw indexes.
     *
     * @param int $item_id:item id
     *                          int $uid:user id
     *                          bool $isEdit
     *
     * @return int
     *             1:guest
     *             2:login user
     *             3:item user
     *             4:index group member
     *             5:index group member and item user
     *             6:index group admin
     *             7:moderator
     *             8:self
     */
    private function parseUser($item_id, $index_id, $uid)
    {
        $ret = array();
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $itemUserBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        if ($uid == XOONIPS_UID_GUEST) {
            $ret[] = 1;

            return $ret;
        }
        if ($userBean->isModerator($uid)) {
            $ret[] = 7;
        }
        $index = $this->getIndex($index_id);
        if ($index['open_level'] == XOONIPS_OL_PRIVATE && $index['uid'] == $uid) {
            $ret[] = 8;

            return $ret;
        }
        if ($index['open_level'] == XOONIPS_OL_GROUP_ONLY) {
            // if group admin
            if ($userBean->isGroupManager($index['groupid'], $uid)) {
                $ret[] = 6;
            }
            // if group member
            if ($userBean->isGroupMember($index['groupid'], $uid)) {
                if ($itemUserBean->isLink($item_id, $uid)) {
                    $ret[] = 5;

                    return $ret;
                } else {
                    $ret[] = 4;

                    return $ret;
                }
            } else {
                if ($itemUserBean->isLink($item_id, $uid)) {
                    $ret[] = 3;

                    return $ret;
                }
            }
        }
        // if public
        if ($index['open_level'] == XOONIPS_OL_PUBLIC) {
            if ($itemUserBean->isLink($item_id, $uid)) {
                $ret[] = 3;

                return $ret;
            }
        }
        $ret[] = 2;

        return $ret;
    }

    public function getCanViewItemIds($index_id, $uid)
    {
        $ret = array();
        static $authen_list = array();
        /*
         $authen_list[index_level][index_state][group_state]=user_type
            index_level
            1:public
            2:group
            3:private
            index_sate
            0:no public no share
            1:public share request
            2:public share
            3:public share withdraw request
            group_state
            3:public
            0:other
            user_type
            1:guest
            2:login user
            3:item user
            4:index group member
            5:index group member and item user
            6:index group admin
            7:moderator
            8:self
            */
        //---------------------- detail--------------------------
        if (empty($authen_list)) {
            // public request
            $authen_list[1][1][0] = array(3, 7);
            // public withdraw request
            $authen_list[1][3][0] = array(1, 2, 3, 4, 5, 6, 7, 8);
            // public
            $authen_list[1][2][0] = array(1, 2, 3, 4, 5, 6, 7, 8);

            // public group share request
            $authen_list[2][1][3] = array(3, 5, 6, 7);

            // public group share withdraw request
            $authen_list[2][3][3] = array(1, 2, 3, 4, 5, 6, 7, 8);

            // public group share
            $authen_list[2][2][3] = array(1, 2, 3, 4, 5, 6, 7, 8);

            // group share request
            $authen_list[2][1][0] = array(5, 6, 7);

            // group share withdraw request
            $authen_list[2][3][0] = array(4, 5, 6, 7);

            // group share
            $authen_list[2][2][0] = array(4, 5, 6, 7);

            // private
            $authen_list[3][0][0] = array(7, 8);
        }

        // get can view indexes
        static $user_check;
        if (!empty($user_check)) {
            $user_type = $user_check;
        } else {
            $user_type = 2;
            if ($uid == XOONIPS_UID_GUEST) {
                $user_type = 1;
            }
            if ($this->usersBean->isModerator($uid)) {
                $user_type = 7;
            }
            $user_check = $user_type;
        }

        foreach ($this->indexItemLinkBean->getIndexItemLinkInfo2($index_id) as $obj) {
            $index = $this->getIndex($obj['index_id']);
            if ($user_type == 2) {
                if ($index['open_level'] == XOONIPS_OL_PRIVATE) {
                    $obj['certify_state'] = 0;
                    $group_state = 0;
                    if ($index['uid'] == $uid) {
                        $user_type = 8;
                    }
                } elseif ($index['open_level'] == XOONIPS_OL_GROUP_ONLY) {
                    if ($this->groupBean->isPublic($index['groupid'])) {
                        $group_state = 3;
                    } else {
                        $group_state = 0;
                    }
                    // if group admin
                    if ($this->usersBean->isGroupManager($index['groupid'], $uid)) {
                        $user_type = 6;
                    } else {
                        // if group member
                        if ($this->usersBean->isGroupMember($index['groupid'], $uid)) {
                            if ($this->itemUsersLinkBean->isLink($obj['item_id'], $uid)) {
                                $user_type = 5;
                            } else {
                                $user_type = 4;
                            }
                        } else {
                            if ($this->itemUsersLinkBean->isLink($obj['item_id'], $uid)) {
                                $user_type = 3;
                            }
                        }
                    }
                } elseif ($index['open_level'] == XOONIPS_OL_PUBLIC) {
                    $group_state = 0;
                    if ($this->itemUsersLinkBean->isLink($obj['item_id'], $uid)) {
                        $user_type = 3;
                    }
                }
            } else {
                if ($index['open_level'] == XOONIPS_OL_PRIVATE) {
                    $obj['certify_state'] = 0;
                    $group_state = 0;
                } elseif ($index['open_level'] == XOONIPS_OL_GROUP_ONLY) {
                    if ($this->groupBean->isPublic($index['groupid'])) {
                        $group_state = 3;
                    } else {
                        $group_state = 0;
                    }
                } elseif ($index['open_level'] == XOONIPS_OL_PUBLIC) {
                    $group_state = 0;
                }
            }

            if (isset($authen_list[$index['open_level']][$obj['certify_state']][$group_state])) {
                if (in_array($user_type, $authen_list[$index['open_level']][$obj['certify_state']][$group_state])) {
                    $ret[] = $obj['item_id'];
                }
            }
        }

        return $ret;
    }

    public function countCanViewItem($index_id, $uid)
    {
        return count($this->getCanViewItemIds($index_id, $uid));
    }

    /**
     * get index's full path.
     *
     * @param int $indexId:index id
     * @param int $uid:userid    id
     *
     * @return string
     */
    public function getFullPathStr($indexId, $uid = 0)
    {
        global $xoopsUser;
        $ret = '';
        if ($uid == 0) {
            $uid = $xoopsUser->getVar('uid');
        }
        $indexes = $this->getFullPathIndexes($indexId);
        foreach ($indexes as $index) {
            if ($index['uid'] == $uid && $index['open_level'] == XOONIPS_OL_PRIVATE && $index['parent_index_id'] == XOONIPS_IID_ROOT) {
                $title = 'Private';
            } else {
                $title = $index['title'];
            }
            $ret = $ret.'/'.$title;
        }

        return $ret;
    }

    public function getAllChildIds($indexId)
    {
        if (!$indexId) {
            return array();
        }
        $ret = array($indexId);
        $childs = $this->getAllChildIndexes($indexId);
        foreach ($childs as $index) {
            $ret[] = $index['index_id'];
        }

        return $ret;
    }

    /**
     * get index data all.
     *
     * @param int $indexId:index id
     *
     * @return array
     */
    public function getIndexAll()
    {
        $ret = array();
        $sql = "SELECT * FROM $this->table";

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
     * get index's path of id.
     *
     * @param int $id, int $start_id
     *
     * @return string
     */
    public function getIndexIDPath($id, $start_id)
    {
        $ret = '';
        if ($id == $start_id) {
            return $ret;
        }

        $pid = $id;
        while ($pid != $start_id) {
            if ($pid == $id) {
                $ret = $id;
            } else {
                $ret = $pid.'/'.$ret;
            }

            $index = $this->getIndex($pid, true);
            if ($index == false) {
                return false;
            }
            $pid = $index['parent_index_id'];
        }

        return $ret;
    }

    /**
     * get id of index's title array
     * create new if not exist.
     *
     * @param array $parent, int $root, int $uid
     *
     * @return int
     */
    public function getIndexID($indexes, $root, $indexType, $isManager, $isModerator)
    {
        $ret = array();
        $parent = $root;
        for ($i = 0; $i < sizeof($indexes); ++$i) {
            $title = $indexes[$i];
            $ret = $this->getChildsIndex($parent, $title);
            if (!empty($ret)) {
                if ($i == sizeof($indexes) - 1) {
                    return $ret['index_id'];
                } else {
                    $parent = $ret;
                    continue;
                }
            } else {
                if ($indexType == 1 && empty($isModerator)) {
                    // error if index is public and user is not moderator
                    return false;
                }
                if ($indexType == 2 && empty($isModerator) && empty($isManager)) {
                    // error if index is group and user is not moderator or manager
                    return false;
                }
                $indexes_create = array_slice($indexes, $i);

                return $this->createIndex($indexes_create, $parent);
            }
        }
    }

    /**
     * insert index.
     *
     * @param int $indexId:index id
     *
     * @return int
     */
    private function createIndex($indexes, $parent)
    {
        $index = $parent;
        $index['parent_index_id'] = $parent['index_id'];
        for ($i = 0; $i < sizeof($indexes); ++$i) {
            $index['title'] = $indexes[$i];
            $ret = $this->insertIndex2($index);
            if (!$ret) {
                return 0;
            } else {
                if ($i == sizeof($indexes) - 1) {
                    return $ret;
                } else {
                    $index['parent_index_id'] = $ret;
                }
            }
        }
    }

    private function insertIndex2($index)
    {
        $weight = $this->getMaxWeight($index['parent_index_id']) + 1;
        $sql = "INSERT INTO $this->table (parent_index_id,uid,groupid,open_level,weight,title,last_update_date,creation_date)";
        $sql = $sql.' VALUES('.$index['parent_index_id'].','.Xoonips_Utils::convertSQLNum($index['uid']).','.Xoonips_Utils::convertSQLNum($index['groupid']).','.$index['open_level'];
        $sql = $sql.','.$weight.','.Xoonips_Utils::convertSQLStr($index['title']).','.time().','.time().')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = $this->getInsertId();

        return $ret;
    }

    /**
     * get id of title.
     *
     * @param int $parent, String $title, int $uid
     *
     * @return int
     */
    private function getChildsIndex($parent, $title)
    {
        $ret = array();
        $sql = "SELECT a.* FROM $this->table a";
        if (is_numeric($parent['uid'])) {
            $sql = $sql.' WHERE a.uid='.intval($parent['uid']);
        } else {
            $sql = $sql.' WHERE a.uid is NULL';
        }
        $sql = $sql.' AND a.open_level='.Xoonips_Utils::convertSQLNum($parent['open_level']).' AND a.parent_index_id='.Xoonips_Utils::convertSQLNum($parent['index_id']).' AND a.title ='.Xoonips_Utils::convertSQLStr($title);

        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }

        if ($row = $this->fetchArray($result)) {
            $ret = &$row;
            self::$index_cache[$row['index_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }
}
