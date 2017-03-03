<?php

/**
 * admin system proxy form
 */
class Xoonips_Admin_SystemProxyForm extends Xoonips_AbstractActionForm {

	const PASSWORD_MASK = '****************';

	/**
	 * is admin mode
	 *
	 * @return bool
	 */
	protected function _isAdminMode() {
		return true;
	}

	/**
	 * get form params
	 *
	 * @return array
	 */
	protected function _getFormParams() {
		$constpref = '_AD_' . strtoupper($this->mDirname);
		return array(
			'proxy_host' => array(
				'type' => self::TYPE_STRING,
			),
			'proxy_port' => array(
				'type' => self::TYPE_INT,
				'label' => constant($constpref . '_SYSTEM_PROXY_PROXY_PORT_TITLE'),
				'depends' => array(
					'required' => true,
					'min' => 1,
				),
			),
			'proxy_user' => array(
				'type' => self::TYPE_STRING,
			),
			'proxy_pass' => array(
				'type' => self::TYPE_STRING,
			),
		);
	}

	/**
	 * get object value
	 *
	 * @param mixed &$obj
	 * @param string $key
	 * @return mixed
	 */
	protected function _getObjectValue(&$obj, $key) {
		if ($key == 'proxy_pass' && !empty($obj[$key]))
			return self::PASSWORD_MASK;
		return $obj[$key];
	}

	/**
	 * set object value
	 *
	 * @param mixed &$obj
	 * @param string $key
	 * @param mixed $value
	 */
	protected function _setObjectValue(&$obj, $key, $value) {
		if ($key == 'proxy_pass' && $value == self::PASSWORD_MASK)
			return;
		$obj[$key] = $value;
	}

}

