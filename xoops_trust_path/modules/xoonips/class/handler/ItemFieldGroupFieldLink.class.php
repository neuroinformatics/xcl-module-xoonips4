<?php

/**
 * item field group field link object
 */
class Xoonips_ItemFieldGroupFieldLinkObjeect extends XoopsSimpleObject {

	/**
	 * constructor
	 */
	public function __construct() {
		$this->initVar('item_field_group_field_detail_id', XOBJ_DTYPE_INT, 0, true);
		$this->initVar('group_id', XOBJ_DTYPE_INT, 0, true);
		$this->initVar('item_field_detail_id', XOBJ_DTYPE_INT, 0, true);
		$this->initVar('edit_weight', XOBJ_DTYPE_INT, 0, true);
		$this->initVar('edit', XOBJ_DTYPE_INT, 0, true);
		$this->initVar('weight', XOBJ_DTYPE_INT, 0, true);
		$this->initVar('released', XOBJ_DTYPE_INT, 0, true);
	}

}

/**
 * item field group field link object handler
 */
class Xoonips_ItemFieldGroupFieldLinkHandler extends XoopsObjectGenericHandler {

	/**
	 * table
	 * @var string
	 */
	public $mTable = '{dirname}_item_field_group_field_detail_link';

	/**
	 * primary id
	 * @var string
	 */
	public $mPrimary = 'item_field_group_field_detail_id';

	/**
	 * object class name
	 * @var string
	 */
	public $mClass = '';

	/**
	 * dirname
	 * @var string
	 */
	public $mDirname = '';

	/**
	 * constructor
	 *
	 * @param XoopsDatabase &$db
	 * @param string $dirname
	 */
	public function __construct(&$db, $dirname) {
		$this->mTable = strtr($this->mTable, array('{dirname}' => $dirname));
		$this->mDirname = $dirname;
		$this->mClass = preg_replace('/Handler$/', 'Object', get_class());
		parent::__construct($db);
	}

}

