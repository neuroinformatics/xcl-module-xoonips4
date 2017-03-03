<?php

class Xoonips_BeanBase {
	
	protected $table = null;
	protected $db = null;
	protected $dirname = null;
	protected $trustDirname = null;

	/**
	 * Constructor
	 **/
	public function __construct($dirname, $trustDirname) {
		global $xoopsDB;
		$this->db = &$xoopsDB;
	    $this->dirname = strtolower($dirname);
    	$this->trustDirname = $trustDirname;
	}
	
	protected function setTableName($name, $d3 = false) {
		if ($d3) {
			$this->table = $this->db->prefix($this->modulePrefix($name));
		} else {
			$this->table = $this->db->prefix($name);
		}
	}

	/**
	 * attach the module dirname.'_' to a given tablename
	 * 
     * @param string $name
     * @return string
	 */
	protected function modulePrefix($name) {
		return $this->dirname . '_' . $name;
	}

	protected function &execute($sql, $limit=0, $start=0) {
		$result = &$this->db->queryF($sql, $limit, $start);
		return $result;
	}
	
	/**
	 * Fetch a result row as an associative array
	 * 
     * @param resource $result
     * @return array
	 */
	protected function fetchArray($result) {
		return $this->db->fetchArray($result);
	}
	
	protected function freeRecordSet($result) {
		$this->db->freeRecordSet($result);
	}
	
	/**
	 * Get the ID generated from the previous INSERT operation
	 * 
     * @return int
	 */
	protected function getInsertId() {
		return $this->db->getInsertId();
	}
	
	/**
	 * Get number of rows in result
	 * 
     * @param resource query result
     * @return int
	 */
	protected function getRowsNum($result) {
		return $this->db->getRowsNum($result);
	}
	
	/**
	 * attach the prefix.'_' to a given tablename
     * 
     * if tablename is empty, only prefix will be returned
	 * 
     * @param string $tablename tablename
     * @return string prefixed tablename, just prefix if tablename is empty
	 */
	protected function prefix($tablename) {
		return $this->db->prefix($tablename);
	}
	
	/**
	 * Get a result row as an enumerated array
	 * 
     * @param resource $result
     * @return array
	 */
	protected function fetchRow($result) {
		return $this->db->fetchRow($result);
	}
}

