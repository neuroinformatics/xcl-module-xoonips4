<?php

use Xoonips\Core\XoopsUtils;

// xoonips login block
function b_xoonips_login_show($options)
{
    // hide block during site login
    $uid = XoopsUtils::getUid();
    if (XoopsUtils::UID_GUEST != $uid) {
        return false;
    }

    $dirname = empty($options[0]) ? 'xoonips' : $options[0];

    // get xoops configurations
    $usercookie = XoopsUtils::getXoopsConfig('usercookie');
    $use_ssl = XoopsUtils::getXoopsConfig('use_ssl');
    $sslloginlink = XoopsUtils::getXoopsConfig('sslloginlink');

    // set variables
    $block = [
        'unamevalue' => ('' != $usercookie && isset($_COOKIE[$usercookie])) ? $_COOKIE[$usercookie] : '',
        'dirname' => $dirname,
    ];
    if (1 == $use_ssl && '' != $sslloginlink) {
        $block['use_ssl'] = $use_ssl;
        $block['sslloginlink'] = $sslloginlink;
    }

    return $block;
}
