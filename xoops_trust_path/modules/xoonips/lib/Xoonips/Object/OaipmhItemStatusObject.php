<?php

namespace Xoonips\Object;

/**
 * oaipmh item status object.
 */
class OaipmhItemStatusObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('item_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('timestamp', XOBJ_DTYPE_INT, null, true);
        $this->initVar('created_timestamp', XOBJ_DTYPE_INT, null, false);
        $this->initVar('modified_timestamp', XOBJ_DTYPE_INT, null, false);
        $this->initVar('deleted_timestamp', XOBJ_DTYPE_INT, null, false);
        $this->initVar('is_deleted', XOBJ_DTYPE_INT, null, false);
    }
}
