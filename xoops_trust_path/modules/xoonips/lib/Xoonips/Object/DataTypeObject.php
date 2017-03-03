<?php

namespace Xoonips\Object;

/**
 * data type object.
 */
class DataTypeObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('data_type_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('name', XOBJ_DTYPE_STRING, null, true, 30);
        $this->initVar('module', XOBJ_DTYPE_STRING, null, false, 255);
    }
}
