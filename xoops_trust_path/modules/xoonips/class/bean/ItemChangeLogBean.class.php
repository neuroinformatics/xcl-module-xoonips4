<?php

/**
 * @brief operate xoonips_item_changelog table
 */
class Xoonips_ItemChangeLogBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('item_changelog', true);
    }

    /**
     * get changeLogInfo.
     *
     * @param int $id:log id
     *
     * @return array
     */
    public function getChangeLogInfo($id)
    {
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `log_id`='.intval($id);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);
        $this->freeRecordSet($result);

        return $row;
    }

    /**
     *  get changelogs.
     *
     * @param int $item_id:item id
     *
     * @return array
     */
    public function getChangeLogs($item_id)
    {
        $ret = [];
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `item_id`='.intval($item_id).' ORDER BY `log_date`';
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
     * insert changeLog.
     *
     * @param array $changelog
     *
     * @return bool true:success,false:failed
     */
    public function insert($changelog)
    {
        $sql  = 'INSERT INTO `'.$this->table.'` (`uid`,`item_id`,`log_date`,`log`)';
        $sql .= ' VALUES('.intval($changelog['uid']).', '.intval($changelog['item_id']);
        $sql .= ', '.intval($changelog['log_date']).', '.Xoonips_Utils::convertSQLStr($changelog['log']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete changeLog.
     *
     * @param int $itemId:item id
     *
     * @return bool true:success,false:failed
     */
    public function delete($itemId)
    {
        $ret = true;
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `item_id`='.intval($itemId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * update changeLog.
     *
     * @param array $changelog
     *
     * @return bool true:success,false:failed
     */
    public function update($changelog)
    {
        $fragment = '';
        if (0 != $changelog['log_date']) {
            //$fragment = 'log_date = '.Xoonips_Utils::convertSQLNum($changelog['log_date']);
            $fragment = '`log_date`='.intval($changelog['log_date']);
        }
        if (!is_null($changelog['log'])) {
            if (strlen($fragment) > 0) {
                $fragment .= ',';
            }
            $fragment .= 'log='.Xoonips_Utils::convertSQLStr($changelog['log']);
        }

        $sql = 'UPDATE `'.$this->table.'` SET '.${fragment}.' WHERE `log_id`='.intval($changelog['log_id']);

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }
}
