<?php

$xoopsOption['pagetype'] = 'user';

require_once __DIR__.'/class/core/Request.class.php';
require_once __DIR__.'/class/core/Response.class.php';
require_once __DIR__.'/class/action/ListAction.class.php';

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$op = $request->getParameter('op');
if ($op == null) {
    $op = 'init';
}

// check request
if (!in_array($op, array('init', 'export', 'exportselect'))) {
    die('illegal request');
}

// set action map
$actionMap = array();
$actionMap['init_success'] = $mydirname.'_list.html';
$actionMap['exportselect_success'] = $mydirname.'_export_select.html';

include XOOPS_ROOT_PATH.'/header.php';

//do action
$action = new Xoonips_ListAction();
$action->doAction($request, $response);

// forward
$response->forward($actionMap);

include XOOPS_ROOT_PATH.'/footer.php';
