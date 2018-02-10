<?php

namespace Xoonips\Handler;

/**
 * oaipmh item status object handler.
 */
class OaipmhItemStatusObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_oaipmh_item_status');
        $this->mPrimaryKey = 'item_id';
        $this->mIsAutoIncrement = false;
    }
}
