<?php

/**
 * data type object
 */
class Xoonips_DataTypeObject extends XoopsSimpleObject {

	/**
	 * constructor
	 */
	public function __construct() {
		$this->initVar('data_type_id', XOBJ_DTYPE_INT, 0, true);
		$this->initVar('name', XOBJ_DTYPE_STRING, '', true, 30);
		$this->initVar('module', XOBJ_DTYPE_STRING, '', true, 255);
	}

}

/**
 * data type object handler
 */
class Xoonips_DataTypeHandler extends XoopsObjectGenericHandler {

	/**
	 * table
	 * @var string
	 */
	public $mTable = '{dirname}_data_type';

	/**
	 * primary id
	 * @var string
	 */
	public $mPrimary = 'data_type_id';

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

	/**
	 * get data types
	 *
	 * @return &object[]
	 */
	public function &getDataTypes() {
		$criteria = new CriteriaElement();
		$criteria->setSort($this->mPrimary);
		return $this->getObjects($criteria, null, null, true);
	}

}

