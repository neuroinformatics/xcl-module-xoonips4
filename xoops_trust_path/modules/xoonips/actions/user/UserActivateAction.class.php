<?php
/**
 *
 *
 */

if (!defined('XOOPS_ROOT_PATH')) exit();

require_once XOONIPS_TRUST_PATH . "/class/user/AbstractEditAction.class.php";
require_once XOONIPS_TRUST_PATH . "/class/user/Notification.class.php";
require_once XOONIPS_TRUST_PATH . '/class/core/BeanFactory.class.php';
require_once XOONIPS_TRUST_PATH . '/class/core/Workflow.class.php';
require_once XOONIPS_TRUST_PATH . "/class/Enum.class.php";

class Xoonips_UserActivateAction extends Xoonips_UserAbstractEditAction
{
	function _getId()
	{
		return isset($_REQUEST['uid']) ? intval(xoops_getrequest('uid')) : 0;
	}
	
	function &_getHandler()
	{
		$handler =& xoops_getmodulehandler('users', 'user');
		return $handler;
	}
	
	/**
	 *  Return false.
	 *  If a user requests dummy uid, kick out him!
	 */
	function isEnableCreate()
	{
		return false;
	}

	/**
	 *  Return false.
	 *  This action would be used by a guest user.
	 */
	function isSecure()
	{
		return false;
	}
	
	function getDefaultView(&$controller, &$xoopsUser)
	{
		//set ticket
		$this->viewData['op'] = $_REQUEST['op'];
		$this->viewData['uid'] = $_REQUEST['uid'];
		$this->viewData['actkey'] = $_REQUEST['actkey'];
		return USER_FRAME_VIEW_INPUT;
	}

	function execute(&$controller, &$xoopsUser)
	{
		global $xoopsDB;
		//laod User Module Language
		$root =& XCube_Root::getSingleton();
		$root->getLanguageManager()->loadModuleAdminMessageCatalog(XCUBE_CORE_USER_MODULE_NAME);

		$certify_user = Xoonips_Utils::getXooNIpsConfig($this->dirname, 'certify_user');
		$user_certify_date = Xoonips_Utils::getXooNIpsConfig($this->dirname, 'user_certify_date');

		$myxoopsConfigUser = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF_USER);
		$result = array();
		$dataname = Xoonips_Enum::WORKFLOW_USER;

		$userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
		$uid = $_REQUEST['uid'];
		$user = $userBean->getUserBasicInfo($uid);
		if ((!isset($_REQUEST['actkey'])) || (!$user)) {
			$controller->executeForward(XOOPS_URL . '/');
		}

		if ($user['actkey'] != xoops_getrequest('actkey')) {
			$controller->executeRedirect(XOOPS_URL . '/', 3, _MD_USER_MESSAGE_ACTKEYNOT);
		}

		if ($user['level'] == 1) {
			$controller->executeRedirect(XOOPS_URL . '/user.php', 3, _MD_XOONIPS_MESSAGE_ACTIVATED_NOT_APPROVE);
                }

		if ($user['level'] > 1) {
			$controller->executeRedirect(XOOPS_URL . '/user.php', 3, _MD_USER_MESSAGE_ACONTACT);
		}
		if ($user_certify_date > 0) {
			$time = (time() - $user['user_regdate']) / (24 * 60 * 60);
			if ($time > $user_certify_date) {
				if (!$userBean->deleteUsers($uid)){
					$controller->executeRedirect(XOOPS_URL.'/', 3, 'DB error!');
				}
				if (!$userBean->deleteGroupsUsersByUid($uid)) {
					redirect_header(XOOPS_URL.'/', 3, 'DB error!');
				}
				$controller->executeRedirect(XOOPS_URL . '/', 3, _MD_XOONIPS_MESSAGE_ACTIVATE_TIMEOUT);
			}
		}
		if (!$userBean->activateUser($user)) {
			$controller->executeRedirect(XOOPS_URL . '/', 3, 'Activation failed!');
		}

		$notification = new Xoonips_UserNotification($xoopsDB, $this->dirname, $this->trustDirname);

		$groupUserLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
                $moderatorUids = $groupUserLinkBean->getModeratorUserIds();

		if ($certify_user == 'on') {
			// certification request
			$title = $user['uname'];
			$url = XOOPS_URL . "/userinfo.php?uid=" . $uid;
			if (Xoonips_Workflow::addItem($title, $this->dirname, $dataname, $uid, $url)) {
				// success to register workflow task
				XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', new XoopsUser($uid));
				$sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $dataname, $uid);
				$notification->accountCertifyRequest($uid, $sendToUsers);
				if ($myxoopsConfigUser['activation_type'] == 2) {
					// activate by xoops admin & certify manual
					$controller->executeRedirect(XOOPS_URL .'/user.php', 5, _MD_XOONIPS_MESSAGE_ACTIVATED_ADMIN_CERTIFY);
				} else if ($myxoopsConfigUser['activation_type'] <= 1) {
					// activate by xoops by user & certify manual
					$controller->executeRedirect(XOOPS_URL .'/user.php', 5, _MD_XOONIPS_MESSAGE_ACTIVATED_USER_CERTIFY);
				}
			} else {
				// workflow not available - force certify automatically
				if (!$userBean->certifyUser($user)) {
					$controller->executeRedirect(XOOPS_URL . '/', 3, 'Activation failed!');
				}
				XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', new XoopsUser($uid));
				XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', new XoopsUser($uid));
				$sendToUsers = $moderatorUids;
				$sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $uid));
				$notification->accountCertifiedAuto($uid, $sendToUsers);
			}
		} else {
			// certification automatically
			if (!$userBean->certifyUser($user)) {
				$controller->executeRedirect(XOOPS_URL . '/', 3, 'Activation failed!');
			}
			XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', new XoopsUser($uid));
			XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', new XoopsUser($uid));
			$sendToUsers = $moderatorUids;
			$sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $uid));
			$notification->accountCertifiedAuto($uid, $sendToUsers);

			if ($myxoopsConfigUser['activation_type'] == 2) {
				// activate xoops account by xoops administrator
				$myxoopsConfig = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF);
				// send e-mail to the registered address
				// notify a completion of certification to the certified user by e-mail
				$xoopsMailer =& getMailer();
				$xoopsMailer->useMail();
				$xoopsMailer->setTemplateDir(Xoonips_Utils::mailTemplateDir($this->dirname, $this->trustDirname));
				$xoopsMailer->setTemplate('user_account_certified_notify_for_user.tpl');
				$xoopsMailer->assign('USER_UNAME', $user['uname']);
				$xoopsMailer->assign('USER_EMAIL', $user['email']);
				$xoopsMailer->assign('USER_DETAIL_URL', XOOPS_URL . '/userinfo.php?uid=' . $uid);
				if (isset($_SESSION['xoopsUserId'])) {
					$certifyUserInfo = $userBean->getUserBasicInfo($_SESSION['xoopsUserId']);
					$certifyUser = $certifyUserInfo['uname'];
				} else {
					$certifyUser = '';
				}
				$xoopsMailer->assign('CERTIFY_USER', $certifyUser);
				$xoopsMailer->assign('SITENAME', $myxoopsConfig['sitename']);
				$xoopsMailer->assign('SITEURL', XOOPS_URL . '/');
				$xoopsMailer->assign('ADMINMAIL', $myxoopsConfig['adminmail']);
				$xoopsMailer->setToUsers(new XoopsUser($uid) );
				$xoopsMailer->setFromEmail($myxoopsConfig['adminmail']);
				$xoopsMailer->setFromName($myxoopsConfig['sitename']);
				$xoopsMailer->setSubject(_MD_XOONIPS_MESSAGE_ACCOUNT_CERTIFIED_AUTO_NOTIFYSBJ);
				if ($xoopsMailer->send()) {
					$controller->executeRedirect(XOOPS_URL .'/user.php', 5, _MD_XOONIPS_MESSAGE_CERTIFY_MAILOK);
				} else {
					$controller->executeRedirect(XOOPS_URL .'/user.php', 5, _MD_XOONIPS_MESSAGE_CERTIFY_MAILNG);
				}
			} else {
				$controller->executeRedirect(XOOPS_URL .'/user.php', 5, _MD_USER_MESSAGE_ACTLOGIN);
			}
		}
		exit();
	}

	function executeViewInput(&$controller, &$xoopsUser, &$render)
	{
		$render->setTemplateName($this->dirname . '_user_activate.html');
		$this->setAttributes($render);
		$controller->executeView();
		exit();
	}
}

