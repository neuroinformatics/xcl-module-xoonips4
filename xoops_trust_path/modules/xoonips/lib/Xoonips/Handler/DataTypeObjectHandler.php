<?php

namespace Xoonips\Handler;

/**
 * data type object handler.
 */
class DataTypeObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_data_type');
        $this->mPrimaryKey = 'data_type_id';
    }
}
