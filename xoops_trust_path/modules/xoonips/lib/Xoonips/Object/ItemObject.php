<?php

namespace Xoonips\Object;

use Xoonips\Core\Functions;
use Xoonips\Core\JoinCriteria;
use Xoonips\Core\XoopsUtils;

/**
 * item object.
 */
class ItemObject extends AbstractObject
{
    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct($dirname);
        $this->initVar('item_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('item_type_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('doi', XOBJ_DTYPE_TEXT, null, false);
        $this->initVar('view_count', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('last_update_date', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('creation_date', XOBJ_DTYPE_INT, 0, true);
    }

    /**
     * check wheter user is owner.
     *
     * @param int $uid
     *
     * @return bool
     */
    public function isOwner($uid)
    {
        $itemUsersLinkHandler = Functions::getXoonipsHandler('ItemUsersLinkObject', $this->mDirname);
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('uid', $uid));
        $criteria->add(new \Criteria('item_id', $this->get('item_id')));

        return $itemUsersLinkHandler->getCount($criteria) > 0;
    }

    /**
     * check wheter item is readable.
     *
     * @param int $uid
     *
     * @return bool
     */
    public function isReadable($uid)
    {
        if (XoopsUtils::isAdmin($uid)) {
            return true;
        }
        if ($this->isOwner($uid)) {
            return true;
        }
        $indexHandler = Functions::getXoonipsHandler('IndexObject', $this->mDirname);
        $indexItemLinkHandler = Functions::getXoonipsHandler('IndexItemLinkObject', $this->mDirname);
        $groupsHandler = Functions::getXoonipsHandler('GroupsObject', $this->mDirname);
        $adminGids = array_keys($groupsHandler->getAdminGroups($uid));
        $myGids = array_keys($groupsHandler->getMyGroups($uid));
        $publicGids = array_keys($groupsHandler->getPublicGroups($uid));
        $indexTable = $indexHandler->getTable();
        $indexItemLinkTable = $indexItemLinkHandler->getTable();
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('item_id', $this->get('item_id'), '=', $indexItemLinkTable));
        $criteriaOpenLevel = new \CriteriaCompo();
        $criteriaOpenLevelPublic = new \CriteriaCompo();
        $criteriaOpenLevelPublic->add(new \Criteria('open_level', $indexHandler::OPEN_LEVEL_PUBLIC, '=', $indexTable));
        $criteriaOpenLevelPublic->add(new \Criteria('certify_state', [$indexItemLinkHandler::CERTIFY_STATE_CERTIFIED, $indexItemLinkHandler::CERTIFY_STATE_WITHDRAW_REQUIRED], 'IN', $indexItemLinkTable));
        $criteriaOpenLevel->add($criteriaOpenLevelPublic);
        if (!empty($adminGids) || !empty($myGids) || !empty($publicGids)) {
            $criteriaOpenLevelGroup = new \CriteriaCompo();
            $criteriaOpenLevelGroup->add(new \Criteria('open_level', $indexHandler::OPEN_LEVEL_GROUP, '=', $indexTable));
            $criteriaOpenLevelGroupSub = new \CriteriaCompo();
            if (!empty($adminGids)) {
                $criteriaOpenLevelGroupSubAdmin = new \Criteria('groupid', $adminGids, 'IN', $indexTable);
                $criteriaOpenLevelGroupSub->add($criteriaOpenLevelGroupSubAdmin);
            }
            if (!empty($myGids)) {
                $criteriaOpenLevelGroupSubMember = new \CriteriaCompo();
                $criteriaOpenLevelGroupSubMember->add(new \Criteria('groupid', $myGids, 'IN', $indexTable));
                $criteriaOpenLevelGroupSubMember->add(new \Criteria('certify_state', [$indexItemLinkHandler::CERTIFY_STATE_CERTIFIED, $indexItemLinkHandler::CERTIFY_STATE_WITHDRAW_REQUIRED], 'IN', $indexItemLinkTable));
                $criteriaOpenLevelGroupSub->add($criteriaOpenLevelGroupSubMember);
            }
            if (!empty($publicGids)) {
                $criteriaOpenLevelGroupSubOpen = new \CriteriaCompo();
                $criteriaOpenLevelGroupSubOpen->add(new \Criteria('groupid', $publicGids, 'IN', $indexTable));
                $criteriaOpenLevelGroupSubOpen->add(new \Criteria('certify_state', [$indexItemLinkHandler::CERTIFY_STATE_CERTIFIED, $indexItemLinkHandler::CERTIFY_STATE_WITHDRAW_REQUIRED], 'IN', $indexItemLinkTable));
                $criteriaOpenLevelGroupSub->add($criteriaOpenLevelGroupSubOpen);
            }
            $criteriaOpenLevel->add($criteriaOpenLevelGroup, 'OR');
        }
        if (XoopsUtils::UID_GUEST != $uid) {
            $criteriaOpenLevelPrivate = new \CriteriaCompo();
            $criteriaOpenLevelPrivate->add(new \Criteria('open_level', $indexHandler::OPEN_LEVEL_PRIVATE, '=', $indexTable));
            $criteriaOpenLevelPrivate->add(new \Criteria('uid', $uid, '=', $indexTable));
            $criteriaOpenLevel->add($criteriaOpenLevelPrivate, 'OR');
        }
        $criteria->add($criteriaOpenLevel);
        $join = new JoinCriteria('INNER', $indexItemLinkTable, 'index_id', $indexTable, 'index_id');
        $numIndex = $indexHandler->getCount($criteria, $join);

        return $numIndex > 0;
    }
}
