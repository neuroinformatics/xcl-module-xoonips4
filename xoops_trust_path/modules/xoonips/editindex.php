<?php

$xoopsOption['pagetype'] = 'user';
require_once __DIR__.'/class/core/Request.class.php';
require_once __DIR__.'/class/core/Response.class.php';
require_once __DIR__.'/class/action/EditIndexAction.class.php';

Xoonips_Utils::denyGuestAccess();

// index tree global variable
$xoonipsURL = 'editindex.php';
$xoonipsEditIndex = true;

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$indexId = $request->getParameter('index_id');
$op = $request->getParameter('op');

if ($op == null) {
    $op = 'init';
}
// check request
if (!in_array($op, array('init', 'save', 'indexEdit', 'update',
    'indexMove', 'move', 'indexDelete', 'delete', 'finish', ))) {
    die('illegal request');
}
if ($indexId == 1) {
    die('illegal request');
}

// set action map
$actionMap = array();
$actionMap['init_success'] = $mydirname.'_index_list.html';
$actionMap['save_success'] = 'redirect_header';
$actionMap['indexEdit_success'] = $mydirname.'_index_edit.html';
$actionMap['update_error'] = $mydirname.'_index_edit.html';
$actionMap['update_success'] = $mydirname.'_common_msg_sub.html';
$actionMap['indexMove_success'] = $mydirname.'_index_move.html';
$actionMap['move_success'] = $mydirname.'_common_msg_sub.html';
$actionMap['indexDelete_success'] = $mydirname.'_index_delete.html';
$actionMap['delete_success'] = $mydirname.'_common_msg_sub.html';
$actionMap['finish_success'] = 'redirect_header';

if ($op == 'init') {
    include XOOPS_ROOT_PATH.'/header.php';
}

// do action
$action = new Xoonips_EditIndexAction();
$action->doAction($request, $response);

// forward
if ($op == 'init' || $op == 'finish' || $op == 'save') {
    $response->forward($actionMap);
} else {
    $response->forwardLayeredWindow($actionMap);
}

if ($op == 'init') {
    include XOOPS_ROOT_PATH.'/footer.php';
}
