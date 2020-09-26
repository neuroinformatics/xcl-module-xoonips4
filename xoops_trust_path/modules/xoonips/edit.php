<?php

XCube_DelegateUtils::call('Xoonips.Edit.Access');

$xoopsOption['pagetype'] = 'user';
require_once __DIR__.'/class/core/Request.class.php';
require_once __DIR__.'/class/core/Response.class.php';
require_once __DIR__.'/class/action/EditAction.class.php';

// access check
Xoonips_Utils::denyGuestAccess();

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$op = $request->getParameter('op');
$item_id = intval($request->getParameter('item_id'));
if (null == $op) {
    $op = 'init';
}

// check request
if (!in_array($op, [
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
])) {
    die('illegal request');
}

if ('editIndex' == $op) {
    global $xoonipsItemId;
    $xoonipsItemId = $item_id;
    $xoonipsTreeCheckBox = true;
    $xoonipsCheckPrivateHandlerId = 'PrivateIndexCheckedHandler';
}

// set action map
$actionMap = [];
$actionMap['init_success'] = $mydirname.'_edit.html';
$actionMap['complete_success'] = $mydirname.'_edit.html';
$actionMap['addFieldGroup_success'] = $mydirname.'_edit.html';
$actionMap['deleteFieldGroup_success'] = $mydirname.'_edit.html';
$actionMap['uploadFile_success'] = $mydirname.'_upload_output.html';
$actionMap['deleteFile_success'] = $mydirname.'_edit.html';
$actionMap['searchUser_success'] = $mydirname.'_edit.html';
$actionMap['deleteUser_success'] = $mydirname.'_edit.html';
$actionMap['searchRelatedItem_success'] = $mydirname.'_edit.html';
$actionMap['deleteRelatedItem_success'] = $mydirname.'_edit.html';
$actionMap['back_success'] = $mydirname.'_edit.html';
$actionMap['confirm_success'] = $mydirname.'_edit_confirm.html';
$actionMap['confirm_error'] = $mydirname.'_edit.html';
$actionMap['save_success'] = $mydirname.'_common_msg_sub.html';
$actionMap['finish_success'] = 'redirect_header';
$actionMap['editIndex_success'] = $mydirname.'_itemindex_edit.html';
$actionMap['confirmIndex_success'] = $mydirname.'_itemindex_confirm.html';
$actionMap['saveIndex_success'] = 'redirect_header';
$actionMap['editOwners_success'] = $mydirname.'_itemowners_edit.html';
$actionMap['searchOwners_success'] = $mydirname.'_itemowners_edit.html';
$actionMap['deleteOwners_success'] = $mydirname.'_itemowners_edit.html';
$actionMap['saveOwners_success'] = 'redirect_header';
$actionMap['deleteConfirm_success'] = $mydirname.'_delete_confirm.html';
$actionMap['delete_success'] = 'redirect_header';

$checkOp = in_array($op, [
    'editIndex',
    'confirmIndex',
    'saveIndex',
    'editOwners',
    'searchOwners',
    'deleteOwners',
    'saveOwners',
    'deleteConfirm',
    'delete',
]);

if ($checkOp) {
    include XOOPS_ROOT_PATH.'/header.php';
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
    include XOOPS_ROOT_PATH.'/footer.php';
}
