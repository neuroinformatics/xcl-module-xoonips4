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

    /**
     * get encorded sort field.
     *
     * @return string
     */
    public function getEncodedSortField()
    {
        return sprintf('%u:%u:%u', $this->get('sort_id'), $this->get('item_type_id'), $this->get('item_field_detail_id'));
    }
}
