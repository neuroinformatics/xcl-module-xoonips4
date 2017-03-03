<?php

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/viewtype/ViewType.class.php';

class Xoonips_ViewTypeHidden extends Xoonips_ViewType {

	public function setTemplate() {
		$this->template = $this->dirname . '_viewtype_hidden.html';
	}

	public function getInputView($field, $value, $groupLoopId) {
		$fieldName = $this->getFieldName($field, $groupLoopId);
		$this->getXoopsTpl()->assign('viewType', 'input');
		$this->getXoopsTpl()->assign('len', $field->getLen());
		$this->getXoopsTpl()->assign('fieldName', $fieldName);
		$this->getXoopsTpl()->assign('value', $value);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}
	
	public function getDisplayView($field, $value, $groupLoopId) {
		$fieldName = $this->getFieldName($field, $groupLoopId);
   		$this->getXoopsTpl()->assign('viewType', 'confirm');
   		$this->getXoopsTpl()->assign('fieldName', $fieldName);
		$this->getXoopsTpl()->assign('value', $value);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}

	public function getDetailDisplayView($field, $value, $display) {
   		$this->getXoopsTpl()->assign('viewType', 'detail');
		$this->getXoopsTpl()->assign('value', $value);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}
}

