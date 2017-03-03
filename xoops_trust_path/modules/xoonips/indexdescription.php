<?php

$xoopsOption['pagetype'] = 'user';
require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/Request.class.php';
require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/Response.class.php';
require_once 'class/action/IndexDescriptionAction.class.php';

Xoonips_Utils::denyGuestAccess();

// index tree global variable 
$xoonipsURL = 'indexdescription.php';
$xoonipsIndexDescription = true;

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$indexId = $request->getParameter('index_id');
$op = $request->getParameter('op');

if ($op == null) {
	$op = 'init';
}
// check request
if (!in_array($op, array('init', 'update', 'delete'))) {
    die('illegal request');
}
if ($indexId == 1) {
    die('illegal request');
}

// set action map
$actionMap = array();
$dirname = Xoonips_Utils::getDirname();
$actionMap['init_success'] = $dirname . '_index_description.html';
$actionMap['update_error'] = 'redirect_header';
$actionMap['update_success'] = 'redirect_header';
$actionMap['delete_error'] = 'redirect_header';
$actionMap['delete_success'] = 'redirect_header';

if ($op=='init') {
    include XOOPS_ROOT_PATH . '/header.php';
}

// do action
$action = new Xoonips_IndexDescriptionAction();
$action->doAction($request, $response);

// forward
$response->forward($actionMap);

if ($op=='init'){
    include XOOPS_ROOT_PATH . '/footer.php';
}

