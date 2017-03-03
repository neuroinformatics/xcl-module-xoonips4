<?php

require_once 'ItemFieldManager.class.php';

class Xoonips_ItemFieldManagerFactory {
  	private static $instance;
	private $itemFieldManagerInstances = array();
	private $dirname;
	private $trustDirname;
 	private $xoopsTpl;

	private function __construct($dirname = null, $trustDirname = null) {
		global $xoopsModule;
		if (is_null($dirname)) {
			$this->dirname = strtolower($xoopsModule->getVar('dirname'));
		} else {
			$this->dirname = $dirname;
		}
		if (is_null($trustDirname)) {
			$this->trustDirname = $xoopsModule->getVar('trust_dirname');
		} else {
			$this->trustDirname = $trustDirname;
		}
		global $xoopsTpl;
		$this->xoopsTpl = $xoopsTpl;
 	}
 	
 	public static function getInstance($dirname = null, $trustDirname = null) {
		if (!isset(self::$instance)) {
        	$c = __CLASS__;
        	self::$instance = new $c($dirname, $trustDirname);
        }

        return self::$instance;
	}
	
	public function getItemFieldManager($id) {
		if (!isset($this->itemFieldManagerInstances[$id])) {
			$this->itemFieldManagerInstances[$id] = new Xoonips_ItemFieldManager();
			$this->itemFieldManagerInstances[$id]->setDirname($this->dirname);			
			$this->itemFieldManagerInstances[$id]->setTrustDirname($this->trustDirname);
			$this->itemFieldManagerInstances[$id]->setXoopsTpl($this->xoopsTpl);
			$this->itemFieldManagerInstances[$id]->init($id);
		}
		return $this->itemFieldManagerInstances[$id];
	}
}

