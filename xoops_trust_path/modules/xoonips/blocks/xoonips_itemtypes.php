<?php

// xoonips itemtypes block
function b_xoonips_itemtypes_show($options)
{
    global $xoopsUser;

    $dirname = empty($options[0]) ? 'xoonips' : $options[0];
    $module_handler = &xoops_gethandler('module');
    $module = &$module_handler->getByDirname($dirname);
    if (!is_object($module)) {
        exit('Access Denied');
    }
    $trustDirname = $module->getVar('trust_dirname');

    require_once dirname(__DIR__).'/class/core/Request.class.php';
    require_once dirname(__DIR__).'/class/core/Response.class.php';
    require_once dirname(__DIR__).'/class/action/ItemTypeAction.class.php';

    // backup form values
    $keys = array();
    foreach (array('op', 'action') as $key) {
        if (array_key_exists($key, $_GET)) {
            $keys[$key]['GET'] = $_GET[$key];
            unset($_GET[$key]);
        }
        if (array_key_exists($key, $_POST)) {
            $keys[$key]['POST'] = $_POST[$key];
            unset($_POST[$key]);
        }
    }

    // get installed itemtypes
    $block = array();
    $request = new Xoonips_Request();
    $response = new Xoonips_Response();
    $action = new Xoonips_ItemTypeAction($dirname);
    $action->doAction($request, $response);

    // restore form values
    foreach ($keys as $key => $value) {
        if (array_key_exists('GET', $value)) {
            $_GET[$key] = $value['GET'];
        }
        if (array_key_exists('POST', $value)) {
            $_POST[$key] = $value['POST'];
        }
    }

    return $response->getViewData();
}
