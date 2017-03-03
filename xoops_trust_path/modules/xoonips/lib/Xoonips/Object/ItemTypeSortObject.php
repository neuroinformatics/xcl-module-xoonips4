<?php

namespace Xoonips\Object;

/**
 * item type sort object.
 */
class ItemTypeSortObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('sort_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('title', XOBJ_DTYPE_STRING, '', true, 255);
    }
}
