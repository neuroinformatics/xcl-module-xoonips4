<?php

namespace Xoonips\Object;

/**
 * view type object.
 */
class ViewTypeObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('view_type_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('preselect', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('multi', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('name', XOBJ_DTYPE_STRING, null, true, 30);
        $this->initVar('module', XOBJ_DTYPE_STRING, null, false, 255);
    }
}
