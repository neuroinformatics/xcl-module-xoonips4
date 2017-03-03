<?php

namespace Xoonips\Object;

/**
 * complement object.
 */
class ComplementObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('complement_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('view_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('title', XOBJ_DTYPE_STRING, '', true, 30);
        $this->initVar('module', XOBJ_DTYPE_STRING, null, false, 255);
    }
}
