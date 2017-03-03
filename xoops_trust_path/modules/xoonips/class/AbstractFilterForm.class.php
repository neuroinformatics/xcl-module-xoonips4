<?php

/**
 * abstract filter form
 */
class Xoonips_AbstractFilterForm {

	/**
	 * sort key number
	 * @var int
	 */
	protected $mSort = 0;

	/**
	 * sort keys
	 * @var array
	 */
	protected $mSortKeys = array();

	/**
	 * criteria
	 * @var Criteria
	 */
	protected $_mCriteria = null;

	/**
	 * page navi
	 * @var Xoops_PageNavi
	 */
	public $mNavi = null;

	/**
	 * object handler
	 * @var XoopsObjectHandler
	 */
	protected $_mHandler = null;

	/**
	 * constructor
	 *
	 * @param &$navi page navi
	 * @param &$handler object handler
	 */
	public function __construct(&$navi, &$handler) {
		$this->mNavi =& $navi;
		$this->_mHandler =& $handler;
		$this->_mCriteria = new CriteriaCompo();
		$this->mNavi->mGetTotalItems->add(array(&$this, 'getTotalItems'));
	}

	/**
	 * get default sort key number
	 *
	 * @return int
	 */
	public function getDefaultSortKey() {
		/* override */
		return null;
	}

	/**
	 * get total items
	 *
	 * @param &$total
	 */
	public function getTotalItems(&$total) {
		$total = $this->_mHandler->getCount($this->getCriteria());
	}

	/**
	 * fetch sort key
	 */
	public function fetchSort() {
		$root =& XCube_Root::getSingleton();
		$this->mSort = intval($root->mContext->mRequest->getRequest('sort'));
		if (!isset($this->mSortKeys[abs($this->mSort)]))
			$this->mSort = $this->getDefaultSortKey();
		$this->mNavi->mSort['sort'] = $this->mSort;
	}

	/**
	 * fetch
	 */
	public function fetch() {
		$this->mNavi->fetch();
		$this->fetchSort();
	}

	/**
	 * get sort key
	 *
	 * @return string
	 */
	public function getSort() {
		$sortkey = abs($this->mNavi->mSort['sort']);
		return isset($this->mSortKeys[$sortkey]) ? $this->mSortKeys[$sortkey] : null;
	}

	/**
	 * get order
	 *
	 * @return string
	 */
	public function getOrder() {
		return ($this->mSort < 0) ? 'DESC' : 'ASC';
	}

	/**
	 * get criteria
	 *
	 * @param int $start
	 * @param int $limit
	 * @return Criteria
	 */
	public function getCriteria($start = null, $limit = null) {
		$t_start = 0;
		$t_limit = 0;
		if ($start === null) {
			$t_start = $this->mNavi->getStart();
		} else {
			$t_start = intval($start);
			$this->mNavi->setStart($t_start);
		}
		if ($limit === null) {
			$t_limit = $this->mNavi->getPerpage();
		} else {
			$t_limit = intval($limit);
			$this->mNavi->setPerpage($t_limit);
		}
		$criteria = $this->_mCriteria;
		$criteria->setStart($t_start);
		$criteria->setLimit($t_limit);
		return $criteria;
	}

}

