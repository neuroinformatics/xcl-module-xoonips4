<?php

require_once '../../../mainfile.php';

if (!defined('XOOPS_TRUST_PATH')) {
    die('set XOOPS_TRUST_PATH into mainfile.php');
}
$mydirpath = dirname(dirname(__FILE__));
require $mydirpath.'/mytrustdirname.php'; // set $mytrustdirname
require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/admin/maintenance_itemdelete.php';
