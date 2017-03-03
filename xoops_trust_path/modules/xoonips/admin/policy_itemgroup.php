<?php

require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/Request.class.php';
require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/Response.class.php';
require_once XOOPS_ROOT_PATH . '/include/cp_header.php';
require_once 'class/action/PolicyItemGroupAction.class.php';

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$op = $request->getParameter('op');
if ($op == null) {
	$op = 'init';
}

// check request
if (!in_array($op, array('init', 'register', 'registersave', 'edit', 'editsave', 'delete',
	'release', 'detailregister', 'detailregistersave', 'sorteditsave'))) {
	die('illegal request');
}

// set action map
$actionMap = array();
$actionMap = array();
$actionMap['init_success'] = 'policy_itemgroup.html';
$actionMap['register_success'] = 'policy_itemgroup_register.html';
$actionMap['registersave_success'] = 'redirect_header';
$actionMap['edit_success'] = 'policy_itemgroup_edit.html';
$actionMap['editsave_success'] = 'redirect_header';
$actionMap['delete_success'] = 'redirect_header';
$actionMap['release_success'] = 'redirect_header';
$actionMap['break_success'] = 'redirect_header';
$actionMap['detailregister_success'] = 'policy_itemgroup_detail_register.html';
$actionMap['detailregistersave_success'] = 'redirect_header';

include XOOPS_ROOT_PATH . '/header.php';

// do action
$action = new Xoonips_PolicyItemGroupAction();
$action->doAction($request, $response);

// forward
$response->forward($actionMap, true);

include XOOPS_ROOT_PATH . '/footer.php';

