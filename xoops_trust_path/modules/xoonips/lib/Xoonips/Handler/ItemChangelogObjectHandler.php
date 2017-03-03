<?php

namespace Xoonips\Handler;

/**
 * item changelog object handler.
 */
class ItemChangelogObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_changelog');
        $this->mPrimaryKey = 'log_id';
    }
}
