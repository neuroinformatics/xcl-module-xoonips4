<?php

namespace Xoonips\Handler;

/**
 * item import link object handler.
 */
class ItemImportLinkObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_import_link');
        $this->mPrimaryKey = array('item_import_log_id', 'item_id');
        $this->mIsAutoIncrement = false;
    }
}
