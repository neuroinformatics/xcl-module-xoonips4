<?php

require_once XOONIPS_TRUST_PATH.'/class/core/Workflow.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/WorkflowClientBase.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/BeanFactory.class.php';
require_once dirname(dirname(__FILE__)).'/core/User.class.php';

class Xoonips_WorkflowClientUser extends Xoonips_WorkflowClientBase
{
    public $dataname = Xoonips_Enum::WORKFLOW_USER;

    public function doCertify($uid, $comment)
    {
        $xoopsUser = new XoopsUser($uid);
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $user = $userBean->getUserBasicInfo($uid);
        if (!$user) {
            return;
        }
        $myxoopsConfig = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF);
        $user_certify_date = Xoonips_Utils::getXooNIpsConfig($this->dirname, 'user_certify_date');
        $deleteUserInfo = new XoopsUser($uid);
        if ($user_certify_date > 0) {
            $time = (time() - $user['user_regdate']) / (24 * 60 * 60);
            if ($time > $user_certify_date) {
                // certify time out
                if (!$userBean->deleteUsers($uid)) {
                    return false;
                }
                if (!$userBean->deleteGroupsUsersByUid($uid)) {
                    return false;
                }
                XCube_DelegateUtils::call('Module.Xoonips.Event.User.Reject', $xoopsUser);
                XCube_DelegateUtils::call('Module.Xoonips.Event.User.Delete', $xoopsUser);
                // send mail to user
                $xoopsMailer = &getMailer();
                $xoopsMailer->useMail();
                $xoopsMailer->setTemplateDir(Xoonips_Utils::mailTemplateDir($this->dirname, $this->trustDirname));
                $xoopsMailer->setTemplate('user_certify_timeout.tpl');
                $xoopsMailer->assign('USERNAME', $user['uname']);
                $xoopsMailer->assign('SITENAME', $myxoopsConfig['sitename']);
                $xoopsMailer->assign('ADMINMAIL', $myxoopsConfig['adminmail']);
                $xoopsMailer->assign('SITEURL', XOOPS_URL.'/');
                $xoopsMailer->setToUsers($deleteUserInfo);
                $xoopsMailer->setFromEmail($myxoopsConfig['adminmail']);
                $xoopsMailer->setFromName($myxoopsConfig['sitename']);
                $xoopsMailer->setSubject(_MD_XOONIPS_MESSAGE_ACTIVATE_TIMEOUT);
                $xoopsMailer->send();
                Xoonips_Workflow::deleteItem($this->dirname, $this->dataname, $uid);
                redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_MESSAGE_ACTIVATE_TIMEOUT);

                return false;
            }
        }
        if (!$userBean->certifyUser($user)) {
            return false;
        }
        $sendToUsers = Xoonips_Workflow::getAllApproverUserIds($this->dirname, $this->dataname, $uid);
        $this->notification->accountCertified($uid, $sendToUsers, $comment);
        //enent_log
        XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', $xoopsUser);

        //send mail to user
        $xoopsMailer = &getMailer();
        $xoopsMailer->useMail();
        $xoopsMailer->setTemplateDir(Xoonips_Utils::mailTemplateDir($this->dirname, $this->trustDirname));
        $xoopsMailer->setTemplate('user_account_certified_notify_for_user.tpl');
        $xoopsMailer->assign('USER_UNAME', $user['uname']);
        $xoopsMailer->assign('USER_EMAIL', $user['email']);
        $xoopsMailer->assign('USER_DETAIL_URL', XOOPS_URL.'/userinfo.php?uid='.$uid);
        if (isset($_SESSION['xoopsUserId'])) {
            $certifyUserInfo = $userBean->getUserBasicInfo($_SESSION['xoopsUserId']);
            $certifyUser = $certifyUserInfo['uname'];
        } else {
            $certifyUser = '';
        }
        $xoopsMailer->assign('CERTIFY_USER', $certifyUser);
        $xoopsMailer->assign('SITENAME', $myxoopsConfig['sitename']);
        $xoopsMailer->assign('SITEURL', XOOPS_URL.'/');
        $xoopsMailer->assign('ADMINMAIL', $myxoopsConfig['adminmail']);
        $xoopsMailer->setToUsers(new XoopsUser($uid));
        $xoopsMailer->setFromEmail($myxoopsConfig['adminmail']);
        $xoopsMailer->setFromName($myxoopsConfig['sitename']);
        $xoopsMailer->setSubject(_MD_XOONIPS_MESSAGE_ACCOUNT_CERTIFIED_NOTIFYSBJ);
        $xoopsMailer->send();
    }

    public function doProgress($uid)
    {
        $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $this->dataname, $uid);
        $this->notification->accountCertifyRequest($uid, $sendToUsers);
    }

    public function doRefuse($uid, $comment)
    {
        $xoopsUser = new XoopsUser($uid);
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $user = $userBean->getUserBasicInfo($uid);
        if (!$user) {
            return;
        }
        if (!$userBean->deleteUsers($uid)) {
            return false;
        }
        if (!$userBean->deleteGroupsUsersByUid($uid)) {
            return false;
        }
        $sendToUsers = Xoonips_Workflow::getAllApproverUserIds($this->dirname, $this->dataname, $uid);
        $this->notification->accountUncertified($user, $sendToUsers, $comment);

        XCube_DelegateUtils::call('Module.Xoonips.Event.User.Reject', $xoopsUser);
        XCube_DelegateUtils::call('Module.Xoonips.Event.User.Delete', $xoopsUser);

        // notify a uncertified to the user by e-mail

        $myxoopsConfig = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF);
        $xoopsMailer = &getMailer();
        $xoopsMailer->useMail();
        $xoopsMailer->setTemplateDir(Xoonips_Utils::mailTemplateDir($this->dirname, $this->trustDirname));
        $xoopsMailer->setTemplate('user_account_uncertified_notify.tpl');
        $xoopsMailer->assign('SITENAME', $myxoopsConfig['sitename']);
        $xoopsMailer->assign('ADMINMAIL', $myxoopsConfig['adminmail']);
        $xoopsMailer->assign('SITEURL', XOOPS_URL.'/');
        $xoopsMailer->assign('USER_UNAME', $user['uname']);
        $xoopsMailer->assign('USER_EMAIL', $user['email']);
        if (isset($_SESSION['xoopsUserId'])) {
            $certifyUserInfo = $userBean->getUserBasicInfo($_SESSION['xoopsUserId']);
            $certifyUser = $certifyUserInfo['uname'];
        } else {
            $certifyUser = '';
        }
        $xoopsMailer->assign('CERTIFY_USER', $certifyUser);
        $xoopsMailer->setToEmails(array($user['email']));
        $xoopsMailer->setFromEmail($myxoopsConfig['adminmail']);
        $xoopsMailer->setFromName($myxoopsConfig['sitename']);
        $xoopsMailer->setSubject(_MD_XOONIPS_MESSAGE_ACCOUNT_REJECTED_NOTIFYSBJ);
        $xoopsMailer->send();
    }
}
