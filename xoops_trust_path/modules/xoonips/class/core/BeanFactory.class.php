<?php

class Xoonips_BeanFactory {
	public static function getBean($c, $dirname = null, $trustDirname = null) {
		static $beans = array();

		if (!is_null($dirname) && is_null($trustDirname)) {
			$module_handler =& xoops_gethandler('module');
			$module =& $module_handler->getByDirname($dirname);
			if (is_object($module)) {
				$dirname = strtolower($module->getVar('dirname'));
				$trustDirname = $module->getVar('trust_dirname');
			} else {
				$trustDirname = $dirname;
			}
		}

		$className = ucfirst($trustDirname) . '_' . $c;
		if (!isset($beans[$className])) {
			require_once XOOPS_TRUST_PATH . "/modules/$trustDirname/class/bean/$c.class.php";
			$beans[$className] = new $className($dirname, $trustDirname);
		}
		return $beans[$className];
	}

}

