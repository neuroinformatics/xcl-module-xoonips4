<?php

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/core/BeanFactory.class.php';
require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/viewtype/ViewTypeText.class.php';

class Xoonips_ViewTypeEmail extends Xoonips_ViewTypeText {

	public function setTemplate() {
		$this->template = $this->dirname . '_viewtype_email.html';	
	}

	public function getEditViewForModerator($field, $value, $groupLoopId) {
		$fieldName = $this->getFieldName($field, $groupLoopId);
		$this->getXoopsTpl()->assign('viewType', 'editForModerator');
		$this->getXoopsTpl()->assign('fieldName', $fieldName);
		$this->getXoopsTpl()->assign('len', $field->getLen());
		$this->getXoopsTpl()->assign('value', $value);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	} 
	
	public function inputCheck(&$errors, $field, $value, $fieldName) {
		//dataCheck
		$field->getDataType()->inputCheck($errors, $field, $value, $fieldName);
		$myxoopsConfigUser = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF_USER);
		$char = "/^[_a-z0-9\-+!#$%&'*\/=?^`{|}~]+(\.[_a-z0-9\-+!#$%&'*\/=?^`{|}~]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i";
		$parameters = array();
		$value = trim($value);
		$chk = true;
		if ($value != '') {
			if (!preg_match($char, $value)) {
				$errors->addError('_MD_' . strtoupper($this->trustDirname) . '_ERROR_INVALID_EMAIL', $fieldName, $parameters);
				$chk = false;
			} elseif ($this->mailIsExist($value)) {
				$errors->addError('_MD_' . strtoupper($this->trustDirname) . '_ERROR_EMAILTAKEN', $fieldName, $parameters);
			}
			
			if ($chk) {
				foreach ($myxoopsConfigUser['bad_emails'] as $bad_email) {
					$preg = '/' . $bad_email . '/';
					if (preg_match($preg, $value)) $chk = false;
				}
				if (!$chk) $errors->addError('_MD_' . strtoupper($this->trustDirname) . '_ERROR_INVALID_EMAIL', $fieldName, $parameters);
			}
				
		} else {
			$parameters[] = $field->getName();
			$errors->addError('_MD_' . strtoupper($this->trustDirname) . '_ERROR_REQUIRED', $fieldName, $parameters);
		}	
	}
	
	public function editCheck(&$errors, $field, $value, $fieldName, $uid) {
		//dateCheck
		$field->getDataType()->inputCheck($errors, $field, $value, $fieldName);
		$char = "/^[_a-z0-9\-+!#$%&'*\/=?^`{|}~]+(\.[_a-z0-9\-+!#$%&'*\/=?^`{|}~]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i";
		$parameters = array();
		$value = trim($value);
		if ($value != '') {
			if (!preg_match($char, $value)) {
				$errors->addError('_MD_' . strtoupper($this->trustDirname) . '_ERROR_INVALID_EMAIL', $fieldName, $parameters);
			} elseif ($this->mailIsExist($value) && $value != $this->getEmail($uid)) {
				$errors->addError('_MD_' . strtoupper($this->trustDirname) . '_ERROR_EMAILTAKEN', $fieldName, $parameters);
			}
		} else {
			$parameters[] = $field->getName();
			$errors->addError('_MD_' . strtoupper($this->trustDirname) . '_ERROR_REQUIRED', $fieldName, $parameters);
		}
	}
	
	private function mailIsExist($value) {
		$ret = false;
		global $xoopsDB;
		$value = Xoonips_Utils::convertSQLStr($value);
		$sql = "select email from " . $xoopsDB->prefix('users') . " where email='" . $value . "'";
		$result = $xoopsDB->queryF($sql);
		if ($row = $xoopsDB->fetchArray($result)) {
			$ret = true;
		}
		return $ret;
	}
	
	private function getEmail($uid) {
		$ret = '';
		global $xoopsDB;
		$uid = Xoonips_Utils::convertSQLNum($uid);
		$sql = "select email from " . $xoopsDB->prefix('users') . " where uid=$uid";
		$result = $xoopsDB->queryF($sql);
		if ($row = $xoopsDB->fetchArray($result)) {
			$ret = $row['email'];
		}
		return $ret;
	}
	
	
	public function isDisplayFieldName() {
		return false;
	}
	
	public function getEditView($field, $value, $groupLoopId) {
		$myxoopsConfigUser = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF_USER);
		$fieldName = $this->getFieldName($field, $groupLoopId);
		$this->getXoopsTpl()->assign('viewType', 'edit');
		$this->getXoopsTpl()->assign('allowChgMail', $myxoopsConfigUser['allow_chgmail']);
		$this->getXoopsTpl()->assign('len', $field->getLen());
		$this->getXoopsTpl()->assign('fieldName', $fieldName);
		$this->getXoopsTpl()->assign('value', $value);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}
	
	public function isDisplay($op) {
		$user = User_User::getInstance();
		$uid = $user->getId();
		$viewemail = 0;
		if ($uid != '') {
			$userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
			$row = $userBean->getUserBasicInfo($uid);
			$viewemail = $row['user_viewemail'];
		}
		$isSelfUser = false;
		$isModerator = false;
		if (isset($_SESSION['xoopsUserId'])) {
			$isSelfUser = ($uid == $_SESSION['xoopsUserId']);
			$userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
			$isModerator = $userBean->isModerator($_SESSION['xoopsUserId']);
		}
		if ($op == Xoonips_Enum::OP_TYPE_DETAIL && $viewemail != 1 && !$isSelfUser && !$isModerator){
			return false;
		}
		return true;
	}
	
	public function mustCheck(&$errors, $field, $value, $fieldName) {
		return true;
	}
}

