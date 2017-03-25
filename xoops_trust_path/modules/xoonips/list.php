<?php

$xoopsOption['pagetype'] = 'user';

require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/Request.class.php';
require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/Response.class.php';
require_once 'class/action/ListAction.class.php';

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
$dirname = Xoonips_Utils::getDirname();
$actionMap['init_success'] = $dirname.'_list.html';
$actionMap['exportselect_success'] = $dirname.'_export_select.html';

include XOOPS_ROOT_PATH.'/header.php';

//do action
$action = new Xoonips_ListAction();
$action->doAction($request, $response);

// forward
$response->forward($actionMap);

include XOOPS_ROOT_PATH.'/footer.php';
