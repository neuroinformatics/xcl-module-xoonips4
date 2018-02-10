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
     * @param \XoopsDatabase $db
     * @param string         $dirname
     */
    public function __construct(\XoopsDatabase $db, $dirname)
    {
        parent::__construct($db, $dirname);
        $this->mTable = $db->prefix($dirname.'_data_type');
        $this->mPrimaryKey = 'data_type_id';
    }

    /**
     * get data types.
     *
     * @return array
     */
    public function getDataTypes()
    {
        $criteria = new \CriteriaElement();
        $criteria->setSort($this->mPrimaryKey);

        return $this->getObjects($criteria, '', false, true);
    }
}
