<?php

if (isset($_SERVER['PATH_INFO']))
	$_SERVER['PATH_INFO'] = '/image' . $_SERVER['PATH_INFO'];
$_GET['action'] = 'Image';

$root =& XCube_Root::getSingleton();
$root->mController->execute();

