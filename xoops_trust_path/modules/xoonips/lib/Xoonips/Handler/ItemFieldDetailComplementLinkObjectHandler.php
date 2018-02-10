<?php

namespace Xoonips\Handler;

/**
 * item field detail complement link object handler.
 */
class ItemFieldDetailComplementLinkObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_field_detail_complement_link');
        $this->mPrimaryKey = 'seq_id';
    }
}
