<?php

namespace Xoonips\Object;

/**
 * config object.
 */
class ConfigObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('name', XOBJ_DTYPE_STRING, '', true, 255);
        $this->initVar('value', XOBJ_DTYPE_TEXT, null, true);
    }
}
