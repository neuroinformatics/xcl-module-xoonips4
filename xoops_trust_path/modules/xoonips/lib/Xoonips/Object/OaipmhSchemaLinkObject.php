<?php

namespace Xoonips\Object;

/**
 * oaipmh schema link object.
 */
class OaipmhSchemaLinkObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('schema_id1', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('schema_id2', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('number', XOBJ_DTYPE_INT, null, true);
    }
}
