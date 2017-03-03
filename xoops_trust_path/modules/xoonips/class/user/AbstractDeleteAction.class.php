<?php

if (!defined('XOOPS_ROOT_PATH')) exit();

require_once XOONIPS_TRUST_PATH . "/class/user/AbstractEditAction.class.php";

class Xoonips_UserAbstractDeleteAction extends Xoonips_UserAbstractEditAction
{
	function isEnableCreate()
	{
		return false;
	}

	function _doExecute()
	{
		return $this->mObjectHandler->delete($this->mObject);
	}
}
