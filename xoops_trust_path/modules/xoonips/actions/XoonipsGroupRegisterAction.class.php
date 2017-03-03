<?php

use Xoonips\Core\FileUtils;

require_once XOONIPS_TRUST_PATH . '/class/user/ActionBase.class.php';
require_once XOONIPS_TRUST_PATH . '/class/core/User.class.php';
require_once XOONIPS_TRUST_PATH . '/class/core/File.class.php';

class Xoonips_GroupRegisterAction extends Xoonips_UserActionBase {

	protected function doInit(&$request, &$response) {
		global $xoopsUser;
		$uid = $xoopsUser->getVar('uid');
		$viewData = array();
		
		$userbean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
		$isModerator = $userbean->isModerator($uid);
		$isGroupManager = $userbean->isGroupAdmin($uid);
		$configVal = Xoonips_Utils::getXooNIpsConfig($this->dirname, 'group_making');
		
		//right check
		if (!$isModerator && $configVal != 'on') {
			$response->setSystemError(_MD_XOONIPS_ERROR_GROUP_NEW);
			return false;
		}
		
		$user = array();
		if (!$isModerator) {
			$user[] = $userbean->getUserBasicInfo($uid);
		}
		
		//init view
		$group = array();
		$result = array();
		XCube_DelegateUtils::call('Module.Xoonips.GetGroupMaximumResources', new XCube_Ref($result), null);
		if (count($result) > 0) {
			foreach ($result as $limit) {
				$group['item_number_limit'] = $limit['itemNumberLimit'];
				$group['index_number_limit'] = $limit['indexNumberLimit'];
				$group['item_storage_limit'] = $limit['itemStorageLimit'] / 1024 / 1024;
			}
		} else {
			$group['item_number_limit'] = 0;
			$group['index_number_limit'] = 0;
			$group['item_storage_limit'] = 0;	
		}	
		$group['is_public'] = 0;
		$group['can_join'] = 0;
		$group['is_hidden'] = 0;
		$group['member_accept'] = 0;
		$group['item_accept'] = 0;
		$token_ticket = $this->createToken('user_group_new');
		$breadcrumbs = array(
			array(
				'name' => _MD_XOONIPS_LANG_GROUP_LIST,
				'url' => 'user.php?op=groupList'
			),
			array(
				'name' => _MD_XOONIPS_LANG_GROUP_REGISTER
			)
		);	
		$viewData['xoops_breadcrumbs'] = $breadcrumbs;	
		$viewData['token_ticket'] = $token_ticket;
		$viewData['group'] = $group;
		$viewData['user'] = $user;
		$viewData['moderator'] = $isModerator;
		$viewData['dirname'] = $this->dirname;
		$viewData['mytrustdirname'] = $this->trustDirname;
		$response->setViewData($viewData);
		$response->setForward('init_success');
		return true;
	}
	
	protected function doRegister(&$request, &$response) {
		$errors = new Xoonips_Errors();
		$viewData = array();

		if (!$this->validateToken('user_group_new')) {
			$response->setSystemError('Ticket error');
			return false;
		}

		$token_ticket = $this->createToken('user_group_new');
		$breadcrumbs = array(
			array(
				'name' => _MD_XOONIPS_LANG_GROUP_LIST,
				'url' => 'user.php?op=groupList'
			),
			array(
				'name' => _MD_XOONIPS_LANG_GROUP_REGISTER
			)
		);

		//get parameter
		$uids = $request->getParameter('uid');
		$moderator = $request->getParameter('moderator');
		$group = $this->setGroup($request);
		
		$group['icon'] = '';
		$group['mime_type'] = '';

		//get group manager
		$userbean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
		$admins = array();
		if (!empty($uids)) {
			foreach ($uids as $uid) {
				$manager = $userbean->getUserBasicInfo($uid);				
				$admins[] = $manager;
			}
		}	
		
		//get icon information
		$file = $request->getFile('filepath');
		if (!empty($file)) {
			$group['icon'] = $file['name'];
			$group['mime_type'] = $file['type'];
		}
		
		//input check
		if ($this->inputCheck($group, $admins, $file, $errors)) {
			$viewData['xoops_breadcrumbs'] = $breadcrumbs;
			$viewData['token_ticket'] = $token_ticket;
			$viewData['group'] = $group;
			$viewData['user'] = $admins;
			$viewData['moderator'] = $moderator;
			$viewData['errors'] = $errors->getView($this->dirname);
			$viewData['dirname'] = $this->dirname;
		        $viewData['mytrustdirname'] = $this->trustDirname;
			$response->setViewData($viewData);
			$response->setForward('input_error');
			return true;	
		}

		// start transaction
		$this->startTransaction();

		$user = Xoonips_User::getInstance();
		$message = '';
		$group_id = $user->doGroupRegistry($group, $uids, $message);
		if (!$group_id) {
			// workflow not configured
			if (is_array($message)) {
				$errors->addError($message[1], 'workflow', null, false);
				$viewData['xoops_breadcrumbs'] = $breadcrumbs;
				$viewData['token_ticket'] = $token_ticket;
				$viewData['group'] = $group;
				$viewData['user'] = $admins;
				$viewData['moderator'] = $moderator;
				$viewData['errors'] = $errors->getView($this->dirname);
				$viewData['dirname'] = $this->dirname;
		                $viewData['mytrustdirname'] = $this->trustDirname;
				$response->setViewData($viewData);
				$response->setForward('input_error');
				$this->rollbackTransaction();
				return true;
			} else {
				$response->setSystemError($message);
			}
			return false;
		}

		//upload group icon
		if (!empty($file)) {
			$uploadDir = XOOPS_ROOT_PATH . '/uploads/xoonips';	
			$uploadfile = $uploadDir .'/group/'. $group_id;
			if(!move_uploaded_file($file['tmp_name'], $uploadfile)) {
				$response->setSystemError(_MD_XOONIPS_ERROR_GROUP_ICON_UPLOAD);
				return false; 		
       		}
		}
		if ($message != '') {
			$viewData['redirect_msg'] = $message;
		}

		$viewData['group'] = $group;
		$viewData['url'] = 'user.php?op=groupList';
		$response->setViewData($viewData);
		$response->setForward('register_success');
		return true;
	}
	
	protected function doSearch(&$request, &$response) {
		$viewData = array();

		if (!$this->validateToken('user_group_new')) {
			$response->setSystemError('Ticket error');
	        	return false;
		}

		//get parameter
		$adminValue = $request->getParameter('adminvalue');
		$users = $request->getParameter('uid');
		
		//get group manager information
		$userbean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
		$uids = array();
		$user = array();
		if (!empty($users)) {
			foreach ($users as $u) {
				$uids[] = $u;
			}
		}		
		if (!empty($adminValue)) {
			$admins = explode(',', $adminValue);
			foreach ($admins as $admin) {
				if (!in_array($admin, $uids)) {
					$uids[] = $admin;
				}		
			}
		}
		foreach ($uids as $uid) {
			$manager = $userbean->getUserBasicInfo($uid);
			$user[] = $manager;
		}
		
		$group = $this->setGroup($request);

		$token_ticket = $this->createToken('user_group_new');
		$breadcrumbs = array(
			array(
				'name' => _MD_XOONIPS_LANG_GROUP_LIST,
				'url' => 'user.php?op=groupList'
			),
			array(
				'name' => _MD_XOONIPS_LANG_GROUP_REGISTER
			)
		);	
		
		$viewData['xoops_breadcrumbs'] = $breadcrumbs;	
		$viewData['token_ticket'] = $token_ticket;
		$viewData['group'] = $group;
		$viewData['user'] = $user;
		$viewData['moderator'] = $request->getParameter('moderator');
		$viewData['dirname'] = $this->dirname;
		$viewData['mytrustdirname'] = $this->trustDirname;
		$response->setViewData($viewData);
		$response->setForward('search_success');
		return true;
	}

	private function setGroup($request) {
		$group = array();		
		$group['name'] = $request->getParameter('name');
		$group['description'] = $request->getParameter('description');
		$group['item_number_limit'] = $request->getParameter('item_number_limit');
		$group['index_number_limit'] = $request->getParameter('index_number_limit');
		$group['item_storage_limit'] = $request->getParameter('item_storage_limit');

		$group['is_public'] =  $request->getParameter('is_public');
		$group['can_join'] =  $request->getParameter('can_join');
		$group['is_hidden'] =  $request->getParameter('is_hidden');
		$group['member_accept'] =  $request->getParameter('member_accept');
		$group['item_accept'] = $request->getParameter('item_accept');
		return $group;
	}

	private function inputCheck($group, $admins, $file, $errors) {
		//input check
		$inputError = false;
		if (trim($group['name']) == '') {
			$parameters = array();
			$parameters[] = _MD_XOONIPS_LANG_GROUP_NAME;
			$errors->addError('_MD_XOONIPS_ERROR_REQUIRED', 'name', $parameters);	
			$inputError = true;						
		}	
		if (strlen(trim($group['name'])) > 50) {
			$parameters = array();
			$parameters[] = _MD_XOONIPS_LANG_GROUP_NAME;
			$parameters[] = 50;
			$errors->addError('_MD_XOONIPS_ERROR_MAXLENGTH', 'name', $parameters);
			$inputError = true;
		}			
		if (empty($admins)) {
			$parameters = array();
			$parameters[] = _MD_XOONIPS_LANG_GROUP_ADMIN;
			$errors->addError('_MD_XOONIPS_ERROR_REQUIRED', 'administrator', $parameters);
			$inputError = true;
		}
		if (trim($group['name']) != '') {
			$groupbean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);		
			if ($groupbean->existsGroup($group['name'])) {
				$parameters = array();
				$errors->addError('_MD_XOONIPS_ERROR_GROUP_NAME_EXISTS', 'name', $parameters);
				$inputError = true;
			}
		}			
		if (!empty($file)) {
			$info = FileUtils::getFileInfo($file['tmp_name'], $file['name']);
			if ($info === false || !array_key_exists('width', $info)) {
				$parameters = array();
				$errors->addError('_MD_XOONIPS_ERROR_GROUP_ICON', 'icon', $parameters);
				$inputError = true;	
			}
		}
		return $inputError;
	}
}

