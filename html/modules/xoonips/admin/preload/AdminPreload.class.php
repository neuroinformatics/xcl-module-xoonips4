<?php

if (!defined('XOOPS_TRUST_PATH')) {
    die('set XOOPS_TRUST_PATH into mainfile.php');
}
$mydirname = basename(dirname(dirname(__DIR__)));
require dirname(dirname(__DIR__)).'/mytrustdirname.php'; // set $mytrustdirname
require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/admin/preload/'.basename(__FILE__);
$cname = ucfirst($mytrustdirname).'_AdminPreloadBase';
$cname::prepare($mydirname, $mytrustdirname);
