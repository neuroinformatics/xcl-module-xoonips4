<?php

namespace Xoonips\Object;

/**
 * view data relation object.
 */
class ViewDataRelationObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('view_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('data_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('data_length', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('data_decimal_places', XOBJ_DTYPE_INT, 0, true);
    }
}
