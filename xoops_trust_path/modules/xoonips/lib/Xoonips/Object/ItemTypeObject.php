<?php

namespace Xoonips\Object;

/**
 * item type object.
 */
class ItemTypeObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('item_type_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('preselect', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('released', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('weight', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('name', XOBJ_DTYPE_STRING, null, true, 30);
        $this->initVar('description', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('icon', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('mime_type', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('template', XOBJ_DTYPE_TEXT, null, false);
        $this->initVar('update_id', XOBJ_DTYPE_INT, null, false);
    }
}
