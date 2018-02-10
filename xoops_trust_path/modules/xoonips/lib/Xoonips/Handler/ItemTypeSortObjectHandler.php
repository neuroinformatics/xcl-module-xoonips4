<?php

namespace Xoonips\Handler;

use Xoonips\Core\Functions;
use Xoonips\Object\ItemTypeSortObject;

/**
 * item type sort object handler.
 */
class ItemTypeSortObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix($dirname.'_item_type_sort');
        $this->mPrimaryKey = 'sort_id';
    }

    /**
     * get sort titles.
     *
     * @return string[]
     */
    public function getSortTitles()
    {
        static $cache = null;
        if (null != $cache) {
            return $cache;
        }
        $criteria = new \CriteriaElement();
        $criteria->setSort($this->mPrimaryKey);
        $criteria->setOrder('ASC');
        $objs = $this->getObjects($criteria);
        $ret = array();
        foreach ($objs as $obj) {
            $ret[$obj->get($this->mPrimaryKey)] = $obj->get('title');
        }
        $cache = $ret;

        return $cache;
    }

    /**
     * get sort fields.
     *
     * @param Object\ItemTypeSortObject $obj
     *
     * @return string[]
     */
    public function getSortFields(ItemTypeSortObject $obj)
    {
        $ret = array();
        $sortId = $obj->get($this->mPrimaryKey);
        $detailHandler = Functions::getXoonipsHandler('ItemTypeSortDetailObject', $this->mDirname);
        $criteria = new \Criteria('sort_id', $sortId);
        $res = $detailHandler->open($criteria);
        if (!$res = $detailHandler->open($criteria)) {
            return $ret;
        }
        while ($obj = $detailHandler->getNext($res)) {
            $ret[] = $obj->getEncodedSortField();
        }
        $this->close($res);

        return $ret;
    }
}
