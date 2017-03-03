<?php

require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/AbstractActionBase.class.php';
require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/BeanFactory.class.php';
require_once 'Notification.class.php';

abstract class Xoonips_ActionBase extends Xoonips_AbstractActionBase {
	protected $log = false;

	public function __construct($dirname = null) {
		parent::__construct($dirname);
		global $xoopsDB;
		$this->notification = new Xoonips_Notification($xoopsDB, $this->dirname, $this->trustDirname);
		$this->log = Xoonips_BeanFactory::getBean('EventLogBean', $this->dirname, $this->trustDirname);
	}

}

