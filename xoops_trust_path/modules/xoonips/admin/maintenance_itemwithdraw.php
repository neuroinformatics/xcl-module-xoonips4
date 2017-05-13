<?php

$xoopsOption['pagetype'] = 'user';
require_once dirname(__DIR__).'/class/core/Request.class.php';
require_once dirname(__DIR__).'/class/core/Response.class.php';
require_once XOOPS_ROOT_PATH.'/include/cp_header.php';
require_once __DIR__.'/class/action/MaintenanceItemWithdrawAction.class.php';

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$op = $request->getParameter('op');
if ($op == null) {
    $op = 'init';
}

// check request
if (!in_array($op, array('init', 'confirm', 'execute'))) {
    die('illegal request');
}

// set action map
$actionMap = array();
$actionMap['init_success'] = 'maintenance_itemwithdraw.html';
$actionMap['index_success'] = 'maintenance_itemwithdraw.html';
$actionMap['confirm_success'] = 'maintenance_itemwithdraw_confirm.html';
$actionMap['confirm_failure'] = 'redirect_header';
$actionMap['execute_success'] = 'maintenance_item_execute.html';
$actionMap['execute_failure'] = 'redirect_header';

include XOOPS_ROOT_PATH.'/header.php';

// do action
$action = new Xoonips_MaintenanceItemWithdrawAction();
$action->doAction($request, $response);

// forward
$response->forward($actionMap, true);

include XOOPS_ROOT_PATH.'/footer.php';
