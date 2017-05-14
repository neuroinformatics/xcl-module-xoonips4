<?php

namespace Xoonips\Handler;

/**
 * index item link object handler.
 */
class IndexItemLinkObjectHandler extends AbstractObjectHandler
{
    const CERTIFY_STATE_NOT_CERTIFED = 0;
    const CERTIFY_STATE_CERTIFY_REQUIRED = 1;
    const CERTIFY_STATE_CERTIFIED = 2;
    const CERTIFY_STATE_WITHDRAW_REQUIRED = 3;

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
