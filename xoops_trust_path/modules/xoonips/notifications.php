<?php

$xoopsOption['pagetype'] = 'notification';

if (!is_object($xoopsUser)) {
    redirect_header(XOOPS_URL.'/user.php', 3, _NOPERM);
    exit();
}

$uid = $xoopsUser->getVar('uid');
$xoopsOption['template_main'] = $mydirname.'_notifications.html';
include XOOPS_ROOT_PATH.'/header.php';
$xoopsTpl->assign('lang_notifications', _MD_XOONIPS_ACCOUNT_NOTIFICATIONS);
$xoopsTpl->assign($mydirname.'_editprofile_url', XOOPS_URL.'/edituser.php?uid='.$uid);
include XOOPS_ROOT_PATH.'/footer.php';
