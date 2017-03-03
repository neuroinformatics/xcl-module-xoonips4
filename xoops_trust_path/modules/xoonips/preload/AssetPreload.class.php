<?php

if (!defined('XOOPS_ROOT_PATH')) exit();

require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/include/common.inc.php';
require_once XOONIPS_TRUST_PATH.'/class/Enum.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/BeanFactory.class.php';

/**
 * asset preload base class
 */
class Xoonips_AssetPreloadBase extends XCube_ActionFilter {

	/**
	 * dirname
	 * @var string
	 */
	public $mDirname = null;

	/**
	 * trust dirname
	 * @var string
	 */
	public $mTrustDirname = null;

	/**
	 * prepare
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 */
	public static function prepare($dirname, $trustDirname) {
		Xoonips_Utils::loadModinfoMessage($dirname);
		$root =& XCube_Root::getSingleton();
		$instance = new self($root->mController);
		$instance->mDirname = $dirname;
		$instance->mTrustDirname = $trustDirname;
		$root->mController->addActionFilter($instance);
	}

	/**
	 * preBlockFilter
	 */
	public function preBlockFilter() {
		static $isFirst = true;

		// record view top page event
		$log = Xoonips_BeanFactory::getBean('EventLogBean', $this->mDirname, $this->mTrustDirname);
		$log->recordViewTopPageEvent();

		if (!$isFirst)
			return;

		// global delegates
		$this->mRoot->mDelegateManager->add('Module.' . $this->mTrustDirname . '.Global.Event.GetAssetManager', 'Xoonips_AssetPreloadBase::getManager');
		$this->mRoot->mDelegateManager->add('Legacy_Utils.CreateModule', 'Xoonips_AssetPreloadBase::getModule');

		// Legacy_WorkflowClient delegates
		$file = XOOPS_TRUST_PATH . '/modules/' . $this->mTrustDirname . '/class/callback/ClientDelegate.class.php';
		$this->mRoot->mDelegateManager->add('Legacy_WorkflowClient.GetClientList', 'Xoonips_WorkflowClientDelegate::getClientList', $file);
		$this->mRoot->mDelegateManager->add('Legacy_WorkflowClient.UpdateStatus', 'Xoonips_WorkflowClientDelegate::updateStatus', $file);
		$this->mRoot->mDelegateManager->add('Xworkflow_WorkflowClient.GetTargetGroupId', 'Xoonips_WorkflowClientDelegate::getTargetGroupId', $file);

		// Site delegates
		$file = XOOPS_TRUST_PATH . '/modules/' . $this->mTrustDirname . '/class/callback/SiteDelegate.class.php';
		$this->mRoot->mDelegateManager->add('Site.JQuery.AddFunction', 'Xoonips_SiteDelegate::jQueryAddFunction', $file);
		$this->mRoot->mDelegateManager->add('Site.CheckLogin.Success', 'Xoonips_SiteDelegate::checkLoginSuccess', $file);
		$this->mRoot->mDelegateManager->add('Site.CheckLogin.Fail', 'Xoonips_SiteDelegate::checkLoginFail', $file);
		$this->mRoot->mDelegateManager->add('Site.Logout.Success', 'Xoonips_SiteDelegate::logoutSuccess', $file);
		$this->mRoot->mDelegateManager->add('User_UserViewAction.GetUserPosts', 'Xoonips_SiteDelegate::recountPost', $file);

		// Module.Xoonips.Event delegetes
		$file = XOONIPS_TRUST_PATH . '/class/callback/EventDelegate.class.php';
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.User.CertifyRequest', 'Xoonips_EventDelegate::userCertifyRequest', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.User.Certify', 'Xoonips_EventDelegate::userCertify', $file);	
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.User.Reject', 'Xoonips_EventDelegate::userReject', $file);		
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.User.Substitute.Begin', 'Xoonips_EventDelegate::userSuBegin', $file);	
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.User.Substitute.End', 'Xoonips_EventDelegate::userSuEnd', $file);	
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.User.Delete', 'Xoonips_EventDelegate::userDelete', $file);	
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.Member.Join', 'Xoonips_EventDelegate::groupMemberJoin', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.Member.JoinRequest', 'Xoonips_EventDelegate::groupMemberJoinRequest', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.Member.JoinCertify', 'Xoonips_EventDelegate::groupMemberJoinCertify', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.Member.JoinReject', 'Xoonips_EventDelegate::groupMemberJoinReject', $file);	
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.Member.Leave', 'Xoonips_EventDelegate::groupMemberLeave', $file);	
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.Member.LeaveRequest', 'Xoonips_EventDelegate::groupMemberLeaveRequest', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.Member.LeaveCertify', 'Xoonips_EventDelegate::groupMemberLeaveCertify', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.Member.LeaveReject', 'Xoonips_EventDelegate::groupMemberLeaveReject', $file);	
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.CertifyRequest', 'Xoonips_EventDelegate::groupCertifyRequest', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.Certify', 'Xoonips_EventDelegate::groupCertify', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.Reject', 'Xoonips_EventDelegate::groupReject', $file);	
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.DeleteRequest', 'Xoonips_EventDelegate::groupDeleteRequest', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.DeleteCertify', 'Xoonips_EventDelegate::groupDeleteCertify', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.DeleteReject', 'Xoonips_EventDelegate::groupDeleteReject', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.Edit', 'Xoonips_EventDelegate::groupEdit', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.OpenRequest', 'Xoonips_EventDelegate::groupOpenRequest', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.OpenCertify', 'Xoonips_EventDelegate::groupOpenCertify', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.OpenReject', 'Xoonips_EventDelegate::groupOpenReject', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.CloseRequest', 'Xoonips_EventDelegate::groupCloseRequest', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.CloseCertify', 'Xoonips_EventDelegate::groupCloseCertify', $file);
		$this->mRoot->mDelegateManager->add('Module.Xoonips.Event.Group.CloseReject', 'Xoonips_EventDelegate::groupCloseReject', $file);

		$isFirst = false;
	}

	/**
	 * get manager
	 *
	 * @param {Trustdirname}_AssetManager &$obj
	 * @param string $dirname
	 */
	public static function getManager(&$obj, $dirname) {
		$mytrustdirname = basename(dirname(dirname(__FILE__)));
		require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/AssetManager.class.php';
		$obj = Xoonips_AssetManager::getInstance($dirname, $mytrustdirname);
	}

	/**
	 * get module
	 *
	 * @param Legacy_AbstractModule &$obj
	 * @param XoopsModule $module
	 */
	public static function getModule(&$obj, $module) {
		$mytrustdirname = basename(dirname(dirname(__FILE__)));
		require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/Module.class.php';
		if ($module->getInfo('trust_dirname') == $mytrustdirname)
			$obj = new Xoonips_Module($module);
	}

	/**
	 * get block
	 *
	 * @param Legacy_AbstractBlockProcedure &$obj
	 * @param XoopsBlock $block
	 */
	public static function getBlock(&$obj, $block) {
		$mytrustdirname = basename(dirname(dirname(__FILE__)));
		$moduleHandler =& xoops_gethandler('module');
		$module =& $moduleHandler->get($block->get('mid'));
		if (is_object($module) && $module->getInfo('trust_dirname') == $mytrustdirname) {
			require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/blocks/' . $block->get('func_file');
			$className = 'Xoonips_' . substr($block->get('show_func'), 4);
			$obj = new $className($block);
		}
	}

}

