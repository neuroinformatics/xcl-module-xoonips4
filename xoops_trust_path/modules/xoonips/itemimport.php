<?php

require_once __DIR__.'/class/core/Request.class.php';
require_once __DIR__.'/class/core/Response.class.php';
require_once __DIR__.'/class/action/ItemImportAction.class.php';

Xoonips_Utils::denyGuestAccess();

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$op = $request->getParameter('op');
if (null == $op) {
    $op = 'init';
}

// check request
if (!in_array($op, ['init', 'import', 'importsave', 'log'])) {
    die('illegal request');
}

// set action map
$actionMap = [];
$actionMap['import_success'] = $mydirname.'_itemimport.html';
$actionMap['importsave_success'] = 'redirect_header';
$actionMap['log_success'] = $mydirname.'_itemimport_log.html';
$actionMap['logdetail_success'] = $mydirname.'_itemimport_logdetail.html';

include XOOPS_ROOT_PATH.'/header.php';

// do action
$action = new Xoonips_ItemImportAction();
$action->doAction($request, $response);

// forward
$response->forward($actionMap);

include XOOPS_ROOT_PATH.'/footer.php';
