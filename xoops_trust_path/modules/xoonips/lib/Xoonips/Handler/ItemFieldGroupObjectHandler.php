<?php

namespace Xoonips\Handler;

/**
 * item field group object handler.
 */
class ItemFieldGroupObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_field_group');
        $this->mPrimaryKey = 'group_id';
    }
}
