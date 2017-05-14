<?php

namespace Xoonips\Object;

/**
 * groups users link object.
 */
class GroupsUsersLinkObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('linkid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('activate', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('groupid', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('uid', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('is_admin', XOBJ_DTYPE_INT, 0, true);
    }
}
