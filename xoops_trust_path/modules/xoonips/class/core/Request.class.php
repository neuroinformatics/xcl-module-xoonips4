<?php

use Xoonips\Core\FileUtils;

class Xoonips_Request {

	public function getParameter($key) {
		$root =& XCube_Root::getSingleton();
		return $root->mContext->mRequest->getRequest($key);
	}

	public function getFile($name) {
		if (!isset($_FILES[$name])) {
			return null;
		}
		$val = $_FILES[$name];
		if ((isset($val['error']) && $val['error'] != 0) || !is_uploaded_file($val['tmp_name'])) {
			return null;
		}
		$val['type'] = FileUtils::guessMimeType($val['tmp_name'], $val['name']);
		if ($val['type'] === false) {
			return null;
		}
		return $val;
	}
}

