<?php

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/datatype/DataType.class.php';

class Xoonips_DataTypeChar extends Xoonips_DataType {

	public function DataTypeChar() {
	}

	public function getSql($id, $len) {
		echo "create table tbl_$id length=$len";
	}

	public function inputCheck(&$errors, $field, $value, $fieldName) {
		$ret = true;
		if (is_array($value)) {
			return true;
		} elseif ($field->getLen() > 0 && strlen(trim($value)) > $field->getLen()) {
			$parameters = array();
			$parameters[] = $field->getName();
			$parameters[] = $field->getLen();
			$errors->addError('_MD_' . strtoupper($this->trustDirname) . '_ERROR_MAXLENGTH', $fieldName, $parameters);
			$ret = false;
		}
		return $ret;
	}

	public function isLikeSearch() {
		return true;
	}

	public function getValueSql($field) {
		$value = array();
		$length = $field->getLen();
		$essential = ($field->getEssential()==1) ? 'NOT NULL' : '';
		$defaultValue = $field->getDefault();
		$default = ' default NULL';
		if ($defaultValue != '') {
			$default = " default '$defaultValue'";
		} else {
			if ($field->getEssential() == 1) {
				$default = '';
			}
		}
		$value[0] = " char($length) " . $essential . $default;
		$value[1] = '';
		return $value;
	}

	public function valueAttrCheck($field, &$errors) {
		$parameters = array();
		$parameters[] = constant('_AM_' . strtoupper($this->trustDirname) . '_LABEL_ITEMTYPE_DATA_LENGTH');
		if ($field->getLen() == '') {
			$errors->addError('_MD_' . strtoupper($this->trustDirname) . '_ERROR_REQUIRED', '', $parameters);
		} elseif ($field->getLen() == 0) {
			$errors->addError('_MD_' . strtoupper($this->trustDirname) . '_CHECK_INPUT_ERROR_MSG', '', $parameters);
		} else {
			if ($field->getDefault() != '' && strlen($field->getDefault()) > $field->getLen()) {
				$parameters = array();
				$parameters[] = constant('_AM_' . strtoupper($this->trustDirname) . '_LABEL_ITEMTYPE_DEFAULT_VALUE');
				$errors->addError('_MD_' . strtoupper($this->trustDirname) . '_CHECK_INPUT_ERROR_MSG', '', $parameters);
			}
		}
	}
}

