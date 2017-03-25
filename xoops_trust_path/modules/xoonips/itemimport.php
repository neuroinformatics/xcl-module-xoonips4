<?php

require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/Request.class.php';
require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/Response.class.php';
require_once 'class/action/ItemImportAction.class.php';

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$op = $request->getParameter('op');
if ($op == null) {
    $op = 'init';
}

// check request
if (!in_array($op, array('init', 'import', 'importsave', 'log'))) {
    die('illegal request');
}

// set action map
$actionMap = array();
$dirname = Xoonips_Utils::getDirname();
$actionMap['import_success'] = $dirname.'_itemimport.html';
$actionMap['importsave_success'] = 'redirect_header';
$actionMap['log_success'] = $dirname.'_itemimport_log.html';
$actionMap['logdetail_success'] = $dirname.'_itemimport_logdetail.html';

include XOOPS_ROOT_PATH.'/header.php';

// do action
$action = new Xoonips_ItemImportAction();
$action->doAction($request, $response);

// forward
$response->forward($actionMap);

include XOOPS_ROOT_PATH.'/footer.php';
