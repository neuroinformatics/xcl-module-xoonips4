<?php

namespace Xoonips\Object;

/**
 * item import log object.
 */
class ItemImportLogObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('item_import_log_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('uid', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('result', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('log', XOBJ_DTYPE_TEXT, null, false);
        $this->initVar('timestamp', XOBJ_DTYPE_INT, 0, true);
    }
}
