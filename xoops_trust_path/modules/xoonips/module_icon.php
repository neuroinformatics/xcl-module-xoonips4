<?php

use Xoonips\Core\ImageUtils;

require_once __DIR__.'/include/common.inc.php';

$fname = 'module_icon.png';
$fpath = dirname(__FILE__).'/images/'.$fname;

ImageUtils::showImage($fpath, $fname);
