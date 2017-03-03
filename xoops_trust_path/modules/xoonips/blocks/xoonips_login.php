<?php

if (!defined('XOOPS_ROOT_PATH')) exit();

// xoonips login block
function b_xoonips_login_show($options) {
	global $xoopsUser;

	// hide block during site login
	if (is_object($xoopsUser)) {
		return false;
	}

	$dirname = empty($options[0]) ? 'xoonips' : $options[0];

	// get xoops configurations
	$myxoopsConfig = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF);
	$usercookie = $myxoopsConfig['usercookie'];
	$use_ssl = $myxoopsConfig['use_ssl'];
	$sslloginlink = $myxoopsConfig['sslloginlink'];

	// set variables
	$block = array(
		'unamevalue' => ($usercookie != '' && isset($_COOKIE[$usercookie])) ? $_COOKIE[$usercookie] : '',
		'dirname' => $dirname);
	if ($use_ssl == 1 && $sslloginlink != '') {
		$block['use_ssl'] = $use_ssl;
		$block['sslloginlink'] = $sslloginlink;
	}
	return $block;
}

