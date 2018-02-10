<?php

namespace Xoonips\Handler;

/**
 * search text object handler.
 */
class SearchTextObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_search_text');
        $this->mPrimaryKey = 'file_id';
        $this->mIsAutoIncrement = false;
    }
}
