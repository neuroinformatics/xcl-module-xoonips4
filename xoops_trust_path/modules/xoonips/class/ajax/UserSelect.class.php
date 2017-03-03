<?php

$GLOBALS['xoopsOption']['pagetype'] = 'user';

require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/core/Request.class.php';
require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/core/Response.class.php';
require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/class/action/UserSelectSubAction.class.php';

class Xoonips_UserSelectAjaxMethod extends Xoonips_AbstractAjaxMethod {

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
		$response = new Xoonips_Response();
		$op = $request->getParameter('op');
		if ($op == null)
			$op = 'init';

		// check request
		if (!in_array($op, array('init', 'search', 'sort')))
			die('illegal request');

		// set action map
		$actionMap = array();
		$actionMap['init_success'] = $this->mDirname . '_ajax_userselect.html';
		$actionMap['search_success'] = $this->mDirname . '_ajax_userselect.html';
		$actionMap['sort_success'] = $this->mDirname . '_ajax_userselect.html';

		//do action
		$action = new Xoonips_UserSelectSubAction();
		$action->doAction($request, $response);

		// forward
		ob_start();
		$response->forwardLayeredWindow($actionMap);
		$this->mResult = ob_get_clean();

		return true;
	}

}
