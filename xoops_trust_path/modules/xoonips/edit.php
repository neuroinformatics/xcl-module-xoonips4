<?php

XCube_DelegateUtils::call("Xoonips.Edit.Access");

$xoopsOption['pagetype'] = 'user';
require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/Request.class.php';
require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/Response.class.php';
require_once 'class/action/EditAction.class.php';

// access check
Xoonips_Utils::denyGuestAccess();

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$op = $request->getParameter('op');
$item_id = $request->getParameter('item_id');
if ($op == null) {
	$op = 'init';
}

// check request
if (!in_array($op, array(
	'init',
	'complete',
	'addFieldGroup',
	'deleteFieldGroup',
	'uploadFile',
	'deleteFile',
	'searchUser',
	'deleteUser',
	'searchRelatedItem',
	'deleteRelatedItem',
	'back',
	'confirm',
	'save',
	'finish',
	'editIndex',
	'confirmIndex',
	'saveIndex',
	'editOwners',
	'searchOwners',
	'deleteOwners',
	'saveOwners',
	'deleteConfirm',
	'delete',
))) {
	die('illegal request');
}

if ($op=='editIndex'){
	global $xoonipsItemId;
	$xoonipsItemId = $item_id;
	$xoonipsTreeCheckBox = true;
	$xoonipsCheckPrivateHandlerId = 'PrivateIndexCheckedHandler';
}

// set action map
$actionMap = array();
$dirname = Xoonips_Utils::getDirname();
$actionMap['init_success'] = $dirname . '_edit.html';
$actionMap['complete_success'] = $dirname . '_edit.html';
$actionMap['addFieldGroup_success'] = $dirname . '_edit.html';
$actionMap['deleteFieldGroup_success'] = $dirname . '_edit.html';
$actionMap['uploadFile_success'] = $dirname . '_upload_output.html';
$actionMap['deleteFile_success'] = $dirname . '_edit.html';
$actionMap['searchUser_success'] = $dirname . '_edit.html';
$actionMap['deleteUser_success'] = $dirname . '_edit.html';
$actionMap['searchRelatedItem_success'] = $dirname . '_edit.html';
$actionMap['deleteRelatedItem_success'] = $dirname . '_edit.html';
$actionMap['back_success'] = $dirname . '_edit.html';
$actionMap['confirm_success'] = $dirname . '_edit_confirm.html';
$actionMap['confirm_error'] = $dirname . '_edit.html';
$actionMap['save_success'] = $dirname . '_common_msg_sub.html';
$actionMap['finish_success'] = 'redirect_header';
$actionMap['editIndex_success'] = $dirname . '_itemindex_edit.html';
$actionMap['confirmIndex_success'] = $dirname . '_itemindex_confirm.html';
$actionMap['saveIndex_success'] = 'redirect_header';
$actionMap['editOwners_success'] = $dirname . '_itemowners_edit.html';
$actionMap['searchOwners_success'] = $dirname . '_itemowners_edit.html';
$actionMap['deleteOwners_success'] = $dirname . '_itemowners_edit.html';
$actionMap['saveOwners_success'] = 'redirect_header';
$actionMap['deleteConfirm_success'] = $dirname . '_delete_confirm.html';
$actionMap['delete_success'] ='redirect_header';

$checkOp = in_array($op, array(
    'editIndex',
    'confirmIndex',
    'saveIndex',
    'editOwners',
    'searchOwners',
    'deleteOwners',
    'saveOwners',
    'deleteConfirm',
    'delete'
));

if ($checkOp) {
	include XOOPS_ROOT_PATH . '/header.php';
}

// do action
$action = new Xoonips_EditAction();
$action->doAction($request, $response);

// forward
if ($checkOp) {
	$response->forward($actionMap);
} else {
	$response->forwardLayeredWindow($actionMap);
}

if ($checkOp) {
	include XOOPS_ROOT_PATH . '/footer.php';
}
