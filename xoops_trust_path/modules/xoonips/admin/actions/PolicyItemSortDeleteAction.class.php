<?php

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/AbstractDeleteAction.class.php';

/**
 * admin policy item sort delete action
 */
class Xoonips_Admin_PolicyItemSortDeleteAction extends Xoonips_AbstractDeleteAction {

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
		return XOOPS_URL . '/modules/' . $this->mAsset->mDirname . '/admin/index.php?action=PolicyItemSort';
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
	 * get header
	 *
	 * return XoopsObjectGenericHandler
	 */
	protected function &_getHandler() {
		return $this->mAsset->getObject('handler', 'ItemSort');
	}

	/**
	 * get action form
	 *
	 * @return {Trustdirname}_AbstractActionForm &
	 */
	protected function &_getActionForm() {
		return $this->mAsset->getObject('form', 'policyItemSort', true, 'delete');
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
				'name' => constant($constpref . '_POLICY_ITEM_SORT_TITLE'),
				'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItemSort',
			),
			array(
				'name' => constant($constpref . '_POLICY_ITEM_SORT_DELETE_TITLE'),
			),
		);
		$render->setTemplateName('policy_item_sort_delete.html');
		$render->setAttribute('title', constant($constpref . '_POLICY_ITEM_SORT_DELETE_TITLE'));
		$render->setAttribute('description', constant($constpref . '_POLICY_ITEM_SORT_DELETE_DESC'));
		$render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
		$render->setAttribute('actionForm', $this->mActionForm);
		$render->setAttribute('constpref', $constpref);
		$render->setAttribute('object', $this->mObject);
	}

}

