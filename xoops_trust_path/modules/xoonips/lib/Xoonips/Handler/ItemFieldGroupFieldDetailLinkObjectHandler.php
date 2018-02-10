<?php

namespace Xoonips\Handler;

/**
 * item field group field detail link object handler.
 */
class ItemFieldGroupFieldDetailLinkObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_field_group_field_detail_link');
        $this->mPrimaryKey = 'item_field_group_field_detail_id';
    }
}
