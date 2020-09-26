<?php

$xoopsOption['pagetype'] = 'user';
require_once __DIR__.'/class/core/Request.class.php';
require_once __DIR__.'/class/core/Response.class.php';
require_once __DIR__.'/class/action/IndexDescriptionAction.class.php';

Xoonips_Utils::denyGuestAccess();

// index tree global variable
$xoonipsURL = 'indexdescription.php';
$xoonipsIndexDescription = true;

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$indexId = intval($request->getParameter('index_id'));
$op = $request->getParameter('op');

if (null == $op) {
    $op = 'init';
}
// check request
if (!in_array($op, ['init', 'update', 'delete'])) {
    die('illegal request');
}
if (in_array($indexId, [0, 1])) {
    die('illegal request');
}

// set action map
$actionMap = [];
$actionMap['init_success'] = $mydirname.'_index_description.html';
$actionMap['update_error'] = 'redirect_header';
$actionMap['update_success'] = 'redirect_header';
$actionMap['delete_error'] = 'redirect_header';
$actionMap['delete_success'] = 'redirect_header';

if ('init' == $op) {
    include XOOPS_ROOT_PATH.'/header.php';
}

// do action
$action = new Xoonips_IndexDescriptionAction();
$action->doAction($request, $response);

// forward
$response->forward($actionMap);

if ('init' == $op) {
    include XOOPS_ROOT_PATH.'/footer.php';
}
