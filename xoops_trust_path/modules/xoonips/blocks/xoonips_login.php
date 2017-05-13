<?php

use Xoonips\Core\XoopsUtils;

// xoonips login block
function b_xoonips_login_show($options)
{
    // hide block during site login
    $uid = XoopsUtils::getUid();
    if ($uid != XoopsUtils::UID_GUEST) {
        return false;
    }

    $dirname = empty($options[0]) ? 'xoonips' : $options[0];

    // get xoops configurations
    $usercookie = XoopsUtils::getXoopsConfig('usercookie');
    $use_ssl = XoopsUtils::getXoopsConfig('use_ssl');
    $sslloginlink = XoopsUtils::getXoopsConfig('sslloginlink');

    // set variables
    $block = array(
        'unamevalue' => ($usercookie != '' && isset($_COOKIE[$usercookie])) ? $_COOKIE[$usercookie] : '',
        'dirname' => $dirname,
    );
    if ($use_ssl == 1 && $sslloginlink != '') {
        $block['use_ssl'] = $use_ssl;
        $block['sslloginlink'] = $sslloginlink;
    }

    return $block;
}
