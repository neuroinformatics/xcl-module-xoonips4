<?php

namespace Xoonips\Object;

/**
 * complement detail object.
 */
class ComplementDetailObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('complement_detail_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('complement_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('code', XOBJ_DTYPE_STRING, '', true, 30);
        $this->initVar('title', XOBJ_DTYPE_STRING, '', true, 255);
    }
}
