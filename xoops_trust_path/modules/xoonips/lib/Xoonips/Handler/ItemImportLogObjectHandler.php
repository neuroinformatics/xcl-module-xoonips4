<?php

namespace Xoonips\Handler;

/**
 * item import log object handler.
 */
class ItemImportLogObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_import_log');
        $this->mPrimaryKey = 'item_import_log_id';
    }
}
