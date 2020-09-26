<?php

namespace Xoonips\Handler;

/**
 * item related to object handler.
 */
class ItemRelatedToObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_related_to');
        $this->mPrimaryKey = ['item_id', 'child_item_id'];
        $this->mIsAutoIncrement = false;
    }
}
