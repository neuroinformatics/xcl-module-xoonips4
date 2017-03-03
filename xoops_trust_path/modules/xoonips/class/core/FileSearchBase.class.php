<?php

class Xoonips_FileSearchBase {

	/**
	 * file handle
	 * @access public
	 * @var resource
	 */
	public $handle = false;

	/**
	 * is xml data
	 * @access public
	 * @var bool
	 */
	public $is_xml = false;
	
	/**
	 * handler is file or process
	 * @access public
	 * @var bool
	 */
	public $is_file = true;

	/**
	 * constractor
	 */
	public function __construct() {
	}

	/**
	 * open file
	 *
	 * @access public
	 * @param string $filename file name
	 * @return bool false if failure
	 */
	public function open($filename) {
		if ($this->handle !== false) {
			return false;
		}
		$this->openImpl($filename);
		if ($this->handle === false) {
			return false;
		}
		return true;
	}

	/**
	 * close file
	 *
	 * @access public
	 * @return bool false if failure
	 */
	public function close() {
		if ($this->handle === false) {
			return false;
		}
		$this->closeImpl();
		return true;
	}

	/**
	 * fetch 'UTF-8' text from file or process
	 *
	 * @access public
	 * @return string fetched data if an error occured false returned 
	 */
	public function fetch() {
		if ($this->handle === false) {
			return false;
		}
		$text = '';
		while (!feof($this->handle)) {
			$tmp = $this->is_xml ? fgetss($this->handle, 8192) : fgets($this->handle, 8192);
			if ($tmp !== false) {
				$text .= $tmp;
			}
		}
		return $this->fetchImpl($text);
	}

	/**
	 * function to open file or process resource
	 *
	 * @acccess protected
	 * @param string $file_path file path
	 */
	protected function openImpl($file_path) {
		if ($this->is_file) {
			$this->handle = @fopen($file_path, 'rb');
		} else {
			$this->handle = @popen($file_path, 'rb');
		}
	}

	/**
	 * function to fetch 'UTF-8' text from file or process
	 *
	 * @access protected
	 * @param string $text fetched data
	 * @return string processed fetched data 
	 */
	protected function fetchImpl($text) {
		return $text;
	}

	/**
	 * function to close file or process resource
	 *
	 * @acccess protected
	 */
	protected function closeImpl() {
		if ($this->is_file) {
			@fclose($this->handle);
		} else {
			@pclose($this->handle);
		}
	}
}

