<?php

use Xoonips\Core\Functions;

/**
 * item file object.
 */
class Xoonips_ItemFileObject extends XoopsSimpleObject
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->initVar('file_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('group_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('item_field_detail_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('original_file_name', XOBJ_DTYPE_STRING, null, true, 255);
        $this->initVar('mime_type', XOBJ_DTYPE_STRING, null, true, 255);
        $this->initVar('file_size', XOBJ_DTYPE_INT, null, true);
        $this->initVar('handle_name', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('caption', XOBJ_DTYPE_STRING, '', true, 255);
        $this->initVar('sess_id', XOBJ_DTYPE_STRING, null, false, 32);
        $this->initVar('search_module_name', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('search_module_version', XOBJ_DTYPE_INT, null, false);
        $this->initVar('timestamp', XOBJ_DTYPE_INT, null, true);
        $this->initVar('download_count', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('occurrence_number', XOBJ_DTYPE_INT, null, true);
    }

    /**
     * get url for download.
     *
     * @param int format
     *
     * @return string/bool false if failure
     */
    public function getDownloadUrl($format = 1)
    {
        $item_id = intval($this->get('item_id'));
        $itemHandler = &Functions::getXoonipsHandler('Item', $this->mDirname);
        $itemObj = &$itemHandler->get($item_id);
        if (!is_object($itemObj)) {
            return false;
        }
        $doi = $itemObj->get('doi');
        switch ($format) {
            case 1:
                return XOOPS_URL.'/modules/'.$this->mDirname.'/download.php/'.(empty($doi) ? $item_id : XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME.':'.$doi).'/'.$this->get('file_id').'/'.$this->get('original_file_name');
                break;
            case 2:
                return XOOPS_URL.'/modules/'.$this->mDirname.'/download.php?'.(empty($doi) ? 'item_id='.$item_id : XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME.'='.$doi);
                break;
        }
    }

    /**
     * get file path.
     *
     * @return string
     */
    public function getFilePath()
    {
        $upload_dir = Functions::getXoonipsConfig($this->mDirname, 'upload_dir');
        $item_id = $this->get('item_id');

        return $upload_dir.'/'.(empty($item_id) ? '' : 'item/'.$item_id.'/').$this->get('file_id');
    }
}

/**
 * item object handler.
 */
class Xoonips_ItemFileHandler extends XoopsObjectGenericHandler
{
    /**
     * constructor.
     *
     * @param XoopsDatabase &$db
     * @param string        $dirname
     */
    public function __construct(&$db, $dirname)
    {
        $this->mTable = $dirname.'_item_file';
        $this->mPrimary = 'file_id';
        $this->mClass = preg_replace('/Handler$/', 'Object', get_class());
        parent::__construct($db);
    }

    /**
     * get objects for download.
     *
     * @param int    $item_id
     * @param string $file_name
     *
     * @return &object[]
     */
    public function &getObjectsForDownload($item_id, $file_name = '')
    {
        $ret = array();
        $tableItemFieldDetail = $this->db->prefix($this->mDirname.'_item_field_detail');
        $tableViewType = $this->db->prefix($this->mDirname.'_view_type');
        $sql = sprintf('SELECT * FROM `%1$s` INNER JOIN `%2$s` ON `%1$s`.`item_field_detail_id`=`%2$s`.`item_field_detail_id` INNER JOIN `%3$s` ON `%2$s`.`view_type_id`=`%3$s`.`view_type_id` WHERE `%1$s`.`item_id`=%4$u AND `%3$s`.`module`=\'ViewTypeFileUpload\'', $this->mTable, $tableItemFieldDetail, $tableViewType, $item_id);
        if (!empty($file_name)) {
            $sql .= sprintf(' AND `%s`.`original_file_name`=%s', $this->mTable, $this->db->quoteString($file_name));
        }
        if ($result = $this->db->query($sql)) {
            while ($row = $this->db->fetchArray($result)) {
                $obj = new $this->mClass();
                $obj->mDirname = $this->getDirname();
                $obj->assignVars($row);
                $obj->unsetNew();
                $ret[] = &$obj;
                unset($obj);
            }
        }

        return $ret;
    }

    /**
     * get most downloaded item object.
     *
     * @param int $limit
     * @param int $term
     *
     * @return $ret
     */
    public function &getMostDownloadedItems($limit, $term)
    {
        $ret = array();
        $tableItem = $this->db->prefix($this->mDirname.'_item');
        $tableItemTitle = $this->db->prefix($this->mDirname.'_item_title');
        $tableIndex = $this->db->prefix($this->mDirname.'_index');
        $tableIndexItemLink = $this->db->prefix($this->mDirname.'_index_item_link');
        if ($limit < 1) {
            return;
        }

        $sql = sprintf('SELECT * FROM `%1$s` INNER JOIN `%2$s` ON `%1$s`.`item_id` = `%2$s`.`item_id` INNER JOIN `%3$s` ON `%1$s`.`item_id` = `%3$s`.`item_id` WHERE `%1$s`.`item_id` IN (SELECT DISTINCT `%5$s`.`item_id` FROM `%5$s` WHERE (`%5$s`.`certify_state` = %7$d OR `%5$s`.`certify_state` = %8$d) AND `%5$s`.`index_id` IN (SELECT `%4$s`.`index_id` FROM `%4$s` WHERE `%4$s`.`open_level` = %6$d)) AND ', $this->mTable, $tableItem, $tableItemTitle, $tableIndex, $tableIndexItemLink, XOONIPS_OL_PUBLIC, XOONIPS_CERTIFIED, XOONIPS_WITHDRAW_REQUIRED);
        if ($term != 0) {
            $sql .= sprintf('`%1$s`.`last_update_date` >= %2$d AND ', $tableItem, $term);
        }
        $sql .= sprintf('`%1$s`.`download_count` != 0 ORDER BY `%1$s`.`download_count` DESC limit %2$d', $this->mTable, $limit);

        if ($result = $this->db->query($sql)) {
            while ($row = $this->db->fetchArray($result)) {
                $r = array();
                $r['title'] = $row['title'];
                $r['download_count'] = $row['download_count'];
                $itemHandler = &Functions::getXoonipsHandler('Item', $this->mDirname);
                $itemObj = &$itemHandler->get($row['item_id']);
                $r['url'] = $itemObj->getUrl();
                unset($itemObj);
                $ret[] = $r;
            }
        }

        return $ret;
    }
}
