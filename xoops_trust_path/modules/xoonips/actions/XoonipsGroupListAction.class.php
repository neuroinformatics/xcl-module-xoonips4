<?php

require_once XOONIPS_TRUST_PATH . '/class/user/ActionBase.class.php';
require_once XOONIPS_TRUST_PATH . '/class/core/User.class.php';

class Xoonips_GroupListAction extends Xoonips_UserActionBase {
	protected function doInit(&$request, &$response) {
		global $xoopsUser;
		$uid = $xoopsUser->getVar('uid');
		$viewData = array();
		
		$groupbean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
		$groups = $groupbean->getGroups(Xoonips_Enum::GROUP_TYPE);
		
		//display group list
		$user = Xoonips_User::getInstance();
		$newflag = false;
		$groupLists = $user->addGroupOperationFlag($groups, $uid, $newflag);
		$viewData['groups'] = $groupLists;
		$viewData['newflag'] = $newflag;
		$token_ticket = $this->createToken('user_group_list');
		$viewData['token_ticket'] = $token_ticket;
		$breadcrumbs = array(
			array(
				'name' => _MD_XOONIPS_LANG_GROUP_LIST
			)
		);
		$viewData['xoops_breadcrumbs'] = $breadcrumbs;
		$viewData['grpNotCertified'] = Xoonips_Enum::GRP_NOT_CERTIFIED;
		$viewData['grpOpenRequired'] = Xoonips_Enum::GRP_OPEN_REQUIRED;
		$viewData['grpPublic'] = Xoonips_Enum::GRP_PUBLIC;
		$viewData['grpCloseRequired'] = Xoonips_Enum::GRP_CLOSE_REQUIRED;
		$viewData['grpDeleteRequired'] = Xoonips_Enum::GRP_DELETE_REQUIRED;
		$viewData['dirname'] = $this->dirname;
		$response->setViewData($viewData);
		$response->setForward('init_success');
		return true;
	}

	protected function doJoin(&$request,&$response){
		global $xoopsUser;
		$uid = $xoopsUser->getVar('uid');
		$viewData = array();
		
		if (!$this->validateToken('user_group_list')) {
			$response->setSystemError('Ticket error');
			return false;
		}
		
		$groupbean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
		$groupId = $request->getParameter('groupid');
		$group = $groupbean->getGroup($groupId);

		// start transaction
		$this->startTransaction();

		$user = Xoonips_User::getInstance();
		$message = '';
		if (!$user->doGroupJoin($group, $uid, $message)) {
			// workflow not configured
			if (is_array($message)) {
				$errors = new Xoonips_Errors();
				$errors->addError($message[1], 'workflow', null, false);
				$viewData['redirect_msg'] = $errors->getView($this->dirname);
				$this->rollbackTransaction();
			} else {
				$viewData['redirect_msg'] = $message;
			}
		} elseif ($message != '') {
			$viewData['redirect_msg'] = $message;
		}

		$token_ticket = $this->createToken('user_group_list');
		$viewData['token_ticket'] = $token_ticket;
		$viewData['url'] = 'user.php?op=groupList';		
		$response->setViewData($viewData);
		$response->setForward('join_success');
		return true;
	}

	protected function doLeave(&$request, &$response) {
		global $xoopsUser;
		$uid = $xoopsUser->getVar('uid');
		$viewData = array();
		
		if (!$this->validateToken('user_group_list')) {
			$response->setSystemError('Ticket error');
			return false;
		}
		
		$groupbean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
		$groupId = $request->getParameter('groupid');
		$group = $groupbean->getGroup($groupId);

		// start transaction
		$this->startTransaction();
		
		$user = Xoonips_User::getInstance();
		$message = '';
		if (!$user->doGroupLeave($group, $uid, $message)) {
			// workflow not configured
			if (is_array($message)) {
				$errors = new Xoonips_Errors();
				$errors->addError($message[1], 'workflow', null, false);
				$viewData['redirect_msg'] = $errors->getView($this->dirname);
				$this->rollbackTransaction();
			} else {
				$viewData['redirect_msg'] = $message;
			}
		} elseif ($message != '') {
			$viewData['redirect_msg'] = $message;
		}
		
		$viewData['url'] = 'user.php?op=groupList';
		$token_ticket = $this->createToken('user_group_list');
		$viewData['token_ticket'] = $token_ticket;
		$response->setViewData($viewData);
		$response->setForward('leave_success');
		return true;
	}

	protected function doDelete(&$request, &$response) {
		$viewData = array();
		
		if (!$this->validateToken('user_group_list')) {
			$response->setSystemError('Ticket error');
			return false;
		}
		
		$groupbean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
		$groupId = $request->getParameter('groupid');
		$group = $groupbean->getGroup($groupId);

		// start transaction
		$this->startTransaction();

		$user = Xoonips_User::getInstance();
		$message = '';
		if (!$user->doGroupDelete($group, $message)) {
			// workflow not configured
			if (is_array($message)) {
				$errors = new Xoonips_Errors();
				$errors->addError($message[1], 'workflow', null, false);
				$viewData['redirect_msg'] = $errors->getView($this->dirname);
				$this->rollbackTransaction();
			} else {
				$viewData['redirect_msg'] = $message;
			}
		} elseif ($message != '') {
			$viewData['redirect_msg'] = $message;
		}

		$viewData['url'] = 'user.php?op=groupList';
		$token_ticket = $this->createToken('user_group_list');
		$viewData['token_ticket'] = $token_ticket;
		$response->setViewData($viewData);
		$response->setForward('delete_success');
		return true;	
	}
	
	/**
	 * group can join,leave check
	 * 
	 * @return bool
	 */
	private function rightCheck($group, $uid, $type) {
		$userbean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
		$groupuserlinkbean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
		$groupUserList = $groupuserlinkbean->getGroupUserLinkInfo($group['groupid'], $uid);
		$isGroupManager = $userbean->isGroupManager($group['groupid'], $uid);
		if ($group['activate'] != Xoonips_Enum::GRP_NOT_CERTIFIED && $group['activate'] != Xoonips_Enum::GRP_DELETE_REQUIRED) {
			if ($group['can_join'] == 1) {
				if ($type == 'join') {
					if (empty($groupUserList)) {
						return true;
					}
				}
				if ($type == 'leave') {
					if (!$isGroupManager && !empty($groupUserList)) {
						if ($groupUserList['activate'] == 0) {
							return true;
						}
					}			
				}
			}	
		}
		return false;
	}
}

