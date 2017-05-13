<?php

// ignore protector check
define('PROTECTOR_SKIP_DOS_CHECK', 1);
define('PROTECTOR_SKIP_FILESCHECKER', 1);

$xoopsOption['nocommon'] = true;
require_once '../../mainfile.php';

$mydirname = basename(__DIR__);
require __DIR__.'/mytrustdirname.php'; // set $mytrustdirname
require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/'.basename(__FILE__);
