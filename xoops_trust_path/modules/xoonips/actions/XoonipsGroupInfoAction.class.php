<?php

require_once dirname(__DIR__).'/class/user/ActionBase.class.php';

class Xoonips_GroupInfoAction extends Xoonips_UserActionBase
{
    protected function doInit(&$request, &$response)
    {
        $viewData = array();
        $groupbean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        $userbean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $groupuserlinkbean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        $groupId = $request->getParameter('groupid');
        $group = $groupbean->getGroup($groupId);
        $file_path = XOOPS_ROOT_PATH.'/uploads/xoonips/group/'.$groupId;
        $showThumbnail = false;
        if (file_exists($file_path)) {
            $showThumbnail = true;
        }

        if (empty($group)) {
            $response->setSystemError(_MD_XOONIPS_MESSAGE_GROUP_EMPTY);

            return false;
        }

        // get resource informations
        $maximumResource = $groupbean->getGroupMaximumResources($groupId);
        $usedResource = $groupbean->getGroupUsedResources($groupId);

        // get group information
        $admins = $userbean->getUsersGroups($groupId, true);
        $members = $userbean->getUsersGroups($groupId, false);
        $thumbnail = sprintf('%s/modules/%s/image.php/group/%u/%s', XOOPS_URL, $this->dirname, $groupId, $group['icon']);

        // edit group member name
        $users = array();
        foreach ($members as $member) {
            $groupUserLinkInfo = $groupuserlinkbean->getGroupUserLinkInfo($groupId, $member['uid']);
            $member['activate'] = $groupUserLinkInfo['activate'];
            $users[] = $member;
        }
        // edit group storage
        $token_ticket = $this->createToken('group_detail');
        $viewData['token_ticket'] = $token_ticket;
        $breadcrumbs = array(
            array(
                'name' => _MD_XOONIPS_LANG_GROUP_LIST,
                'url' => 'user.php?op=groupList',
            ),
            array(
                'name' => _MD_XOONIPS_LANG_GROUP_INFO,
            ),
        );
        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['group'] = $group;
        $viewData['admins'] = $admins;
        $viewData['members'] = $users;
        $viewData['itemNum'] = $usedResource['itemNum'];
        $viewData['indexNum'] = $usedResource['indexNum'];
        $viewData['fileSize'] = $usedResource['fileSize'] / 1024 / 1024;
        $viewData['itemNumberLimit'] = $maximumResource['itemNumberLimit'];
        $viewData['indexNumberLimit'] = $maximumResource['indexNumberLimit'];
        $viewData['itemStorageLimit'] = $maximumResource['itemStorageLimit'] / 1024 / 1024;
        $viewData['thumbnail'] = $thumbnail;
        $viewData['showThumbnail'] = $showThumbnail;
        $response->setViewData($viewData);
        $response->setForward('init_success');

        return true;
    }
}
