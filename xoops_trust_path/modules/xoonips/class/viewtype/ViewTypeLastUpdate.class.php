<?php

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/viewtype/ViewTypeDate.class.php';

class Xoonips_ViewTypeLastUpdate extends Xoonips_ViewTypeDate {

	public function setTemplate() {
		$this->template = $this->dirname . '_viewtype_lastupdate.html';
	}

	public function getInputView($field, $value, $groupLoopId) {
	}

	public function getEditView($field, $value, $groupLoopId) {
		$fieldName = $this->getFieldName($field, $groupLoopId);
		$this->getXoopsTpl()->assign('viewType', 'edit');
		$this->getXoopsTpl()->assign('fieldName', $fieldName);
		$this->getXoopsTpl()->assign('value', $value + $this->getTimeZoneOffset());
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}

	public function getDisplayView($field, $value, $groupLoopId) {
		return $this->getEditView($field, $value, $groupLoopId);
	}

	public function getDetailDisplayView($field, $value, $display) {
		$this->getXoopsTpl()->assign('viewType', 'detail');
		$this->getXoopsTpl()->assign('value', $value + $this->getTimeZoneOffset());
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}

	public function getMetaInfo($field, $value) {
		return $this->formatDatetime($value);
	}

	public function isDisplay($op) {
		//hidden when regist form
		if ($op == Xoonips_Enum::OP_TYPE_REGISTRY) return false;
		return true;
	}

	private function formatDatetime($str) {
		$ret = '';
		if (strlen($str) == 10) {
			$ret = date(XOONIPS_DATETIME_FORMAT, $str);
		}
		return $ret;
	}

	public function doSearch($field, &$data, &$sqlStrings, $groupLoopId, $scopeSearchFlg, $isExact) {
		$tableName = $field->getTableName();
		$columnName = $field->getColumnName();
		$value = $data[$this->getFieldName($field, $groupLoopId)];

		if (isset($sqlStrings[$tableName])) {
			$tableData = &$sqlStrings[$tableName];
		} else {
			$tableData = array();
			$sqlStrings[$tableName] = &$tableData;
		}
		if ($value != '') {
			if ($field->getScopeSearch() == 1 && $scopeSearchFlg) {
				$value[0] = trim($value[0]);
				$value[1] = trim($value[1]);
				if ($value[0] != '') {
					$value[0] = $this->getTimes($value[0]);
					$v = $field->getDataType()->convertSQLStr($value[0]);
					$tableData[] = "$columnName>=$v";
				}
				if ($value[1] != '') {
					$value[1] = $this->getTimes($value[1]);
					$v = $field->getDataType()->convertSQLStr($value[1]);
					$tableData[] = "$columnName<=$v";
				}
			} else {
				$value = $field->getDataType()->convertSQLStrLike(trim($value));
				$value2 = $this->getTimes($value);
				if ($value2 != $value) {
					$tableData[] = "$columnName >= $value2 AND $columnName < $value2 + 86400";
				} else {
					$tableData[] = "$columnName like '%$value%'";
				}

			}
		}
	}

	private function getTimes($value){
		$char = "/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/";
		if (!preg_match($char, $value)) {
			return $value;
		}
		$valueArr = array();
		$valueArr = explode('-', $value);
		return mktime(0, 0, 0, $valueArr[1], $valueArr[2], $valueArr[0]);
	}

	public function getDefaultValueBlockView($list, $value, $disabled = '') {
		$this->getXoopsTpl()->assign('viewType', 'default');
		$this->getXoopsTpl()->assign('value', $value);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}

	public function mustCreateItemExtendTable() {
		return false;
	}

	public function getMetadata($field, &$data) {
		$table = $field->getTableName();
		$column = $field->getColumnName();
		$date = $data[$table][$column];
		if (empty($date)) {
			return '';
		}
		return $date;
	}

	public function editCheck(&$errors, $field, $value, $fieldName, $uid) {
	}

	public function isDate() {
		return false;
	}
}

