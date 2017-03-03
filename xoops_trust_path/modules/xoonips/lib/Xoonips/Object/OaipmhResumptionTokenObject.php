<?php

namespace Xoonips\Object;

/**
 * oaipmh resumption token object.
 */
class OaipmhResumptionTokenObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('resumption_token', XOBJ_DTYPE_STRING, '', true, 255);
        $this->initVar('metadata_prefix', XOBJ_DTYPE_STRING, null, false, 30);
        $this->initVar('verb', XOBJ_DTYPE_STRING, null, false, 32);
        $this->initVar('args', XOBJ_DTYPE_TEXT, null, false);
        $this->initVar('last_item_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('limit_row', XOBJ_DTYPE_INT, null, false);
        $this->initVar('publish_date', XOBJ_DTYPE_INT, null, false);
        $this->initVar('expire_date', XOBJ_DTYPE_INT, null, false);
    }
}
