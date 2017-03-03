<?php

namespace Xoonips\Object;

/**
 * item type search condition detail object.
 */
class ItemTypeSearchConditionDetailObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('condition_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_field_detail_id', XOBJ_DTYPE_INT, 0, true);
    }
}
