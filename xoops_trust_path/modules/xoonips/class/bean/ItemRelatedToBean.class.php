<?php

require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/BeanBase.class.php';

/**
 * @brief operate xoonips_item_related_to table
 */
class Xoonips_ItemRelatedToBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('item_related_to', true);
    }

    /**
     * get item_related_to information by id.
     *
     * @param int $id:item_id
     *
     * @return item_related_to information
     */
    public function getRelatedToInfo($id)
    {
        $id = Xoonips_Utils::convertSQLNum($id);
        $sql = "SELECT * FROM $this->table WHERE item_id=$id";
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

    /**
     * deleteboth by id.
     *
     * @param int $id:item_id
     *
     * @return bool
     */
    public function deleteBoth($id)
    {
        $id = Xoonips_Utils::convertSQLNum($id);
        $sql = "DELETE FROM $this->table WHERE item_id=$id OR child_item_id=$id";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

  /**
   * update before delete.
   *
   * @param array $related
   *
   * @return bool
   */
  public function deletefield($related)
  {
      $item_id = Xoonips_Utils::convertSQLNum($related['item_id']);
      $child_item_id = Xoonips_Utils::convertSQLNum($related['child_item_id']);
      $sql = "DELETE FROM $this->table"
            ." WHERE item_id=$item_id"
            ." AND child_item_id=$child_item_id";
      $result = $this->execute($sql);
      if (!$result) {
          return false;
      }

      return true;
  }

  /**
   * Insert related.
   *
   * @param array $related
   *
   * @return boolaen true:Sucess,false:Fail
   */
  public function insert($related)
  {
      $item_id = Xoonips_Utils::convertSQLNum($related['item_id']);
      $child_item_id = Xoonips_Utils::convertSQLNum($related['child_item_id']);
      $sql = "INSERT INTO $this->table (item_id,child_item_id)"
        ." VALUES( $item_id , $child_item_id )";
      $result = $this->execute($sql);
      if (!$result) {
          return false;
      }

      return true;
  }

  /**
   * Update related.
   *
   * @param array $related
   *
   * @return boolaen true:Sucess,false:Fail
   */
  public function update($related)
  {
      $item_id = Xoonips_Utils::convertSQLNum($related['item_id']);
      $child_item_id = Xoonips_Utils::convertSQLNum($related['child_item_id']);
      $original_related_id = Xoonips_Utils::convertSQLNum($related['related_to']);
      $sql = "UPDATE $this->table SET child_item_id = $child_item_id"
        ." WHERE item_id = $item_id"
        ." AND child_item_id = $original_related_id";
      $result = $this->execute($sql);
      if (!$result) {
          return false;
      }

      return true;
  }
}
