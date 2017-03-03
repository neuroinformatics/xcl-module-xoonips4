<?php

use Xoonips\Core\JoinCriteria;

/**
 * index object.
 */
class Xoonips_IndexObject extends XoopsSimpleObject
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->initVar('index_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('parent_index_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('groupid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('open_level', XOBJ_DTYPE_INT, null, true);
        $this->initVar('weight', XOBJ_DTYPE_INT, null, true);
        $this->initVar('title', XOBJ_DTYPE_STRING, '', true, 255);
        $this->initVar('detailed_title', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('icon', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('mime_type', XOBJ_DTYPE_STRING, null, false, 255);
        $this->initVar('detailed_description', XOBJ_DTYPE_TEXT, '', false);
        $this->initVar('last_update_date', XOBJ_DTYPE_INT, null, true);
        $this->initVar('creation_date', XOBJ_DTYPE_INT, null, true);
    }
}

/**
 * index object handler.
 */
class Xoonips_IndexHandler extends XoopsObjectGenericHandler
{
    private $mTableIndexItemLink;
    private $mTableItem;
    private $mTableGroups;
    private $mTableItemUsersLink;

    /**
     * constructor.
     *
     * @param XoopsDatabase &$db
     * @param string        $dirname
     */
    public function __construct(&$db, $dirname)
    {
        $this->mTable = $dirname.'_index';
        $this->mPrimary = 'index_id';
        $this->mClass = preg_replace('/Handler$/', 'Object', get_class());
        parent::__construct($db);
        $this->mTableIndexItemLink = $this->db->prefix($this->mDirname.'_index_item_link');
        $this->mTableItem = $this->db->prefix($this->mDirname.'_item');
        $this->mTableGroups = $this->db->prefix('groups');
        $this->mTableItemUsersLink = $this->db->prefix($this->mDirname.'_item_users_link');
    }

    /**
     * get public index list.
     *
     * @param int $uid
     *
     * @return array
     */
    public function getPublicIndexList($uid)
    {
        $isModerator = ($uid == XOONIPS_UID_GUEST ? false : Xoonips_Utils::isAdmin($uid, $this->mDirname));
        $criteriaList = new Criteria('open_level', XOONIPS_OL_PUBLIC, '=', $this->mTable);
        $criteriaCount = $this->_getReadableCountCriteriaForPublic($uid, $isModerator);

        return $this->_getIndexList($isModerator, $criteriaList, $criteriaCount);
    }

    /**
     * get group index list.
     *
     * @param int $uid
     * @param int $gid
     *
     * @return array
     */
    public function getGroupIndexList($uid, $gid)
    {
        $isModerator = ($uid == XOONIPS_UID_GUEST ? false : Xoonips_Utils::isAdmin($uid, $this->mDirname));
        $criteriaList = new CriteriaCompo(new Criteria('open_level', XOONIPS_OL_GROUP_ONLY, '=', $this->mTable));
        $criteriaList->add(new Criteria('groupid', $gid, '=', $this->mTable));
        $criteriaCount = $this->_getReadableCountCriteriaForGroup($uid, $gid, $isModerator);

        return $this->_getIndexList($isModerator, $criteriaList, $criteriaCount);
    }

    /**
     * get private index list.
     *
     * @param int $uid
     * @param int $gid
     *
     * @return array
     */
    public function getPrivateIndexList($uid)
    {
        $isModerator = ($uid == XOONIPS_UID_GUEST ? false : Xoonips_Utils::isAdmin($uid, $this->mDirname));
        $criteriaList = new CriteriaCompo(new Criteria('open_level', XOONIPS_OL_PRIVATE, '=', $this->mTable));
        $criteriaList->add(new Criteria('uid', $uid, '=', $this->mTable));
        $criteriaCount = $this->_getReadableCountCriteriaForPrivate($uid, $isModerator);

        return $this->_getIndexList($isModerator, $criteriaList, $criteriaCount);
    }

    /**
     * get index list.
     *
     * @param object $criteriaList
     * @param object $criteriaCount
     *
     * @return array
     */
    private function _getIndexList($uid, $criteriaList, $criteriaCount)
    {
        $isModerator = ($uid == XOONIPS_UID_GUEST ? false : Xoonips_Utils::isAdmin($uid, $this->mDirname));
        // get index list
        $field = '`'.$this->mTable.'`.`index_id`, `'.$this->mTable.'`.`parent_index_id`, `'.$this->mTable.'`.`uid`, `'.$this->mTable.'`.`groupid`, `'.$this->mTable.'`.`weight`, `'.$this->mTable.'`.`title`, \'0\' AS `num_items`';
        $orderBy = ' ORDER BY `'.$this->mTable.'`.`weight` ASC';
        $sql = 'SELECT '.$field.' FROM '.$this->mTable.' '.$criteriaList->renderWhere().$orderBy;
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        $ret = array();
        while ($row = $this->db->fetchArray($result)) {
            $indexId = $row['index_id'];
            $ret[$indexId] = $row;
        }
        $this->db->freeRecordSet($result);
        // count items
        $join = new JoinCriteria('LEFT', $this->mTableIndexItemLink, 'index_id', $this->mTable, 'index_id');
        $join->cascade(new JoinCriteria('LEFT', $this->mTableItem, 'item_id', $this->mTableIndexItemLink, 'item_id'));
        $join->cascade(new JoinCriteria('LEFT', $this->mTableGroups, 'groupid', $this->mTable, 'groupid'));
        $join->cascade(new JoinCriteria('LEFT', $this->mTableItemUsersLink, 'item_id', $this->mTableItem, 'item_id'));
        $criteriaCount->setGroupby('index_id');
        $field = '`'.$this->mTable.'`.`index_id`, COUNT(DISTINCT `'.$this->mTableIndexItemLink.'`.`item_id`) AS `num_items`';
        $sql = 'SELECT '.$field.' FROM '.$this->mTable.' '.$join->render().' '.$criteriaCount->renderWhere().$criteriaCount->getGroupby();
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        while ($row = $this->db->fetchArray($result)) {
            $indexId = $row['index_id'];
            $ret[$indexId]['num_items'] = $row['num_items'];
        }
        $this->db->freeRecordSet($result);

        return $ret;
    }

    /**
     * get readable count criteria object for public index.
     *
     * @param int  $uid
     * @param bool $isModerator
     *
     * @return object
     */
    private function _getReadableCountCriteriaForPublic($uid, $isModerator)
    {
        // public - (PUBLIC_INDEX && (MODERATOR || ITEM_OWNER || VISIBLE_ITEM_CERTIFY_STATE))
        $criteria = new CriteriaCompo(new Criteria('open_level', XOONIPS_OL_PUBLIC, '=', $this->mTable));
        if (!$isModerator) {
            $criteria2 = new CriteriaCompo(new Criteria('uid', $uid, '=', $this->mTableItemUsersLink));
            $criteria2->add(new Criteria('certify_state', array(XOONIPS_CERTIFIED, XOONIPS_WITHDRAW_REQUIRED), 'IN', $this->mTableIndexItemLink), 'OR');
            $criteria->add($criteria2);
        }

        return $criteria;
    }

    /**
     * get readable count criteria object for group index.
     *
     * @param int  $uid
     * @param int  $gid
     * @param bool $isModerator
     *
     * @return object
     */
    private function _getReadableCountCriteriaForGroup($uid, $gid, $isModerator)
    {
        $memberHandler = &xoops_gethandler('member');
        $membershipHandler = &xoops_gethandler('membership');
        $gids = $memberHandler->getGroupsByUser($uid);
        $adminGids = array();
        if (!empty($gids)) {
            $groupCriteria = new CriteriaCompo('groupid', $gids, 'IN');
            $groupCriteria->add(new Criteria('is_admin', 1));
            $adminGObjs = &$membershipHandler->getObjects($groupCriteria);
            $adminGids = array_keys($adminGObjs);
        }
        // group - (GROUP_INDEX && SELECTED_GROUP && (MODERATOR || ITEM_OWNER || GROUP_ADMIN || ((PUBLIC_GROUP || GROUP_MEMBER) && VISIBLE_ITEM_CERTIFY_STATE)))
        $criteria = new CriteriaCompo(new Criteria('open_level', XOONIPS_OL_GROUP_ONLY, '=', $this->mTable));
        if ($gid > 0) {
            $criteria->add(new Criteria('groupid', $gid, '=', $this->mTableGroups));
        }
        if (!$isModerator) {
            $criteria2 = new CriteriaCompo(new Criteria('uid', $uid, '=', $this->mTableItemUsersLink));
            if (!empty($adminGids)) {
                $criteria2->add(new Criteria('groupid', $adminGids, 'IN', $this->mTable), 'OR');
            }
            $criteria3 = new CriteriaCompo();
            $criteria4 = new CriteriaCompo(new Criteria('is_public', 1, '=', $this->mTableGroups));
            if (!empty($gids)) {
                $criteria4->add(new Criteria('groupid', $gids, 'IN', $this->mTable), 'OR');
            }
            $criteria3->add($criteria4);
            $criteria3->add(new Criteria('certify_state', array(XOONIPS_CERTIFIED, XOONIPS_WITHDRAW_REQUIRED), 'IN', $this->mTableIndexItemLink));
            $criteria2->add($criteria3, 'OR');
            $criteria->add($criteria2);
        }

        return $criteria;
    }

    /**
     * get readable count criteria object for private index.
     *
     * @param int  $uid
     * @param bool $isModerator
     *
     * @return object
     */
    private function _getReadableCountCriteriaForPrivate($uid, $isModerator)
    {
        // private - (PRIVATE && SELECTED_USER && (MODERATOR || ITEM_OWNER))
        $criteria = new CriteriaCompo(new Criteria('open_level', XOONIPS_OL_PRIVATE, '=', $this->mTable));
        $criteria->add(new Criteria('uid', $uid, '=', $this->mTable));
        if (!$isModerator) {
            $criteria->add(new Criteria('uid', $uid, '=', $this->mTableItemUsersLink));
        }

        return $criteria;
    }
}
