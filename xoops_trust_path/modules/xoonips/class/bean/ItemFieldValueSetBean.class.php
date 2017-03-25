<?php

require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/BeanBase.class.php';

/**
 * @brief operate xoonips_item_field_value_set table
 */
class Xoonips_ItemFieldValueSetBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('item_field_value_set', true);
    }

    /**
     * get select names.
     *
     * @return itemtype select_name
     */
    public function getSelectNames()
    {
        $sql = "SELECT DISTINCT select_name FROM $this->table ";
        $sql .= ' ORDER BY select_name';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = &$row['select_name'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * update item type weight.
     *
     * @param string $title_id    title_id
     * @param string $select_name select_name
     * @param int    $weight      weight
     *
     * @return bool true:success, false:failed
     */
    public function updateItemTypeWeight($title_id, $select_name, $weight)
    {
        $title_id = Xoonips_Utils::convertSQLStr($title_id);
        $select_name = Xoonips_Utils::convertSQLStr($select_name);
        $weight = Xoonips_Utils::convertSQLNum($weight);
        $sql = "UPDATE $this->table SET weight=$weight";
        $sql .= " WHERE title_id=$title_id";
        $sql .= " AND select_name=$select_name";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update select_name.
     *
     * @param string $oldname old select_name
     * @param string $newname new select_name
     *
     * @return bool true:success,false:failed
     */
    public function updateSelectName($oldname, $newname)
    {
        $oldname = Xoonips_Utils::convertSQLStr($oldname);
        $newname = Xoonips_Utils::convertSQLStr($newname);
        $sql = "UPDATE $this->table SET select_name=$newname";
        $sql .= " WHERE select_name=$oldname";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get item type by select names.
     *
     * @param string $select_name select name
     *
     * @return array itemtype select_name
     */
    public function getValueDetail($select_name)
    {
        $select_name = Xoonips_Utils::convertSQLStr($select_name);
        $sql = "SELECT * FROM $this->table";
        $sql .= " WHERE select_name=$select_name";
        $sql .= ' ORDER BY weight';
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
     * delete item field values.
     *
     * @param string $title_id    title_id
     * @param string $select_name select_name
     *
     * @return bool true:success,false:failed
     */
    public function deleteItemsValue($title_id, $select_name)
    {
        $title_id = Xoonips_Utils::convertSQLStr($title_id);
        $select_name = Xoonips_Utils::convertSQLStr($select_name);
        $sql = "DELETE FROM $this->table";
        $sql .= " WHERE title_id =$title_id";
        $sql .= " AND select_name =$select_name";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get the title of the select name.
     *
     * @param string $select_name select name
     *
     * @return bool true:success,false:failed
     */
    public function getItemTypeValueCount($select_name)
    {
        $select_name = Xoonips_Utils::convertSQLStr($select_name);
        $sql = "SELECT title FROM $this->table";
        $sql .= " WHERE select_name=$select_name";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return  $ret;
    }

    /**
     * check the new select name.
     *
     * @param string $newname new select name
     *
     * @return array select_name
     */
    public function checkSelectNames($newname)
    {
        $newname = Xoonips_Utils::convertSQLStr($newname);
        $sql = "SELECT select_name FROM $this->table";
        $sql .= " WHERE select_name=$newname";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return  $ret;
    }

    /**
     * get Info by title_id and select name.
     *
     * @param var $title_id    title_id
     * @param var $select_name select_name
     *
     * @return array value
     */
    public function getInfo($title_id, $select_name)
    {
        $title_id = Xoonips_Utils::convertSQLStr($title_id);
        $select_name = Xoonips_Utils::convertSQLStr($select_name);
        $sql = "SELECT * FROM $this->table";
        $sql .= " WHERE title_id=$title_id";
        $sql .= " AND select_name=$select_name";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);

        $this->freeRecordSet($result);

        return $row;
    }

    /**
     * get value set by select name.
     *
     * @param string $select_name select name
     *
     * @return array value set
     */
    public function getValue($select_name)
    {
        $select_name = Xoonips_Utils::convertSQLStr($select_name);
        $sql = "SELECT * FROM $this->table";
        $sql .= " WHERE select_name=$select_name";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);

        $this->freeRecordSet($result);

        return $row;
    }

    /**
     * check title.
     *
     * @param string $select_name select name
     * @param string $title       title
     *
     * @return bool
     */
    public function checkTitle($select_name, $title)
    {
        $select_name = Xoonips_Utils::convertSQLStr($select_name);
        $title = Xoonips_Utils::convertSQLStr($title);
        $sql = "SELECT count(select_name) AS cnt FROM $this->table";
        $sql .= " WHERE select_name=$select_name";
        $sql .= " AND title=$title";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = $this->fetchArray($result);

        $this->freeRecordSet($result);

        return $ret['cnt'];
    }

    /**
     * check title id.
     *
     * @param string $select_name select name
     * @param string $title_id    title id
     *
     * @return array
     */
    public function checkTitleId($select_name, $title_id)
    {
        $select_name = Xoonips_Utils::convertSQLStr($select_name);
        $title_id = Xoonips_Utils::convertSQLStr($title_id);
        $sql = "SELECT count(select_name) AS cnt FROM $this->table";
        $sql .= " WHERE select_name=$select_name";
        $sql .= " AND title_id=$title_id";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = $this->fetchArray($result);

        $this->freeRecordSet($result);

        return $ret['cnt'];
    }

    /**
     * check used title id.
     *
     * @param string $select_name select name
     *                            string $title_id id
     *
     * @return array
     */
    public function checkUsedTitleId($select_name, $title_id)
    {
        $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $itemTypeList = $itemTypeBean->getItemTypeList();
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $cnt = 0;
        foreach ($itemTypeList as $itemType) {
            $itemFieldManager = Xoonips_ItemFieldManagerFactory::getInstance($this->dirname, $this->trustDirname)->getItemFieldManager($itemType['item_type_id']);
            foreach ($itemFieldManager->getFields() as $field) {
                if ($field->getListId() == $select_name) {
                    $cnt = $cnt + $itemBean->getCountUsedFieldValue($field->getTableName(), $field->getColumnName(), $title_id);
                }
            }
        }

        return $cnt;
    }

    /**
     * get max.
     *
     * @param string $select_name select name
     * @param string $col         column name
     *
     * @return
     */
    public function getMax($select_name, $col)
    {
        $select_name = Xoonips_Utils::convertSQLStr($select_name);
        $sql = "SELECT MAX(CONVERT($col, UNSIGNED)) FROM $this->table"
        ." WHERE select_name=$select_name";
        $result = $this->execute($sql);
        $max = $this->fetchRow($result);
        if ($max[0] > 0) {
            return $max[0];
        }

        return 0;
    }

    /**
     * insert value.
     *
     * @param array $value value
     *
     * @return bool
     */
    public function insertValue($value)
    {
        $select_name = Xoonips_Utils::convertSQLStr($value['select_name']);
        $title_id = Xoonips_Utils::convertSQLStr($value['title_id']);
        $title = Xoonips_Utils::convertSQLStr($value['title']);
        $weight = Xoonips_Utils::convertSQLNum($value['weight']);
        $sql = "INSERT INTO $this->table (select_name, title_id, title, weight)";
        $sql .= " VALUES( $select_name , $title_id , $title , $weight )";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update value.
     *
     * @param array $value value
     *
     * @return bool
     */
    public function updateValue($value)
    {
        $select_name = Xoonips_Utils::convertSQLStr($value['select_name']);
        $title_id = Xoonips_Utils::convertSQLStr($value['title_id']);
        $title_id_db = Xoonips_Utils::convertSQLStr($value['title_id_db']);
        $title = Xoonips_Utils::convertSQLStr($value['title']);
        $sql = "UPDATE $this->table SET title=$title , title_id=$title_id";
        $sql .= " WHERE title_id=$title_id_db";
        $sql .= " AND select_name=$select_name";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get the title of the name.
     *
     * @param string $select_name select name
     * @param string $title_id    title_id
     *
     * @return bool true:success,false:failed
     */
    public function getItemTypeValueTitle($select_name, $title_id)
    {
        $select_name = Xoonips_Utils::convertSQLStr($select_name);
        $title_id = Xoonips_Utils::convertSQLStr($title_id);
        $sql = "SELECT title FROM $this->table";
        $sql .= " WHERE select_name=$select_name";
        $sql .= " AND title_id=$title_id";
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = '';
        while ($row = $this->fetchArray($result)) {
            $ret = $row['title'];
        }
        $this->freeRecordSet($result);

        return  $ret;
    }

        /**
         * get item list data for Export Item Type XML Element.
         *
         * @return itemtype select_name
         **/
        public function getExportItemTypeValueSet()
        {
            $sql = "SELECT * FROM $this->table ";
            $sql .= ' ORDER BY select_name';
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
}
