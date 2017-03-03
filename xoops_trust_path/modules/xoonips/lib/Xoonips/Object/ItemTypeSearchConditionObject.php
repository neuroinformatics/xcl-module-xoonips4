<?php

namespace Xoonips\Object;

/**
 * item type search condition object.
 */
class ItemTypeSearchConditionObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('condition_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('title', XOBJ_DTYPE_STRING, '', true, 255);
    }
}
