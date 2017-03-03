<?php

error_reporting(0);
require_once 'class/core/Oaipmh.class.php';
require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/Request.class.php';

$oaipmh = new Xoonips_Oaipmh(Xoonips_Utils::getDirname(), Xoonips_Utils::getTrustDirname());

$request = new Xoonips_Request();
$args['verb'] = $request->getParameter('verb');
$args['metadataPrefix'] = $request->getParameter('metadataPrefix');
$args['set'] = $request->getParameter('set');
$args['from'] = $request->getParameter('from');
$args['until'] = $request->getParameter('until');
$args['identifier'] = $request->getParameter('identifier');
$args['resumptionToken'] = $request->getParameter('resumptionToken');

$oaipmh->exec($args);

