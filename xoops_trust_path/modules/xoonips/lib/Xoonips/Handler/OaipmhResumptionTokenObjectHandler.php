<?php

namespace Xoonips\Handler;

/**
 * oaipmh resumption token object handler.
 */
class OaipmhResumptionTokenObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_oaipmh_resumption_token');
        $this->mPrimaryKey = 'resumption_token';
        $this->mIsAutoIncrement = false;
    }
}
