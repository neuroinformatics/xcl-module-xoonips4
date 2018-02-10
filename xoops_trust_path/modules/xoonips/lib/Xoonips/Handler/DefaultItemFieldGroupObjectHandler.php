<?php

namespace Xoonips\Handler;

/**
 * default item field group object handler.
 */
class DefaultItemFieldGroupObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_default_item_field_group');
        $this->mPrimaryKey = 'group_id';
    }
}
