<?php

namespace Xoonips\Handler;

/**
 * item field detail object handler.
 */
class ItemFieldDetailObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_field_detail');
        $this->mPrimaryKey = 'item_field_detail_id';
    }
}
