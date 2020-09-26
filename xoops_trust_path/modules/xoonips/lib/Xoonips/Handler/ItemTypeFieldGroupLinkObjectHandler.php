<?php

namespace Xoonips\Handler;

/**
 * item type field group link object handler.
 */
class ItemTypeFieldGroupLinkObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_type_field_group_link');
        $this->mPrimaryKey = 'item_type_field_group_id';
        $this->mObjects = [];
    }
}
