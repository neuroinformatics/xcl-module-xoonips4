<?php

use Xoonips\Core\Functions;
use Xoonips\Core\XoopsUtils;

require_once dirname(__DIR__).'/core/Workflow.class.php';
require_once dirname(__DIR__).'/core/WorkflowClientBase.class.php';
require_once dirname(__DIR__).'/core/User.class.php';

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
        $sitename = XoopsUtils::getXoopsConfig('sitename');
        $adminmail = XoopsUtils::getXoopsConfig('adminmail');
        $user_certify_date = Functions::getXoonipsConfig($this->dirname, 'user_certify_date');
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
                $xoopsMailer->assign('SITENAME', $sitename);
                $xoopsMailer->assign('ADMINMAIL', $adminmail);
                $xoopsMailer->assign('SITEURL', XOOPS_URL.'/');
                $xoopsMailer->setToUsers($deleteUserInfo);
                $xoopsMailer->setFromEmail($adminmail);
                $xoopsMailer->setFromName($sitename);
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
        $xoopsMailer->assign('SITENAME', $sitename);
        $xoopsMailer->assign('SITEURL', XOOPS_URL.'/');
        $xoopsMailer->assign('ADMINMAIL', $adminmail);
        $xoopsMailer->setToUsers(new XoopsUser($uid));
        $xoopsMailer->setFromEmail($adminmail);
        $xoopsMailer->setFromName($sitename);
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

        $sitename = XoopsUtils::getXoopsConfig('sitename');
        $adminmail = XoopsUtils::getXoopsConfig('adminmail');
        $xoopsMailer = &getMailer();
        $xoopsMailer->useMail();
        $xoopsMailer->setTemplateDir(Xoonips_Utils::mailTemplateDir($this->dirname, $this->trustDirname));
        $xoopsMailer->setTemplate('user_account_uncertified_notify.tpl');
        $xoopsMailer->assign('SITENAME', $sitename);
        $xoopsMailer->assign('ADMINMAIL', $adminmail);
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
        $xoopsMailer->setToEmails([$user['email']]);
        $xoopsMailer->setFromEmail($adminmail);
        $xoopsMailer->setFromName($sitename);
        $xoopsMailer->setSubject(_MD_XOONIPS_MESSAGE_ACCOUNT_REJECTED_NOTIFYSBJ);
        $xoopsMailer->send();
    }
}
