<?php

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

$mydirname = basename(dirname(dirname(dirname(__FILE__))));
require dirname(dirname(dirname(__FILE__))).'/mytrustdirname.php';
require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/admin/preload/AdminPreload.class.php';
call_user_func_array(ucfirst($mytrustdirname).'_AdminPreloadBase::prepare', array($mydirname, $mytrustdirname));
