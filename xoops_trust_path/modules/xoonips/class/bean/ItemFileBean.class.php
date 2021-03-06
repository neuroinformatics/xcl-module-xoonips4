<?php

/**
 * @brief operate xoonips_item_file table
 */
class Xoonips_ItemFileBean extends Xoonips_BeanBase
{
    private $detailtable;

    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('item_file', true);

        $this->detailtable = $this->prefix($this->modulePrefix('item_field_detail'));
    }

    /**
     * count file.
     *
     * @param
     *
     * @return int
     */
    public function countFile()
    {
        $sql = 'SELECT COUNT(*) AS `cnt` FROM `'.$this->table.'`';
        $result = $this->execute($sql);
        if (!$result) {
            return 0;
        }
        $row = $this->fetchArray($result);
        $this->freeRecordSet($result);

        return $row['cnt'];
    }

    /**
     * insert file.
     *
     * @param array $file
     *
     * @return bool true:success,false:failed
     */
    public function insertFile($file)
    {
        $ret = true;
        if (empty($file['group_id'])) {
            $group_id = 0;
        } else {
            $group_id = Xoonips_Utils::convertSQLNum($file['group_id']);
        }
        $sql = 'INSERT INTO `'.$this->table.'` (`item_id`, `item_field_detail_id`, `original_file_name`, `mime_type`, `file_size`, `handle_name`,';
        $sql .= ' `caption`, `sess_id`, `search_module_name`, `search_module_version`, `timestamp`, `download_count`, `occurrence_number`, `group_id`)';
        $sql .= ' VALUES ('.Xoonips_Utils::convertSQLNum($file['item_id']);
        $sql .= ','.Xoonips_Utils::convertSQLNum($file['item_field_detail_id']).','.Xoonips_Utils::convertSQLStr($file['original_file_name']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($file['mime_type']).','.Xoonips_Utils::convertSQLNum($file['file_size']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($file['handle_name']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($file['caption']).','.Xoonips_Utils::convertSQLStr($file['sess_id']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($file['search_module_name']).','.Xoonips_Utils::convertSQLNum($file['search_module_version']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($file['timestamp']).','.Xoonips_Utils::convertSQLNum($file['download_count']);
        $sql .= ','.Xoonips_Utils::convertSQLNum($file['occurrence_number']).','.$group_id.')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * InsertFile get file id.
     *
     * @param type $file
     * @param type $fileId
     *
     * @return bool
     */
    public function insertFileWithFileId($file, &$fileId)
    {
        $ret = $this->insertFile($file);
        if (true == $ret) {
            $fileId = $this->getInsertId();
        }

        return $ret;
    }

    /**
     * insert file.
     *
     * @param array $file
     *
     * @return bool true:success,false:failed
     */
    public function insertUploadFile($file, &$insertId)
    {
        $sql = 'INSERT INTO `'.$this->table.'` (`item_id`, `item_field_detail_id`, `original_file_name`, `mime_type`, `file_size`, `sess_id`, `search_module_name`, `search_module_version`, `group_id`, `timestamp`)';
        $sql .= ' VALUES ('.Xoonips_Utils::convertSQLNum($file['item_id']).','.Xoonips_Utils::convertSQLNum($file['item_field_detail_id']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($file['original_file_name']).','.Xoonips_Utils::convertSQLStr($file['mime_type']);
        $sql .= ','.Xoonips_Utils::convertSQLNum($file['file_size']).','.Xoonips_Utils::convertSQLStr($file['sess_id']);
        $sql .= ','.Xoonips_Utils::convertSQLStr($file['search_module_name']).','.Xoonips_Utils::convertSQLStr($file['search_module_version']);
        $sql .= ','.Xoonips_Utils::convertSQLNum($file['group_id']).', 0)';

        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $insertId = $this->getInsertId();

        return true;
    }

    /**
     * update file.
     *
     * @param int $fileId:file_id
     *                            array $file
     *
     * @return bool true:success,false:failed
     */
    public function updateFile($fileId, $file)
    {
        $ret = true;
        $sql = 'UPDATE `'.$this->table.'` SET `item_id`='.Xoonips_Utils::convertSQLNum($file['item_id']);
        $sql .= ',`item_field_detail_id`='.Xoonips_Utils::convertSQLNum($file['item_field_detail_id']);
        $sql .= ',`original_file_name`='.Xoonips_Utils::convertSQLStr($file['original_file_name']);
        $sql .= ',`mime_type`='.Xoonips_Utils::convertSQLStr($file['mime_type']);
        $sql .= ',`file_size`='.Xoonips_Utils::convertSQLNum($file['file_size']);
        $sql .= ',`handle_name`='.Xoonips_Utils::convertSQLStr($file['handle_name']);
        $sql .= ',`caption`='.Xoonips_Utils::convertSQLStr($file['caption']);
        $sql .= ',`sess_id`='.Xoonips_Utils::convertSQLStr($file['sess_id']);
        $sql .= ',`search_module_name`='.Xoonips_Utils::convertSQLStr($file['search_module_name']);
        $sql .= ',`search_module_version`='.Xoonips_Utils::convertSQLNum($file['search_module_version']);
        $sql .= ',`timestamp`='.Xoonips_Utils::convertSQLNum($file['timestamp']);
        $sql .= ',`download_count`='.Xoonips_Utils::convertSQLNum($file['download_count']);
        $sql .= ' WHERE `file_id`='.intval($fileId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * update file.
     *
     * @param int $fileId:file_id
     *                            array $file
     *
     * @return bool true:success,false:failed
     */
    public function updateFile2($fileId, $file)
    {
        $ret = true;
        if (empty($file['group_id'])) {
            $group_id = 0;
        } else {
            $group_id = Xoonips_Utils::convertSQLNum($file['group_id']);
        }
        $sql = 'UPDATE `'.$this->table.'` SET `item_id`='.Xoonips_Utils::convertSQLNum($file['item_id']);
        $sql .= ',`item_field_detail_id`='.Xoonips_Utils::convertSQLNum($file['item_field_detail_id']);
        $sql .= ',`original_file_name`='.Xoonips_Utils::convertSQLStr($file['original_file_name']);
        $sql .= ',`mime_type`='.Xoonips_Utils::convertSQLStr($file['mime_type']);
        $sql .= ',`file_size`='.Xoonips_Utils::convertSQLNum($file['file_size']);
        $sql .= ',`handle_name`='.Xoonips_Utils::convertSQLStr($file['handle_name']);
        $sql .= ',`caption`='.Xoonips_Utils::convertSQLStr($file['caption']);
        $sql .= ',`sess_id`='.Xoonips_Utils::convertSQLStr($file['sess_id']);
        $sql .= ',`search_module_name`='.Xoonips_Utils::convertSQLStr($file['search_module_name']);
        $sql .= ',`search_module_version`='.Xoonips_Utils::convertSQLNum($file['search_module_version']);
        $sql .= ',`timestamp`='.Xoonips_Utils::convertSQLNum($file['timestamp']);
        $sql .= ',`download_count`='.Xoonips_Utils::convertSQLNum($file['download_count']);
        $sql .= ',`occurrence_number`='.Xoonips_Utils::convertSQLNum($file['occurrence_number']);
        $sql .= ' WHERE `file_id`='.intval($fileId).' AND `group_id`='.intval($group_id);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * delete item file.
     *
     * @param int $itemId:item_id
     *
     * @return bool true:success,false:failed
     */
    public function deleteFiles($itemId)
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
     * get file.
     *
     * @param int $fileId:file id
     *
     * @return array
     */
    public function getFile($fileId)
    {
        $ret = [];
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `file_id`='.intval($fileId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        while ($row = $this->fetchArray($result)) {
            $ret = $row;
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * get file.
     *
     * @param
     *
     * @return array
     */
    public function getFiles()
    {
        $ret = [];
        $sql = 'SELECT * FROM `'.$this->table.'`';
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
     * get files.
     *
     * @param int $itemId:item_id
     * @param int $group_id
     *
     * @return array
     */
    public function getFilesByItemId($itemId, $group_id = null)
    {
        $ret = [];

        $g_sql = '';
        if (!is_null($group_id)) {
            $g_sql = ' AND `group_id`='.intval($group_id);
        }

        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `item_id`='.intval($itemId).$g_sql;
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
     * count group file size.
     *
     * @param int $groupId:groupId
     *
     * @return int
     */
    public function countGroupFileSizes($groupId)
    {
        $ret = 0;
        $tblIndex = $this->prefix($this->modulePrefix('index'));
        $tblLink = $this->prefix($this->modulePrefix('index_item_link'));
        $sql = 'SELECT SUM(`c`.`file_size`) AS `sum` FROM `'.$this->table.'` `c` WHERE `c`.`item_id` IN'
            .' (SELECT `b`.`item_id` FROM `'.$tblIndex.'` `a`,`'.$tblLink.'` `b`'
            .'   WHERE `a`.`groupid`='.intval($groupId).' AND `a`.`open_level`=2 AND `a`.`index_id`=`b`.index_id` AND `b`.`certify_state`>=2'
            .'   AND `b`.`item_id` NOT IN (SELECT `item_id` FROM `'.$tblIndex.'` `d`, `'.$tblLink.'` `e` WHERE `d`.`open_level`=1 AND `d`.`index_id`=`e`.`index_id` AND `e`.`certify_state`>=2'
            .' )'
            .')';
        $result = $this->execute($sql);
        if (!$result) {
            return 0;
        }
        if ($row = $this->fetchArray($result)) {
            if (0 != $row['sum']) {
                $ret = $row['sum'];
            }
        }
        $this->freeRecordSet($result);

        return $ret;
    }

    /**
     * count user file size.
     *
     * @param uid
     *
     * @return array
     */
    public function countUserFileSizes($uid)
    {
        $indexTable = $this->prefix($this->modulePrefix('index_item_link'));
        $userTable = $this->prefix($this->modulePrefix('item_users_link'));
        $sql = 'SELECT SUM(`a`.`file_size`) AS `sizes` FROM `'.$this->table.'` `a` INNER JOIN `'.$userTable.'` `b` ON `a`.`item_id`=`b`.`item_id`'
            .' WHERE `b`.`uid`='.intval($uid).' AND `b`.`item_id` NOT IN (SELECT `item_id` FROM `'.$indexTable.'` WHERE `certify_state`>=2)';
        $result = $this->execute($sql);
        if (!$result) {
            return 0;
        }
        $row = $this->fetchArray($result);
        $this->freeRecordSet($result);
        if (null === $row['sizes']) {
            return 0;
        }

        return $row['sizes'];
    }

    /**
     * delete file.
     *
     * @param int $fileId:file id
     *
     * @return bool true:success,false:failed
     */
    public function delete($fileId)
    {
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `file_id`='.intval($fileId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * can preview.
     *
     * @param int $fileId:file id
     *
     * @return bool true:can,false:can not
     */
    public function canPreview($fileId)
    {
        $tblDetail = $this->prefix($this->modulePrefix('item_field_detail'));
        $tblViewtype = $this->prefix($this->modulePrefix('view_type'));
        $sql = 'SELECT c.name FROM `'.$this->table.'` `a`'
            .' LEFT JOIN `'.$tblDetail.'` `b` ON (`a`.`item_field_detail_id`=`b`.`item_field_detail_id`))'
            .' LEFT JOIN `'.$tblViewtype.'` `c` ON (`c`.`view_type_id`=`b`.`view_type_id`)'
            .' WHERE `a`.`file_id`='.intval($fileId);
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }
        $row = $this->fetchArray($result);
        $this->freeRecordSet($result);
        if ($row && 'preview' == $row['name']) {
            return true;
        }

        return false;
    }

    /**
     * get file information.
     *
     * @param int $fileId: file id
     *
     * @return array
     */
    public function getFileInformation($fileId)
    {
        $ret = [];
        $sql = 'SELECT `original_file_name`, `file_size`, `mime_type`, `timestamp`, `download_count`';
        $sql .= ' FROM '.$this->table.' WHERE `file_id`='.intval($fileId);
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
     * get files.
     *
     * @param int $item_detail_id:item_id
     * @param int $item_id
     * @param int $group_id
     *
     * @return array
     */
    public function getFilesByDetailId($item_detail_id, $item_id, $group_id = 0)
    {
        $ret = [];
        $sql = 'SELECT * FROM `'.$this->table.'` WHERE `item_field_detail_id`='.intval($item_detail_id).' AND `item_id`='.intval($item_id).' AND `group_id`='.intval($group_id);
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
}
