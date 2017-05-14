<?php

namespace Xoonips\Object;

/**
 * groups object.
 */
class GroupsObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('groupid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('activate', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('name', XOBJ_DTYPE_STRING, '', true, 50);
        $this->initVar('description', XOBJ_DTYPE_TEXT, '', true);
        $this->initVar('icon', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('mime_type', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('is_public', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('can_join', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('is_hidden', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('member_accept', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_accept', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_number_limit', XOBJ_DTYPE_INT, null, false);
        $this->initVar('index_number_limit', XOBJ_DTYPE_INT, null, false);
        $this->initVar('item_storage_limit', XOBJ_DTYPE_INT, null, false);
        $this->initVar('index_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('group_type', XOBJ_DTYPE_STRING, '', true, 255);
    }
}
