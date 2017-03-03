<?php

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/datatype/DataType.class.php';

class Xoonips_DataTypeDatetime extends Xoonips_DataType {

	public function getSql($id, $len) {
	}

	public function inputCheck(&$errors, $field, $value, $fieldName) {
		$char = "([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})";
		if (is_array($value)) {
			$value[0] = trim($value[0]);
			$value[1] = trim($value[1]);
			if ($value[0] !== '') {
				$dateArray = explode('-', $value[0]);
				$yearValue = $dateArray[0];
				$monthValue = $dateArray[1];
				$dayValue = $dateArray[2];
				if (!checkdate($monthValue, $dayValue, $yearValue)) {
					$parameters = array();
					$parameters[] = $field->getName() . '[from]';
					$errors->addError('_MD_' . strtoupper($this->trustDirname) . '_ERROR_DATE', $fieldName, $parameters);
				}
			}
			if ($value[1] !== '') {
				$dateArray = explode('-', $value[0]);
				$yearValue = $dateArray[0];
				$monthValue = $dateArray[1];
				$dayValue = $dateArray[2];
				if (!checkdate($monthValue, $dayValue, $yearValue)) {
					$parameters = array();
					$parameters[] = $field->getName() . '[to]';
					$errors->addError('_MD_' . strtoupper($this->trustDirname) . '_ERROR_DATE', $fieldName, $parameters);
				}
			}
		} else {
			$value = trim($value);
			if ($value !== '') {
				$dateArray = explode('-', $value);
				$yearValue = $dateArray[0];
				$monthValue = $dateArray[1];
				$dayValue = $dateArray[2];
				if (!checkdate($monthValue, $dayValue, $yearValue)) {
					$parameters = array();
					$parameters[] = $field->getName();
					$errors->addError('_MD_' . strtoupper($this->trustDirname) . '_ERROR_DATE', $fieldName, $parameters);
				}
			}
			if ($field->getLen() > 0 && strlen($value) > $field->getLen()) {
				$parameters = array();
				$parameters[] = $field->getName();
				$parameters[] = $field->getLen();
				$errors->addError('_MD_' . strtoupper($this->trustDirname) . '_ERROR_MAXLENGTH', $fieldName, $parameters);
			}
		}
	}

	public function getValueSql($field) {
		$value = array();
		$essential = ($field->getEssential() == 1) ? 'NOT NULL' : '';
		$value[0] = ' timestamp ' . $essential;
		$value[1] = '';
		return $value;
	}

	public function valueAttrCheck($field, &$errors) {
		return true;
	}
}

