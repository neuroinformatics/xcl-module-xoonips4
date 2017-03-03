<?php

if (!defined('XOOPS_TRUST_PATH')) {
    exit();
}

// define XOONIPS_TRUST_DIRNAME
define('XOONIPS_TRUST_DIRNAME', basename(dirname(__DIR__)));
define('XOONIPS_TRUST_PATH', XOOPS_TRUST_PATH.'/modules/'.XOONIPS_TRUST_DIRNAME);

// item certify state
define('XOONIPS_NOT_CERTIFIED', 0);
define('XOONIPS_CERTIFY_REQUIRED', 1);
define('XOONIPS_CERTIFIED', 2);
define('XOONIPS_WITHDRAW_REQUIRED', 3);

// index level
define('XOONIPS_OL_PUBLIC', 1);
define('XOONIPS_OL_GROUP_ONLY', 2);
define('XOONIPS_OL_PRIVATE', 3);
