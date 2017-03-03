<?php

/**
 * file search plugin class for Excel
 */
class Xoonips_FileSearchExcel extends Xoonips_FileSearchBase {

	/**
	 * constractor
	 */
	public function __construct() {
		parent::__construct();
		$this->is_xml = true;
		$this->is_file = false;
	}

	/**
	 * get definition of Excel file search
	 * 
	 * @return array definition of Excel file search
	 */
	public function getSearchInstance() {
		return array(
			'name' => 'excel',
			'display_name' => 'MS-Excel 95/97/2000/XP/2003',
			'mime_type' => array('application/vnd.ms-excel', 'application/msword'),
			'extensions' => array('xls', 'xlt', 'xlm', 'xld', 'xla', 'xlc', 'xlw', 'xll'),
			'version' => '2.0'
		);
	}

	/**
	 * open file or process resource
	 *
	 * @acccess protected
	 * @param string $filename file name
	 */
	protected function openImpl($filename) {
		parent::openImpl(sprintf('xlhtml -nh %s', escapeshellarg($filename)));
	}
}

