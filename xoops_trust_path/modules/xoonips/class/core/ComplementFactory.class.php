<?php

require_once dirname(__FILE__) . '/BeanFactory.class.php';

/**
 * complement factory class
 */
class Xoonips_ComplementFactory {

	/**
	 * instances cache
	 * @var {Trustdirname}_Complement[]
	 */
	private $complementInstances = array();

	/**
	 * constructor
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 */
	private function __construct($dirname, $trustDirname) {
		$bean = Xoonips_BeanFactory::getBean('ComplementBean', $dirname, $trustDirname);
		$complements = $bean->getComplementInfo();
		if (!$complements)
			return;
		foreach ($complements as $comp){
			$compId = $comp['complement_id'];
			$vtId = $comp['view_type_id'];
			$module = $comp['module'];
			if (empty($module))
				continue;
			$className = ucfirst($trustDirname) . '_' . $module;
			if (!file_exists($fpath = sprintf('%s/modules/%s/class/complement/%s.class.php', XOOPS_TRUST_PATH, $trustDirname, $module)))
				return false; // module class is not found
			$mydirname = $dirname;
			$mytrustdirname = $trustDirname;
			require_once $fpath; 
			$this->complementInstances[$vtId] = new $className($compId, $dirname, $trustDirname);
		}
	}

	/**
	 * get complement factory instance
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @return {Trustdirname}_ComplementFactory
	 */
	public static function getInstance($dirname, $trustDirname) {
		static $instance = array();
		if (!isset($instance[$dirname]))
			$instance[$dirname] = new self($dirname, $trustDirname);
		return $instance[$dirname];
	}

	/**
	 * get complement instance
	 *
	 * @param int $id
	 * @return {Trustdirname}_Complemnet
	 */
	public function getComplement($id){
		return $this->complementInstances[$id];
	}

}

