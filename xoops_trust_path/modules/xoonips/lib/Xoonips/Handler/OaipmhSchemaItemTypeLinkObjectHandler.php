<?php

namespace Xoonips\Handler;

/**
 * oaipmh schema item type link object handler.
 */
class OaipmhSchemaItemTypeLinkObjectHandler extends AbstractObjectHandler
{
    /**
     * constructor.
     *
     * @param \XoopsDatabase $db
     * @param string         $dirname
     */
    public function __construct(\XoopsDatabase $db, $dirname)
    {
        parent::__construct($db, $dirname);
        $this->mTable = $db->prefix($dirname.'_oaipmh_schema_item_type_link');
        $this->mPrimaryKey = ['schema_id', 'item_type_id', 'item_field_detail_id'];
        $this->mIsAutoIncrement = false;
    }
}
