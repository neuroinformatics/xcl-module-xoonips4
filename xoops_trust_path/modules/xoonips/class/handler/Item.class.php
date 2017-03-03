<?php

/**
 * item object
 */
class Xoonips_ItemObject extends XoopsSimpleObject
{

    /**
     * constructor
     */
    public function __construct()
    {
        $this->initVar('item_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('item_type_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('doi', XOBJ_DTYPE_STRING, '', true);
        $this->initVar('view_count', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('last_update_date', XOBJ_DTYPE_INT, null, true);
        $this->initVar('creation_date', XOBJ_DTYPE_INT, null, true);
    }

    /**
     * get url of detail page
     *
     * @return string
     */
    public function getUrl()
    {
        $doi = $this->get('doi');
        $item_id = $this->get('item_id');
        return XOOPS_URL . '/modules/' . $this->mDirname . '/detail.php?' . (empty($doi) ? 'item_id=' . $item_id : XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME . '=' . $doi);
    }

    /**
     * is readable
     *
     * @return bool
     */
    public function isReadable($uid)
    {
        $trustDirname = Xoonips_Utils::getTrustDirnameByDirname($this->mDirname);
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->mDirname, $trustDirname);
        return $itemBean->canView($this->get('item_id'), $uid);
    }

    /**
     * is download limit
     *
     * @return bool false if not limit
     */
    public function isDownloadLimit()
    {
        $trustDirname = Xoonips_Utils::getTrustDirnameByDirname($this->mDirname);
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->mDirname, $trustDirname);
        return !$itemBean->getDownloadLimit($this->get('item_id'), $this->get('item_type_id'));
    }

    /**
     * is download notify
     *
     * @return bool false if not notification needed
     */
    public function isDownloadNotify()
    {
        $trustDirname = Xoonips_Utils::getTrustDirnameByDirname($this->mDirname);
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->mDirname, $trustDirname);
        return $itemBean->getDownloadNotify($this->get('item_id'), $this->get('item_type_id'));
    }
}

/**
 * item object handler
 */
class Xoonips_ItemHandler extends XoopsObjectGenericHandler
{

    /**
     * constructor
     *
     * @param XoopsDatabase &$db
     * @param string $dirname
     */
    public function __construct(&$db, $dirname)
    {
        $this->mTable = $dirname . '_item';
        $this->mPrimary = 'item_id';
        $this->mClass = preg_replace('/Handler$/', 'Object', get_class());
        parent::__construct($db);
    }

    /**
     * get object by doi
     *
     * @param string $doi
     * @return &object
     */
    public function &getByDoi($doi)
    {
        $ret = null;
        $criteria = new Criteria('doi', $doi);
        $objArr =& $this->getObjects($criteria);
        if (count($objArr) == 1) {
            $ret =& $objArr[0];
        }
        return $ret;
    }
}
