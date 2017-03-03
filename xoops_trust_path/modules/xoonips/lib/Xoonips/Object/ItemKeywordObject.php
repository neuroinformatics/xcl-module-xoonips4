<?php

namespace Xoonips\Object;

/**
 * item keyword object.
 */
class ItemKeywordObject extends AbstractObject
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
        $this->initVar('keyword_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('keyword', XOBJ_DTYPE_STRING, '', true, 255);
    }
}
