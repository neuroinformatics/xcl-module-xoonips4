<?php

namespace Xoonips\Handler;

/**
 * complement object handler.
 */
class ComplementObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_complement');
        $this->mPrimaryKey = 'complement_id';
    }
}
