<?php

namespace Xoonips\Handler;

/**
 * oaipmh schema value set object handler.
 */
class OaipmhSchemaValueSetObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_oaipmh_schema_value_set');
        $this->mPrimaryKey = 'seq_id';
    }
}
