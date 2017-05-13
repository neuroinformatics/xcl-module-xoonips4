<?php

require_once '../../../mainfile.php';

if (!defined('XOOPS_TRUST_PATH')) {
    die('set XOOPS_TRUST_PATH into mainfile.php');
}
$mydirname = basename(dirname(__DIR__));
require dirname(__DIR__).'/mytrustdirname.php'; // set $mytrustdirname
require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/admin/'.basename(__FILE__);
