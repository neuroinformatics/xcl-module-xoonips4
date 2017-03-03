<?php

require_once XOOPS_MODULE_PATH . '/user/admin/actions/GroupListAction.class.php';

class Xoonips_GroupListAction extends User_GroupListAction {

	protected $mDirname = '';
	protected $mTrustDirname = '';

	function setDirname($dirname, $trustDirname) {
		$this->mDirname = $dirname;
		$this->mTrustDirname = $trustDirname;
	}

	function executeViewIndex(&$controller, &$xoopsUser, &$render) {
		parent::executeViewIndex($controller, $xoopsUser, $render);
		$constpref = '_AD_' . strtoupper($this->mDirname);
		$render->setAttribute('constpref', $constpref);
	}

}

