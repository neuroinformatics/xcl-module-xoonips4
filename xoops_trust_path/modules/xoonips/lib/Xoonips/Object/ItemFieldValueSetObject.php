<?php

namespace Xoonips\Object;

/**
 * item field value set object.
 */
class ItemFieldValueSetObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('select_name', XOBJ_DTYPE_STRING, '', true, 50);
        $this->initVar('title_id', XOBJ_DTYPE_STRING, '0', true, 30);
        $this->initVar('title', XOBJ_DTYPE_STRING, '', true, 255);
        $this->initVar('weight', XOBJ_DTYPE_INT, 0, true);
    }
}
