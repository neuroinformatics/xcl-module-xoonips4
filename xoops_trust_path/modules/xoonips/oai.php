<?php

error_reporting(0);
require_once __DIR__.'/class/core/Request.class.php';
require_once __DIR__.'/class/core/Oaipmh.class.php';

$oaipmh = new Xoonips_Oaipmh($mydirname, $mytrustdirname);

$request = new Xoonips_Request();
$args['verb'] = $request->getParameter('verb');
$args['metadataPrefix'] = $request->getParameter('metadataPrefix');
$args['set'] = $request->getParameter('set');
$args['from'] = $request->getParameter('from');
$args['until'] = $request->getParameter('until');
$args['identifier'] = $request->getParameter('identifier');
$args['resumptionToken'] = $request->getParameter('resumptionToken');
$args['setSpec'] = $request->getParameter('setSpec');

$oaipmh->exec($args);
