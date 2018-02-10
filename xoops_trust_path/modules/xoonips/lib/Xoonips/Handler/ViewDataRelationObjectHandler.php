<?php

namespace Xoonips\Handler;

/**
 * view data relation object handler.
 */
class ViewDataRelationObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_view_data_relation');
        $this->mPrimaryKey = array('view_type_id', 'data_type_id');
        $this->mIsAutoIncrement = false;
    }
}
