<?php

require_once dirname(__DIR__).'/core/BeanBase.class.php';

/**
 * @brief operate xoonips_item_keyword table
 */
class Xoonips_ItemKeywordBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('item_keyword', true);
    }

    /**
     * insert keyword.
     *
     * @param item keyword
     *
     * @return bool true:success,false:failed
     */
    public function insertKeyword($keyword)
    {
        $ret = true;
        $sql = "INSERT INTO $this->table (item_id,keyword_id,keyword)";
        $sql = $sql.' VALUES('.Xoonips_Utils::convertSQLNum($keyword['item_id']);
        $sql = $sql.','.Xoonips_Utils::convertSQLNum($keyword['keyword_id']).','.Xoonips_Utils::convertSQLStr($keyword['keyword']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * delete keywords by id.
     *
     * @param item id
     *
     * @return bool true:success,false:failed
     */
    public function delete($id)
    {
        $ret = true;
        $sql = "DELETE FROM $this->table WHERE item_id=$id";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * get keywords.
     *
     * @param item id
     *
     * @return array
     */
    public function getKeywords($id)
    {
        $ret = array();
        $sql = "SELECT * FROM $this->table WHERE item_id=$id ORDER BY keyword_id";
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
     * update keywords by id.
     *
     * @param item id,keyword
     *
     * @return bool true:success,false:failed
     */
    public function updateKeywords($id, $keyword)
    {
        $ret = true;
        $sql = "UPDATE $this->table set keyword=".Xoonips_Utils::convertSQLStr($keyword);
        $sql = $sql.' WHERE item_id='.$id;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        //$this->freeRecordSet($result);
        return $ret;
    }

    public function updateKeywords2($id, $keyword_id, $keyword)
    {
        $ret = true;
        $sql = "UPDATE $this->table set keyword=".Xoonips_Utils::convertSQLStr($keyword);
        $sql = $sql.' WHERE item_id='.$id.' and keyword_id='.$keyword_id;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        //$this->freeRecordSet($result);
        return $ret;
    }
}
