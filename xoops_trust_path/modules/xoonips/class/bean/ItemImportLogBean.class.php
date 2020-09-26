<?php

/**
 * @brief operate xoonips_item_changelog table
 */
class Xoonips_ItemImportLogBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('item_import_log', true);
        $this->linktable = $this->prefix($this->modulePrefix('item_import_link'));
        $this->itemtable = $this->prefix($this->modulePrefix('item'));
    }

    /**
     * Get the ID generated from the previous INSERT operation.
     *
     * @return int
     */
    public function getInsertId()
    {
        return $this->db->getInsertId();
    }

    /**
     * get ImportLogByUID.
     *
     * @param int $uid
     *
     * @return array
     */
    public function getImportLogByUID($uid)
    {
        $ret = [];
        $sql = 'SELECT * FROM '.$this->table.' WHERE uid='.$uid.' ORDER BY timestamp DESC';
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
     * get ImportLogInfo.
     *
     * @param int $id:item_import_log_id
     *
     * @return array
     */
    public function getImportLogInfo($id)
    {
        $sql = 'SELECT * FROM '.$this->table.' WHERE item_import_log_id='.$id;
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);
        $this->freeRecordSet($result);

        return $row;
    }

    /**
     *  get ImportLogItems.
     *
     * @param int $id:item_import_log_id
     *
     * @return array
     */
    public function getImportLogItems($id)
    {
        $ret = [];
        /*
        $sql = "SELECT i.* FROM ".$this->itemtable." AS i"
        ." LEFT JOIN ".$this->linktable." AS l ON i.item_id=l.item_id"
        ." WHERE l.item_import_log_id=$id";
        */
        $sql = 'SELECT * FROM '.$this->linktable." WHERE item_import_log_id=$id";
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
     * insert importLog.
     *
     * @param array $importLog
     *
     * @return bool true:success,false:failed
     */
    public function insert($importLog)
    {
        $sql = sprintf('INSERT INTO `%s` (`uid`, `result`, `log`, `timestamp`) VALUES(%d, %d, %s, %d)', $this->table, Xoonips_Utils::convertSQLNum($importLog['uid']), Xoonips_Utils::convertSQLNum($importLog['result']), Xoonips_Utils::convertSQLStr($importLog['log']), Xoonips_Utils::convertSQLNum($importLog['timestamp']));
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * insert importLogLink.
     *
     * @param $id, $item_id
     *
     * @return bool true:success,false:failed
     */
    public function insertLink($id, $item_id)
    {
        $sql = 'INSERT INTO '.$this->linktable.' (item_import_log_id,item_id)'
        .' VALUES('.Xoonips_Utils::convertSQLNum($id)
        .','.Xoonips_Utils::convertSQLNum($item_id)
        .')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }
}
