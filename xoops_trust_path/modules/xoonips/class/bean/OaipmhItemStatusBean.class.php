<?php

/**
 * @brief operate xoonips_oaipmh_item_status table
 */
class Xoonips_OaipmhItemStatusBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('oaipmh_item_status', true);
    }

    /**
     * insert.
     *
     * @param int $itemId:item id
     *
     * @return bool true:success,false:failed
     */
    public function insert($itemId)
    {
        $sql = 'INSERT INTO `'.$this->table.'` (`item_id`, `timestamp`, `created_timestamp`, `is_deleted`)';
        $sql .= ' VALUES ('.intval($itemId).', '.time().', '.time().', 0)';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    public function insertByGroupOpen($groupId)
    {
        $tblIndex = $this->prefix($this->modulePrefix('index'));
        $tblLink = $this->prefix($this->modulePrefix('index_item_link'));
        $tblGroup = $this->prefix('groups');
        $time = time();
        $sql = 'INSERT INTO `'.$this->table.'` (`item_id`, `timestamp`, `created_timestamp`, `is_deleted`) ';
        $sql .= ' SELECT DISTINCT `b`.`item_id`, '.$time.', '.$time.', 0 FROM `'.$tblIndex.'` `a`, `'.$tblLink.'` `b` ';
        $sql .= ' WHERE `a`.`groupid`='.intval($groupId).' AND `a`.`open_level`='.XOONIPS_OL_GROUP_ONLY;
        $sql .= ' AND `a`.`index_id`=`b`.`index_id` AND `b`.`certify_state`>='.XOONIPS_CERTIFIED;
        $sql .= ' AND `b`.`item_id` NOT IN ';
        $sql .= ' ( SELECT `d`.`item_id` FROM `'.$tblIndex.'` `c`, `'.$tblLink.'` `d`';
        $sql .= ' WHERE (`c`.`open_level`='.XOONIPS_OL_PUBLIC.' AND `c`.`index_id`=`d`.`index_id`';
        $sql .= ' AND `d`.`certify_state`>='.XOONIPS_CERTIFIED.') OR ';
        $sql .= ' (`c`.`open_level`='.XOONIPS_OL_GROUP_ONLY.' AND `c`.`index_id`=`d`.`index_id`';
        $sql .= ' AND `d`.`certify_state`>='.XOONIPS_CERTIFIED.' AND `c`.`groupid` IN ';
        $sql .= ' (SELECT `e`.`groupid` FROM `'.$tblGroup.'` `e` WHERE (`e`.`activate`='.Xoonips_Enum::GRP_PUBLIC;
        $sql .= ' OR `e`.`activate`='.Xoonips_Enum::GRP_CLOSE_REQUIRED.') AND `e`.`groupid`!='.intval($groupId);
        $sql .= ' ))) AND `b`.`item_id` NOT IN (SELECT `f`.`item_id` FROM `'.$this->table.'` `f`)';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    public function updateByGroupOpen($groupId)
    {
        $tblIndex = $this->prefix($this->modulePrefix('index'));
        $tblLink = $this->prefix($this->modulePrefix('index_item_link'));
        $tblGroup = $this->prefix('groups');
        $tblTemp = $this->table.'_tmp';
        $sql1 = 'CREATE TEMPORARY TABLE `'.$tblTemp.'`';
        $sql1 .= ' SELECT `b`.`item_id` FROM `'.$tblIndex.'` `a`, `'.$tblLink.'` `b`';
        $sql1 .= ' WHERE `a`.`groupid`='.$groupId.' AND `a`.`open_level`='.XOONIPS_OL_GROUP_ONLY;
        $sql1 .= ' AND `a`.`index_id`=`b`.`index_id` AND `b`.`certify_state`>='.XOONIPS_CERTIFIED;
        $sql1 .= ' AND `b`.`item_id` NOT IN ';
        $sql1 .= ' ( SELECT `d`.`item_id` FROM `'.$tblIndex.'` `c`, `'.$tblLink.'` `d`';
        $sql1 .= ' WHERE (`c`.`open_level`='.XOONIPS_OL_PUBLIC.' AND `c`.`index_id`=`d`.`index_id`';
        $sql1 .= ' AND `d`.`certify_state`>='.XOONIPS_CERTIFIED.') ';
        $sql1 .= ' OR (`c`.`open_level`='.XOONIPS_OL_GROUP_ONLY.' AND `c`.`index_id`=`d`.`index_id`';
        $sql1 .= ' AND `d`.`certify_state`>='.XOONIPS_CERTIFIED.' AND `c`.`groupid` IN ';
        $sql1 .= ' ( SELECT `e`.`groupid` FROM `'.$tblGroup.'` `e` WHERE ( `e`.`activate`='.Xoonips_Enum::GRP_PUBLIC;
        $sql1 .= ' OR `e`.`activate`='.Xoonips_Enum::GRP_CLOSE_REQUIRED.') AND `e`.`groupid`!='.$groupId;
        $sql1 .= ' ))) AND `b`.`item_id` IN ( SELECT `f`.`item_id` FROM `'.$this->table.'` `f`)';
        $result = $this->execute($sql1);
        if (!$result) {
            return false;
        }
        $sql2 = 'UPDATE `'.$this->table.'` SET `timestamp`='.time().', `is_deleted`=0, `modified_timestamp`='.time();
        $sql2 .= ' WHERE `item_id` IN ( SELECT `item_id` FROM `'.$tblTemp.'`)';
        $result = $this->execute($sql2);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete by item id.
     *
     * @param int $itemId:item id
     *
     * @return bool true:success,false:failed
     */
    public function delete($itemId)
    {
        $sql = 'UPDATE `'.$this->table.'` SET `timestamp`='.time().', `deleted_timestamp`='.time();
        $sql .= ', `is_deleted`=1, `modified_timestamp`='.time().' WHERE `item_id`='.intval($itemId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    public function deleteByGroupOpen($groupId)
    {
        $tblIndex = $this->prefix($this->modulePrefix('index'));
        $tblLink = $this->prefix($this->modulePrefix('index_item_link'));
        $tblGroup = $this->prefix('groups');
        $time = time();
        $sql = 'UPDATE `'.$this->table.'` SET `timestamp`='.time().', `deleted_timestamp`='.time();
        $sql .= ', `is_deleted`=1, `modified_timestamp`='.time().' WHERE `item_id` IN ';
        $sql .= ' ( SELECT `b`.`item_id` FROM `'.$tblIndex.'` `a`, `'.$tblLink.'` `b`';
        $sql .= ' WHERE `a`.`groupid`='.intval($groupId).' AND `a`.`open_level`='.XOONIPS_OL_GROUP_ONLY;
        $sql .= ' AND `a`.`index_id`=`b`.`index_id` AND `b`.`certify_state`>='.XOONIPS_CERTIFIED;
        $sql .= ' AND `b`.`item_id` NOT IN ';
        $sql .= ' ( SELECT `item_id` FROM `'.$tblIndex.'` `c`, `'.$tblLink.'` `d`';
        $sql .= ' WHERE ( `c`.`open_level`='.XOONIPS_OL_PUBLIC.' AND `c`.`index_id`=`d`.`index_id`';
        $sql .= ' AND `d`.`certify_state`>='.XOONIPS_CERTIFIED.') OR';
        $sql .= ' ( `c`.`open_level`='.XOONIPS_OL_GROUP_ONLY.' AND `c`.`index_id`=`d`.`index_id`';
        $sql .= ' AND `d`.`certify_state`>='.XOONIPS_CERTIFIED.' AND `c`.`groupid` IN';
        $sql .= ' ( SELECT `e`.`groupid` FROM `'.$tblGroup.'` `e` WHERE ( `e`.`activate`='.Xoonips_Enum::GRP_PUBLIC;
        $sql .= ' OR `e`.`activate`='.Xoonips_Enum::GRP_CLOSE_REQUIRED.') AND `e`.`groupid`!='.intval($groupId);
        $sql .= ' ))))';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * select by item id.
     *
     * @param int $itemId:item id
     *
     * @return array
     */
    public function select($itemId)
    {
        $ret = [];
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `item_id`='.intval($itemId);
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
     * update by item id.
     *
     * @param int $itemId:item id
     *
     * @return bool true:success,false:failed
     */
    public function update($itemId)
    {
        $sql = 'UPDATE `'.$this->table.'` SET `timestamp`='.time().', `is_deleted`=0, ';
        $sql .= ' `deleted_timestamp`=NULL, `modified_timestamp`='.time().' WHERE `item_id`='.intval($itemId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update by item id.
     *
     * @param int $itemId:item id
     *                         array $changInfo
     *                         [0]:has change except index
     *                         [1]:has public index change
     *                         [2]:has public cancle
     *
     * @return bool true:success,false:failed
     */
    public function updateByChangeInfo($itemId, $changInfo)
    {
        // if open
        if ($this->isOpen($itemId)) {
            if ($changInfo[0] || $changInfo[1]) {
                if (!$this->update($itemId)) {
                    return false;
                }
            }
            if ($changInfo[2]) {
                if (!$this->delete($itemId)) {
                    return false;
                }
            }
        } else {
            if (!is_array($changInfo) || count($changInfo) < 3) {
                return true;
            }
            if ($changInfo[1]) {
                $statusInfo = $this->select($itemId);
                if (count($statusInfo) > 0) {
                    if (!$this->update($itemId)) {
                        return false;
                    }
                } else {
                    if (!$this->insert($itemId)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * is open item.
     *
     * @param int $itemId:item id
     *
     * @return bool true:yes,false:no
     */
    public function isOpen($itemId)
    {
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `item_id`='.intval($itemId).' AND `is_deleted`=0';
        $result = $this->execute($sql);
        if ($result && $this->fetchArray($result)) {
            return true;
        }
        $this->freeRecordSet($result);

        return false;
    }

    /**
     * update item status.
     *
     * @param int $itemId:item id
     *
     * @return bool true:yes,false:no
     */
    public function updateItemStatus($itemId)
    {
        $statusInfo = $this->select($itemId);
        if (false === $statusInfo) {
            return false;
        }
        if (count($statusInfo) > 0) {
            if (!$this->update($itemId)) {
                return false;
            }
        } else {
            if (!$this->insert($itemId)) {
                return false;
            }
        }

        return true;
    }

    public function getOpenItem4Oaipmh($from, $until, $set, $startIID, $limit, $deletion_track, $setSpec)
    {
        $ret = [];
        $itemTable = $this->prefix($this->modulePrefix('item'));
        $itemtypeTable = $this->prefix($this->modulePrefix('item_type'));
        if ($limit < 0) {
            return false;
        }

        $sql_from = $this->table.' AS stat, '
            .$itemTable.' AS item LEFT JOIN '.$itemtypeTable
            .' AS itemtype on item.item_type_id=itemtype.item_type_id ';
        $where = '';
        $child_xids = [];
        if ($set && 'index' != substr($set, 0, 5)) {  // item type mode
            $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
            $itemTypeInfo = $itemTypeBean->getItemTypeByName($set);
            if (!$itemTypeInfo) {
                return false;
            }
            $where .= ' `itemtype`.`item_type_id`='.intval($itemTypeInfo['item_type_id']).' AND ';
        } elseif ($set && 'index' == substr($set, 0, 5)) {  // index number mode
            $set_indexes = explode(':', $set);
            if (count($set_indexes) > 0) {
                $base_xid = substr($set_indexes[count($set_indexes) - 1], 5, strlen($set_indexes[count($set_indexes) - 1]));
                $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
                $child_xids = $indexBean->getAllChildIds($base_xid);
                if (!$child_xids) {
                    return false;
                }
                $imploded_ids = '';
                for ($i = 0; $i < count($child_xids); ++$i) {
                    $imploded_ids .= intval($child_xids[$i]);
                    if ($i < count($child_xids) - 1) {
                        $imploded_ids .= ',';
                    }
                }
                $where .= ' `link`.`index_id` IN ('.$imploded_ids.') AND ';
                $sql_from .= ' LEFT JOIN '.$this->prefix($this->modulePrefix('index_item_link')).' AS link on basic.item_id=link.item_id '
                       .' LEFT JOIN '.$this->prefix($this->modulePrefix('index')).' AS idx on link.index_id=idx.index_id ';
            }
        }
        if ($setSpec) {
            preg_match_all('/\d+/', $setSpec, $m);
            if (count($m) > 0) {
                $m = $m[0];
                if (count($m) > 0) {
                    $linkTable = $this->prefix($this->modulePrefix('index_item_link'));
                    $indexTable = $this->prefix($this->modulePrefix('index'));
                    $where .= ' EXISTS(SELECT 1 FROM '
                        .'`'.$linkTable.'` as `link` '
                        .' INNER JOIN `'.$indexTable.'` as `idx1` '
                        .'  ON `link`.`index_id` = `idx1`.`index_id` '
                        .' AND `idx1`.`index_id` = '.$m[count($m) - 1].' ';
                    for ($i = 1; $i < count($m); ++$i) {
                        $where .= ' INNER JOIN `'.$indexTable.'` as `idx'.($i + 1).'` '
                            .'  ON `idx'.($i + 1).'`.`index_id` = `idx'.$i.'`.`parent_index_id` '
                            .' AND `idx'.($i + 1).'`.`index_id` = '.$m[count($m) - ($i + 1)].' ';
                    }
                    $where .= ' WHERE `item`.`item_id` = `link`.`item_id`) AND ';
                }
            }
        }

        $sql = 'SELECT distinct `stat`.`item_id`, `item`.`item_type_id`, `item`.`doi`, `itemtype`.`name` as `item_type_name`, `itemtype`.`name` as `item_type_display`, `stat`.`is_deleted` FROM '
            .$sql_from
            .' WHERE `item`.`item_id`=`stat`.`item_id` AND '
            .' `item`.`item_type_id`=`itemtype`.`item_type_id` AND '
            .$where;
        if (0 != $from) {
            $sql .= intval($from).'<=`stat`.`timestamp` AND ';
        }
        if (0 != $until) {
            $sql .= ' `stat`.`timestamp`<='.intval($until).' AND ';
        }
        $deletion_track = intval($deletion_track);
        $sql .= ' `stat`.`item_id`>='.intval($startIID);
        $sql .= ' AND (`stat`.`is_deleted`=0 OR (`stat`.`is_deleted`=1 && `deleted_timestamp`>';
        $sql .= (time() - 60 * 60 * 24 * $deletion_track).'))';
        $sql .= ' ORDER BY `stat`.`item_id` ';
        if ($limit > 0) {
            $sql .= ' LIMIT '.intval($limit);
        }
        if ($result = $this->execute($sql)) {
            while ($row = $this->fetchArray($result)) {
                $row['nijc_code'] = $nijc_code;
                $ret[] = $row;
            }
        }

        return $ret;
    }
}
