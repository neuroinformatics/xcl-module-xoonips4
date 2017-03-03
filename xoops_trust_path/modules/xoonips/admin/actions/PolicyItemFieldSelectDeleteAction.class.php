<?php

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/AbstractDeleteAction.class.php';

/**
 * admin policy item field select delete action
 */
class Xoonips_Admin_PolicyItemFieldSelectDeleteAction extends Xoonips_AbstractDeleteAction {

	/**
	 * is admin
	 *
	 * @return bool
	 */
	protected function _isAdmin() {
		return true;
	}

	/**
	 * get page url
	 *
	 * @return string
	 */
	protected function _getUrl() {
		return XOOPS_URL . '/modules/' . $this->mAsset->mDirname . '/admin/index.php?action=PolicyItemFieldSelect';
	}

	/**
	 * get style sheet
	 *
	 * @return string
	 */
	protected function _getStylesheet() {
		return '/modules/' . $this->mAsset->mDirname . '/admin/index.php/css/admin_style.css';
	}

	/**
	 * get id
	 *
	 * @return int
	 */
	protected function _getId() {
		$req = $this->mRoot->mContext->mRequest;
		$dataId = $req->getRequest(_REQUESTED_DATA_ID);
		return isset($dataId) ? trim($dataId) : trim($req->getRequest('name'));
	}

	/**
	 * get header
	 *
	 * return XoopsObjectGenericHandler
	 */
	protected function &_getHandler() {
		return $this->mAsset->getObject('handler', 'ItemFieldValueSet');
	}

	/**
	 * get action form
	 *
	 * @return {Trustdirname}_AbstractActionForm &
	 */
	protected function &_getActionForm() {
		return $this->mAsset->getObject('form', 'policyItemFieldSelect', true, 'delete');
	}

	/**
	 * setup object
	 */
	protected function _setupObject() {
		$id = $this->_getId();
		$this->mObjectHandler =& $this->_getHandler();
 		$values = $this->mObjectHandler->getValueSet($id);
		$this->mObject = array(
			'name' => $id,
			'values' => $values,
		);
	}

	/**
	 * save object
	 *
	 * @return bool
	 */
	protected function _saveObject() {
		return $this->mObjectHandler->deleteByName($this->mObject['name']);
	}

	/**
	 * execute view input
	 *
	 * @param XCube_RenderTarget &$render
	 */
	public function executeViewInput(&$render) {
		$dirname = $this->mAsset->mDirname;
		$constpref = '_AD_' . strtoupper($dirname);
		// breadcrumbs
		$breadcrumbs = array(
			array(
				'name' => constant($constpref . '_TITLE'),
				'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php',
			),
			array(
				'name' => constant($constpref . '_POLICY_TITLE'),
				'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=Policy',
			),
			array(
				'name' => constant($constpref . '_POLICY_ITEM_TITLE'),
				'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItem',
			),
			array(
				'name' => constant($constpref . '_POLICY_ITEM_FIELD_SELECT_TITLE'),
				'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItemQuickSearch',
			),
			array(
				'name' => constant($constpref . '_POLICY_ITEM_FIELD_SELECT_DELETE_TITLE'),
			),
		);
		$render->setTemplateName('policy_item_field_select_delete.html');
		$render->setAttribute('title', constant($constpref . '_POLICY_ITEM_FIELD_SELECT_TITLE'));
		$render->setAttribute('description', constant($constpref . '_POLICY_ITEM_FIELD_SELECT_DELETE_DESC'));
		$render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
		$render->setAttribute('actionForm', $this->mActionForm);
		$render->setAttribute('constpref', $constpref);
		$render->setAttribute('object', $this->mObject);
	}

}

