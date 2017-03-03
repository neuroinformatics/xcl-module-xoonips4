<?php

$load_header = true;
if (array_key_exists('PATH_INFO', $_SERVER) && preg_match('/^\/([a-z0-9]+)\//', $_SERVER['PATH_INFO'], $matches)) {
	$actions = array('ajax', 'css', 'image', 'js');
	$load_header = !in_array($matches[1], $actions);
}
if ($load_header)
	require_once XOOPS_ROOT_PATH . '/header.php';

$root =& XCube_Root::getSingleton();
$root->mController->execute();

if ($load_header)
	require_once XOOPS_ROOT_PATH . '/footer.php';
