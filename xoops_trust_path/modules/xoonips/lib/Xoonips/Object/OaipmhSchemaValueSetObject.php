<?php

namespace Xoonips\Object;

/**
 * oaipmh schema value set object.
 */
class OaipmhSchemaValueSetObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('seq_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('schema_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('value', XOBJ_DTYPE_STRING, null, true, 255);
    }
}
