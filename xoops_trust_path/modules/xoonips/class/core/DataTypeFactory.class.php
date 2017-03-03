<?php

/**
 * data type factory class
 */
class Xoonips_DataTypeFactory {

	/**
	 * instances cache
	 * @var {Trustdirname}_DataType[]
	 */
	private $dataTypeInstances = array();

	/**
	 * constructor
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 */
	private function __construct($dirname, $trustDirname) {
		global $xoopsDB;

		// get data type data
		$sql = sprintf('SELECT * FROM `%s`', $xoopsDB->prefix($dirname . '_data_type'));

		$result = $xoopsDB->query($sql);
		while ($row = $xoopsDB->fetchArray($result)) {
			$typeModule = $row['module'];
			$className = ucfirst($trustDirname) . '_' . $typeModule;
			if (!file_exists($fpath = sprintf('%s/modules/%s/class/datatype/%s.class.php', XOOPS_TRUST_PATH, $trustDirname, $typeModule)))
				return false; // module class is not found
			$mydirname = $dirname;
			$mytrustdirname = $trustDirname;
			require_once $fpath; 
			$dataType = new $className();
			$dataTypeId = $row['data_type_id'];
			$dataType->setId($dataTypeId);
			$dataType->setName($row['name']);
			$dataType->setModule($typeModule);
			$dataType->setTrustDirname($trustDirname);
			$this->dataTypeInstances[$dataTypeId] = $dataType;
		}
	}

	/**
	 * get data type factory instance
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @return {Trustdirname}_DataTypeFactory
	 */
	public static function getInstance($dirname, $trustDirname) {
		static $instance = array();
		if (!isset($instance[$dirname]))
			$instance[$dirname] = new self($dirname, $trustDirname);
		return $instance[$dirname];
	}

	/**
	 * get data type instance
	 *
	 * @param string $id
	 * @return {Trustdirname}_DataType
	 */
	public function getDataType($id) {
		return $this->dataTypeInstances[$id];
	}

}

