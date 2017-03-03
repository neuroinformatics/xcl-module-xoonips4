<?php

namespace Xoonips\Handler;

/**
 * item field value set object handler.
 */
class ItemFieldValueSetObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_field_value_set');
        $this->mPrimaryKey = array('select_name', 'title_id');
        $this->mIsAutoIncrement = false;
    }
}
