<?php

/**
 * admin policy item field select delete form
 */
class Xoonips_Admin_PolicyItemFieldSelectDeleteForm extends Xoonips_AbstractActionForm {

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
			'name' => array(
				'type' => self::TYPE_STRING,
				'label' => constant($constpref . '_POLICY_ITEM_FIELD_SELECT_NAME'),
				'depends' => array(
					'required' => true,
				),
			),
		);
	}

	/**
	 * validate name
	 */
	public function validate_name() {
		$constpref = '_AD_' . strtoupper($this->mDirname);
		$trustDirname = $this->mAsset->mTrustDirname;
		$name = $this->get('name');
		// check exists
		$handler = Xoonips_Utils::getModuleHandler('ItemField', $this->mDirname);
		if (!in_array($name, $handler->getSelectNames()))
			$this->addErrorMessage(XCube_Utils::formatString(constant($constpref . '_ERROR_INPUTVALUE'), constant($constpref . '_POLICY_ITEM_FIELD_SELECT_NAME')));
		// check used
		$handler = Xoonips_Utils::getModuleHandler('ItemFieldValueSet', $this->mDirname);
		if (in_array($name, $handler->getUsedSelectNames()))
			$this->addErrorMessage(XCube_Utils::formatString(constant($constpref . '_ERROR_INPUTVALUE'), constant($constpref . '_POLICY_ITEM_FIELD_SELECT_NAME')));
	}

}

