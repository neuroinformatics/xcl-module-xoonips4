<?php

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

$mydirname = basename(dirname(dirname(__FILE__)));
require XOOPS_ROOT_PATH.'/modules/'.$mydirname.'/mytrustdirname.php'; // set $mytrustdirname

require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/preload/AssetPreload.class.php';
Xoonips_AssetPreloadBase::prepare($mydirname, $mytrustdirname);

require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/preload/UserPreload.class.php';
Xoonips_UserPreloadBase::prepare($mydirname, $mytrustdirname);
