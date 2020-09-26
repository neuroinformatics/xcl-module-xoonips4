<?php

namespace Xoonips\Object;

use Xoonips\Core\Functions;

/**
 * item file object.
 */
class ItemFileObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('file_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('item_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('group_id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('item_field_detail_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('original_file_name', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('mime_type', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('file_size', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('handle_name', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('caption', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('sess_id', XOBJ_DTYPE_STRING, null, false, 32);
        $this->initVar('search_module_name', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('search_module_version', XOBJ_DTYPE_INT, null, false);
        $this->initVar('timestamp', XOBJ_DTYPE_INT, null, true);
        $this->initVar('download_count', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('occurrence_number', XOBJ_DTYPE_INT, 1, true);
    }

    /**
     * get file path.
     *
     * @return string
     */
    public function getFilePath()
    {
        $upload_dir = Functions::getXoonipsConfig($this->mDirname, 'upload_dir');
        $itemId = $this->get('item_id');
        $fileId = $this->get('file_id');
        if (0 == $itemId) {
            $fpath = $upload_dir.'/'.$fileId;
        } else {
            $fpath = $upload_dir.'/'.$itemId.'/'.$fileId;
        }

        return $fpath;
    }
}
