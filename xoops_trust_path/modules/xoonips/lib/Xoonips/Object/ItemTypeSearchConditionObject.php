<?php

namespace Xoonips\Object;

use Xoonips\Core\Functions;

/**
 * item type search condition object.
 */
class ItemTypeSearchConditionObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('condition_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('title', XOBJ_DTYPE_STRING, '', true, 255);
    }

    /**
     * get item field detail objects.
     *
     * @return array
     */
    public function getItemFieldDetailObjects()
    {
        $ret = array();
        $itscdHandler = &Functions::getXoonipsHandler('ItemTypeSearchConditionDetailObject', $this->mDirname);
        $ifdHandler = &Functions::getXoonipsHandler('ItemFieldDetailObject', $this->mDirname);
        $cid = $this->get('condition_id');
        $criteria = new \Criteria('condition_id', $cid);
        $itscdObjs = $itscdHandler->getObjects($criteria);
        $fids = array();
        foreach ($itscdObjs as $itscdObj) {
            $fids[] = $itscdObj->get('item_field_detail_id');
        }
        if (!empty($fids)) {
            $criteria = new \Criteria('item_field_detail_id', $fids, 'IN');
            $ret = $ifdHandler->getObjects($criteria);
        }

        return $ret;
    }

    /**
     * update item field detail ids.
     *
     * @param array $fids
     *
     * @return bool
     */
    public function updateItemFieldDetailIds($fids)
    {
        $itscdHandler = &Functions::getXoonipsHandler('ItemTypeSearchConditionDetailObject', $this->mDirname);
        $cid = $this->get('condition_id');
        $criteria = new \Criteria('condition_id', $cid);
        if ($itscdHandler->deleteAll($criteria) == false) {
            return false;
        }
        foreach ($fids as $fid) {
            $itscdObj = $itscdHandler->create();
            $itscdObj->set('condition_id', $cid);
            $itscdObj->set('item_field_detail_id', $fid);
            if ($itscdHandler->insert($itscdObj) === false) {
                return false;
            }
        }

        return true;
    }
}
