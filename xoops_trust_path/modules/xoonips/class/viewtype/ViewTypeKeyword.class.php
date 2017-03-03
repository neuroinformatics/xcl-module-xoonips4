<?php

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/viewtype/ViewTypeText.class.php';

class Xoonips_ViewTypeKeyword extends Xoonips_ViewTypeText {

	public function doRegistry($field, &$data, &$sqlStrings, $groupLoopId) {
		$tableName = $field->getTableName();
		$columnName = $field->getColumnName();
		$value = $this->getData($field, $data, $groupLoopId);

		$tableData;
		$columnData;

		if (isset($sqlStrings[$tableName])) {
			$tableData = &$sqlStrings[$tableName];
		} else {
			$tableData = array();
			$sqlStrings[$tableName] = &$tableData;
		}

		if (isset($tableData[$columnName])) {
			$columnData = &$tableData[$columnName];
		} else {
			$columnData = array();
			$tableData[$columnName] = &$columnData;
		}

		$vas = explode(',', $value);
		foreach ($vas as $va) {
			$columnData[] = trim($va);
		}
	}
	
	public function getMetadata($field, &$data) {
		$table = $field->getTableName();
		$column = $field->getColumnName();
		$keywords = array();
		foreach ($data[$table] as $value) {
			$keywords[] = $value[$column];
		}
		return implode(',', $keywords);
	}
	
	/**
	 *
	 * must Create item_extend table
	 *
	 * @param
	 * @return boolean
	 */
	public function mustCreateItemExtendTable() {
		return false;
	}
}

