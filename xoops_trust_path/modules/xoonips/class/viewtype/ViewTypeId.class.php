<?php

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/core/BeanFactory.class.php';
require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/viewtype/ViewType.class.php';

class Xoonips_ViewTypeId extends Xoonips_ViewTypeText {

	public function inputCheck(&$errors, $field, $value, $fieldName) {
		if (strlen($value) > 0) {
			$matches = array();
			$res = preg_match('/' . XOONIPS_CONFIG_DOI_FIELD_PARAM_PATTERN . '/', $value, $matches);
			if (strlen($value) > XOONIPS_CONFIG_DOI_FIELD_PARAM_MAXLEN || $res == 0 || $matches[0] != $value) {
				$parameters = array();
				$parameters[] = XOONIPS_CONFIG_DOI_FIELD_PARAM_MAXLEN;
				$errors->addError('_MD_XOONIPS_ITEM_DOI_INVALID_ID', '', $parameters);
			} else {
				$itemBasicBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
				if ($itemBasicBean->checkExistdoi(0, $value)) {
					$parameters = array();
					$parameters[] = '';
					$errors->addError('_MD_XOONIPS_ITEM_DOI_DUPLICATE_ID', '', $parameters);
				}
			}
		}
	}

	public function editCheck(&$errors, $field, $value, $fieldName, $itemid) {
		if (strlen($value) > 0) {
			$matches = array();
			$res = preg_match('/' . XOONIPS_CONFIG_DOI_FIELD_PARAM_PATTERN . '/', $value, $matches);
			if (strlen($value) > XOONIPS_CONFIG_DOI_FIELD_PARAM_MAXLEN || $res == 0 || $matches[0] != $value) {
				$parameters = array();
				$parameters[] = XOONIPS_CONFIG_DOI_FIELD_PARAM_MAXLEN;
				$errors->addError('_MD_XOONIPS_ITEM_DOI_INVALID_ID', '', $parameters);
			} else {
				$itemBasicBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
				if ($itemBasicBean->checkExistdoi($itemid, $value)) {
					$parameters = array();
					$parameters[] = '';
					$errors->addError('_MD_XOONIPS_ITEM_DOI_DUPLICATE_ID', '', $parameters);
				}
			}
		}
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

