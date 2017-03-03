<?php

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/core/BeanFactory.class.php';
require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/core/File.class.php';
require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/core/Request.class.php';
require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/viewtype/ViewType.class.php';

class Xoonips_ViewTypePreview extends Xoonips_ViewType {

	public function setTemplate() {
		$this->template = $this->dirname . '_viewtype_preview.html';
	}

	public function getInputView($field, $value, $groupLoopId) {
		$fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
		$fieldName = $this->getFieldName($field, $groupLoopId);
		$fileName = $fieldName . '_file';
		if ($this->isLayered()) {
			$fileId = 'none';
		} else {
			$file = new Xoonips_File($this->dirname, $this->trustDirname);
			$fileId = $file->uploadFile($fileName, 'preview', 0, $field->getId());
		}
		if (!empty($value)) {
			if ($fileId == 'none') {
				$fileId = $value;
			} else {
				$fileBean->delete($value);
			}
		}
		$preview = !empty($fileId) ? $fileBean->getFile($fileId) : false;
		$this->getXoopsTpl()->assign('preview', $preview);
   		$this->getXoopsTpl()->assign('viewType', 'input');
		$this->getXoopsTpl()->assign('fieldName', $fieldName);
		$this->getXoopsTpl()->assign('fileName', $fileName);
		$this->getXoopsTpl()->assign('value', $value);
		$this->getXoopsTpl()->assign('fileId', $fileId);
		$this->getXoopsTpl()->assign('dirname', $this->dirname);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}

	public function fileUpload($field, $value, $groupLoopId) {
		$fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
		$fieldName = $this->getFieldName($field, $groupLoopId);
		$fileName = $fieldName . '_file';
		if (empty($value)) {
			$file = new Xoonips_File($this->dirname, $this->trustDirname);
			$fileId = $file->uploadFile($fileName, 'preview', 0, $field->getId(), $field->getFieldGroupId());
		} else {
			$fileId = $value;
		}
		$preview = !empty($fileId) ? $fileBean->getFile($fileId) : false;
		$this->getXoopsTpl()->assign('preview', $preview);
		$this->getXoopsTpl()->assign('viewType', 'fileUpload');
		$this->getXoopsTpl()->assign('fieldName', $fieldName);
		$this->getXoopsTpl()->assign('value', $value);
		$this->getXoopsTpl()->assign('fileId', $fileId);
		$this->getXoopsTpl()->assign('dirname', $this->dirname);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}

	public function getDisplayView($field, $value, $groupLoopId) {
		$fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
		$fieldName = $this->getFieldName($field, $groupLoopId);
		$fileName = $fieldName . '_file';
		if ($this->isLayered()) {
			$fileId = 'none';
		} else {
			$file = new Xoonips_File($this->dirname, $this->trustDirname);
			$fileId = $file->uploadFile($fileName, 'preview', 0, $field->getId());
		}
		if (!empty($value)) {
			if ($fileId == 'none') {
				$fileId = $value;
			} else {
				$fileBean->delete($value);
			}
		}
		$preview = !empty($fileId) ? $fileBean->getFile($fileId) : false;
		$this->getXoopsTpl()->assign('preview', $preview);
		$this->getXoopsTpl()->assign('viewType', 'confirm');
		$this->getXoopsTpl()->assign('fieldName', $fieldName);
		$this->getXoopsTpl()->assign('value', $value);
		$this->getXoopsTpl()->assign('fileId', $fileId);
		$this->getXoopsTpl()->assign('dirname', $this->dirname);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}

	public function getEditView($field, $value, $groupLoopId) {
		return $this->getInputView($field, $value, $groupLoopId);
	}

	public function getDetailDisplayView($field, $value, $display) {
		$fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
		$fileId = $value;
		$preview = !empty($fileId) ? $fileBean->getFile($fileId) : false;
		$this->getXoopsTpl()->assign('preview', $preview);
		$this->getXoopsTpl()->assign('viewType', 'detail');
		$this->getXoopsTpl()->assign('fileId', $fileId);
		$this->getXoopsTpl()->assign('dirname', $this->dirname);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}
	
	public function getSearchView($field, $groupLoopId) {
		$fieldName = $this->getFieldName($field, $groupLoopId);
		$this->getXoopsTpl()->assign('viewType', 'search');
		$this->getXoopsTpl()->assign('fieldName', $fieldName);
		return $this->getXoopsTpl()->fetch('db:'. $this->template);
	}
	
	public function mustCheck(&$errors, $field, $value, $fieldName) {
		if ($field->getEssential() == 1 && $value == '') {
			$fileName = $fieldName . '_file';
			$request = new Xoonips_Request();
			$file = $request->getFile($fileName);
			if (empty($file)) {
				$parameters = array();
				$parameters[] = $field->getName();
				$errors->addError('_MD_XOONIPS_ERROR_REQUIRED', $fieldName, $parameters);
			}
		}
	}
	
	public function inputCheck(&$errors, $field, $value, $fieldName) {
		$request = new Xoonips_Request();
		$req_file = $request->getParameter($fieldName);
		if (!$req_file) return true;

		$file_ids = array();
		if (is_array($req_file)) $file_ids = $req_file;
		else $file_ids[] = $req_file;
		
		// storage limit check
		global $xoopsUser;
		$uid = $xoopsUser->getVar('uid');
		$itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
		$filesizes = $itemBean->getFilesizePrivate($uid);
		foreach ($file_ids as $file_id) {
			$filesizes = $filesizes + $itemBean->getFilesizePrivateByFileId($file_id);
		}
		$privateItemLimit = $itemBean->getPrivateItemLimit($uid);
		if ($filesizes > $privateItemLimit['itemStorage'] && $privateItemLimit['itemStorage'] > 0) {
			$parameters = array();
			$parameters[] = '';
			$errors->addError('_MD_XOONIPS_ITEM_WARNING_ITEM_STORAGE_LIMIT', '', $parameters);
		}
	}

	public function editCheck(&$errors, $field, $value, $fieldName, $uid) {
		$this->inputCheck($errors, $field, $value, $fieldName);
	}
	
	public function doRegistry($field, &$data, &$sqlStrings, $groupLoopId) {
		$tableName = $field->getTableName();
		$columnName = $this->getData($field, $data, $groupLoopId);
		$bean = Xoonips_BeanFactory::getBean('ItemFieldDetailComplementLinkBean', $this->dirname, $this->trustDirname);
		$result = $bean->getItemTypeDetail($field->getItemTypeId(), $field->getId());
		$value = '';
		if ($result) {
			$value= $data[$this->getFieldName($field, $groupLoopId, $result[0]['item_field_detail_id'])];
		}
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
		$columnData[0] = $field->getDataType()->convertSQLStr($value);
		$columnData[1] = '';
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
			$tableData[] = $value;
		}
	}
	
	/**
	 *
	 * get default value block view
	 *
	 * @param $list, $value, $disabled
	 * @return string
	 */
	public function getDefaultValueBlockView($list, $value, $disabled = '') {
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
	
	public function getMetadata($field, &$data) {
		$table = $field->getTableName();
		$column = $field->getColumnName();
		$detail_id = $field->getId();
		foreach ($data[$table] as $value) {
			if ($value['item_field_detail_id'] == $detail_id) {
				return $value[$column];
			}
		}
		return '';
	}
}

