<?php

$xoopsOption['pagetype'] = 'user';
require_once __DIR__.'/class/core/Request.class.php';
require_once __DIR__.'/class/core/Response.class.php';
require_once __DIR__.'/class/action/DetailAction.class.php';

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$op = $request->getParameter('op');
if ($op == null) {
    $op = 'init';
}

$xoonipsTreeCheckBox = false;
$xoonipsCheckPrivateHandlerId = 'PrivateIndexCheckedHandler';

// check op request
if (!in_array($op, array('init', 'print', 'export'))) {
    die('illegal request');
}

// set action map
$actionMap = array();
$actionMap['init_success'] = $mydirname.'_detail.html';

include XOOPS_ROOT_PATH.'/header.php';

// do action
$action = new Xoonips_DetailAction();
$action->doAction($request, $response);

// forward
$response->forward($actionMap);

include XOOPS_ROOT_PATH.'/footer.php';
