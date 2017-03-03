<?php

require_once XOONIPS_TRUST_PATH . '/class/core/AbstractActionBase.class.php';
require_once XOONIPS_TRUST_PATH . '/class/core/BeanFactory.class.php';
require_once 'Notification.class.php';

abstract class Xoonips_UserActionBase extends Xoonips_AbstractActionBase {	
	
	public function __construct($dirname = null)  {
		parent::__construct($dirname);
		$root =& XCube_Root::getSingleton();
		$root->mLanguageManager->loadPageTypeMessageCatalog('xoonips');
		global $xoopsDB;
		$this->notification = new Xoonips_UserNotification($xoopsDB, $this->dirname, $this->trustDirname);		
	}
}

