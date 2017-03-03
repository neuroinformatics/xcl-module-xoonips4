<?php

namespace Xoonips\Object;

/**
 * item type field group link object.
 */
class ItemTypeFieldGroupLinkObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('item_type_field_group_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('item_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('group_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('edit_weight', XOBJ_DTYPE_INT, null, true);
        $this->initVar('edit', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('weight', XOBJ_DTYPE_INT, null, true);
        $this->initVar('released', XOBJ_DTYPE_INT, 0, true);
    }
}
