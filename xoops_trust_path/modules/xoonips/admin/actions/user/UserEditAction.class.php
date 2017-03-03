<?php

require_once XOOPS_MODULE_PATH . '/user/admin/actions/UserEditAction.class.php';
require_once XOONIPS_TRUST_PATH . '/class/core/BeanFactory.class.php';
require_once XOONIPS_TRUST_PATH . '/class/core/Workflow.class.php';
require_once XOONIPS_TRUST_PATH . '/class/user/Notification.class.php';
require_once XOONIPS_TRUST_PATH . '/class/Enum.class.php';

class Xoonips_UserEditAction extends User_UserEditAction {

	protected $mDirname = '';
	protected $mTrustDirname = '';

	function setDirname($dirname, $trustDirname) {
		$this->mDirname = $dirname;
		$this->mTrustDirname = $trustDirname;
	}

	function _setupObject() {
		parent::_setupObject();
		if ($this->mObject == null && $this->isEnableCreate()) {
			$certify_user = Xoonips_Utils::getXoonipsConfig($this->mDirname, 'certify_user');
			if ($certify_user == 'auto')
				$this->mObject->set('level', 2);
		}
	}

	function executeViewInput(&$controller, &$xoopsUser, &$render) {
		parent::executeViewInput($controller, $xoopsUser, $render);
		$constpref = '_AD_' . strtoupper($this->mDirname);
		$render->setAttribute('constpref', $constpref);
	}

	function _doExecute() {
		$ret = parent::_doExecute();
		if ($ret === true) {
			$isNew = (!$this->_getId());
			// workflow and notification
			$ret = self::_updateUserWorkflow($this->mObject->get('uid'), $isNew, $this->mDirname, $this->mTrustDirname);
		}
		return $ret;
	}

	/**
	 * update user workflow
	 *
	 * @param int $uid
	 * @param bool $isNew
	 * @param string $dirname
	 * @param string $trustDirname
	 * @return bool
	 */
	protected static function _updateUserWorkflow($uid, $isNew, $dirname, $trustDirname) {
		$root = & XCube_Root::getSingleton();
		$xoopsDB =& $root->mController->getDB();
		$certify_user = Xoonips_Utils::getXoonipsConfig($dirname, 'certify_user');
		$notification = new Xoonips_UserNotification($xoopsDB, $dirname, $trustDirname);
		$groupsUsersLink = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $dirname, $trustDirname);
		$moderatorUids = $groupsUsersLink->getModeratorUserIds();
		$xoopsUserHandler =& Xoonips_Utils::getXoopsHandler('user');
		$xoopsUser =& $xoopsUserHandler->get($uid);
		$level = $xoopsUser->get('level');
		$uname = $xoopsUser->get('uname');
		$url = XOOPS_URL . "/userinfo.php?uid=" . $uid;
		$firstApprovalUids = Xoonips_Workflow::getCurrentApproverUserIds($dirname, Xoonips_Enum::WORKFLOW_USER, $uid);
		$allApprovalUids = array_unique(array_merge($moderatorUids, Xoonips_Workflow::getAllApproverUserIds($dirname, Xoonips_Enum::WORKFLOW_USER, $uid)));
		if ($isNew) {
			// register new user
			if ($level == 1 && $certify_user == 'auto') {
				// auto certify mode enabled - force update level and certify automatically
				$xoopsUser->set('level', 2);
				if (!$xoopsUserHandler->insert($xoopsUser))
					return false;
				XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', $xoopsUser);
				XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', $xoopsUser);
				$notification->accountCertifiedAuto($uid, $allApprovalUids);
			} else if ($level == 1) {
				// not certified user - try to use workflow
				if (Xoonips_Workflow::addItem($uname, $dirname, Xoonips_Enum::WORKFLOW_USER, $uid, $url)) {
					// success to register workflow task - send certify request
					XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', $xoopsUser);
					$notification->accountCertifyRequest($uid, $firstApprovalUids);
				} else {
					// workflow not ready - force update level and certify automatically
					$xoopsUser->set('level', 2);
					if (!$xoopsUserHandler->insert($xoopsUser))
						return false;
					XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', $xoopsUser);
					XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', $xoopsUser);
					$notification->accountCertifiedAuto($uid, $allApprovalUids);
				}
			} else if ($level == 2) {
				// certified user - certify automatically
				$doNotify = 'auto';
				XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', $xoopsUser);
				XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', $xoopsUser);
				$notification->accountCertifiedAuto($uid, $allApprovalUids);
			}
		} else {
			// existing user
			if ($level == 1 && $certify_user == 'auto') {
				// auto certify mode enabled - force update level and certify automatically
				$xoopsUser->set('level', 2);
				if (!$xoopsUserHandler->insert($xoopsUser))
					return false;
				if (Xoonips_Workflow::isInProgressItem($dirname, Xoonips_Enum::WORKFLOW_USER, $uid)) {
					// workflow task found - delete in progress task
					Xoonips_Workflow::deleteItem($dirname, Xoonips_Enum::WORKFLOW_USER, $uid);
				} else {
					// workflow task not found
					XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', $xoopsUser);
				}
				XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', $xoopsUser);
				$notification->accountCertifiedAuto($uid, $allApprovalUids);
			} else if ($level == 1) {
				// not certified user - check current workflow progress
				if (!Xoonips_Workflow::isInProgressItem($dirname, Xoonips_Enum::WORKFLOW_USER, $uid)) {
					// workflow task not found - try to register workflow task
					if (Xoonips_Workflow::addItem($uname, $dirname, Xoonips_Enum::WORKFLOW_USER, $uid, $url)) {
						// success to register workflow task - send certify request
						XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', $xoopsUser);
						$notification->accountCertifyRequest($uid, $firstApprovalUids);
					} else {
						// workflow not ready - force update level and certify automatically
						$xoopsUser->set('level', 2);
						if (!$xoopsUserHandler->insert($xoopsUser))
							return false;
						XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', $xoopsUser);
						XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', $xoopsUser);
						$notification->accountCertifiedAuto($uid, $allApprovalUids);
					}
				}
			} else if ($level == 2) {
				// certified user - certify if workflow exists
				if (Xoonips_Workflow::isInProgressItem($dirname, Xoonips_Enum::WORKFLOW_USER, $uid)) {
					Xoonips_Workflow::deleteItem($dirname, Xoonips_Enum::WORKFLOW_USER, $uid);
					XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', $xoopsUser);
					$notification->accountCertified($uid, $allApprovalUids, '');
				}
			}
		}
		return true;
	}

}

