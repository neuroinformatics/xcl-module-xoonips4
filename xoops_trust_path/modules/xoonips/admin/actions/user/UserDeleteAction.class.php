<?php

require_once XOOPS_MODULE_PATH.'/user/admin/actions/UserDeleteAction.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/User.class.php';

class Xoonips_UserDeleteAction extends User_UserDeleteAction
{
    protected $mDirname = '';
    protected $mTrustDirname = '';

    public function setDirname($dirname, $trustDirname)
    {
        $this->mDirname = $dirname;
        $this->mTrustDirname = $trustDirname;
    }

    public function _doExecute()
    {
        $uid = $this->mObject->get('uid');
        $userClass = Xoonips_User::getInstance();
        $userClass->setId($uid);
        $message = '';
        if (!$userClass->deleteUserCheck($uid, $message)) {
            $controller->executeRedirect('./index.php?action=UserList', 1, $message);
        }
        XCube_DelegateUtils::call('Legacy.Admin.Event.UserDelete', new XCube_Ref($this->mObject));
        if (!$userClass->deleteUser($uid)) {
            XCube_DelegateUtils::call('Legacy.Admin.Event.UserDelete.Fail', new XCube_Ref($this->mObject));

            return USER_FRAME_VIEW_ERROR;
        }
        XCube_DelegateUtils::call('Legacy.Admin.Event.UserDelete.Success', new XCube_Ref($this->mObject));

        return USER_FRAME_VIEW_SUCCESS;
    }
}
