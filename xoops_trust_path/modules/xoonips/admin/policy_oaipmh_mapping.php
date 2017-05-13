<?php

require_once dirname(__DIR__).'/class/core/Request.class.php';
require_once dirname(__DIR__).'/class/core/Response.class.php';
require_once XOOPS_ROOT_PATH.'/include/cp_header.php';
require_once __DIR__.'/class/action/PolicyOaipmhMappingAction.class.php';

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$op = $request->getParameter('op');
if ($op == null) {
    $op = 'init';
}

// check request
if (!in_array($op, array('init', 'change', 'join', 'add', 'delete', 'autocreate', 'update'))) {
    die('illegal request');
}

// set action map
$actionMap = array();
$actionMap['init_success'] = 'policy_oaipmh_mapping.html';
$actionMap['change_success'] = 'policy_oaipmh_mapping.html';
$actionMap['join_success'] = 'policy_oaipmh_mapping.html';
$actionMap['add_success'] = 'policy_oaipmh_mapping.html';
$actionMap['delete_success'] = 'policy_oaipmh_mapping.html';
$actionMap['autocreate_success'] = 'redirect_header';
$actionMap['update_success'] = 'redirect_header';

include XOOPS_ROOT_PATH.'/header.php';

// do action
$action = new Xoonips_PolicyOaipmhMappingAction();
$action->doAction($request, $response);

// forward
$response->forward($actionMap, true);

include XOOPS_ROOT_PATH.'/footer.php';
