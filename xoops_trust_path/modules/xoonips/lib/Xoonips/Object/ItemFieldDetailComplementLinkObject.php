<?php

namespace Xoonips\Object;

/**
 * item field detail complement link object.
 */
class ItemFieldDetailComplementLinkObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('seq_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('released', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('complement_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('base_group_id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('base_item_field_detail_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('complement_detail_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('group_id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('item_field_detail_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('update_id', XOBJ_DTYPE_INT, null, false);
    }
}
