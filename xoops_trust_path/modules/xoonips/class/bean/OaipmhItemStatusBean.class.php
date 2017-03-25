<?php

require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/BeanBase.class.php';

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
        $sql = "INSERT INTO $this->table (item_id,timestamp,created_timestamp,is_deleted)"
            ." VALUES ($itemId,".time().','.time().',0)';
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
        $sql = 'INSERT INTO '.$this->table.' (item_id,timestamp,created_timestamp,is_deleted)'
            ." select distinct b.item_id,$time,$time,0 from $tblIndex a,$tblLink b"
            ." where a.groupid=$groupId and a.open_level=".XOONIPS_OL_GROUP_ONLY
            .' and a.index_id=b.index_id and b.certify_state>='.XOONIPS_CERTIFIED
            ." and b.item_id not in (select d.item_id from $tblIndex c,$tblLink d"
            .' where (c.open_level='.XOONIPS_OL_PUBLIC.' and c.index_id=d.index_id'
            .' and d.certify_state>='.XOONIPS_CERTIFIED.') or '
            .' (c.open_level='.XOONIPS_OL_GROUP_ONLY.' and c.index_id=d.index_id'
            .' and d.certify_state>='.XOONIPS_CERTIFIED.' and c.groupid in'
            ." (select e.groupid from $tblGroup e where (e.activate=".Xoonips_Enum::GRP_PUBLIC
            .' or e.activate='.Xoonips_Enum::GRP_CLOSE_REQUIRED.") and e.groupid!=$groupId)))"
            .' and b.item_id not in (select f.item_id from'.$this->table.' f)';
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
        $sql = "CREATE TEMPORARY TABLE $tblTemp select b.item_id from $tblIndex a,$tblLink b"
            ." where a.groupid=$groupId and a.open_level=".XOONIPS_OL_GROUP_ONLY
            .' and a.index_id=b.index_id and b.certify_state>='.XOONIPS_CERTIFIED
            ." and b.item_id not in (select d.item_id from $tblIndex c,$tblLink d"
            .' where (c.open_level='.XOONIPS_OL_PUBLIC.' and c.index_id=d.index_id'
            .' and d.certify_state>='.XOONIPS_CERTIFIED.') or'
            .' (c.open_level='.XOONIPS_OL_GROUP_ONLY.' and c.index_id=d.index_id'
            .' and d.certify_state>='.XOONIPS_CERTIFIED.' and c.groupid in'
            ." (select e.groupid from $tblGroup e where (e.activate=".Xoonips_Enum::GRP_PUBLIC
            .' or e.activate='.Xoonips_Enum::GRP_CLOSE_REQUIRED.") and e.groupid!=$groupId)))"
            .' and b.item_id in (select f.item_id from '.$this->table.' f)';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $sql = "UPDATE $this->table SET timestamp=".time().',is_deleted=0,modified_timestamp='.time()
            ." WHERE item_id in (select item_id from $tblTemp)";
        $result = $this->execute($sql);
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
        $sql = "UPDATE $this->table SET timestamp=".time().',deleted_timestamp='.time()
            .',is_deleted=1,modified_timestamp='.time()." WHERE item_id=$itemId";
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
        $sql = "UPDATE $this->table SET timestamp=".time().',deleted_timestamp='.time()
            .',is_deleted=1,modified_timestamp='.time().' WHERE item_id in'
            ." (select b.item_id from $tblIndex a,$tblLink b"
            ." where a.groupid=$groupId and a.open_level=".XOONIPS_OL_GROUP_ONLY
            .' and a.index_id=b.index_id and b.certify_state>='.XOONIPS_CERTIFIED
            ." and b.item_id not in (select item_id from $tblIndex c,$tblLink d"
            .' where (c.open_level='.XOONIPS_OL_PUBLIC.' and c.index_id=d.index_id'
            .' and d.certify_state>='.XOONIPS_CERTIFIED.') or'
            .' (c.open_level='.XOONIPS_OL_GROUP_ONLY.' and c.index_id=d.index_id'
            .' and d.certify_state>='.XOONIPS_CERTIFIED.' and c.groupid in'
            ." (select e.groupid from $tblGroup e where (e.activate=".Xoonips_Enum::GRP_PUBLIC
            .' or e.activate='.Xoonips_Enum::GRP_CLOSE_REQUIRED.") and e.groupid!=$groupId))))";
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
        $ret = array();
        $sql = "SELECT * FROM $this->table WHERE item_id=$itemId";
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
        $sql = "UPDATE $this->table SET timestamp=".time().', is_deleted=0, ';
        $sql = $sql.'deleted_timestamp=NULL, modified_timestamp='.time()." WHERE item_id=$itemId";
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
        $sql = "SELECT * FROM $this->table WHERE item_id=$itemId AND is_deleted=0";
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
        if ($statusInfo === false) {
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

    public function getOpenItem4Oaipmh($from, $until, $set, $startIID, $limit, $deletion_track)
    {
        $ret = array();
        $itemTable = $this->prefix($this->modulePrefix('item'));
        $itemtypeTable = $this->prefix($this->modulePrefix('item_type'));
        if ($limit < 0) {
            return false;
        }

        $sql_from = $this->table.' AS stat, '
            .$itemTable.' AS item LEFT JOIN '.$itemtypeTable
            .' AS itemtype on item.item_type_id=itemtype.item_type_id ';
        $where = '';
        $child_xids = array();
        if ($set && substr($set, 0, 5) != 'index') {  // item type mode
            $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
            $itemTypeInfo = $itemTypeBean->getItemTypeByName($set);
            if (!$itemTypeInfo) {
                return false;
            }
            $where .= ' itemtype.item_type_id='.$itemTypeInfo['item_type_id'].' AND ';
        } elseif ($set && substr($set, 0, 5) == 'index') {  // index number mode
            $set_indexes = explode(':', $set);
            if (count($set_indexes) > 0) {
                $base_xid = substr($set_indexes[count($set_indexes) - 1], 5, strlen($set_indexes[count($set_indexes) - 1]));
                $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
                $child_xids = $indexBean->getAllChildIds($base_xid);
                if (!$child_xids) {
                    return false;
                }
                $where .= ' link.index_id in('.implode(',', $child_xids).') AND ';
                $sql_from .= ' LEFT JOIN '.$this->prefix($this->modulePrefix('index_item_link')).' AS link on basic.item_id=link.item_id '
                       .' LEFT JOIN '.$this->prefix($this->modulePrefix('index')).' AS idx on link.index_id=idx.index_id ';
            }
        }
        $sql = 'SELECT distinct stat.item_id, item.item_type_id, item.doi, itemtype.name as item_type_name, itemtype.name as item_type_display, stat.is_deleted FROM '
            .$sql_from
            .' WHERE item.item_id=stat.item_id AND '
            .' item.item_type_id=itemtype.item_type_id AND '
            .$where;
        if ($from != 0) {
            $sql .= "$from <= stat.timestamp AND ";
        }
        if ($until != 0) {
            $sql .= " stat.timestamp <= $until AND ";
        }
        $sql .= " stat.item_id >= $startIID ";
        $sql .= ' AND (stat.is_deleted=0 OR (stat.is_deleted=1 && deleted_timestamp>';
        $sql .= (time() - 60 * 60 * 24 * $deletion_track).'))';
        $sql .= ' order by stat.item_id ';
        if ($limit > 0) {
            $sql .= " limit $limit";
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
