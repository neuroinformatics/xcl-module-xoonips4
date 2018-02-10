<?php

namespace Xoonips\Handler;

/**
 * groups users link object handler.
 */
class GroupsUsersLinkObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix('groups_users_link');
        $this->mPrimaryKey = 'linkid';
    }
}
