<?php

/**
 * item field value set handler
 */
class Xoonips_ItemFieldValueSetHandler extends XoopsObjectHandler {

	/**
	 * table
	 * @var string
	 */
	public $mTable = '{dirname}_item_field_value_set';

	/**
	 * dirname
	 * @var string
	 */
	public $mDirname = '';

	/**
	 * constructor
	 *
	 * @param XoopsDatabase &$db
	 * @param string $dirname
	 */
	public function __construct(&$db, $dirname) {
		$this->mTable = strtr($this->mTable, array('{dirname}' => $dirname));
		$this->mDirname = $dirname;
		$this->mTable = $db->prefix($this->mTable);
		parent::__construct($db);
	}

	/**
	 * get select names
	 *
	 * @param string $name
	 * @return string[]
	 */
	public function getSelectNames() {
		$ret = array();
		$sql = sprintf('SELECT DISTINCT `select_name` FROM `%s`', $this->mTable);
		if (!($result = $this->db->query($sql)))
			return $ret;
		while ($row = $this->db->fetchArray($result)) {
			$name = $row['select_name'];
			$ret[] = $name;
		}
		$this->db->freeRecordSet($result);
		return $ret;
	}

	/**
	 * get item field value set
	 *
	 * @param string $name
	 * @return {string 'title_id', string 'title', int 'weight'}[]
	 */
	public function getValueSet($name) {
		$ret = array();
		$_name = $this->db->quoteString($name);
		$sql = sprintf('SELECT `title_id`, `title`, `weight` FROM `%s` WHERE `select_name`=%s ORDER BY `weight` ASC', $this->mTable, $_name);
		if (!($result = $this->db->query($sql)))
			return $ret;
		while ($row = $this->db->fetchArray($result))
			$ret[] = $row;
		$this->db->freeRecordSet($result);
		return $ret;
	}

	/**
	 * get item field value set
	 *
	 * @param string $name
	 * @param {string 'title_id', string 'title', int 'weight'}[]
	 * @param bool $force
	 * @return bool
	 */
	public function setValueSet($name, $values, $force = false) {
		if (!$this->_validate($name, $values))
			return false;
		if (!$this->_deleteByNameBody($name, $force))
			return false;
		usort($values, array(get_class(), '_weightCompare'));
		$_name = $this->db->quoteString($name);
		$_weight = 0;
		foreach ($values as $value) {
			$_title_id = $this->db->quoteString($value['title_id']);
			$_title = $this->db->quoteString($value['title']);
			$_weight++;
			$sql = sprintf('INSERT INTO `%s` (`select_name`, `title_id`, `title`, `weight`) VALUES (%s, %s, %s, %u)', $this->mTable, $_name, $_title_id, $_title, $_weight);
			if ($force) {
				if (!$this->db->queryF($sql))
					return false;
			} else {
				if (!$this->db->query($sql))
					return false;
			}
		}
		return true;
	}

	/**
	 * delete by name
	 *
	 * @param string $name
	 * @param bool $force
	 * @return bool
	 */
	public function deleteByName($name, $force = false) {
		$handler = Xoonips_Utils::getModuleHandler('ItemField', $this->mDirname);
		$names = $handler->getUsedSelectNames();
		if (in_array($name, $names))
			return false;
		return $this->_deleteByNameBody($name, $force);
	}

	/**
	 * delete by name (body)
	 *
	 * @param string $name
	 * @param bool $force
	 * @return bool
	 */
	public function _deleteByNameBody($name, $force) {
		$_name = $this->db->quoteString($name);
		$sql = sprintf ('DELETE FROM `%s` WHERE `select_name`=%s', $this->mTable, $_name);
		if ($force) {
			if (!$this->db->queryF($sql))
				return false;
		} else {
			if (!$this->db->query($sql))
				return false;
		}
		return true;
	}

	/**
	 * validate
	 *
	 * @param string $name
	 * @param array $values
	 * @return bool
	 */
	private function _validate($name, $values) {
		if (!is_array($values) && empty($values))
			return false; // invalid value type
		$ids = array();
		foreach ($values as $value) {
			if (!isset($value['title_id']) || !isset($value['title']) || !isset($value['weight']))
				return false; // required keys not found
			if (in_array($value['title_id'], $ids))
				return false; // duplicated id
			$ids[] = $value['title_id'];
		}
		$handler = Xoonips_Utils::getModuleHandler('ItemField', $this->mDirname);
		$names = $handler->getUsedSelectNames();
		if (in_array($name, $names)) {
			$curValues = $this->getValueSet($name);
			foreach ($curValues as $value) {
				if (!in_array($value['title_id'], $ids))
					return false; // required title_id not found
			}
		}
		return true;
	}

	/**
	 * weight compare
	 *
	 * @param array $a
	 * @param array $b
	 * @return bool
	 */
	private static function _weightCompare($a, $b) {
		return $a['weight'] > $b['weight'];
	}

}

