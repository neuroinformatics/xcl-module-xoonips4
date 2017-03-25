<?php

use Xoonips\Core\FileUtils;

require_once XOONIPS_TRUST_PATH.'/class/user/ActionBase.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/User.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/File.class.php';
require_once XOONIPS_TRUST_PATH.'/class/Enum.class.php';

class Xoonips_GroupEditAction extends Xoonips_UserActionBase
{
    protected function doInit(&$request, &$response)
    {
        global $xoopsUser;
        $uid = $xoopsUser->getVar('uid');
        $groupId = $request->getParameter('groupid');
        $viewData = array();

        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);

        $group = $groupBean->getGroup($groupId);
        $group['item_storage_limit'] = $group['item_storage_limit'] / 1024 / 1024;
        $managers = $userBean->getUsersGroups($groupId, true);
        $isModerator = $userBean->isModerator($uid);
        $isGroupManager = $userBean->isGroupManager($group['groupid'], $uid);

        //right check,only moderator and group manager can use
        if (0 < $group['activate'] && $group['activate'] < 5) {
            if (!$isModerator && !$isGroupManager) {
                $response->setSystemError(_MD_XOONIPS_ERROR_GROUP_EDIT);

                return false;
            }
        } else {
            $response->setSystemError(_MD_XOONIPS_ERROR_GROUP_EDIT);

            return false;
        }

        //get icon
        $thumbnail = sprintf('%s/modules/%s/image.php/group/%u/%s', XOOPS_URL, $this->dirname, $groupId, $group['icon']);

        //if show icon
        //$showThumbnail,0:file not exist,1:file exist,2:file delete
        $file_path = XOOPS_ROOT_PATH.'/uploads/xoonips/group/'.$groupId;
        $showThumbnail = 0;
        if (file_exists($file_path)) {
            $showThumbnail = 1;
        }

        //activate not 1 or 3, warning show
        $warning = '';
        if ($group['activate'] == Xoonips_Enum::GRP_NOT_CERTIFIED) {
            $warning = _MD_XOONIPS_MESSAGE_GROUP_CERTIFY_REQUESTING;
        } elseif ($group['activate'] == Xoonips_Enum::GRP_OPEN_REQUIRED) {
            $warning = _MD_XOONIPS_MESSAGE_GROUP_OPEN_REQUESTING;
        } elseif ($group['activate'] == Xoonips_Enum::GRP_CLOSE_REQUIRED) {
            $warning = _MD_XOONIPS_MESSAGE_GROUP_CLOSE_REQUESTING;
        } elseif ($group['activate'] == Xoonips_Enum::GRP_DELETE_REQUIRED) {
            $warning = _MD_XOONIPS_MESSAGE_GROUP_DELETE_REQUESTING;
        }

        $token_ticket = $this->createToken('user_group_edit');
        $breadcrumbs = array(
            array(
                'name' => _MD_XOONIPS_LANG_GROUP_LIST,
                'url' => 'user.php?op=groupList',
            ),
            array(
                'name' => _MD_XOONIPS_LANG_GROUP_EDIT,
            ),
        );

        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['group'] = $group;
        $viewData['admins'] = $managers;
        $viewData['moderator'] = $isModerator;
        $viewData['thumbnail'] = $thumbnail;
        $viewData['showThumbnail'] = $showThumbnail;
        $viewData['warning'] = $warning;
        $viewData['gname'] = $group['name'];
        $viewData['groupentry'] = $group['can_join'];
        $viewData['grouppublic'] = $group['is_public'];
        $viewData['grouphidden'] = $group['is_hidden'];
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('init_success');

        return true;
    }

    protected function doUpdate(&$request, &$response)
    {
        $errors = new Xoonips_Errors();
        $viewData = array();

        if (!$this->validateToken('user_group_edit')) {
            $response->setSystemError('Ticket error');

            return false;
        }

        $token_ticket = $this->createToken('user_group_edit');
        $breadcrumbs = array(
            array(
                'name' => _MD_XOONIPS_LANG_GROUP_LIST,
                'url' => 'user.php?op=groupList',
            ),
            array(
                'name' => _MD_XOONIPS_LANG_GROUP_EDIT,
            ),
        );

        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);

        //get hidden parameter
        $uids = $request->getParameter('uid');
        $gname = $request->getParameter('gname');
        $groupId = $request->getParameter('groupid');
        $warning = $request->getParameter('warning');
        $gentry = $request->getParameter('groupentry');
        $gpublic = $request->getParameter('grouppublic');
        $ghidden = $request->getParameter('grouphidden');
        $moderator = $request->getParameter('moderator');
        $showThumbnail = $request->getParameter('showThumbnail');

        //get group parameter
        $group = $groupBean->getGroup($groupId);
        $groupPublic = $group['is_public'];
        $this->setGroup($request, $group);
        if ($group['is_public'] == '') {
            $group['is_public'] = $gpublic;
        }
        if ($group['can_join'] == '') {
            $group['can_join'] = $gentry;
        }
        if ($group['is_hidden'] == '') {
            $group['is_hidden'] = $ghidden;
        }

        //get uploaded icon
        $thumbnail = sprintf('%s/modules/%s/image.php/group/%u/%s', XOOPS_URL, $this->dirname, $groupId, $group['icon']);

        //get upload icon information
        $file = $request->getFile('filepath');
        if (!empty($file)) {
            $group['icon'] = $file['name'];
            $group['mime_type'] = $file['type'];
        } elseif ($showThumbnail == 2) {
            $group['icon'] = null;
            $group['mime_type'] = null;
        }

        //get group manager
        $admins = array();
        if (!empty($uids)) {
            foreach ($uids as $uid) {
                $manager = $userBean->getUserBasicInfo($uid);
                $admins[] = $manager;
            }
        }

        //input check
        if ($this->inputCheck($group, $gname, $admins, $file, $errors)) {
            $viewData['xoops_breadcrumbs'] = $breadcrumbs;
            $viewData['token_ticket'] = $token_ticket;
            $viewData['group'] = $group;
            $viewData['admins'] = $admins;
            $viewData['moderator'] = $moderator;
            $viewData['thumbnail'] = $thumbnail;
            $viewData['gname'] = $gname;
            $viewData['warning'] = $warning;
            $viewData['groupentry'] = $gentry;
            $viewData['grouppublic'] = $gpublic;
            $viewData['grouphidden'] = $ghidden;
            $viewData['showThumbnail'] = $showThumbnail;
            $viewData['errors'] = $errors->getView($this->dirname);
            $viewData['dirname'] = $this->dirname;
            $viewData['mytrustdirname'] = $this->trustDirname;
            $response->setViewData($viewData);
            $response->setForward('input_error');

            return true;
        }

        // start transaction
        $this->startTransaction();

        $user = Xoonips_User::getInstance();
        $message = '';
        if (!$user->doGroupEdit($groupPublic, $group, $uids, $message)) {
            // workflow not configured
            if (is_array($message)) {
                $errors->addError($message[1], 'workflow', null, false);
                $viewData['xoops_breadcrumbs'] = $breadcrumbs;
                $viewData['token_ticket'] = $token_ticket;
                $viewData['group'] = $group;
                $viewData['admins'] = $admins;
                $viewData['moderator'] = $moderator;
                $viewData['thumbnail'] = $thumbnail;
                $viewData['gname'] = $gname;
                $viewData['warning'] = $warning;
                $viewData['groupentry'] = $gentry;
                $viewData['grouppublic'] = $gpublic;
                $viewData['grouphidden'] = $ghidden;
                $viewData['showThumbnail'] = $showThumbnail;
                $viewData['errors'] = $errors->getView($this->dirname);
                $viewData['dirname'] = $this->dirname;
                $viewData['mytrustdirname'] = $this->trustDirname;
                $response->setViewData($viewData);
                $response->setForward('input_error');
                $this->rollbackTransaction();

                return true;
            } else {
                $response->setSystemError($message);
            }
        }
        if ($message != '') {
            $viewData['redirect_msg'] = $message;
        }

        //delete uploaded icon
        if ($showThumbnail == 2) {
            $uploadDir = XOOPS_ROOT_PATH.'/uploads/xoonips';
            $uploadfile = $uploadDir.'/group/'.$groupId;
            unlink($uploadfile);
        }

        //upload group icon
        if (!empty($file)) {
            $uploadDir = XOOPS_ROOT_PATH.'/uploads/xoonips';
            $uploadfile = $uploadDir.'/group/'.$groupId;
            if (!move_uploaded_file($file['tmp_name'], $uploadfile)) {
                $response->setSystemError(_MD_XOONIPS_ERROR_GROUP_ICON_UPLOAD);

                return false;
            }
        }

        if (empty($viewData['redirect_msg'])) {
            $viewData['redirect_msg'] = _MD_XOONIPS_MESSAGE_GROUP_EDIT_SUCCESS;
        }
        $viewData['url'] = 'user.php?op=groupList';
        $response->setViewData($viewData);
        $response->setForward('update_success');

        return true;
    }

    protected function doSearch(&$request, &$response)
    {
        $viewData = array();

        if (!$this->validateToken('user_group_edit')) {
            $response->setSystemError('Ticket error');

            return false;
        }

        $adminValue = $request->getParameter('adminvalue');
        $users = $request->getParameter('uid');
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        $uids = array();
        $admins = array();

        //get group manager
        if (!empty($users)) {
            foreach ($users as $user) {
                $uids[] = $user;
            }
        }
        if (!empty($adminValue)) {
            $values = explode(',', $adminValue);
            foreach ($values as $value) {
                if (!in_array($value, $uids)) {
                    $uids[] = $value;
                }
            }
        }
        foreach ($uids as $uid) {
            $manager = $userBean->getUserBasicInfo($uid);
            $admins[] = $manager;
        }

        //get parameter and group information
        $groupId = $request->getParameter('groupid');
        $group = $groupBean->getGroup($groupId);
        $this->setGroup($request, $group);

        $thumbnail = sprintf('%s/modules/%s/image.php/group/%u/%s', XOOPS_URL, $this->dirname, $groupId, $group['icon']);

        $token_ticket = $this->createToken('user_group_edit');
        $breadcrumbs = array(
            array(
                'name' => _MD_XOONIPS_LANG_GROUP_LIST,
                'url' => 'user.php?op=groupList',
            ),
            array(
                'name' => _MD_XOONIPS_LANG_GROUP_EDIT,
            ),
        );

        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['group'] = $group;
        $viewData['admins'] = $admins;
        $viewData['thumbnail'] = $thumbnail;
        $viewData['showThumbnail'] = $request->getParameter('showThumbnail');
        $viewData['moderator'] = $request->getParameter('moderator');
        $viewData['gname'] = $request->getParameter('gname');
        $viewData['warning'] = $request->getParameter('warning');
        $viewData['groupentry'] = $request->getParameter('groupentry');
        $viewData['grouppublic'] = $request->getParameter('grouppublic');
        $viewData['grouphidden'] = $request->getParameter('grouphidden');
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('search_success');

        return true;
    }

    private function setGroup($request, &$group)
    {
        $group['name'] = $request->getParameter('name');
        $group['description'] = $request->getParameter('description');
        $group['item_number_limit'] = $request->getParameter('item_number_limit');
        $group['index_number_limit'] = $request->getParameter('index_number_limit');
        $group['item_storage_limit'] = $request->getParameter('item_storage_limit');

        $group['is_public'] = $request->getParameter('is_public');
        $group['can_join'] = $request->getParameter('can_join');
        $group['is_hidden'] = $request->getParameter('is_hidden');
        $group['member_accept'] = $request->getParameter('member_accept');
        $group['item_accept'] = $request->getParameter('item_accept');

        return $group;
    }

    private function inputCheck($group, $gname, $admins, $file, &$errors)
    {
        $inputError = false;
        if (trim($group['name']) == '') {
            $parameters = array();
            $parameters[] = _MD_XOONIPS_LANG_GROUP_NAME;
            $errors->addError('_MD_XOONIPS_ERROR_REQUIRED', 'name', $parameters);
            $inputError = true;
        }
        if (strlen(trim($group['name'])) > 50) {
            $parameters = array();
            $parameters[] = _MD_XOONIPS_LANG_GROUP_NAME;
            $parameters[] = 50;
            $errors->addError('_MD_XOONIPS_ERROR_MAXLENGTH', 'name', $parameters);
            $inputError = true;
        }
        if (empty($admins)) {
            $parameters = array();
            $parameters[] = _MD_XOONIPS_LANG_GROUP_ADMIN;
            $errors->addError('_MD_XOONIPS_ERROR_REQUIRED', 'administrator', $parameters);
            $inputError = true;
        }
        if (trim($gname) != trim($group['name'])) {
            $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
            if ($groupBean->existsGroup($group['name'])) {
                $parameters = array();
                $errors->addError('_MD_XOONIPS_ERROR_GROUP_NAME_EXISTS', 'name', $parameters);
                $inputError = true;
            }
        }
        if (!empty($file)) {
            $info = FileUtils::getFileInfo($file['tmp_name'], $file['name']);
            if ($info === false || !array_key_exists('width', $info)) {
                $parameters = array();
                $errors->addError('_MD_XOONIPS_ERROR_GROUP_ICON', 'icon', $parameters);
                $inputError = true;
            }
        }

        return $inputError;
    }
}
