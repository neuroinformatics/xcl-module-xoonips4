<?php

if (!defined('XOOPS_TRUST_PATH')) {
    die('set XOOPS_TRUST_PATH into mainfile.php');
}
$mydirname = basename(dirname(__DIR__));
require dirname(__DIR__).'/mytrustdirname.php'; // set $mytrustdirname
require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/preload/'.basename(__FILE__);
$cname = ucfirst($mytrustdirname).'_AssetPreloadBase';
$cname::prepare($mydirname, $mytrustdirname);

require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/preload/UserPreload.class.php';
$cname = ucfirst($mytrustdirname).'_UserPreloadBase';
$cname::prepare($mydirname, $mytrustdirname);
