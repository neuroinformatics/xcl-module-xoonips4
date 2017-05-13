<?php

require_once __DIR__.'/class/core/Request.class.php';
require_once __DIR__.'/class/core/Sitemaps.class.php';

$id = null;
$request = new Xoonips_Request();
$id = $request->getParameter('id');

$sitemaps = new Xoonips_Sitemaps($mydirname, $mytrustdirname);
$sitemaps->output($id);
