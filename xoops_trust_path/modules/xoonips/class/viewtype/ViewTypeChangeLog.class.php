<?php

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/core/BeanFactory.class.php';
require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/viewtype/ViewType.class.php';

class Xoonips_ViewTypeChangeLog extends Xoonips_ViewType {

	public function setTemplate() {
		$this->template = $this->dirname . '_viewtype_changelog.html';
	}

	public function getInputView($field, $value, $groupLoopId) {
	}

	public function getEditView($field, $value, $groupLoopId) {
		$changeLogInfos = array();
		if (!empty($value)) {
			$vas = explode(',', $value);
			foreach ($vas as $v) {
				$changeLogInfos[] = $this->getChangeLogInfo($v);
			}
		}
		$fieldName = $this->getFieldName($field, $groupLoopId);
		$this->getXoopsTpl()->assign('viewType', 'input');
		$this->getXoopsTpl()->assign('changeLogInfos', $changeLogInfos);
		$this->getXoopsTpl()->assign('fieldName', $fieldName);
		$this->getXoopsTpl()->assign('value', $value);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}

	public function getDisplayView($field, $value, $groupLoopId) {
		$changeLogInfos = array();
		if (!empty($value)) {
			$vas = explode(',', $value);
			foreach ($vas as $v) {
				$changeLogInfos[] = $this->getChangeLogInfo($v);
			}
		}
		$fieldName = $this->getFieldName($field, $groupLoopId);
		$this->getXoopsTpl()->assign('viewType', 'confirm');
		$this->getXoopsTpl()->assign('changeLogInfos', $changeLogInfos);
		$this->getXoopsTpl()->assign('fieldName', $fieldName);
		$this->getXoopsTpl()->assign('value', $value);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}

	public function getDetailDisplayView($field, $value, $display) {
		$changeLogInfos = array();
		if (!empty($value)) {
			$vas = explode(',', $value);
			foreach ($vas as $v) {
				$changeLogInfos[] = $this->getChangeLogInfo($v);
			}
		}
		$this->getXoopsTpl()->assign('viewType', 'detail');
		$this->getXoopsTpl()->assign('changeLogInfos', $changeLogInfos);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}
	
	public function getSearchView($field, $groupLoopId) {
		$fieldName = $this->getFieldName($field, $groupLoopId);
		$this->getXoopsTpl()->assign('viewType', 'search');
		$this->getXoopsTpl()->assign('fieldName', $fieldName);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}

	public function getMetaInfo($field, $value) {
		$ret='';
		$logs = array();
		if (!empty($value)) {
			$vas = explode(',', $value);
			foreach ($vas as $va) {
				$logs[]= $this->getMetaChangeLogInfo($va);
			}
		}
		return $ret.implode("\r\n", $logs);
	}

	public function isDisplay($op) {
		//hidden when regist form
		if ($op == Xoonips_Enum::OP_TYPE_REGISTRY) return false;
		return true;
	}
	
	private function getMetaChangeLogInfo($lid) {
		if ($lid == '') return '';
		$changeLogBean = Xoonips_BeanFactory::getBean('ItemChangeLogBean', $this->dirname, $this->trustDirname);
		$changeLogInfo = $changeLogBean->getChangeLogInfo($lid);
		if ($changeLogInfo) {
			$logDate = $this->formatDatetime($changeLogInfo['log_date']);
			$log = $changeLogInfo['log'];
			return "$logDate    $log";
		}
	}

	private function getChangeLogInfo($lid) {
		if ($lid=='') return '';
		$changeLogBean = Xoonips_BeanFactory::getBean('ItemChangeLogBean', $this->dirname, $this->trustDirname);
		$changeLogInfo = $changeLogBean->getChangeLogInfo($lid);
		if ($changeLogInfo) {
			$logDate = $this->formatDatetime($changeLogInfo['log_date']);
			$log = $changeLogInfo['log'];
			return array('logDate' => $logDate, 'log' => $log);
		}
	}

	private function formatDatetime($str) {
		$ret='';
		if (strlen($str) == 10) {
			$ret = date(XOONIPS_DATE_FORMAT, $str);
		}
		return $ret;
	}
	
	/**
	 *
	 * get default value block view
	 *
	 * @param $list, $value, $disabled
	 * @return string
	 */
	public function getDefaultValueBlockView($list, $value, $disabled='') {
		$this->getXoopsTpl()->assign('viewType', 'default');
		$this->getXoopsTpl()->assign('value', $value);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
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
	
	/**
	 *
	 * get entity data
	 *
	 * @param object $field
	 * 		   array $d
	 * @return mix
	 */
	public function getEntitydata($field, &$data) {
		$ret = array();
		$table = $field->getTableName();
		foreach ($data[$table] as $key => $value) {
			$ret[$key]['log_date'] = $this->formatDatetime($value['log_date']);
			$ret[$key]['log'] = $value['log'];
		}
		return $ret;
	}
}

