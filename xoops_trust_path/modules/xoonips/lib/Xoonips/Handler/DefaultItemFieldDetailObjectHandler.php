<?php

namespace Xoonips\Handler;

/**
 * default item field detail object handler.
 */
class DefaultItemFieldDetailObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_default_item_field_detail');
        $this->mPrimaryKey = 'item_field_detail_id';
    }
}
