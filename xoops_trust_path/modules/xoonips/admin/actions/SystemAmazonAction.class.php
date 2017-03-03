<?php

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/admin/class/AbstractConfigAction.class.php';

/**
 * admin system amazon action
 */
class Xoonips_Admin_SystemAmazonAction extends Xoonips_Admin_AbstractConfigAction {

	/**
	 * get page url
	 *
	 * @return string
	 */
	protected function _getUrl() {
		return XOOPS_URL . '/modules/' . $this->mAsset->mDirname . '/admin/index.php?action=SystemAmazon';
	}

	/**
	 * get config keys
	 *
	 * @return array
	 */
	protected function getConfigKeys() {
		return array('access_key', 'secret_access_key');
	}

	/**
	 * setup action form
	 *
	 * {Trustdirname}_AbstractActionForm &
	 */
	protected function &_getActionForm() {
		return $this->mAsset->getObject('form', 'system', true, 'amazon');
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
				'name' => constant($constpref . '_SYSTEM_TITLE'),
				'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=System',
			),
			array(
				'name' => constant($constpref . '_SYSTEM_AMAZON_TITLE'),
			),
		);
		$render->setTemplateName('system_amazon.html');
		$render->setAttribute('title', constant($constpref . '_SYSTEM_AMAZON_TITLE'));
		$render->setAttribute('description', constant($constpref . '_SYSTEM_AMAZON_DESC'));
		$render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
		$render->setAttribute('actionForm', $this->mActionForm);
		$render->setAttribute('constpref', $constpref);
	}

}

