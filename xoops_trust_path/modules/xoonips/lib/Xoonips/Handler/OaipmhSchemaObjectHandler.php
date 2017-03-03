<?php

namespace Xoonips\Handler;

/**
 * oaipmh schema object handler.
 */
class OaipmhSchemaObjectHandler extends AbstractObjectHandler
{
    /**
     * constructor.
     *
     * @param \XoopsDatabase &$db
     * @param string         $dirname
     */
    public function __construct(\XoopsDatabase &$db, $dirname)
    {
        parent::__construct($db, $dirname);
        $this->mTable = $db->prefix($dirname.'_oaipmh_schema');
        $this->mPrimaryKey = 'schema_id';
    }
}
