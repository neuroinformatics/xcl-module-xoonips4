<?php

namespace Xoonips\Object;

/**
 * item object.
 */
class ItemObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('item_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('item_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('doi', XOBJ_DTYPE_TEXT, null, false);
        $this->initVar('view_count', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('last_update_date', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('creation_date', XOBJ_DTYPE_INT, 0, true);
    }
}
