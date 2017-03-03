<?php

/**
 * admin policy action
 */
class Xoonips_Admin_PolicyAction extends Xoonips_AbstractAction {

	/**
	 * get style sheet
	 * 
	 * @return string
	 */
	protected function _getStylesheet() {
		return '/modules/' . $this->mAsset->mDirname . '/admin/index.php/css/admin_style.css';
	}

	/**
	 * getDefaultView
	 * 
	 * @return Enum
	 */
	public function getDefaultView() {
		return $this->_getFrameViewStatus('INDEX');
	}

	/**
	 * executeViewIndex
	 * 
	 * @param XCube_RenderTarget &$render
	 */
	public function executeViewIndex(&$render) {
		$dirname = $this->mModule->mXoopsModule->get('dirname');
		$constpref = '_AD_' . strtoupper($dirname);
		// breadcrumbs
		$breadcrumbs = array(
			array(
				'name' => constant($constpref . '_TITLE'),
				'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php',
			),
			array(
				'name' => constant($constpref . '_POLICY_TITLE'),
			),
		);
		$menu = array(
			array(
				'title' => constant($constpref . '_POLICY_USER_TITLE'),
				'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyUser',
			),
			array(
				'title' => constant($constpref . '_POLICY_GROUP_TITLE'),
				'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyGroup',
			),		  
			array(
				'title' => constant($constpref . '_POLICY_ITEM_TITLE'),
				'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItem',
			),		  
			array(
				'title' => constant($constpref . '_POLICY_INDEX_TITLE'),
				'link' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyIndex',
			),		  
		);
		$render->setTemplateName('admin_menu.html');
		$render->setAttribute('title', constant($constpref . '_POLICY_TITLE'));
		$render->setAttribute('description', constant($constpref . '_POLICY_DESC'));
		$render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
		$render->setAttribute('adminMenu', $menu);
	}
}

