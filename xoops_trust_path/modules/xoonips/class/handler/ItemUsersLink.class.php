<?php

/**
 * item users link object.
 */
class Xoonips_ItemUsersLinkObject extends XoopsSimpleObject
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->initVar('item_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('weight', XOBJ_DTYPE_INT, null, true);
    }
}

/**
 * item users link object handler.
 */
class Xoonips_ItemUsersLinkHandler extends XoopsObjectGenericHandler
{
    /**
     * table.
     *
     * @var string
     */
    public $mTable = '{dirname}_item_users_link';

    /**
     * primary id.
     *
     * @var string
     */
    public $mPrimary = 'item_id';

    /**
     * object class name.
     *
     * @var string
     */
    public $mClass = '';

    /**
     * dirname.
     *
     * @var string
     */
    public $mDirname = '';

    /**
     * constructor.
     *
     * @param XoopsDatabase &$db
     * @param string        $dirname
     */
    public function __construct(&$db, $dirname)
    {
        $this->mTable = strtr($this->mTable, array('{dirname}' => $dirname));
        $this->mDirname = $dirname;
        $this->mClass = preg_replace('/Handler$/', 'Object', get_class());
        parent::__construct($db);
    }

    /**
     * check whether user is owner.
     *
     * @param int $item_id
     * @param int $uid
     *
     * @return bool
     */
    public function isOwner($item_id, $uid)
    {
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('item_id', $item_id));
        $criteria->add(new Criteria('uid', $uid));

        return $this->getCount($criteria) > 0;
    }

    /**
     * get owner ids.
     *
     * @param int $item_id
     *
     * @return int[] owner user ids
     */
    public function getOwnerIds($item_id)
    {
        $criteria = new Criteria('item_id', $item_id);
        $criteria->setSort('weight');
        $criteria->setOrder('ASC');
        $objs = &$this->getObjects($criteria);
        $uids = array();
        foreach ($objs as $obj) {
            $uids[] = $obj->get('uid');
        }

        return $uids;
    }

    /**
     * set owner ids.
     *
     * @param int   $item_id
     * @param int[] $uids
     *
     * @return bool
     */
    public function setOwnerIds($item_id, $uids, $force = false)
    {
        if (empty($uids)) {
            return false;
        }
        $criteria = new Criteria('item_id', $item_id);
        if (!$this->deleteAll($criteria, $force)) {
            return false;
        }
        $weight = 0;
        foreach ($uids as $uid) {
            $obj = &$this->create();
            $obj->set('item_id', $item_id);
            $obj->set('uid', $uid);
            $obj->set('weight', $weight);
            ++$weight;
        }
        $criteria->setSort('weight');
        $criteria->setOrder('ASC');
        $objs = $this->getObjects($criteria);
        $ret = array();
        foreach ($objs as $obj) {
            $ret[] = $obj->get('uid');
        }

        return $ret;
    }
}
