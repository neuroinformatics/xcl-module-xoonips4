<?php

/**
 * admin policy group form
 */
class Xoonips_Admin_PolicyGroupForm extends Xoonips_AbstractActionForm {

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
			'general' => array(
				'group_making' => array(
					'type' => self::TYPE_STRING,
					'label' => constant($constpref . '_POLICY_GROUP_CONSTRUCT_PERMIT_TITLE'),
					'depends' => array(
						'required' => true,
						'mask' => '/^(?:on|off)$/',
					),
				),
				'group_making_certify' => array(
					'type' => self::TYPE_STRING,
					'label' => constant($constpref . '_POLICY_GROUP_CONSTRUCT_CERTIFY_TITLE'),
					'depends' => array(
						'required' => true,
						'mask' => '/^(?:on|off)$/',
					),
				),
				'group_publish_certify' => array(
					'type' => self::TYPE_STRING,
					'label' => constant($constpref . '_POLICY_GROUP_PUBLISH_CERTIFY_TITLE'),
					'depends' => array(
						'required' => true,
						'mask' => '/^(?:on|off)$/',
					),
				),
			),
			'initval' => array(
				'group_item_number_limit' => array(
					'type' => self::TYPE_INT,
					'label' => constant($constpref . '_POLICY_GROUP_INITVAL_MAXITEM_TITLE'),
					'depends' => array(
						'required' => true,
						'min' => 0,
					),
				),
				'group_index_number_limit' => array(
					'type' => self::TYPE_INT,
					'label' => constant($constpref . '_POLICY_GROUP_INITVAL_MAXINDEX_TITLE'),
					'depends' => array(
						'required' => true,
						'min' => 0,
					),
				),
				'group_item_storage_limit' => array(
					'type' => self::TYPE_FLOAT,
					'label' => constant($constpref . '_POLICY_GROUP_INITVAL_MAXDISK_TITLE'),
					'depends' => array(
						'required' => true,
						'min' => 0,
					),
				),
			),
		);
	}

}

