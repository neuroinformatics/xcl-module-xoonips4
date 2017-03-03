<?php

require_once XOONIPS_TRUST_PATH . '/class/core/Workflow.class.php';
require_once XOONIPS_TRUST_PATH . '/class/core/WorkflowClientBase.class.php';
require_once XOONIPS_TRUST_PATH . '/class/core/BeanFactory.class.php';
require_once dirname(dirname(__FILE__)) . '/core/User.class.php';

class Xoonips_WorkflowClientGroupDelete extends Xoonips_WorkflowClientBase {

	var $dataname = Xoonips_Enum::WORKFLOW_GROUP_DELETE;

	/**
	 *
	 *
	**/
	public function doCertify($gid, $comment) {
		$groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
		$group = $groupBean->getGroup($gid);	
		$group_handler =& xoops_gethandler('group');
		$xoopsGroup = $group_handler->get($gid);	
	
		$user = Xoonips_User::getInstance();
		$message = '';
		if (!$user->doGroupDeleted($group, $xoopsGroup, false, $message, $comment)) {
			return false;
		}
	}

	/**
	 *
	 *
	**/
	public function doProgress($gid) {
		$sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $this->dataname, $gid);
		$this->notification->groupDeleteRequest($gid, $sendToUsers);
	}

	/**
	 *
	 *
	**/
	public function doRefuse($gid, $comment) {
		$groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
		$group_handler =& xoops_gethandler('group');
		$xoopsGroup = $group_handler->get($gid);
		if (!$groupBean->groupsCertify($gid)) {
			return false;
		}
		//event log
		XCube_DelegateUtils::call('Module.Xoonips.Event.Group.DeleteReject', $xoopsGroup);
		
		//send to group admins and certifyUsers
		$groupUserLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
		$sendToUsers = array();
		foreach ($groupUserLinkBean->getAdminUserIds($gid) as $id) {
			$sendToUsers[] = $id; 
		}
		$sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $this->dataname, $gid));
		$sendToUsers = array_unique($sendToUsers);
		$this->notification->groupDeleteRejected($gid, $sendToUsers, $comment);
	}
	
}

