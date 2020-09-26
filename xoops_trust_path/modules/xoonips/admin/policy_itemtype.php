<?php

require_once dirname(__DIR__).'/class/core/Request.class.php';
require_once dirname(__DIR__).'/class/core/Response.class.php';
require_once XOOPS_ROOT_PATH.'/include/cp_header.php';
require_once __DIR__.'/class/action/PolicyItemTypeAction.class.php';

$request = new Xoonips_Request();
$response = new Xoonips_Response();
$op = $request->getParameter('op');
if (null == $op) {
    $op = 'init';
}

// check request
if (!in_array($op, ['init', 'register', 'registersave', 'edit', 'editsave', 'copy', 'import', 'importsave', 'delete',
    'release', 'break', 'export', 'exports',
    'complement', 'complementsave',
    'groupregister', 'groupregistersave', 'sorteditsave',
])) {
    die('illegal request');
}

// set action map
$actionMap = [];
$actionMap['init_success'] = 'policy_itemtype.html';
$actionMap['register_success'] = 'policy_itemtype_register.html';
$actionMap['registersave_success'] = 'redirect_header';
$actionMap['edit_success'] = 'policy_itemtype_edit.html';
$actionMap['editsave_success'] = 'redirect_header';
$actionMap['copy_success'] = 'redirect_header';
$actionMap['import_success'] = 'policy_itemtype_import.html';
$actionMap['export_success'] = 'redirect_header';
$actionMap['exports_success'] = 'redirect_header';
$actionMap['delete_success'] = 'redirect_header';
$actionMap['release_success'] = 'redirect_header';
$actionMap['break_success'] = 'redirect_header';
$actionMap['complement_success'] = 'policy_itemtype_complement.html';
$actionMap['complementsave_success'] = 'redirect_header';
$actionMap['groupregister_success'] = 'policy_itemtype_group_register.html';
$actionMap['groupregistersave_success'] = 'redirect_header';

include XOOPS_ROOT_PATH.'/header.php';

// do action
$action = new Xoonips_PolicyItemTypeAction();
$action->doAction($request, $response);

// forward
$response->forward($actionMap, true);

include XOOPS_ROOT_PATH.'/footer.php';
