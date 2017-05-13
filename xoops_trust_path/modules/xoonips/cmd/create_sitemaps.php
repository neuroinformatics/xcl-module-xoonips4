<?php

use Xoonips\Core\XoopsUtils;

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_METHOD'] = 'POST';
define('XOOPS_XMLRPC', 1);

$mytrustdirpath = dirname(__DIR__);
$mytrustdirname = basename($mytrustdirpath);

$ping = false;
$opts = getopt('p');
if (array_key_exists('p', $opts)) {
    $ping = true;
}

$mainfile = dirname(dirname(dirname($mytrustdirpath))).'/html/mainfile.php';
if (basename($mainfile) != 'mainfile.php' || !file_exists($mainfile)) {
    printUsage('mainfile.php not found');
}
require_once $mainfile;
$dirnames = XoopsUtils::getDirnameListByTrustDirname($mytrustdirname);
$mydirname = $dirnames[0];

// load required classes
require_once $mytrustdirpath.'/class/core/Sitemaps.class.php';

$sitemaps = new Xoonips_Sitemaps($mydirname, $mytrustdirname);
if (!$sitemaps->create()) {
    printUsage('sitemaps create failed.');
}

// ping
if ($ping) {
    if (!$sitemaps->ping()) {
        printUsage('sitemaps ping failed.');
    }
}

exit;

function printUsage($message)
{
    if ($message) {
        fprintf(STDERR, 'Error: '.$message.PHP_EOL.PHP_EOL);
    }
    exit(2);
}
