<?php

namespace Xoonips\Object;

/**
 * oaipmh schema object.
 */
class OaipmhSchemaObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('schema_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('metadata_prefix', XOBJ_DTYPE_STRING, null, true, 30);
        $this->initVar('name', XOBJ_DTYPE_STRING, null, true, 255);
        $this->initVar('min_occurences', XOBJ_DTYPE_INT, null, true);
        $this->initVar('max_occurences', XOBJ_DTYPE_INT, null, true);
        $this->initVar('weight', XOBJ_DTYPE_INT, null, true);
    }
}
