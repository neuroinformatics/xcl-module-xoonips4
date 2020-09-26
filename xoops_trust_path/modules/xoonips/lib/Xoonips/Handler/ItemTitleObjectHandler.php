<?php

namespace Xoonips\Handler;

/**
 * item title object handler.
 */
class ItemTitleObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_title');
        $this->mPrimaryKey = ['item_id', 'title_id'];
        $this->mIsAutoIncrement = false;
    }
}
