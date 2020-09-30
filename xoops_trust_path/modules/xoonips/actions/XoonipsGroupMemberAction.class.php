<?php

require_once dirname(__DIR__).'/class/user/ActionBase.class.php';
require_once dirname(__DIR__).'/class/core/User.class.php';

class Xoonips_GroupMemberAction extends Xoonips_UserActionBase
{
    protected function doInit(&$request, &$response)
    {
        global $xoopsUser;
        $uid = $xoopsUser->getVar('uid');
        $groupId = intval($request->getParameter('groupid'));
        $viewData = [];

        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        $groupUserLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);

        $group = $groupBean->getGroup($groupId);
        if (empty($group)) {
            $response->setSystemError(_MD_XOONIPS_MESSAGE_GROUP_EMPTY);

            return false;
        }

        $managers = $userBean->getUsersGroups($groupId, true);
        $users = $userBean->getUsersGroups($groupId, false);
        $members = [];
        foreach ($users as $user) {
            $groupUserLinkInfo = $groupUserLinkBean->getGroupUserLinkInfo($groupId, $user['uid']);
            $user['deleteFlag'] = true;
            $user['activate'] = $groupUserLinkInfo['activate'];
            if (1 == $groupUserLinkInfo['activate'] || 2 == $groupUserLinkInfo['activate']) {
                $user['deleteFlag'] = false;
            }
            $members[] = $user;
        }
        $isModerator = $userBean->isModerator($uid);
        $isGroupManager = $userBean->isGroupManager($group['groupid'], $uid);

        //right check,only moderator and group manager can use
        if (0 < $group['activate'] && $group['activate'] < 5) {
            if (!$isModerator && !$isGroupManager) {
                $response->setSystemError(_MD_XOONIPS_ERROR_GROUP_MEMBER);

                return false;
            }
        } else {
            $response->setSystemError(_MD_XOONIPS_ERROR_GROUP_MEMBER);

            return false;
        }

        $token_ticket = $this->createToken('user_group_member');
        $breadcrumbs = [
            [
                'name' => _MD_XOONIPS_LANG_GROUP_LIST,
                'url' => 'user.php?op=groupList',
            ],
            [
                'name' => _MD_XOONIPS_LANG_GROUP_MEMBER_EDIT,
            ],
        ];
        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['group'] = $group;
        $viewData['admins'] = $managers;
        $viewData['members'] = $members;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('init_success');

        return true;
    }

    protected function doUpdate(&$request, &$response)
    {
        $viewData = [];
        if (!$this->validateToken('user_group_member')) {
            $response->setSystemError('Ticket error');

            return false;
        }
        $token_ticket = $this->createToken('user_group_member');
        $breadcrumbs = [
            [
                'name' => _MD_XOONIPS_LANG_GROUP_LIST,
                'url' => 'user.php?op=groupList',
            ],
            [
                'name' => _MD_XOONIPS_LANG_GROUP_MEMBER_EDIT,
            ],
        ];

        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);

        //get parameter
        $groupId = intval($request->getParameter('groupid'));
        $group = $groupBean->getGroup($groupId);
        if (empty($group)) {
            $response->setSystemError(_MD_XOONIPS_MESSAGE_GROUP_EMPTY);

            return false;
        }

        $memberIds = $request->getParameter('memberid');
        $members = $userBean->getUsersGroups($groupId, false);

        // start transaction
        $this->startTransaction();

        //update group member
        $user = Xoonips_User::getInstance();
        $message = '';
        if (!$user->doGroupMember($groupId, $members, $memberIds, $message)) {
            $response->setSystemError($message);

            return false;
        }

        $viewData['redirect_msg'] = _MD_XOONIPS_MESSAGE_GROUP_MEMBER_SUCCESS;
        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['url'] = 'user.php?op=groupList';
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('update_success');

        return true;
    }

    protected function doSearch(&$request, &$response)
    {
        $viewData = [];
        if (!$this->validateToken('user_group_member')) {
            $response->setSystemError('Ticket error');

            return false;
        }

        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        $groupUserLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);

        //get parameter
        $memberValue = $request->getParameter('membervalue');
        $memberIds = $request->getParameter('memberid');
        $adminIds = $request->getParameter('adminid');
        $groupId = intval($request->getParameter('groupid'));
        $group = $groupBean->getGroup($groupId);
        if (empty($group)) {
            $response->setSystemError(_MD_XOONIPS_MESSAGE_GROUP_EMPTY);

            return false;
        }

        //get group members
        $uids = [];
        $members = [];
        if (!empty($memberIds)) {
            foreach ($memberIds as $memberId) {
                $uids[] = $memberId;
            }
        }
        if (!empty($memberValue)) {
            $values = explode(',', $memberValue);
            foreach ($values as $value) {
                if (!in_array($value, $uids)) {
                    $uids[] = $value;
                }
            }
        }
        foreach ($uids as $uid) {
            if (!in_array($uid, $adminIds)) {
                $member = $userBean->getUserBasicInfo($uid);
                $groupUserLinkInfo = $groupUserLinkBean->getGroupUserLinkInfo($groupId, $member['uid']);
                $member['deleteFlag'] = true;
                if ($groupUserLinkInfo) {
                    $member['activate'] = $groupUserLinkInfo['activate'];
                    if (1 == $groupUserLinkInfo['activate']) {
                        $member['deleteFlag'] = false;
                    }
                    if (2 == $groupUserLinkInfo['activate']) {
                        $member['deleteFlag'] = false;
                    }
                }
                $members[] = $member;
            }
        }

        //get group managers
        $admins = [];
        foreach ($adminIds as $adminId) {
            $admin = $userBean->getUserBasicInfo($adminId);
            $admins[] = $admin;
        }

        //get group infomation

        $token_ticket = $this->createToken('user_group_member');
        $breadcrumbs = [
            [
                'name' => _MD_XOONIPS_LANG_GROUP_LIST,
                'url' => 'user.php?op=groupList',
            ],
            [
                'name' => _MD_XOONIPS_LANG_GROUP_MEMBER_EDIT,
            ],
        ];

        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['group'] = $group;
        $viewData['admins'] = $admins;
        $viewData['members'] = $members;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('search_success');

        return true;
    }
}
