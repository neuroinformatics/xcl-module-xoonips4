<?php

namespace Xoonips\Handler;

/**
 * item type search condition detail object handler.
 */
class ItemTypeSearchConditionDetailObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_type_search_condition_detail');
        $this->mPrimaryKey = array('condition_id', 'item_type_id', 'item_field_detail_id');
        $this->mIsAutoIncrement = false;
    }
}
