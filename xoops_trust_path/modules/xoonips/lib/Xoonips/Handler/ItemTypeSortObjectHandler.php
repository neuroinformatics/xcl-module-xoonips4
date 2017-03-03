<?php

namespace Xoonips\Handler;

/**
 * item type sort object handler.
 */
class ItemTypeSortObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_type_sort');
        $this->mPrimaryKey = 'sort_id';
    }
}
