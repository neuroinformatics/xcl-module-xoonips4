<?php

XCube_DelegateUtils::call('Xoonips.Register.Access');

$xoopsOption['pagetype'] = 'user';
require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/Request.class.php';
require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/Response.class.php';
require_once 'class/action/RegisterAction.class.php';

// access check
Xoonips_Utils::denyGuestAccess();

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$op = $request->getParameter('op');
if ($op == null) {
    $op = 'init';
}

// check request
if (!in_array($op, array(
    'init',
    'selectItemtype',
    'register',
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
))) {
    die('illegal request');
}

// set action map
$actionMap = array();
$dirname = Xoonips_Utils::getDirname();
$actionMap['init_success'] = $dirname.'_register_top.html';
$actionMap['selectItemtype_success'] = $dirname.'_register_select_itemtype.html';
$actionMap['register_success'] = $dirname.'_register.html';
$actionMap['complete_success'] = $dirname.'_register.html';
$actionMap['addFieldGroup_success'] = $dirname.'_register.html';
$actionMap['deleteFieldGroup_success'] = $dirname.'_register.html';
$actionMap['uploadFile_success'] = $dirname.'_upload_output.html';
$actionMap['deleteFile_success'] = $dirname.'_register.html';
$actionMap['searchUser_success'] = $dirname.'_register.html';
$actionMap['deleteUser_success'] = $dirname.'_register.html';
$actionMap['searchRelatedItem_success'] = $dirname.'_register.html';
$actionMap['deleteRelatedItem_success'] = $dirname.'_register.html';
$actionMap['back_success'] = $dirname.'_register.html';
$actionMap['confirm_success'] = $dirname.'_register_confirm.html';
$actionMap['confirm_error'] = $dirname.'_register.html';
$actionMap['save_success'] = $dirname.'_common_msg_sub.html';
$actionMap['finish_success'] = 'redirect_header';

if ($op == 'init') {
    include XOOPS_ROOT_PATH.'/header.php';
}

// do action
$action = new Xoonips_RegisterAction();
$action->doAction($request, $response);

// forward
if ($op == 'init') {
    $response->forward($actionMap);
} else {
    $response->forwardLayeredWindow($actionMap);
}

if ($op == 'init') {
    include XOOPS_ROOT_PATH.'/footer.php';
}
