<?php

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/AbstractEditAction.class.php';

/**
 * admin policy user action
 */
class Xoonips_Admin_PolicyUserAction extends Xoonips_AbstractEditAction {

	/**
	 * config keys
	 * @var array
	 */
	private $_mConfigKeys = array(
		'regist' => array(
			'certify_user',
			'user_certify_date',
		),
		'initval' => array(
			'private_item_number_limit',
			'private_index_number_limit',
			'private_item_storage_limit',
		),
	);

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
		return XOOPS_URL . '/modules/' . $this->mAsset->mDirname . '/admin/index.php?action=PolicyUser';
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
	 * get action form
	 *
	 * @return {Trustdirname}_AbstractActionForm &
	 */
	protected function &_getActionForm() {
		return $this->mAsset->getObject('form', 'policy', true, 'user');
	}

	/**
	 * setup object
	 */
	protected function _setupObject() {
		$this->mObject = $this->getUserPolicies();
	}

	/**
	 * save object
	 *
	 * @return bool
	 */
	protected function _saveObject() {
		return $this->setUserPolicies($this->mObject);
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
				'name' => constant($constpref . '_POLICY_USER_TITLE'),
			),
		);
		$render->setTemplateName('policy_user.html');
		$render->setAttribute('title', constant($constpref . '_POLICY_USER_TITLE'));
		$render->setAttribute('description', constant($constpref . '_POLICY_USER_DESC'));
		$render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
		$render->setAttribute('actionForm', $this->mActionForm);
		$render->setAttribute('constpref', $constpref);
	}

	/**
	 * get user policies
	 *
	 * @return array
	 */
	protected function getUserPolicies() {
		$ret = array();
		$ret['mode'] = '';
		$ret['activate_user'] = Xoonips_Utils::getModuleConfig('user', 'activation_type');
		foreach ($this->_mConfigKeys as $mode => $keys) {
			foreach ($keys as $key) {
				$value = Xoonips_Utils::getXooNIpsConfig($this->mAsset->mDirname, $key);
				if ($key == 'private_item_storage_limit')
					$value /= (1024 * 1024);
				$ret[$key] = $value;
			}
		}
		return $ret;
	}

	/**
	 * set user policies
	 *
	 * @param array $configs
	 * @return bool
	 */
	protected function setUserPolicies($policies) {
		foreach ($this->_mConfigKeys as $mode => $keys) {
			if ($mode == $policies['mode']) {
				if ($mode == 'regist')
					if (!Xoonips_Utils::setModuleConfig('user', 'activation_type', $policies['activate_user']))
						return false;
				foreach ($keys as $key) {
					$value = $policies[$key];
					if ($key == 'private_item_storage_limit')
						$value *= (1024 * 1024);
					if (!Xoonips_Utils::setXooNIpsConfig($this->mAsset->mDirname, $key, $value))
						return false;
				}
				return true;
			}
		}
		return false;
	}

}

