<?php

namespace Xoonips\Handler;

/**
 * index item link object handler.
 */
class IndexItemLinkObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_index_item_link');
        $this->mPrimaryKey = 'index_item_link_id';
    }
}
