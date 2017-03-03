<?php

namespace Xoonips\Object;

/**
 * item type sort detail object.
 */
class ItemTypeSortDetailObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('sort_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_field_detail_id', XOBJ_DTYPE_INT, null, false);
    }
}
