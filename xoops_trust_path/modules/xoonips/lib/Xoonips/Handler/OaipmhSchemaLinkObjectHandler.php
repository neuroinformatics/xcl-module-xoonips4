<?php

namespace Xoonips\Handler;

/**
 * oaipmh schema link object handler.
 */
class OaipmhSchemaLinkObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_oaipmh_schema_link');
        $this->mPrimaryKey = ['schema_id1', 'schema_id2'];
        $this->mIsAutoIncrement = false;
    }
}
