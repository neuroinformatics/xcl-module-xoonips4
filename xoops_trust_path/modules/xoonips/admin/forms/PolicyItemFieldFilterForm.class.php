<?php

/**
 * admin policy item field filter form
 */
class Xoonips_Admin_PolicyItemFieldFilterForm extends Xoonips_AbstractFilterForm {

	const SORT_KEY_XML = 1;
	const SORT_KEY_NAME = 2;

	/**
	 * sort keys
	 * @var array
	 */
	protected $mSortKeys = array(
		self::SORT_KEY_XML => 'xml',
		self::SORT_KEY_NAME => 'name',
	);

	/**
	 * get default sort key
	 *
	 * @return int
	 */
	public function getDefaultSortKey() {
		return self::SORT_KEY_XML;
	}

	/**
	 * fetch
	 */
	public function fetch() {
		parent::fetch();
		$this->_mCriteria->add(new Criteria('ISNULL(`update_id`)', 1));
		$this->_mCriteria->addSort($this->getSort(), $this->getOrder());
	}

}

