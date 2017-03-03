<?php

namespace Xoonips\Object;

/**
 * item field group field detail link object.
 */
class ItemFieldGroupFieldDetailLinkObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('item_field_group_field_detail_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('group_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_field_detail_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('edit_weight', XOBJ_DTYPE_INT, null, true);
        $this->initVar('edit', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('weight', XOBJ_DTYPE_INT, null, true);
        $this->initVar('released', XOBJ_DTYPE_INT, 0, true);
    }
}
