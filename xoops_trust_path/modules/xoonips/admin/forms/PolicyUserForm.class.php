<?php

/**
 * admin policy user form
 */
class Xoonips_Admin_PolicyUserForm extends Xoonips_AbstractActionForm {

	/**
	 * is admin mode
	 *
	 * @return bool
	 */
	protected function _isAdminMode() {
		return true;
	}

	/**
	 * is multiple mode
	 *
	 * @return bool
	 */
	protected function _isMultipleMode() {
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
			'regist' => array(
				'activate_user' => array(
					'type' => self::TYPE_INT,
					'label' => constant($constpref . '_POLICY_USER_REGIST_ACTIVATE_TITLE'),
					'depends' => array(
						'required' => true,
						'intRange' => true,
						'min' => 0,
						'max' => 2,
					),
				),
				'certify_user' => array(
					'type' => self::TYPE_STRING,
					'label' => constant($constpref . '_POLICY_USER_REGIST_CERTIFY_TITLE'),
					'depends' => array(
						'required' => true,
						'mask' => '/^(?:on|auto)$/',
					),
				),
				'user_certify_date' => array(
					'type' => self::TYPE_INT,
					'label' => constant($constpref . '_POLICY_USER_REGIST_DATELIMIT_TITLE'),
					'depends' => array(
						'required' => true,
						'min' => 0,
					),
				),
			),
			'initval' => array(
				'private_item_number_limit' => array(
					'type' => self::TYPE_INT,
					'label' => constant($constpref . '_POLICY_USER_INITVAL_MAXITEM_TITLE'),
					'depends' => array(
						'required' => true,
						'min' => 0,
					),
				),
				'private_index_number_limit' => array(
					'type' => self::TYPE_INT,
					'label' => constant($constpref . '_POLICY_USER_INITVAL_MAXINDEX_TITLE'),
					'depends' => array(
						'required' => true,
						'min' => 0,
					),
				),
				'private_item_storage_limit' => array(
					'type' => self::TYPE_FLOAT,
					'label' => constant($constpref . '_POLICY_USER_INITVAL_MAXDISK_TITLE'),
					'depends' => array(
						'required' => true,
						'min' => 0,
					),
				),
			),
		);
	}

}

