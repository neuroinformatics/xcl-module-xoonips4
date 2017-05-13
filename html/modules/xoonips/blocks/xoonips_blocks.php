<?php

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

if (!defined('XOOPS_TRUST_PATH')) {
    die('set XOOPS_TRUST_PATH into mainfile.php');
}

$mydirpath = dirname(__DIR__);
require $mydirpath.'/mytrustdirname.php'; // set $mytrustdirname
require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/blocks/xoonips_blocks.php';
