<?php

require_once XOOPS_MODULE_PATH.'/user/admin/actions/UserListAction.class.php';
require_once XOONIPS_TRUST_PATH.'/admin/forms/user/UserFilterForm.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/User.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/BeanFactory.class.php';

class Xoonips_UserListAction extends User_UserListAction
{
    protected $mDirname = '';
    protected $mTrustDirname = '';

    public function setDirname($dirname, $trustDirname)
    {
        $this->mDirname = $dirname;
        $this->mTrustDirname = $trustDirname;
    }

    public function &_getFilterForm()
    {
        $filter = new Xoonips_UserFilterForm($this->_getPageNavi(), $this->_getHandler());

        return $filter;
    }

    public function executeViewIndex(&$controller, &$xoopsUser, &$render)
    {
        $render->setTemplateName('user_list.html');
        $render->setAttribute('objects', $this->mObjects);
        $render->setAttribute('pageNavi', $this->mFilter->mNavi);
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('filterForm', $this->mFilter);
        $render->setAttribute('pageArr', $this->mpageArr);

        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->mDirname, $this->mTrustDirname);
        $active_total = $userBean->getCountActivateUsers(true);
        $inactive_total = $userBean->getCountActivateUsers(false);
        $render->setAttribute('activeUserTotal', $active_total);
        $render->setAttribute('inactiveUserTotal', $inactive_total);
        $render->setAttribute('UserTotal', $active_total + $inactive_total);
        $constpref = '_AD_'.strtoupper($this->mDirname);
        $render->setAttribute('constpref', $constpref);
    }

    public function _processConfirm(&$controller, &$xoopsUser)
    {
        $postsArr = $this->mActionForm->get('posts');
        $userHandler = &xoops_getmodulehandler('users');
        foreach (array_keys($postsArr) as $uid) {
            $user = &$userHandler->get($uid);
            if (is_object($user)) {
                $this->mUserObjects[$uid] = &$user;
            }
            unset($user);
        }

        return USER_FRAME_VIEW_INPUT;
    }

    public function _processSave(&$controller, &$xoopsUser)
    {
        $postsArr = $this->mActionForm->get('posts');
        $userHandler = &xoops_gethandler('user');
        // update posts
        foreach (array_keys($postsArr) as $uid) {
            if ($uid == 1) {
                continue;
            }
            $user = &$userHandler->get($uid);
            if (!is_object($user)) {
                continue;
            }
            $olddata = $user->get('posts');
            $newdata = $this->mActionForm->get('posts', $uid);
            if ($olddata != $newdata) {
                $user->set('posts', $newdata);
                if (!$userHandler->insert($user)) {
                    return USER_FRAME_VIEW_ERROR;
                }
            }
        }
        // delete users
        $userClass = Xoonips_User::getInstance();
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->mDirname, $this->mTrustDirname);
        foreach (array_keys($postsArr) as $uid) {
            if ($uid == 1) {
                continue;
            }
            if ($this->mActionForm->get('delete', $uid) != 1) {
                continue;
            }
            $user = &$userHandler->get($uid);
            if (!is_object($user)) {
                continue;
            }
            // xoonips permission check
            $message = '';
            if (!$userClass->deleteUserCheck($uid, $message)) {
                $controller->executeRedirect('./index.php?action=UserList', 1, $message);
            }
            XCube_DelegateUtils::call('Legacy.Admin.Event.UserDelete', new XCube_Ref($user));
            if ($userClass->deleteUser($uid)) {
                XCube_DelegateUtils::call('Legacy.Admin.Event.UserDelete.Success', new XCube_Ref($user));
            } else {
                XCube_DelegateUtils::call('Legacy.Admin.Event.UserDelete.Fail', new XCube_Ref($user));

                return USER_FRAME_VIEW_ERROR;
            }
        }

        return USER_FRAME_VIEW_SUCCESS;
    }

    public function executeViewInput(&$controller, &$xoopsUser, &$render)
    {
        $render->setTemplateName('user_list_confirm.html');
        $render->setAttribute('userObjects', $this->mUserObjects);
        $render->setAttribute('actionForm', $this->mActionForm);
        $t_arr = $this->mActionForm->get('posts');
        $render->setAttribute('uids', array_keys($t_arr));
        $constpref = '_AD_'.strtoupper($this->mDirname);
        $render->setAttribute('constpref', $constpref);
    }
}
