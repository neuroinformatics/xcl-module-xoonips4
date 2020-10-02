<?php

/**
 * @brief operate xoonips_item_type_sort_detail table
 */
class Xoonips_ViewTypeBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('view_type', true);
    }

    /**
     * select itemtype viewtype.
     *
     * @param
     *
     * @return array
     */
    public function getViewtypeList()
    {
        $ret = [];
        $sql = 'SELECT * FROM `'.$this->table.'` ORDER BY `view_type_id`';
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret[] = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * select itemtype viewtype by id.
     *
     * @param
     *
     * @return array
     */
    public function getViewtypeById($viewId)
    {
        $ret = null;
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `view_type_id`='.intval($viewId);
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * select viewtype vy name.
     *
     * @param $name name
     *
     * @return int
     */
    public function selectByName($name)
    {
        $ret = '';
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `name`='.Xoonips_Utils::convertSQLStr($name);
        $result = $this->execute($sql);
        if (!$result) {
            return $ret;
        }
        while ($row = $this->fetchArray($result)) {
            $ret = $row['view_type_id'];
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * insert viewtype.
     *
     * @param array $viewtype
     * @param int   $insertId
     *
     * @return bool true:success,false:failed
     */
    public function insert($viewtype, &$insertId)
    {
        $sql = 'INSERT INTO `'.$this->table.'` (`preselect`,`multi`,`name`,`module`)';
        $sql .= ' VALUES('.intval($viewtype['preselect']).','.intval($viewtype['multi']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($viewtype['name']).','.Xoonips_Utils::convertSQLStr($viewtype['module']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $insertId = $this->getInsertId();

        return true;
    }
}
