<?php

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

require_once XOONIPS_TRUST_PATH.'/class/core/BeanFactory.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/Workflow.class.php';
require_once XOONIPS_TRUST_PATH.'/class/Enum.class.php';

// xoonips usermenu block
function b_xoonips_user_show($options)
{
    global $xoopsUser;

    $dirname = empty($options[0]) ? 'xoonips' : $options[0];

    // hide block if user is guest
    if (!is_object($xoopsUser)) {
        return false;
    }

    $uid = $xoopsUser->getVar('uid');

    $block = array();

    // get xoonips module id
    $module_handler = &xoops_gethandler('module');
    $module = &$module_handler->getByDirname($dirname);
    if (!is_object($module)) {
        exit('Access Denied');
    }
    $trustDirname = $module->getVar('trust_dirname');

    // get xoonips user information
    $userBean = Xoonips_BeanFactory::getBean('UsersBean', $dirname);
    $userInfo = $userBean->getUserBasicInfo($uid);
    if (!$userInfo) {
        // not xoonips user
        return false;
    }
    $is_certified = $userInfo['level'];
    if ($is_certified < Xoonips_Enum::USER_CERTIFIED) {
        // user is not certified
        return false;
    }
    $uname = $xoopsUser->getVar('uname', 's');
    $is_admin = $userBean->isModerator($uid);

    // get count of private messages
    $pm_handler = &xoops_gethandler('privmessage');
    $criteria = new CriteriaCompo(new Criteria('read_msg', 0));
    $criteria->add(new Criteria('to_userid', $uid));
    $new_messages = $pm_handler->getCount($criteria);

    // check workflow status
    $workflow_dirname = Xoonips_Workflow::getDirname();
    $workflow_isapprovaluser = Xoonips_Workflow::isApprover($uid);
    $workflow_myitemcount = Xoonips_Workflow::countInProgressItems($uid);

    // assign block template variables
    $block = array(
        'is_su' => isset($_SESSION[$dirname.'_old_uid']),
        'uid' => $uid,
        'new_messages' => $new_messages,
        'workflow_dirname' => $workflow_dirname,
        'workflow_isapprovaluser' => $workflow_isapprovaluser,
        'workflow_myitemcount' => $workflow_myitemcount,
        'lang_su_end' => sprintf(_MB_XOONIPS_USER_SU_END, $uname),
        'xoonips_isadmin' => $is_admin,
        'dirname' => $dirname, );

    return $block;
}
