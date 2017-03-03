<?php

namespace Xoonips\Object;

/**
 * item changelog object.
 */
class ItemChangelogObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('log_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('uid', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('log_date', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('log', XOBJ_DTYPE_TEXT, null, false);
    }
}
