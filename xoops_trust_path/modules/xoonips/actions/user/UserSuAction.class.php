<?php

if (!defined('XOOPS_ROOT_PATH')) exit();

class Xoonips_UserSuAction extends Xoonips_UserAction {

	private $mConstPref = '';
	private $mSessionUid = null;
	private $mTargetUid = null;
	private $mOriginalUid = null;
	private $mErrorMessage = '';

	protected function _getPagetitle() {
		return constant($this->mConstPref . '_USER_LANG_SU');
	}

	function prepare(&$controller, &$xoopsUser, &$moduleConfig) {
		$this->mConstPref = '_MD_' . strtoupper($this->dirname);
		$this->mSessionUid = $this->trustDirname . '_old_uid';
		$this->mOriginalUid = isset($_SESSION[$this->mSessionUid]) ? intval($_SESSION[$this->mSessionUid]) : 0;
		if ($this->mOriginalUid > 0 && !Xoonips_Utils::userExists($this->mOriginalUid))
			$controller->executeForward(XOOPS_URL . '/');
		$this->mTargetUid = intval(XCube_Root::getSingleton()->mContext->mRequest->getRequest('uid'));
	}

	function isSecure() {
		return true;
	}

	function hasPermission(&$controller, &$xoopsUser, $moduleConfig) {
		if ($this->mOriginalUid == 0) {
			$uid = $xoopsUser->get('uid');
			if (!Xoonips_Utils::isAdmin($uid, $this->dirname))
				return false;
		}
		return true;
	}

	function getDefaultView(&$controller, &$xoopsUser) {
		if ($this->mOriginalUid > 0)
			return $this->endSwitchUser($controller, $xoopsUser);
		$breadcrumbs = array(
			array(
				'name' => constant($this->mConstPref . '_USER_LANG_SU')
			)
		);
		$this->viewData['xoops_breadcrumbs'] = $breadcrumbs;
		$this->viewData['uid'] = $this->mTargetUid;
		$this->viewData['dirname'] = $this->dirname;
		return USER_FRAME_VIEW_INPUT;
	}

	function execute(&$controller, &$xoopsUser) {
		if ($this->mOriginalUid > 0)
			return $this->endSwitchUser($controller, $xoopsUser);
		return $this->beginSwitchUser($controller, $xoopsUser);
	}

	function executeViewInput(&$controller, &$xoopsUser, &$render) {
		$render->setAttribute('constpref', $this->mConstPref);
		$render->setAttribute('mytrustdirname', $this->trustDirname);
		$render->setTemplateName($this->dirname . '_user_su.html');
		$this->setAttributes($render);
	}

	function executeViewSuccess(&$controller, &$xoopsUser, &$render) {
		$message = $this->mOriginalUid == 0 ? constant($this->mConstPref . '_USER_MESSAGE_SU_START') : constant($this->mConstPref . '_USER_MESSAGE_SU_END');
		$controller->executeRedirect(XOOPS_URL . '/', 3, $message, false);
	}

	function executeViewError(&$controller, &$xoopsUser, &$render) {
		$url = XOOPS_URL . '/user.php?op=su';
		if ($this->mTargetUid > 0)
			$url .= '&uid=' . $this->mTargetUid;
		$controller->executeRedirect($url, 3, $this->mErrorMessage, false);
	}

	/**
	 * begin switch user
	 */
	private function beginSwitchUser(&$controller, &$xoopsUser) {
		$uid = $xoopsUser->get('uid');
		if ($this->mTargetUid == 0 || $this->mTargetUid == $uid) {
			$this->mErrorMessage = constant($this->mConstPref . '_USER_ERROR_SU_NO_ACCOUNT');
			$this->mTargetUid = 0;
			return USER_FRAME_VIEW_ERROR;
		}
		$userHandler =& xoops_gethandler('user');
		$userObj =& $userHandler->get($this->mTargetUid);
		if (!is_object($userObj) || $userObj->get('level') < 2) {
			$this->mErrorMessage = constant($this->mConstPref . '_USER_ERROR_SU_NO_ACCOUNT');
			$this->mTargetUid = 0;
			return USER_FRAME_VIEW_ERROR;
		}
		$pass = XCube_Root::getSingleton()->mContext->mRequest->getRequest('pass');
		$criteria = new CriteriaCompo();
		$criteria->add(new Criteria('uid', $uid));
		$criteria->add(new Criteria('pass', md5($pass)));
		if ($userHandler->getCount($criteria) != 1) {
			$this->mErrorMessage = constant($this->mConstPref . '_USER_ERROR_SU_BAD_PASSWORD');
			return USER_FRAME_VIEW_ERROR;
		}
		$this->changeUserId($this->mTargetUid);
		$_SESSION[$this->mSessionUid] = $uid;
		XCube_DelegateUtils::call('Module.Xoonips.Event.User.Substitute.Begin', new XoopsUser($uid), new XoopsUser($this->mTargetUid)
);
		return USER_FRAME_VIEW_SUCCESS;
	}

	/**
	 * end switch user
	 */
	private function endSwitchUser(&$controller, &$xoopsUser) {
		$uid = $xoopsUser->get('uid');
		XCube_DelegateUtils::call('Module.Xoonips.Event.User.Substitute.End', new XoopsUser($this->mOriginalUid), new XoopsUser($uid));
		$this->changeUserId($this->mOriginalUid);
		unset($_SESSION[$this->mSessionUid]);
		return USER_FRAME_VIEW_SUCCESS;
	}

	/**
	 * change user id
	 *
	 * @param int $userId
	 */
	private function changeUserId($userId) {
		$userObj = new XoopsUser($userId);
		$groupIds = $userObj->getGroups();
		$_SESSION['xoopsUserId'] = $userId;
		$_SESSION['xoopsUserGroups'] = $groupIds;
	}

}
