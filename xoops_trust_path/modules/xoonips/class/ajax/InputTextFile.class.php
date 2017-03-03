<?php

use Xoonips\Core\StringUtils;

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/core/Request.class.php';

class Xoonips_InputTextFileAjaxMethod extends Xoonips_AbstractAjaxMethod {

	public function getType() {
		return 'html';
	}

	/**
	 * execute
	 *
	 * return bool
	 */
	public function execute() {

		Xoonips_Utils::denyGuestAccess();

		$request = new Xoonips_Request();
		$name = $request->getParameter('name');
		$elementId = $request->getParameter('elementId');

		$text = false;
		$file = $request->getFile('file');
		$errorMessage = false;
                $textFromOpener = true;
		if (!is_null($file)) {
        		// file was uploaded
        		$originalFileName = $file['name'];
        		$mimeType = $file['type'];
        		$fileName = $file['tmp_name'];
        		$error = (int) $file['error'];
        		if ($error != 0) {
                		if ($error == UPLOAD_ERR_INI_SIZE) {
                        		$errorMessage = _MD_XOONIPS_ITEM_UPLOAD_FILE_TOO_LARGE;
                		} else {
                       			 $errorMessage = _MD_XOONIPS_ITEM_UPLOAD_FILE_FAILED;
                		}
        		} else {
                		// check mime type
                		if (strstr($mimeType, 'text/plain') === false) {
                        		$errorMessage = 'unsupported file type : ' . $mimeType;
                		} else {
                        		$text = file_get_contents($fileName);
                        		// convert encoding to _CHARSET
					$encoding = StringUtils::detectTextEncoding($text);
                                        $text = StringUtils::convertEncoding($text, _CHARSET, $encoding, 'h');
                        		$textFromOpener = false;
                		}
        		}
		}

		ob_start();
		xoops_header(false);

		$xoopsTpl = new XoopsTpl();
		$xoopsTpl->assign('dirname', $this->mDirname);
		$xoopsTpl->assign('displayName', $name);
		$xoopsTpl->assign('name', $name);
		$xoopsTpl->assign('elementId', $elementId);
		$xoopsTpl->assign('text', $text);
		$xoopsTpl->assign('textFromOpener', $textFromOpener);
		$xoopsTpl->assign('errorMessage', $errorMessage);
		$dirname = Xoonips_Utils::getDirname();
		$xoopsTpl->display('db:'. $dirname . '_ajax_textfileinput.html');

		xoops_footer();
		$this->mResult = ob_get_clean();

		return true;
	}

}
