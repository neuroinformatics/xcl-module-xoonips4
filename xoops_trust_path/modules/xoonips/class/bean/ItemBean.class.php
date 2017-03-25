<?php

require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/BeanBase.class.php';

/**
 * @brief operate xoonips_item table
 */
class Xoonips_ItemBean extends Xoonips_BeanBase
{
    /**
   * Constructor.
   **/
  public function __construct($dirname, $trustDirname)
  {
      parent::__construct($dirname, $trustDirname);
      $this->setTableName('item', true);
      $this->typelinktable = $this->prefix($this->modulePrefix('item_type_field_group_link'));
      $this->grouplinktable = $this->prefix($this->modulePrefix('item_field_group_field_detail_link'));
      $this->detailtable = $this->prefix($this->modulePrefix('item_field_detail'));
      $this->grouptable = $this->prefix($this->modulePrefix('item_field_group'));
  }

  /**
   * get item basic information by id.
   *
   * @param int $id:item_id
   *
   * @return array
   **/
  public function getItemBasicInfo($id)
  {
      $sql = "SELECT * FROM $this->table WHERE item_id=".$id;
      $result = $this->execute($sql);
      if (!$result) {
          return false;
      }
      $row = $this->fetchArray($result);
      $this->freeRecordSet($result);

      return $row;
  }

  /**
   * get item basic information by doi.
   *
   * @param string $doi:doi
   *
   * @return array
   **/
  public function getBydoi($doi)
  {
      $sql = "SELECT * FROM $this->table WHERE doi=".Xoonips_Utils::convertSQLStr($doi);
      $result = $this->execute($sql);
      if (!$result) {
          return false;
      }
      $row = $this->fetchArray($result);
      $this->freeRecordSet($result);

      return $row;
  }

  /**
   * getBydoi method has bug!
   * if fetch empty record set ,then retunrn false.
   * But upper function expect array().
   *
   * @param type $doi
   *
   * @return type
   */
  public function getBydoi2($doi)
  {
      $sql = "SELECT * FROM $this->table WHERE doi=".Xoonips_Utils::convertSQLStr($doi);
      $result = $this->execute($sql);
      if (!$result) {
          return false;
      }
      $row = $this->fetchArray($result);
      $this->freeRecordSet($result);
      if (is_bool($row) && $row === false) {
          return array();
      }

      return $row;
  }

   /**
    * get doi information by item id.
    *
    * @param string $doi:doi
    *
    * @return item id
    **/
   public function getItemIdBydoi($doi)
   {
       $row = $this->getBydoi2($doi);
       if (empty($row)) {
           return false;
       }

       return $row['item_id'];
   }

  /**
   * check exist doi.
   *
   * @param blob $doi:doi
   *
   * @return bool true:success, false:failed
   **/
  public function checkExistdoi($itemid, $doi)
  {
      $sql = "SELECT * FROM $this->table WHERE item_id<>$itemid AND doi=".Xoonips_Utils::convertSQLStr($doi);
      $result = $this->execute($sql);
      if (!$result) {
          return false;
      }
      $ret = false;
      while ($row = $this->fetchArray($result)) {
          $ret = true;
      }
      $this->freeRecordSet($result);

      return $ret;
  }

  /**
   * update the View Count by id.
   *
   * @param  int $id:item_id
   *
   * @return  bool true:success,false:failed
   */
  public function updateViewCount($id)
  {
      $sql = "UPDATE $this->table SET view_count=view_count+1 WHERE item_id=".$id;
      $result = $this->execute($sql);

      if (!$result) {
          return false;
      }

      return true;
  }

  /**
   * delete by id.
   *
   * @param  int $id:item_id
   *
   * @return  bool true:success,false:failed
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

    public function groupby($item_ids)
    {
        $ret = array();
        $sql = "SELECT * FROM $this->table WHERE item_id IN(".implode(',', $item_ids).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[$row['item_type_id']][] = $row['item_id'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

  /**
   * check title.
   *
   * @param  var $name:name
   *          var $title:title
   *
   * @return  bool
   */
  public function checkItemtype($itemtypeid)
  {
      $sql = 'SELECT COUNT(item_id) AS cnt FROM '.$this->table.' WHERE item_type_id='.$itemtypeid;
      $result = $this->execute($sql);
      if (!$result) {
          return false;
      }
      $ret = $this->fetchArray($result);
      $this->freeRecordSet($result);

      return $ret['cnt'];
  }

  /**
   * check item field.
   *
   * @param  var $name:name
   *         var $title:title
   *
   * @return  bool
   */
  public function checkItemfield($itemfieldid)
  {
      $sql = 'SELECT count(i.item_id) as cnt FROM '.$this->table.' AS i'
    .' LEFT JOIN '.$this->typelinktable.' AS g ON i.item_type_id=g.item_type_id'
    .' LEFT JOIN '.$this->grouplinktable.' AS d ON g.group_id=d.group_id'
    .' WHERE d.item_field_detail_id='.$itemfieldid;
      $result = $this->execute($sql);
      if (!$result) {
          return false;
      }
      $ret = $this->fetchArray($result);

      $this->freeRecordSet($result);

      return $ret['cnt'];
  }

  /**
   * check item group.
   *
   * @param  var $name:name
   *          var $title:title
   *
   * @return  bool
   */
  public function checkItemgroup($itemgroupid)
  {
      $sql = 'SELECT count(i.item_id) as cnt FROM '.$this->table.' AS i'
    .' LEFT JOIN '.$this->typelinktable.' AS g ON i.item_type_id=g.item_type_id'
    .' WHERE g.group_id='.$itemgroupid;
      $result = $this->execute($sql);
      if (!$result) {
          return false;
      }
      $ret = $this->fetchArray($result);

      $this->freeRecordSet($result);

      return $ret['cnt'];
  }

  /**
   * get item_field_detail values from item_id.
   *
   * @param int $item_id
   *
   * @return more than 1 array:success, 0 array:fail
   */
  public function getItemTypeDetails($item_id)
  {
      $sql = 'select detail.* from '.$this->typelinktable.' as link, '.
    $this->table.' as item, '.
    $this->grouplinktable.' as grp, '.
    $this->detailtable.' as detail where '.
    'item.item_type_id = link.item_type_id and '.
    'link.group_id = grp.group_id and '.
    'detail.item_field_detail_id =grp.item_field_detail_id and '.
    'item_id ='.$item_id;

      $result = $this->execute($sql);
      $ret = array();
      if (!$result) {
          return array();
      }
      while ($row = $this->fetchArray($result)) {
          $ret[] = $row;
      }
      $this->freeRecordSet($result);

      return $ret;
  }

  /**
   * Get ItemGroup by item_id.
   *
   * @param int $item_id
   *
   * @return array item_file
   */
  public function getItemFieldGroup($item_id)
  {
      $sql = 'select grp.*,item_field_detail_id from '.$this->typelinktable.' as link, '.
    $this->table.' as item, '.
    $this->grouplinktable.' as grplnk, '.
    $this->grouptable.' as grp where '.
    'item.item_type_id = link.item_type_id and '.
    'link.group_id = grp.group_id and '.
    'link.group_id = grplnk.group_id and '.
    'grplnk.released = 1 and '.
    'grp.released = 1 and '.
    'link.released = 1 and '.
    'item_id ='.$item_id;
      $result = $this->execute($sql);
      $ret = array();
      if (!$result) {
          return array();
      }
      while ($row = $this->fetchArray($result)) {
          $ret[] = $row;
      }
      $this->freeRecordSet($result);

      return $ret;
  }
}
