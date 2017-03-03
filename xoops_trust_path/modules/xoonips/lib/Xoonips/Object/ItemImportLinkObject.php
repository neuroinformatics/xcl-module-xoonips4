<?php

namespace Xoonips\Object;

/**
 * item import link object.
 */
class ItemImportLinkObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('item_import_log_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_id', XOBJ_DTYPE_INT, 0, true);
    }
}
