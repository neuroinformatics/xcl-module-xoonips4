<?php

require_once __DIR__.'/class/core/Request.class.php';
require_once __DIR__.'/class/core/Response.class.php';
require_once __DIR__.'/class/action/SearchAction.class.php';

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$op = $request->getParameter('op');
if (null == $op) {
    $op = 'quick';
}

// check request
if (!in_array($op, ['quick', 'advanced', 'export'])) {
    die('illegal request');
}

// set action map
$actionMap = [];
$actionMap['quick_init_success'] = $mydirname.'_quicksearch.html';
$actionMap['quick_search_success'] = $mydirname.'_itemselect_item_list.html';
$actionMap['advanced_init_success'] = $mydirname.'_advanced_search.html';
$actionMap['advanced_search_success'] = $mydirname.'_itemselect_item_list.html';

include XOOPS_ROOT_PATH.'/header.php';

//do action
$action = new Xoonips_SearchAction();
$action->doAction($request, $response);

// forward
$response->forward($actionMap);

include XOOPS_ROOT_PATH.'/footer.php';
