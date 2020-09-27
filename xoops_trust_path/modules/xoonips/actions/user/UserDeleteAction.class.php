<?php

use Xoonips\Core\XoopsUtils;

require_once dirname(dirname(__DIR__)).'/class/user/Notification.class.php';
require_once dirname(dirname(__DIR__)).'/class/core/User.class.php';

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
        return XoopsUtils::getUserName(XoopsUtils::getUid());
    }

    public function prepare(&$controller, &$xoopsUser, &$moduleConfig)
    {
        $this->mSelfDelete = XoopsUtils::getXoopsConfig('self_delete', XOOPS_CONF_USER);
        $this->mSelfDeleteConfirmMessage = XoopsUtils::getXoopsConfig('self_delete_confirm', XOOPS_CONF_USER);

        $this->_mDoDelete = new XCube_Delegate('bool &', 'Legacy_Controller', 'XoopsUser');
        $this->_mDoDelete->register('Xoonips_UserDeleteAction._doDelete');

        $this->_mDoDelete->add([&$this, '_doDelete']);

        // pre condition check
        if (!$this->mSelfDelete) {
            $controller->executeForward(XOOPS_URL.'/');
        }

        $uid = is_object($xoopsUser) ? $xoopsUser->get('uid') : XOONIPS_UID_GUEST;
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        // userType (0:guest 1:user 2:groupManager 3:moderator)
        $userType = $userBean->getUserType($uid);
        if (1 != $userType) {
            $controller->executeRedirect(XOOPS_URL.'/', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
        }
        $user = Xoonips_User::getInstance();
        $message = '';
        if (!$user->deleteUserCheck($uid, $message)) {
            $controller->executeRedirect(XOOPS_URL.'/', 3, $message);
        }

        if (is_object($xoopsUser)) {
            $handler = xoops_getmodulehandler('users', 'user');
            $this->mObject = $handler->get($xoopsUser->get('uid'));
        }
    }

    public function isSecure()
    {
        return true;
    }

    public function hasPermission(&$controller, &$xoopsUser, $moduleConfig)
    {
        if (1 == $xoopsUser->get('uid')) {
            return false;
        }

        return true;
    }

    public function getDefaultView(&$controller, &$xoopsUser)
    {
        $uid = $xoopsUser->get('uid');
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $user = $userBean->getUserBasicInfo($uid);
        $this->viewData['notice'] = $this->mSelfDeleteConfirmMessage;
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
        $flag = false;
        $this->_mDoDelete->call(new XCube_Ref($flag), $controller, $xoopsUser);

        if ($flag) {
            XCube_DelegateUtils::call('Legacy.Event.UserDelete', new XCube_Ref($this->mObject));
            XCube_DelegateUtils::call('Legacy.Admin.Event.UserDelete.Success', new XCube_Ref($this->mObject));

            return USER_FRAME_VIEW_SUCCESS;
        }
        XCube_DelegateUtils::call('Legacy.Admin.Event.UserDelete.Fail', new XCube_Ref($this->mObject));

        return USER_FRAME_VIEW_ERROR;
    }

    /**
     * Exection deleting.
     *
     * @return bool
     */
    public function _doDelete(&$flag, $controller, $xoopsUser)
    {
        $user = Xoonips_User::getInstance();
        $flag = $user->deleteUser($xoopsUser->get('uid'));
    }

    public function executeViewInput(&$controller, &$xoopsUser, &$render)
    {
        // TODO: fix constant variable in template
        $render->setTemplateName('xoonips_user_delete.html');
        $this->setAttributes($render);
    }

    public function executeViewSuccess(&$controller, &$xoopsUser, &$render)
    {
        $controller->executeRedirect(XOOPS_URL.'/user.php?op=logout', 3, _MD_XOONIPS_MESSAGE_USER_DELETED);
    }

    public function executeViewError(&$controller, &$xoopsUser, &$render)
    {
        if ('' != $render->getAttribute('errMsg')) {
            $errorMsg = $render->getAttribute('errMsg');
        } else {
            $errorMsg = _MD_XOONIPS_ERROR_DBUPDATE_FAILED;
        }
        $controller->executeRedirect(XOOPS_URL.'/', 3, $errorMsg);
    }
}
