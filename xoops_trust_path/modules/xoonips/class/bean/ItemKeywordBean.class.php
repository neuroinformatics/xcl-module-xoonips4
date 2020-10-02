<?php

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
        $sql  = 'INSERT INTO `'.$this->table.'` (`item_id`,`keyword_id`,`keyword`) VALUES ';
        $sql .= '('.intval($keyword['item_id']).','.intval($keyword['keyword_id']).','.Xoonips_Utils::convertSQLStr($keyword['keyword']).')';
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
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `item_id`='.intval($id);
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
        $ret = [];
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `item_id`='.intval($id).' ORDER BY `keyword_id`';
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
        $sql = 'UPDATE `'.$this->table.'` SET `keyword`='.Xoonips_Utils::convertSQLStr($keyword).' WHERE `item_id`='.intval($id);
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
        $sql  = 'UPDATE `'.$this->table.'` SET `keyword`='.Xoonips_Utils::convertSQLStr($keyword);
        $sql .= ' WHERE `item_id`='.intval($id).' AND `keyword_id`='.intval($keyword_id);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        //$this->freeRecordSet($result);
        return $ret;
    }
}
