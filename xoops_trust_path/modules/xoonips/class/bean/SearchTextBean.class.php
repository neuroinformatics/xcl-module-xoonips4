<?php

require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/BeanBase.class.php';

/**
 * @brief operate xoonips_search_text table
 *
 */
class Xoonips_SearchTextBean extends Xoonips_BeanBase {

	/**
	 * Constructor
	 **/
	public function __construct($dirname, $trustDirname) {
		parent::__construct($dirname, $trustDirname);
		$this->setTableName('search_text', true);
	}

	/**
	 * delete search text
	 *
	 * @param int $fileId file id
	 * @return bool true:success,false:failed
	 */
	public function delete($fileId) {
		$sql = "DELETE FROM $this->table WHERE file_id=$fileId";
		$result = $this->execute($sql);
		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * insert search text
	 *
	 * @param $info
	 * @return bool true:success,false:failed
	 */
	public function insert($tmpfile) {
		$sql = "LOAD DATA INFILE '$tmpfile' INTO TABLE " . $this->table . ' ( file_id, search_text )';
		$result = $this->execute($sql);
		if ($result === false) {
			$sql = "LOAD DATA LOCAL INFILE '$tmpfile' INTO TABLE " . $this->table . '( file_id, search_text )';
			$result = $this->execute($sql);
		}
		return true;
	}
}

