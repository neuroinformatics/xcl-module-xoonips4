<?php

namespace Xoonips\Handler;

/**
 * view type object handler.
 */
class ViewTypeObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_view_type');
        $this->mPrimaryKey = 'view_type_id';
    }
}
