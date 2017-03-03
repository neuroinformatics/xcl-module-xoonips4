<?php

/**
 * index item link object
 */
class Xoonips_IndexItemLinkObject extends XoopsSimpleObject {

	/**
	 * constructor
	 */
	public function __construct() {
		$this->initVar('index_item_link_id', XOBJ_DTYPE_INT, 0, true);
		$this->initVar('index_id', XOBJ_DTYPE_INT, null, true);
		$this->initVar('item_id', XOBJ_DTYPE_INT, null, true);
		$this->initVar('certify_state', XOBJ_DTYPE_INT, null, true);
	}

}

/**
 * index item link object handler
 */
class Xoonips_IndexItemLinkHandler extends XoopsObjectGenericHandler {

	/**
	 * table
	 * @var string
	 */
	public $mTable = '{dirname}_index_item_link';

	/**
	 * primary id
	 * @var string
	 */
	public $mPrimary = 'index_item_link_id';

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

