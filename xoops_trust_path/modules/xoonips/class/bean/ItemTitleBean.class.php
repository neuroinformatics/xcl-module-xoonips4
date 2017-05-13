<?php

require_once dirname(__DIR__).'/core/BeanBase.class.php';

/**
 * @brief operate xoonips_item_title table
 */
class Xoonips_ItemTitleBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('item_title', true);
    }

    /**
     * get item title information by id.
     *
     * @param item id
     *
     * @return item title information
     */
    public function getItemTitleInfo($id)
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

    /**
     * search item id  by title.
     *
     * @param title
     *
     * @return item id
     */
    public function searchItemIdByTitle($title)
    {
        $ret = array();
        $title = Xoonips_Utils::convertSQLStrLike($title);
        $sql = 'SELECT distinct item_id FROM '.$this->table." WHERE title LIKE '%$title%'";
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
     * delete by id.
     *
     * @param  item id
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

    public function getItemTitle($itemId)
    {
        $sql = 'SELECT * FROM '.$this->table.' WHERE item_id='.$itemId;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = '';
        if ($row = $this->fetchArray($result)) {
            $ret = $row['title'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

  /**
   * insert item_title.
   *
   * @param type   $item_id
   * @param type   $item_field_detail_id
   * @param stirng $title
   * @param int    $title_id
   *
   * @return bool true:Success,falase:Fail
   */
  public function insertTitle($item_id, $item_field_detail_id, $title, $title_id)
  {
      $sql = 'INSERT INTO '.$this->table.' (item_id,item_field_detail_id,title,title_id)';
      $sql .= ' VALUES(';
      $sql .= Xoonips_Utils::convertSQLNum($item_id);
      $sql .= ','.Xoonips_Utils::convertSQLNum($item_field_detail_id);
      $sql .= ','.Xoonips_Utils::convertSQLStr($title);
      $sql .= ','.Xoonips_Utils::convertSQLNum($title_id);
      $sql .= ')';
      $result = $this->execute($sql);
      if (!$result) {
          return false;
      }

      return true;
  }

  /**
   * update item_title.
   *
   * @param int    $item_id
   * @param int    $item_field_detail_id
   * @param string $title
   * @param int    $title_id
   *
   * @return bool true:Success,falase:Fail
   */
  public function updateTitle($item_id, $item_field_detail_id, $title, $title_id)
  {
      $sql = 'UPDATE '.$this->table." SET title=\"${title}\" where ".
    'item_id='.Xoonips_Utils::convertSQLNum($item_id).
    ' and item_field_detail_id = '.Xoonips_Utils::convertSQLNum($item_field_detail_id).
    ' and title_id = '.Xoonips_Utils::convertSQLNum($title_id);
      $result = $this->execute($sql);
      if (!$result) {
          return false;
      }

      return true;
  }
}
