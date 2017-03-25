<?php

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

require_once XOONIPS_TRUST_PATH.'/class/user/Notification.class.php';
require_once XOONIPS_TRUST_PATH.'/class/Enum.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/User.class.php';

/***
 * @internal
 * This action is for self delete function.
 *
 * Site owner want various procedure to this action. Therefore, this action may
 * have to implement main logic with Delegate only.
 */
class Xoonips_UserDeleteAction extends Xoonips_UserAction
{
    public $mActionForm = null;
    public $mObject = null;

    public $mSelfDelete = false;
    public $mSelfDeleteConfirmMessage = '';

    public $_mDoDelete;

    /**
     * _getPageAction.
     *
     * @param	void
     *
     * @return string
     **/
    protected function _getPageAction()
    {
        return _DELETE;
    }

    /**
     * _getPageTitle.
     *
     * @param	void
     *
     * @return string
     **/
    protected function _getPagetitle()
    {
        return Legacy_Utils::getUserName(Legacy_Utils::getUid());
    }

    public function prepare(&$controller, &$xoopsUser, &$moduleConfig)
    {
        $myxoopsConfigUser = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF_USER);
        $this->mSelfDelete = $myxoopsConfigUser['self_delete'];
        $this->mSelfDeleteConfirmMessage = $myxoopsConfigUser['self_delete_confirm'];

        $this->_mDoDelete = new XCube_Delegate('bool &', 'Legacy_Controller', 'XoopsUser');
        $this->_mDoDelete->register('Xoonips_UserDeleteAction._doDelete');

        $this->_mDoDelete->add(array(&$this, '_doDelete'));

        // pre condition check

        if (!$this->mSelfDelete) {
            $controller->executeForward(XOOPS_URL.'/');
        }

        if (is_object($xoopsUser)) {
            $handler = &xoops_getmodulehandler('users', 'user');
            $this->mObject = &$handler->get($xoopsUser->get('uid'));
        }
    }

    public function isSecure()
    {
        return true;
    }

    public function hasPermission(&$controller, &$xoopsUser, $moduleConfig)
    {
        return true;
    }

    public function getDefaultView(&$controller, &$xoopsUser)
    {
        $uid = isset($_GET['uid']) ? intval(xoops_getrequest('uid')) : $xoopsUser->get('uid');
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $myxoopsConfigUser = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF_USER);
        // userType (0:guest 1:user 2:groupManager 3:moderator)
        $userType = $userBean->getUserType($uid);
        // userself
        $isUserSelf = false;
        $xoopsUserId = 0;
        if (isset($_SESSION['xoopsUserId'])) {
            $xoopsUserId = $_SESSION['xoopsUserId'];
        }
        if ($uid == $xoopsUserId) {
            $isUserSelf = true;
        }
        if ($userType != Xoonips_Enum::USER_TYPE_USER || !$isUserSelf || !$this->mSelfDelete) {
            $controller->executeRedirect(XOOPS_URL.'/', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
        }
        //set ticket
        $user = $userBean->getUserBasicInfo($uid);
        $notice = $myxoopsConfigUser['self_delete_confirm'];
        $this->viewData['notice'] = $notice;
        $this->viewData['username'] = $user['uname'];
        $this->viewData['uid'] = $uid;
        $this->viewData['dirname'] = $this->dirname;

        return USER_FRAME_VIEW_INPUT;
    }

    /**
     * FIXME: Need FORCE LOGOUT here?
     */
    public function execute(&$controller, &$xoopsUser)
    {
        $uid = $xoopsUser->get('uid');

        // userself
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $userType = $userBean->getUserType($uid);
        $isUserSelf = false;
        if (isset($_SESSION['xoopsUserId']) && $uid == $_SESSION['xoopsUserId']) {
            $isUserSelf = true;
        }
        if ($userType != Xoonips_Enum::USER_TYPE_USER || !$isUserSelf || !$this->mSelfDelete) {
            $controller->executeRedirect(XOOPS_URL.'/', 3, $message);

            return USER_FRAME_VIEW_ERROR;
        }

        $user = Xoonips_User::getInstance();
        $message = '';
        if (!$user->deleteUserCheck($uid, $message)) {
            $controller->executeRedirect(XOOPS_URL.'/', 3, $message);

            return USER_FRAME_VIEW_ERROR;
        }

        $xoopsUser = new XoopsUser($uid);
        if (!$user->deleteUser($uid)) {
            XCube_DelegateUtils::call('Legacy.Admin.Event.UserDelete.Fail', new XCube_Ref($xoopsUser));

            return USER_FRAME_VIEW_ERROR;
        } else {
            XCube_DelegateUtils::call('Legacy.Admin.Event.UserDelete.Success', new XCube_Ref($xoopsUser));
        }

        return USER_FRAME_VIEW_SUCCESS;
    }

    /**
     * Exection deleting.
     *
     * @return bool
     */
    public function _doDelete(&$flag, &$controller, &$xoopsUser)
    {
        $uid = $_REQUEST['uid'];
        $handler = &xoops_gethandler('member');
        if ($handler->deleteUser($xoopsUser)) {
            $handler = &xoops_gethandler('online');
            $handler->destroy($this->mObject->get('uid'));
            xoops_notification_deletebyuser($this->mObject->get('uid'));
            $flag = true;
        }

        $flag |= false;
    }

    public function executeViewInput(&$controller, &$xoopsUser, &$render)
    {
        $render->setTemplateName('xoonips_user_delete.html');
        $this->setAttributes($render);
    }

    public function executeViewSuccess(&$controller, &$xoopsUser, &$render)
    {
        $controller->executeRedirect(XOOPS_URL.'/user.php?op=logout', 3, _MD_XOONIPS_MESSAGE_USER_DELETED);
    }

    public function executeViewError(&$controller, &$xoopsUser, &$render)
    {
        if ($render->getAttribute('errMsg') != '') {
            $errorMsg = $render->getAttribute('errMsg');
        } else {
            $errorMsg = _MD_XOONIPS_ERROR_DBUPDATE_FAILED;
        }
        $controller->executeRedirect(XOOPS_URL.'/', 3, $errorMsg);
    }
}
