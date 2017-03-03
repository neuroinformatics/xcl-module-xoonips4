<?php

namespace Xoonips\Object;

/**
 * item related to object.
 */
class ItemRelatedToObject extends AbstractObject
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
        $this->initVar('child_item_id', XOBJ_DTYPE_INT, 0, true);
    }
}
