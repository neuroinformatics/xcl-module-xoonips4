<?php

namespace Xoonips\Handler;

/**
 * index object handler.
 */
class IndexObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_index');
        $this->mPrimaryKey = 'index_id';
    }
}
