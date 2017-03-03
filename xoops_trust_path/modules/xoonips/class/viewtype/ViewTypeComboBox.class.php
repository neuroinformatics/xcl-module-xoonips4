<?php

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/viewtype/ViewType.class.php';

class Xoonips_ViewTypeComboBox extends Xoonips_ViewType {

	public function hasSelectionList() {
		return true;
	}

	public function setTemplate() {
		$this->template = $this->dirname . '_viewtype_combobox.html';
	}

	public function getInputView($field, $value, $groupLoopId) {
		$fieldName = $this->getFieldName($field, $groupLoopId);
		$this->getXoopsTpl()->assign('viewType', 'input');
		$this->getXoopsTpl()->assign('list', $field->getList());
		$this->getXoopsTpl()->assign('fieldName', $fieldName);
		$this->getXoopsTpl()->assign('value', $value);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}

	public function getDisplayView($field, $value, $groupLoopId) {
		$ret = '';
		$list = $field->getList();
		if ($value !== '') {
			$ret = isset($list[$value]) ? $list[$value] : '';
		}
		$fieldName = $this->getFieldName($field, $groupLoopId);
		$this->getXoopsTpl()->assign('viewType', 'confirm');
		$this->getXoopsTpl()->assign('valueName', $ret);
		$this->getXoopsTpl()->assign('fieldName', $fieldName);
		$this->getXoopsTpl()->assign('value', $value);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}

	public function getDetailDisplayView($field, $value, $display) {
		$ret = '';
		$list = $field->getList();
		if ($value !== ''){
			$ret = isset($list[$value]) ? $list[$value] : '';
		}
		$this->getXoopsTpl()->assign('viewType', 'detail');
		$this->getXoopsTpl()->assign('valueName', $ret);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}

	public function getMetaInfo($field, $value) {
		$ret = '';
		$list = $field->getList();
		if ($value !== '') {
			$ret = isset($list[$value]) ? $list[$value] : '';
		}
		return $ret;
	}

	/**
	 *
	 * get list block view
	 *
	 * @param $value, $disabled
	 * @return string
	 */
	public function getListBlockView($value, $disabled = '') {
		$selectValues = $this->getItemtypeValueSet();
		$this->getXoopsTpl()->assign('viewType', 'list');
		$this->getXoopsTpl()->assign('selectValues', $selectValues);
		$this->getXoopsTpl()->assign('disabled', $disabled);
		$this->getXoopsTpl()->assign('value', $value);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}

	/**
	 *
	 * get default value block view
	 *
	 * @param $list, $value, $disabled
	 * @return string
	 */
	public function getDefaultValueBlockView($list, $value, $disabled = '') {
		$selectValues = $this->getItemtypeValueDetail($list);
		foreach ($selectValues as $selectValue) {
			if ($value == '') {
				$value = $selectValue['title_id'];
			}
			break;
		}
		$this->getXoopsTpl()->assign('viewType', 'default');
		$this->getXoopsTpl()->assign('selectValues', $selectValues);
		$this->getXoopsTpl()->assign('disabled', $disabled);
		$this->getXoopsTpl()->assign('value', $value);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}
}

