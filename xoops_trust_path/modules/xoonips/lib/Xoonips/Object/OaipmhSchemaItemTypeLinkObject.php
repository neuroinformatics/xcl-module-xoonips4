<?php

namespace Xoonips\Object;

/**
 * oaipmh schema item type link object.
 */
class OaipmhSchemaItemTypeLinkObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('schema_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('group_id', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('item_field_detail_id', XOBJ_DTYPE_STRING, null, true, 255);
        $this->initVar('value', XOBJ_DTYPE_TEXT, null, false);
    }
}
