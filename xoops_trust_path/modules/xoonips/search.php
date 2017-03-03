<?php

require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/Request.class.php';
require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/Response.class.php';
require_once 'class/action/SearchAction.class.php';

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$op = $request->getParameter('op');
if ($op == null) {
	$op = 'quick';
}

// check request
if (!in_array($op, array('quick', 'advanced', 'export'))) {
	die('illegal request');
}

// set action map
$actionMap = array();
$dirname = Xoonips_Utils::getDirname();
$actionMap['quick_init_success'] = $dirname . '_quicksearch.html';
$actionMap['quick_search_success'] = $dirname . '_itemselect_item_list.html';
$actionMap['advanced_init_success'] = $dirname . '_advanced_search.html';
$actionMap['advanced_search_success'] = $dirname . '_itemselect_item_list.html';

include XOOPS_ROOT_PATH . '/header.php';

//do action
$action = new Xoonips_SearchAction();
$action->doAction($request, $response);

// forward
$response->forward($actionMap);

include XOOPS_ROOT_PATH . '/footer.php';

