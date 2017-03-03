<?php

require_once 'Error.class.php';

class Xoonips_Errors {
	private $errors = array();
	public function __construct() {
	}
	
	public function setErrors($v) {
		$this->errors = $v;
	}
	
	public function getErrors() {
		return $this->errors;
	}
	
	public function getView($dirname, $isAdmin = false) {
		if (count($this->errors) == 0) {
			return '';
		}

		global $xoopsTpl;
		$xoopsTpl->assign('errors', $this->errors);
		$xoopsTpl->assign('dirname', $dirname);
		$xoopsTpl->assign('isAdmin', $isAdmin);
		return $xoopsTpl->fetch('db:' . $dirname . '_error.html');

	}

	public function addError($msgId, $fieldName, $parameters, $isConst = true) {
		$error = new Xoonips_Error($msgId, $fieldName, $parameters, $isConst);
		$this->errors[] = $error;
	}

	public function hasError() {
		if (count($this->errors) > 0) {
			return true;
		} else {
			return false;
		}
	}
}

