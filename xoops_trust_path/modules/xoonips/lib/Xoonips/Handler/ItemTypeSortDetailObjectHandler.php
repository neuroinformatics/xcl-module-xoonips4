<?php

namespace Xoonips\Handler;

/**
 * item type sort detail object handler.
 */
class ItemTypeSortDetailObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_type_sort_detail');
        $this->mPrimaryKey = ['sort_id', 'item_type_id'];
        $this->mIsAutoIncrement = false;
    }
}
