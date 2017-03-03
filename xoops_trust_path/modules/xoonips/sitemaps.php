<?php

require_once 'class/core/Request.class.php';
require_once 'class/core/Sitemaps.class.php';

$id = null;
$request = new Xoonips_Request();
$id = $request->getParameter('id');

$sitemaps = new Xoonips_Sitemaps(Xoonips_Utils::getDirname(), Xoonips_Utils::getTrustDirname());
$sitemaps->output($id);
exit;
