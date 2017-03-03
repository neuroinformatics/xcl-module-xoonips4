<?php

/**
 * item sort object
 */
class Xoonips_ItemSortObject extends XoopsSimpleObject {

	/**
	 * constructor
	 */
	public function __construct() {
		$this->initVar('sort_id', XOBJ_DTYPE_INT, 0, true);
		$this->initVar('title', XOBJ_DTYPE_STRING, '', true, 255);
	}

}

/**
 * item sort object handler
 */
class Xoonips_ItemSortHandler extends XoopsObjectGenericHandler {

	/**
	 * table
	 * @var string
	 */
	public $mTable = '{dirname}_item_type_sort';

	/**
	 * detail table
	 * @var string
	 */
	public $mTableDetail = '{dirname}_item_type_sort_detail';

	/**
	 * primary id
	 * @var string
	 */
	public $mPrimary = 'sort_id';

	/**
	 * object class name
	 * @var string
	 */
	public $mClass = '';

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
		$this->mClass = preg_replace('/Handler$/', 'Object', get_class());
		parent::__construct($db);
		$this->mTableDetail = strtr($this->mTableDetail, array('{dirname}' => $dirname));
		$this->mTableDetail = $this->db->prefix($this->mTableDetail);
	}

	/**
	 * delete object
	 *
	 * @param XoopsObject &$obj
	 * @param bool $force
	 * @return bool
	 */
	public function delete(&$obj, $force = false) {
		$pid = $obj->get($this->mPrimary);
		if ($pid == 1)
			return false; // id = 1 is not deletable
		if (!parent::delete($obj, $force))
			return false;
		if (!$this->_deleteSortField($pid, false, false, false, $force))
			return false;
		return true;
	}

	/**
	 * delete plural objects
	 *
	 * @param Criteria $criteria
	 * @param bool $force
	 * @return bool
	 */
	public function deleteAll($criteria, $force = false) {
		// always failure
		return false;
	}

	/**
	 * get sort titles
	 *
	 * @return string[]
	 */
	public function getSortTitles() {
		static $cache = null;
		if ($cache != null)
			return $cache;
		$criteria = new CriteriaElement();
		$criteria->setSort('sort_id');
		$criteria->setOrder('ASC');
		$objs =& $this->getObjects($criteria);
		$ret = array();
		foreach ($objs as $obj)
			$ret[$obj->get('sort_id')] = $obj->get('title');
		$cache = $ret;
		return $cache;
	}

	/**
	 * get sort fields
	 *
	 * @param {Trustdirname}_ItemSortObject &$obj
	 * @return string[]
	 */
	public function getSortFields(&$obj) {
		$ret = array();
		$sort_id = $obj->get('sort_id');
		$sql = sprintf('SELECT * FROM `%s` WHERE `sort_id`=%u', $this->mTableDetail, $sort_id);
		if (!($result = $this->db->query($sql)))
			return $ret;
		while ($row = $this->db->fetchArray($result)) {
			$ret[] = $this->encodeSortField($row['item_type_id'], 0, $row['item_field_detail_id']); // FIXME: use field group id
		}
		return $ret;
	}

	/**
	 * update sort fields
	 *
	 * @param {Trustdirname}_ItemSortObject &$obj
	 * @param string[] $newFields
	 * @param bool $force
	 * @return bool
	 */
	public function updateSortFields(&$obj, $newFields, $force = false) {
		$ret = true;
		$pid = $obj->get($this->mPrimary);
		$curFields = $this->getSortFields($obj);
		$addFields = array_diff($newFields, $curFields);
		$delFields = array_diff($curFields, $newFields);
		foreach ($delFields as $field) {
			list($tId, $gId, $fId) = $this->decodeSortField($field);
			$ret &= $this->_deleteSortField($pid, $tId, $gId, $fId, $force);
		}
		foreach ($addFields as $field) {
			list($tId, $gId, $fId) = $this->decodeSortField($field);
			$ret &= $this->_insertSortField($pid, $tId, $gId, $fId, $force);
		}
		return $ret;
	}

	/**
	 * delete sort fields by item type id
	 *
	 * @param int $tId
	 * @param bool $force
	 * @return bool
	 */
	public function deleteSortFieldsByItemTypeId($tId, $force = false) {
		return $this->_deleteSortField(false, $tId, false, false, $force);
	}

	/**
	 * encode sort field
	 *
	 * @param int $tId
	 * @param int $gId
	 * @param int $fId
	 * @return string
	 */
	public function encodeSortField($tId, $gId, $fId) {
		return sprintf('%u:%u:%u', $tId, $gId, $fId);
	}

	/**
	 * decode sort field
	 *
	 * @param string $field
	 * @return int[]
	 */
	public function decodeSortField($field) {
		return explode(':', $field);
	}

	/**
	 * get all selectable sort fields
	 *
	 * @return array
	 */
	public function getSelectableSortFields() {
		$ret = array();
		$itTable = $this->db->prefix($this->mDirname . '_item_type');
		$igTable = $this->db->prefix($this->mDirname . '_item_field_group');
		$ifTable = $this->db->prefix($this->mDirname . '_item_field_detail');
		$it_ig_linkTable = $this->db->prefix($this->mDirname . '_item_type_field_group_link');
		$ig_if_linkTable = $this->db->prefix($this->mDirname . '_item_field_group_field_detail_link');
		$sql = sprintf('SELECT `it`.`item_type_id`, `it`.`name` AS `item_type_name`, `ig`.`group_id` AS `item_field_group_id`, `ig`.`name` AS `item_field_group_name`, `if`.`item_field_detail_id` AS `item_field_id`, `if`.`name` AS `item_field_name` FROM `%s` AS `it`', $itTable);
		$sql .= sprintf(' INNER JOIN `%s` AS `it_ig_link` ON `it_ig_link`.`item_type_id`=`it`.`item_type_id`', $it_ig_linkTable);
		$sql .= sprintf(' INNER JOIN `%s` AS `ig` ON `ig`.`group_id`=`it_ig_link`.`group_id`', $igTable);
		$sql .= sprintf(' INNER JOIN `%s` AS `ig_if_link` ON `ig_if_link`.`group_id`=`ig`.`group_id`', $ig_if_linkTable);
		$sql .= sprintf(' INNER JOIN `%s` AS `if` ON `if`.`item_field_detail_id`=`ig_if_link`.`item_field_detail_id`', $ifTable);
		$sql .= sprintf(' WHERE `it`.`released` = 1 ORDER BY `it`.`weight`, `it`.`item_type_id`, `ig`.`weight`, `ig`.`group_id`, `if`.`weight`, `if`.`item_field_detail_id`');
		if (!($res = $this->db->query($sql)))
			return $ret;
		while ($row = $this->db->fetchArray($res)) {
			$tId = $row['item_type_id'];
			$tName = $row['item_type_name'];
			$gId = $row['item_field_group_id'];
			$gName = $row['item_field_group_name'];
			$fId = $row['item_field_id'];
			$fName = $row['item_field_name'];
			if (!isset($ret[$tId])) {
				$ret[$tId] = array(
					'item_type_id' => $tId,
					'item_type_name' => $tName,
					'fields' => array(),
				);
			}
			$ret[$tId]['fields'][] = array(
				'item_field_group_id' => $gId,
				'item_field_group_name' => $gName,
				'item_field_id' => $fId,
				'item_field_name' => $fName,
				'key' => $this->encodeSortField($tId, 0, $fId), // FIXME: use $gId
			);
		}
		$this->db->freeRecordSet($res);
		return $ret;
	}

	/**
	 * get export data for item type
	 *
	 * @param int $tId
	 * @return array
	 */
	public function getExportDataForItemType($tId) {
		$ret = array();
		$fields = array();
		$itTable = $this->db->prefix($this->mDirname . '_item_type');
		$igTable = $this->db->prefix($this->mDirname . '_item_field_group');
		$ifTable = $this->db->prefix($this->mDirname . '_item_field_detail');
		$it_ig_linkTable = $this->db->prefix($this->mDirname . '_item_type_field_group_link');
		$ig_if_linkTable = $this->db->prefix($this->mDirname . '_item_field_group_field_detail_link');
		// get list of sort title and selected item field id by item type id
		$sql = sprintf('SELECT `ig`.`group_id` AS `item_field_group_id`, `ig`.`xml` AS `item_field_group_xml`, `if`.`item_field_detail_id` AS `item_field_id`, `if`.`xml` AS `item_field_xml` FROM `%s` AS `it`', $itTable);
		$sql .= sprintf(' INNER JOIN `%s` AS `it_ig_link` ON `it_ig_link`.`item_type_id`=`it`.`item_type_id`', $it_ig_linkTable);
		$sql .= sprintf(' INNER JOIN `%s` AS `ig` ON `ig`.`group_id`=`it_ig_link`.`group_id`', $igTable);
		$sql .= sprintf(' INNER JOIN `%s` AS `ig_if_link` ON `ig_if_link`.`group_id`=`ig`.`group_id`', $ig_if_linkTable);
		$sql .= sprintf(' INNER JOIN `%s` AS `if` ON `if`.`item_field_detail_id`=`ig_if_link`.`item_field_detail_id`', $ifTable);
		$sql .= sprintf(' WHERE `it`.`item_type_id`=%u', $tId);
		if (!($res = $this->db->query($sql)))
			return $ret;
		while ($row = $this->db->fetchArray($res)) {
			$gId = $row['item_field_group_id'];
			$gXml = $row['item_field_group_xml'];
			$fId = $row['item_field_id'];
			$fXml = $row['item_field_xml'];
			$key = $this->encodeSortField($tId, 0, $fId); // FIXME: use $gId
			$fields[$key] = sprintf('%s:%s', $gXml, $fXml);
		}
		$this->db->freeRecordSet($res);
		// get list of sort title and selected item field id by item type id
		$sql = sprintf('SELECT `s`.`title`, `sd`.`item_field_detail_id` FROM `%s` AS `s`', $this->mTable);
		$sql .= sprintf(' INNER JOIN `%s` AS `sd` ON `sd`.`sort_id`=`s`.`sort_id`', $this->mTableDetail);
		$sql .= sprintf(' WHERE `sd`.`item_type_id`=%u', $tId);
		if (!($res = $this->db->query($sql)))
			return $ret;
		while ($row = $this->db->fetchArray($res)) {
			$title = $row['title'];
			$gId = 0; // FIXME: field group id is missing in the sort detail table, is this ok?
			$fId = $row['item_field_detail_id'];
			$key = $this->encodeSortField($tId, $gId, $fId);
			if (!array_key_exists($key, $fields))
				continue;
			$ret[] = array(
				'sort_id' => $title,
				'item_field_detail_id' => $fields[$key],
			);
		}
		$this->db->freeRecordSet($res);
		return $ret;
	}

	/**
	 * check whether item sort condtion has an item field
	 *
	 * @param int $fId
	 * @return bool
	 */
	public function hasSortField($fId) {
		$sql = sprintf('SELECT COUNT(*) `c` FROM `%s` WHERE `item_field_detail_id`=%u', $this->mTableDetail, $fId);
		$result = $this->db->query($sql);
                if (!$result)
                        return false;
                $ret = $this->db->fetchArray($result);
                return ($ret['c'] > 0);
	}

	/**
	 * insert item sort detail records
	 *
	 * @param int $sId
	 * @param int $tId
	 * @param int $gId
	 * @param int $fId
	 * @param bool $force
	 * @return bool
	 */
	private function _insertSortField($sId, $tId, $gId, $fId, $force = false) {
		// FIXME: field group id is missing in the sort detail table, is this ok?
		$sql = sprintf('INSERT INTO `%s` (`sort_id`, `item_type_id`, `item_field_detail_id`) VALUE (%u, %u, %u)', $this->mTableDetail, $sId, $tId, $fId);
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
	 * delete item sort detail records
	 *
	 * @param int $sId
	 * @param int $tId
	 * @param int $gId
	 * @param int $fId
	 * @param bool $force
	 * @return bool
	 */
	private function _deleteSortField($sId = false, $tId = false, $gId = false, $fId = false, $force = false) {
		// FIXME: field group id is missing in the sort detail table, is this ok?
		$sql = sprintf('DELETE FROM `%s`', $this->mTableDetail);
		$where = array();
		if ($sId !== false)
			$where[] = sprintf('`sort_id`=%u', $sId);
		if ($tId !== false)
			$where[] = sprintf('`item_type_id`=%u', $tId);
		if ($fId !== false)
			$where[] = sprintf('`item_field_detail_id`=%u', $fId);
		if (!empty($where))
			$sql .= ' WHERE ' . implode(' AND ', $where);
		if ($force) {
			if (!$this->db->queryF($sql))
				return false;
		} else {
			if (!$this->db->query($sql))
				return false;
		}
		return true;
	}

}

