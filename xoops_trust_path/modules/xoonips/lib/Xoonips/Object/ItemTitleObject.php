<?php

namespace Xoonips\Object;

/**
 * item title object.
 */
class ItemTitleObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('item_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_field_detail_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('title_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('title', XOBJ_DTYPE_TEXT, null, true);
    }
}
