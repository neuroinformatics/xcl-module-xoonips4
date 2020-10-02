<?php

require_once dirname(__DIR__).'/core/DataTypeFactory.class.php';

/**
 * @brief operate xoonips_item_extend table
 */
class Xoonips_ItemExtendBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('item_field_detail', true);
    }

    /**
     * get item extend information by id.
     *
     * @param item_id , tableName, group_id
     *
     * @return item extend information
     */
    public function getItemExtendInfo($id, $tableName, $group_id = 0)
    {
        $group_id = ($group_id) ? $group_id : 0;
        $table = $this->prefix($tableName);
        $sql = 'SELECT * FROM `'.$table.'` WHERE `item_id`='.intval($id).' AND `group_id`='.intval($group_id);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $ret = [];
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * delete by id.
     *
     * @param  item_id , tableName, group_id
     *
     * @return bool true:success,false:failed
     */
    public function delete($id, $tableName, $group_id = 0)
    {
        $group_id = ($group_id) ? $group_id : 0;
        $table = $this->prefix($tableName);
        $sql = 'DELETE FROM `'.$table.'` WHERE `item_id`='.intval($id).' AND `group_id`='.intval($group_id);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * update by id.
     *
     * @param  item_id , tableName, group_id
     *
     * @return bool true:success,false:failed
     */
    public function update($id, $tableName, $group_id = 0)
    {
        $group_id = ($group_id) ? $group_id : 0;
        $table = $this->prefix($tableName);
        $sql = 'UPDATE `'.$table.'` SET `value`=`value`+1 WHERE `item_id`='.intval($id).' AND `group_id`='.intval($group_id);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * insert.
     *
     * @param  item_id , tableName, value, number, group_id
     *
     * @return bool true:success,false:failed
     */
    public function insert($id, $tableName, $value, $number = 1, $group_id = 0)
    {
        $group_id = ($group_id) ? $group_id : 0;
        $table = $this->prefix($tableName);
        $sql  = 'INSERT INTO `'.$table.'`';
        $sql .= ' (`item_id`, `group_id`, `value`, `occurrence_number`) VALUES (';
        $sql .= intval($id).', '.intval($group_id).', '.$value.', '.intval($number).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    public function insert2($id, $tableName, $number = 1, $group_id = 0)
    {
        $group_id = ($group_id) ? $group_id : 0;
        $table = $this->prefix($tableName);
        $sql  = 'INSERT INTO `'.$table.'` (`item_id`, `group_id`, `occurrence_number`)';
        $sql .= ' VALUES ('.intval($id).', '.intval($group_id).', '.intval($number).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * get Item Extend Table.
     *
     * @param
     *
     * @return table name
     */
    public function getItemExtendTable()
    {
        $ret = [];
        $sql = 'SELECT DISTINCT `table_name` FROM `'.$this->table.'` WHERE `released`=1';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            if (false !== strpos($row['table_name'], $this->modulePrefix('item_extend'))) {
                $ret[] = $row['table_name'];
            }
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * update by value.
     *
     * @param  item_id , tableName, occurrence_number ,group_id
     *
     * @return bool true:success,false:failed
     */
    public function updateVal($item_id, $tableName, $value, $occurrence_number, $group_id = 0)
    {
        $table = $this->prefix($tableName);
        $sql  = 'UPDATE  `' .$table. '` SET `value`='.$value;
        $sql .= ' WHERE `item_id`='.intval($item_id).' AND `group_id`='.intval($group_id).' AND `occurrence_number`='.intval($occurrence_number);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }
}
