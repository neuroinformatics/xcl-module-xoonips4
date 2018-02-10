<?php

namespace Xoonips\Handler;

/**
 * item users link object handler.
 */
class ItemUsersLinkObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_users_link');
        $this->mPrimaryKey = array('item_id', 'uid');
        $this->mIsAutoIncrement = false;
    }
}
