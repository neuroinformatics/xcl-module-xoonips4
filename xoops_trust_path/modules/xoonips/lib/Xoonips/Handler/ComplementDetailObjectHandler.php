<?php

namespace Xoonips\Handler;

/**
 * complement detail object handler.
 */
class ComplementDetailObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_complement_detail');
        $this->mPrimaryKey = 'complement_detail_id';
    }
}
