<?php

namespace Xoonips\Object;

/**
 * item field group object.
 */
class ItemFieldGroupObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('group_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('preselect', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('released', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('name', XOBJ_DTYPE_STRING, '', true, 255);
        $this->initVar('xml', XOBJ_DTYPE_STRING, '', true, 30);
        $this->initVar('weight', XOBJ_DTYPE_INT, null, true);
        $this->initVar('occurrence', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('update_id', XOBJ_DTYPE_INT, null, false);
    }
}
