<?php

/**
 * @brief operate xoonips_index_item_link table
 */
class Xoonips_IndexItemLinkBean extends Xoonips_BeanBase
{
    private static $cache = array();

    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('index_item_link', true);
    }

    /**
     * get index item link information by item id.
     *
     * @param int $id :item_id
     *
     * @return array
     */
    public function getIndexItemLinkInfo($item_id)
    {
        $sql = "SELECT * FROM $this->table WHERE item_id=".$item_id;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
            self::$cache[$row['index_id']][$row['item_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get index item link information by index id.
     *
     * @param int $index_id :item_id
     *
     * @return array
     */
    public function getIndexItemLinkInfo2($index_id)
    {
        $sql = "SELECT * FROM $this->table WHERE index_id=".$index_id;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
            self::$cache[$row['index_id']][$row['item_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get public index item link information by item id.
     *
     * @param int $id :item_id
     *
     * @return array
     */
    public function getPublicIndexItemLinkInfo($item_id)
    {
        global $xoopsDB;
        $indexTable = $xoopsDB->prefix($this->modulePrefix('index'));
        $sql = "SELECT a.* FROM $this->table a,$indexTable b WHERE a.item_id=".$item_id;
        $sql .= ' AND a.index_id=b.index_id and b.open_level='.XOONIPS_OL_PUBLIC;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
            self::$cache[$row['index_id']][$row['item_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get index item link information by item id and index id.
     *
     * @param int $itemId  :item id
     * @param int $indexId :index id
     *
     * @return array
     */
    public function getInfo($itemId, $indexId)
    {
        if (isset(self::$cache[$indexId][$itemId])) {
            return self::$cache[$indexId][$itemId];
        }
        $sql = "SELECT * FROM $this->table WHERE item_id=$itemId and index_id=$indexId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        if ($row = $this->fetchArray($result)) {
            $ret = $row;
            self::$cache[$row['index_id']][$row['item_id']] = $row;
        }

        return $ret;
    }

    /**
     * get index item link information by index item id.
     *
     * @param int $id :item_id
     *
     * @return array
     */
    public function getIndexItemLinkInfoByIndexItemLinkId($indexItemLinkId)
    {
        $sql = "SELECT * FROM $this->table WHERE index_item_link_id=".$indexItemLinkId;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        if ($row = $this->fetchArray($result)) {
            $ret = $row;
            self::$cache[$row['index_id']][$row['item_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get item id  by index id.
     *
     * @param int $id :index_id
     *
     * @return array
     */
    public function getItemIdsByIndexId($id)
    {
        $ret = array();
        $sql = "SELECT index_id, item_id FROM $this->table WHERE index_id=".$id;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row['item_id'];
            self::$cache[$row['index_id']][$row['item_id']] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * delete by id.
     *
     * @param int $id:item_id
     *
     * @return bool true:success,false:failed
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
     * delete by id.
     *
     * @param int $indexId:index_id
     *
     * @return bool true:success,false:failed
     */
    public function deleteByIndexId($indexId)
    {
        $sql = "DELETE FROM $this->table WHERE index_id=$indexId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     *  judge if the item is Pending.
     *
     * @param int $id:item_id
     *
     * @return bool true:is Pending,false:not Pending
     */
    public function isPending($itemId)
    {
        $sql = "SELECT item_id FROM $this->table";
        $sql = $sql." WHERE item_id = $itemId AND (certify_state=".XOONIPS_CERTIFY_REQUIRED;
        $sql = $sql.' OR certify_state='.XOONIPS_WITHDRAW_REQUIRED.')';
        if (($result = $this->execute($sql)) && $this->getRowsNum($result) > 0) {
            return true;
        }

        return false;
    }

    /**
     * get index item link (except certify_state=3).
     *
     * @param string $indexIds :index_ids
     *
     * @return array
     */
    public function exceptWithDraw($indexIds, $itemId)
    {
        $ret = array();
        $sql = "SELECT DISTINCT index_id FROM $this->table WHERE index_id IN ( $indexIds ) AND";
        $sql = $sql." item_id=$itemId AND certify_state<>".XOONIPS_WITHDRAW_REQUIRED;
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row['index_id'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * insert.
     *
     * @param int $indexId:index_id,$itemId:item_id,$certifyState:certify_state
     *
     * @return bool true:success,false:failed
     */
    public function insert($indexId, $itemId, $certifyState, &$indexItemLinkId = 0)
    {
        $sql = "INSERT INTO $this->table (index_id, item_id, certify_state) ";
        $sql = $sql."VALUES ($indexId, $itemId, $certifyState)";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $indexItemLinkId = $this->getInsertId();

        return true;
    }

    /**
     * update.
     *
     * @param int $indexId:index_id,$itemId:item_id,$certifyState:certify_state
     *
     * @return bool true:success,false:failed
     */
    public function update($indexId, $itemId, $certifyState)
    {
        $sql = "UPDATE $this->table SET certify_state=$certifyState WHERE index_id=$indexId AND item_id=$itemId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete by combination of index id and item id.
     *
     * @param int $indexId:index_id,$itemId:item_id
     *
     * @return bool true:success,false:failed
     */
    public function deleteById($indexId, $itemId)
    {
        $sql = "DELETE FROM $this->table WHERE index_id=$indexId AND item_id=$itemId";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * deletePrivateIndex.
     *
     * @param  $itemId:item_id
     *
     * @return bool true:success,false:failed
     */
    public function deletePrivateIndex($itemId)
    {
        $sql = "DELETE FROM $this->table WHERE item_id=$itemId AND certify_state=".XOONIPS_NOT_CERTIFIED;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * is linked to user index.
     *
     * @param int $item_id :item_id
     *                     int $uid:user id
     *
     * @return bool:true-yes,false-no
     */
    private function isLinked2UserIndex($item_id, $uid)
    {
        $ret = false;
        $tblIndex = $this->prefix($this->modulePrefix('index'));
        $sql = "SELECT a.index_id FROM $this->table a, $tblIndex b ";
        $sql = $sql." WHERE a.index_id=b.index_id AND a.item_id=$item_id";
        $sql = $sql.' AND b.open_level='.XOONIPS_OL_PRIVATE." AND b.uid=$uid ";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        if ($row = $this->fetchArray($result)) {
            $ret = true;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get index item link information by id.
     *
     * @param int $id :item_id
     *
     * @return bool
     */
    public function Link2UserRootIndex($item_id, $uid)
    {
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        if ($this->isLinked2UserIndex($item_id, $uid) == false) {
            $index = $indexBean->getPrivateIndex($uid);

            return $this->insert($index['index_id'], $item_id, XOONIPS_NOT_CERTIFIED);
        }

        return true;
    }

    /**
     * get item linked pubic and public group index.
     *
     * @param int $item_id :item id
     *
     * @return array
     */
    public function getOpenIndexIds($item_id)
    {
        $ret = array();
        $tblIndex = $this->prefix($this->modulePrefix('index'));
        $tblGroup = $this->prefix('groups');
        $sql = "SELECT a.index_id FROM ($this->table a";
        $sql = $sql." LEFT JOIN $tblIndex b ON(a.index_id=b.index_id))";
        $sql = $sql." LEFT JOIN $tblGroup c ON(b.groupid=c.groupid)";
        $sql = $sql." WHERE a.item_id=$item_id AND (a.certify_state=".XOONIPS_CERTIFIED;
        $sql = $sql.' OR a.certify_state='.XOONIPS_WITHDRAW_REQUIRED.')';
        $sql = $sql.' AND (b.open_level='.XOONIPS_OL_PUBLIC;
        $sql = $sql.' OR (b.open_level='.XOONIPS_OL_GROUP_ONLY;
        $sql = $sql.' AND (c.activate='.Xoonips_Enum::GRP_PUBLIC;
        $sql = $sql.' OR c.activate='.Xoonips_Enum::GRP_CLOSE_REQUIRED.')))';
        $sql = $sql.' ORDER BY a.index_id';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row['index_id'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get item linked pubic and public group index.
     *
     * @param array $newIndexIds :new open index ids
     *                           array $oldIndexIds :old open index ids
     *
     * @return array
     *               [1]:public change
     *               [2]:public cancel
     */
    public function compareOpenIndex($newIndexIds, $oldIndexIds)
    {
        $ret = array(1 => false, 2 => false);
        if ($newIndexIds === false || $oldIndexIds === false) {
            return false;
        }
        if (count($oldIndexIds) > 0) {
            // public cancle
            if (count($newIndexIds) == 0) {
                $ret[2] = true;

                return $ret;
            }
            // public change
            foreach ($oldIndexIds as $key => $value) {
                if (!isset($newIndexIds[$key]) || $value != $newIndexIds[$key]) {
                    $ret[1] = true;

                    return $ret;
                }
            }
        } elseif (count($newIndexIds) > 0) {
            $ret[1] = true;

            return $ret;
        }

        return $ret;
    }

  /**
   * updateIndexid.
   *
   * @param int $indexId:index_id,$itemId:item_id,$certifyState:certify_state
   *
   * @return bool true:success,false:failed
   */
  public function updateIndexid($indexId, $itemId, $certifyState, $indexItemLinkId)
  {
      $sql = 'UPDATE '.$this->table." SET index_id = ${indexId} ,certify_state = ${certifyState},item_id = ${itemId} where index_item_link_id = $indexItemLinkId";
      $result = $this->execute($sql);
      if (!$result) {
          return false;
      }

      return true;
  }
}
