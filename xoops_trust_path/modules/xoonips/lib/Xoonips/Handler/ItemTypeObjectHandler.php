<?php

namespace Xoonips\Handler;

/**
 * item type object handler.
 */
class ItemTypeObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_type');
        $this->mPrimaryKey = 'item_type_id';
    }
}
