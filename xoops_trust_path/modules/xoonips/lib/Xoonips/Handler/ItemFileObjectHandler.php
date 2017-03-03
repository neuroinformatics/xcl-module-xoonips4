<?php

namespace Xoonips\Handler;

/**
 * item file object handler.
 */
class ItemFileObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_file');
        $this->mPrimaryKey = 'file_id';
    }
}
