<?php

if (array_key_exists('PATH_INFO', $_SERVER) && preg_match('/^\/([a-z0-9]+)\//', $_SERVER['PATH_INFO'], $matches)) {
    if (in_array($matches[1], ['css', 'image', 'js'])) {
        // ignore protector check
        define('PROTECTOR_SKIP_DOS_CHECK', 1);
        define('PROTECTOR_SKIP_FILESCHECKER', 1);
    }
}

require_once '../../mainfile.php';

if (!defined('XOOPS_TRUST_PATH')) {
    die('set XOOPS_TRUST_PATH into mainfile.php');
}
$mydirname = basename(__DIR__);
require __DIR__.'/mytrustdirname.php'; // set $mytrustdirname
require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/'.basename(__FILE__);
