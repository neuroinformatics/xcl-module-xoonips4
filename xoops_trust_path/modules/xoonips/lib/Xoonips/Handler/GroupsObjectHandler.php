<?php

namespace Xoonips\Handler;

use Xoonips\Core\Functions;
use Xoonips\Core\JoinCriteria;

/**
 * groups object handler.
 */
class GroupsObjectHandler extends AbstractObjectHandler
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
        $this->mTable = $db->prefix('groups');
        $this->mPrimaryKey = 'groupid';
    }

    /**
     * get public groups.
     *
     * @return array
     */
    public function getPublicGroups()
    {
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('activate', 1));
        $criteria->add(new \Criteria('is_public', 1));
        $criteria->add(new \Criteria('group_type', 'XooNIps'));
        $criteria->setSort('name', 'ASC');

        return $this->getObjects($criteria, '', false, true);
    }

    /**
     * get admin group.
     *
     * @param int $uid
     *
     * @return array
     */
    public function getAdminGroups($uid)
    {
        return $this->_getGroups($uid, true);
    }

    /**
     * get my groups.
     *
     * @param int $uid
     *
     * @return array
     */
    public function getMyGroups($uid)
    {
        return $this->_getGroups($uid, false);
    }

    /**
     * get groups.
     *
     * @param int  $uid
     * @param bool $isAdminOnly
     *
     * @return array
     */
    protected function _getGroups($uid, $isAdminOnly)
    {
        $groupsUsersLinkHandler = Functions::getXoonipsHandler('GroupsUsersLinkObject', $this->mDirname);
        $groupsUsersLinkTable = $groupsUsersLinkHandler->getTable();
        $join = new JoinCriteria('INNER', $groupsUsersLinkTable, 'groupid', $this->mTable, 'groupid');
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('activate', 1, '=', $this->mTable));
        $criteria->add(new \Criteria('group_type', 'XooNIps', '=', $this->mTable));
        $criteria->add(new \Criteria('uid', $uid, '=', $groupsUsersLinkTable));
        //$criteria->add(new \Criteria('activate', 1, '=', $groupsUsersLinkTable));
        if ($isAdminOnly) {
            $criteria->add(new \Criteria('is_admin', 1, '=', $groupsUsersLinkTable));
        }
        $criteria->setSort('name', 'ASC');
        $fields = sprintf('%1$s.*, %2$s.is_admin, %2$s.activate AS joinCertified', $this->mTable, $groupsUsersLinkTable);

        return $this->getObjects($criteria, $fields, false, true, $join);
    }
}
