<?php

use Xoonips\Core\XoopsUtils;

class Xoonips_UserSuAction extends Xoonips_UserAction
{
    private $mConstPref = '';
    private $mSessionUid = null;
    private $mTargetUid = null;
    private $mOriginalUid = null;
    private $mErrorMessage = '';

    protected function _getPagetitle()
    {
        return constant($this->mConstPref.'_USER_LANG_SU');
    }

    public function prepare(&$controller, &$xoopsUser, &$moduleConfig)
    {
        $this->mConstPref = '_MD_'.strtoupper($this->dirname);
        $this->mSessionUid = $this->trustDirname.'_old_uid';
        $this->mOriginalUid = isset($_SESSION[$this->mSessionUid]) ? intval($_SESSION[$this->mSessionUid]) : 0;
        if ($this->mOriginalUid > 0 && !XoopsUtils::userExists($this->mOriginalUid)) {
            $controller->executeForward(XOOPS_URL.'/');
        }
        $this->mTargetUid = intval(XCube_Root::getSingleton()->mContext->mRequest->getRequest('uid'));
    }

    public function isSecure()
    {
        return true;
    }

    public function hasPermission(&$controller, &$xoopsUser, $moduleConfig)
    {
        if (0 == $this->mOriginalUid) {
            $uid = $xoopsUser->get('uid');
            if (!XoopsUtils::isAdmin($uid, $this->dirname)) {
                return false;
            }
        }

        return true;
    }

    public function getDefaultView(&$controller, &$xoopsUser)
    {
        if ($this->mOriginalUid > 0) {
            return $this->endSwitchUser($controller, $xoopsUser);
        }
        $breadcrumbs = [
            [
                'name' => constant($this->mConstPref.'_USER_LANG_SU'),
            ],
        ];
        $this->viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $this->viewData['uid'] = $this->mTargetUid;
        $this->viewData['dirname'] = $this->dirname;

        return USER_FRAME_VIEW_INPUT;
    }

    public function execute(&$controller, &$xoopsUser)
    {
        if ($this->mOriginalUid > 0) {
            return $this->endSwitchUser($controller, $xoopsUser);
        }

        return $this->beginSwitchUser($controller, $xoopsUser);
    }

    public function executeViewInput(&$controller, &$xoopsUser, &$render)
    {
        $render->setAttribute('constpref', $this->mConstPref);
        $render->setAttribute('mytrustdirname', $this->trustDirname);
        $render->setTemplateName($this->dirname.'_user_su.html');
        $this->setAttributes($render);
    }

    public function executeViewSuccess(&$controller, &$xoopsUser, &$render)
    {
        $message = 0 == $this->mOriginalUid ? constant($this->mConstPref.'_USER_MESSAGE_SU_START') : constant($this->mConstPref.'_USER_MESSAGE_SU_END');
        $controller->executeRedirect(XOOPS_URL.'/', 3, $message, false);
    }

    public function executeViewError(&$controller, &$xoopsUser, &$render)
    {
        $url = XOOPS_URL.'/user.php?op=su';
        if ($this->mTargetUid > 0) {
            $url .= '&uid='.$this->mTargetUid;
        }
        $controller->executeRedirect($url, 3, $this->mErrorMessage, false);
    }

    /**
     * begin switch user.
     */
    private function beginSwitchUser(&$controller, &$xoopsUser)
    {
        $uid = $xoopsUser->get('uid');
        if (0 == $this->mTargetUid || $this->mTargetUid == $uid) {
            $this->mErrorMessage = constant($this->mConstPref.'_USER_ERROR_SU_NO_ACCOUNT');
            $this->mTargetUid = 0;

            return USER_FRAME_VIEW_ERROR;
        }
        $userHandler = &xoops_gethandler('user');
        $userObj = &$userHandler->get($this->mTargetUid);
        if (!is_object($userObj) || $userObj->get('level') < 2) {
            $this->mErrorMessage = constant($this->mConstPref.'_USER_ERROR_SU_NO_ACCOUNT');
            $this->mTargetUid = 0;

            return USER_FRAME_VIEW_ERROR;
        }
        $pass = XCube_Root::getSingleton()->mContext->mRequest->getRequest('pass');
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('uid', $uid));
        $criteria->add(new Criteria('pass', md5($pass)));
        if (1 != $userHandler->getCount($criteria)) {
            $this->mErrorMessage = constant($this->mConstPref.'_USER_ERROR_SU_BAD_PASSWORD');

            return USER_FRAME_VIEW_ERROR;
        }
        $this->changeUserId($this->mTargetUid);
        $_SESSION[$this->mSessionUid] = $uid;
        XCube_DelegateUtils::call('Module.Xoonips.Event.User.Substitute.Begin', new XoopsUser($uid), new XoopsUser($this->mTargetUid)
);

        return USER_FRAME_VIEW_SUCCESS;
    }

    /**
     * end switch user.
     */
    private function endSwitchUser(&$controller, &$xoopsUser)
    {
        $uid = $xoopsUser->get('uid');
        XCube_DelegateUtils::call('Module.Xoonips.Event.User.Substitute.End', new XoopsUser($this->mOriginalUid), new XoopsUser($uid));
        $this->changeUserId($this->mOriginalUid);
        unset($_SESSION[$this->mSessionUid]);

        return USER_FRAME_VIEW_SUCCESS;
    }

    /**
     * change user id.
     *
     * @param int $userId
     */
    private function changeUserId($userId)
    {
        $userObj = new XoopsUser($userId);
        $groupIds = $userObj->getGroups();
        $_SESSION['xoopsUserId'] = $userId;
        $_SESSION['xoopsUserGroups'] = $groupIds;
    }
}
