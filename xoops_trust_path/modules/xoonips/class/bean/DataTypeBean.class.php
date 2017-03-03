<?php

require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/BeanBase.class.php';
/**
 * @brief operate xoonips_data_type table
 *
 */
class Xoonips_DataTypeBean extends Xoonips_BeanBase {

	/**
	 * Constructor
	 **/
	public function __construct($dirname, $trustDirname) {
		parent::__construct($dirname, $trustDirname);
		$this->setTableName('data_type', true);
	}

	/**
	 * select itemtype viewtype
	 *
	 * @param $viewtypeId view_type_id
	 * @return array
	 */
	public function selectDatatypesByViewtype($viewtypeId) {
		$ret = array();
		if (empty($viewtypeId)) return $ret;
		$table = $this->prefix($this->modulePrefix('view_data_relation'));
		$sql = "SELECT * FROM $this->table t1, $table t2 WHERE t1.data_type_id=t2.data_type_id AND t2.view_type_id=$viewtypeId";
		$result = $this->execute($sql);
		if (!$result) {
			return $ret;
		}
		while ($row = $this->fetchArray($result)) {
			$ret[] = $row;
		}
		$this->freeRecordSet($result);
		return $ret;
	}

	/**
	 * select itemtype datatype
	 *
	 * @param
	 * @return array
	 */
	public function getDatatypeList() {
		$ret = array();
		$sql = 'SELECT * FROM '. $this->table . ' ORDER BY data_type_id';
		$result = $this->execute($sql);
		if (!$result) {
			return $ret;
		}
		while ($row = $this->fetchArray($result)) {
			$ret[] = $row;
		}
		$this->freeRecordSet($result);
		return $ret;
	}

	/**
	 * select itemtype datatype by id
	 *
	 * @param
	 * @return array
	 */
	public function getDatatypeById($dataId) {
		$ret = null;
		$sql = 'SELECT * FROM '. $this->table . ' where data_type_id=' .$dataId;
		$result = $this->execute($sql);
		if (!$result) {
			return $ret;
		}
		while ($row = $this->fetchArray($result)) {
			$ret = $row;
		}
		$this->freeRecordSet($result);
		return $ret;
	}
	
	/**
	 * select datatype vy name
	 *
	 * @param $name name
	 * @return int
	 */
	public function selectByName($name) {
		$ret = '';
		$sql = "SELECT * FROM $this->table WHERE name=" . Xoonips_Utils::convertSQLStr($name);
		$result = $this->execute($sql);
		if (!$result) {
			return $ret;
		}
		while ($row = $this->fetchArray($result)) {
			$ret= $row['data_type_id'];
		}
		$this->freeRecordSet($result);
		return $ret;
	}

	/**
	 * insert datatype
	 *
	 * @param array $datatype
	 * @param int $insertId
	 * @return bool true:success,false:failed
	 */
	public function insert($datatype, &$insertId) {
		$sql = "INSERT INTO $this->table (name,module)";	
		$sql .= ' VALUES(' . Xoonips_Utils::convertSQLStr($datatype['name']) . ',' . Xoonips_Utils::convertSQLStr($datatype['module']) . ')';
		$result = $this->execute($sql);
		if (!$result) {
			return false;
		}
		$insertId = $this->getInsertId();
		return true;
	}

	/**
	 * insert view_data_relation
	 *
	 * @param array $relation
	 * @return bool true:success,false:failed
	 */
	public function insertRelation($relation) {
		$table = $this->prefix($this->modulePrefix('view_data_relation'));
		$sql = "INSERT INTO $table (view_type_id,data_type_id,data_length,data_decimal_places)";	
		$sql .= ' VALUES(' . Xoonips_Utils::convertSQLNum($relation['view_type_id']) . ',' . Xoonips_Utils::convertSQLNum($relation['data_type_id']);
		$sql .= ',' . Xoonips_Utils::convertSQLNum($relation['data_length']) . ',' . Xoonips_Utils::convertSQLNum($relation['data_decimal_places']) . ')';
		
		$result = $this->execute($sql);
		if (!$result) {
			return false;
		}
		return true;
	}
}

