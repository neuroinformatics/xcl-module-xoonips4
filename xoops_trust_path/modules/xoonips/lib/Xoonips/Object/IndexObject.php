<?php

namespace Xoonips\Object;

/**
 * index object.
 */
class IndexObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('index_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('parent_index_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('groupid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('open_level', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('weight', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('title', XOBJ_DTYPE_STRING, '', true, 255);
        $this->initVar('detailed_title', XOBJ_DTYPE_TEXT, null, false);
        $this->initVar('icon', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('mime_type', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('detailed_description', XOBJ_DTYPE_TEXT, null, false);
        $this->initVar('last_update_date', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('creation_date', XOBJ_DTYPE_INT, 0, true);
    }
}
