<?php

namespace Xoonips\Handler;

/**
 * item keyword object handler.
 */
class ItemKeywordObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_keyword');
        $this->mPrimaryKey = ['item_id', 'keyword_id'];
        $this->mIsAutoIncrement = false;
    }
}
