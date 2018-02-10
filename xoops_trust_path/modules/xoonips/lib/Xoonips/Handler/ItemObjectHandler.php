<?php

namespace Xoonips\Handler;

/**
 * item object handler.
 */
class ItemObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item');
        $this->mPrimaryKey = 'item_id';
    }
}
