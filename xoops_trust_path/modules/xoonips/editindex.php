<?php

$xoopsOption['pagetype'] = 'user';
require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/Request.class.php';
require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/Response.class.php';
require_once 'class/action/EditIndexAction.class.php';

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
    'indexMove', 'move', 'indexDelete', 'delete', 'finish'))) {
    die('illegal request');
}
if ($indexId == 1) {
    die('illegal request');
}

// set action map
$actionMap = array();
$dirname = Xoonips_Utils::getDirname();
$actionMap['init_success'] = $dirname . '_index_list.html';
$actionMap['save_success'] = 'redirect_header';
$actionMap['indexEdit_success'] = $dirname . '_index_edit.html';
$actionMap['update_error'] = $dirname . '_index_edit.html';
$actionMap['update_success'] = $dirname . '_common_msg_sub.html';
$actionMap['indexMove_success'] = $dirname . '_index_move.html';
$actionMap['move_success'] = $dirname . '_common_msg_sub.html';
$actionMap['indexDelete_success'] = $dirname . '_index_delete.html';
$actionMap['delete_success'] = $dirname . '_common_msg_sub.html';
$actionMap['finish_success'] = 'redirect_header';

if ($op=='init'){
    include XOOPS_ROOT_PATH . '/header.php';
}

// do action
$action = new Xoonips_EditIndexAction();
$action->doAction($request, $response);

// forward
if ($op=='init' || $op=='finish' || $op=='save'){
    $response->forward($actionMap);
} else {
	$response->forwardLayeredWindow($actionMap);
}

if ($op=='init'){
    include XOOPS_ROOT_PATH . '/footer.php';
}

