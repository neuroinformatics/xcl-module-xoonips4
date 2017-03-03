<?php

namespace Xoonips\Handler;

/**
 * item type search condition object handler.
 */
class ItemTypeSearchConditionObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_type_search_condition');
        $this->mPrimaryKey = 'condition_id';
    }
}
