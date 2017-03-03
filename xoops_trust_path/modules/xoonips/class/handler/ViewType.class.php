<?php

/**
 * view type object
 */
class Xoonips_ViewTypeObject extends XoopsSimpleObject {

	/**
	 * constructor
	 */
	public function __construct() {
		$this->initVar('view_type_id', XOBJ_DTYPE_INT, 0, true);
		$this->initVar('preselect', XOBJ_DTYPE_INT, 0, true);
		$this->initVar('multi', XOBJ_DTYPE_INT, 0, true);
		$this->initVar('name', XOBJ_DTYPE_STRING, '', true, 30);
		$this->initVar('module', XOBJ_DTYPE_STRING, '', true, 255);
	}

	/**
	 * get data types information
	 *
	 * @return array
	 */
	public function getDataTypesInfo() {
		$root =& XCube_Root::getSingleton();
		$db =& $root->mController->getDB();
		$ret = array();
		$sql = sprintf('SELECT * FROM `%s` WHERE `view_type_id`=%u ORDER BY `data_type_id`', $db->prefix($this->mDirname . '_view_data_relation'), $this->get('view_type_id'));
		if (!$result = $db->query($sql))
			return $ret;
		while ($row = $db->fetchArray($result)) {
			$dtId = $row['data_type_id'];
			$ret[$dtId] = array(
				'length' => $row['data_length'],
				'decimal_places' => $row['data_decimal_places'],
			);
		}
                $db->freeRecordSet($result);
		return $ret;
	}

	/**
	 * check whether view type has selection list
	 *
	 * @return bool
	 */
	public function hasSelectionList() {
		$viewType = Xoonips_ViewTypeFactory::getInstance($this->mDirname, 'xoonips')->getViewType($this->get('view_type_id'));
                return $viewType->hasSelectionList();
	}

	/**
	 * get input html of default value for item field editing
	 *
	 * @param mixed $list
	 * @param mixed $value
	 * @param bool $disabled
	 * @return string
	 */
	public function getDefaultValueAdminHtml($list, $value, $disabled) {
		$viewType = Xoonips_ViewTypeFactory::getInstance($this->mDirname, 'xoonips')->getViewType($this->get('view_type_id'));
                return trim($viewType->getDefaultValueBlockView($list, $value, $disabled));
	}

}

/**
 * view type object handler
 */
class Xoonips_ViewTypeHandler extends XoopsObjectGenericHandler {

	/**
	 * table
	 * @var string
	 */
	public $mTable = '{dirname}_view_type';

	/**
	 * primary id
	 * @var string
	 */
	public $mPrimary = 'view_type_id';

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
	 * get view types
	 *
	 * @return &object[]
	 */
	public function &getViewTypes() {
		$criteria = new CriteriaElement();
		$criteria->setSort($this->mPrimary);
		return $this->getObjects($criteria, null, null, true);
	}

	/**
	 * get table information
	 *
	 * @return array
	 */
	public function getTableInfo($viewTypeId) {
		// FIXME: this mapping should hold in the database or module class.
		static $tableMap = array(
			'ViewTypeHidden' => false,
			'ViewTypeText' => false,
			'ViewTypeTextArea' => false,
			'ViewTypeRadioBox' => false,
			'ViewTypeCheckBox' => false,
			'ViewTypeComboBox' => false,
			'ViewTypeId' => array('item','doi'),
			'ViewTypeTitle' => array('item_title', 'title'),
			'ViewTypeKeyword' => array('item_keyword', 'keyword'),
			'ViewTypeLastUpdate' => array('item', 'last_update_date'),
			'ViewTypeCreationDate' => array('item', 'creation_date'),
			'ViewTypeCreatUser' => array('item_users_link', 'uid'),
			'ViewTypeChangeLog' => array('item_changelog', 'log'),
			'ViewTypeIndex' => array('index_item_link', 'index_id'),
			'ViewTypeRelatedTo' => array('item_related_to', 'child_item_id'),
			'ViewTypeDate' => false,
			'ViewTypePreview' => array('item_file', 'file_id'),
			'ViewTypeFileUpload' => array('item_file', 'file_id'),
			'ViewTypeFileType' => false,
			'ViewTypeDownloadLimit', false,
			'ViewTypeDownloadNotify', false,
			'ViewTypeReadme', false,
			'ViewTypeRights', false,
			'ViewTypeUrl', false,
			'ViewTypePubmedId', false,
			'ViewTypeIsbn', false,
			'ViewTypeKana', false,
		);
		$ret = false;
		$obj = $this->get($viewTypeId);
		if (is_object($obj)) {
			$module = $obj->get('module');
			if (array_key_exists($module, $tableMap)) {
				$ret = $tableMap[$module];
			}
		}
		return $ret;
	}

}

