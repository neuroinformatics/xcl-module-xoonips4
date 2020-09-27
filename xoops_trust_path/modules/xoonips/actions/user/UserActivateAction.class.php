<?php

use Xoonips\Core\Functions;
use Xoonips\Core\XoopsUtils;

require_once dirname(dirname(__DIR__)).'/class/user/AbstractEditAction.class.php';
require_once dirname(dirname(__DIR__)).'/class/user/Notification.class.php';
require_once dirname(dirname(__DIR__)).'/class/core/Workflow.class.php';

class Xoonips_UserActivateAction extends Xoonips_UserAbstractEditAction
{
    public function _getId()
    {
        $root = &XCube_Root::getSingleton();
        $uid = intval($root->mContext->mRequest->getRequest('uid'));

        return $uid;
    }

    public function &_getHandler()
    {
        $handler = &xoops_getmodulehandler('users', 'user');

        return $handler;
    }

    /**
     *  Return false.
     *  If a user requests dummy uid, kick out him!
     */
    public function isEnableCreate()
    {
        return false;
    }

    /**
     *  Return false.
     *  This action would be used by a guest user.
     */
    public function isSecure()
    {
        return false;
    }

    public function getDefaultView(&$controller, &$xoopsUser)
    {
        //set ticket
        $root = &XCube_Root::getSingleton();
        $this->viewData['op'] = $root->mContext->mRequest->getRequest('op');
        $this->viewData['uid'] = intval($root->mContext->mRequest->getRequest('uid'));
        $this->viewData['actkey'] = $root->mContext->mRequest->getRequest('actkey');

        return USER_FRAME_VIEW_INPUT;
    }

    public function execute(&$controller, &$xoopsUser)
    {
        global $xoopsDB;
        //laod User Module Language
        $root = &XCube_Root::getSingleton();
        $root->getLanguageManager()->loadModuleAdminMessageCatalog(XCUBE_CORE_USER_MODULE_NAME);

        $certify_user = Functions::getXoonipsConfig($this->dirname, 'certify_user');
        $user_certify_date = Functions::getXoonipsConfig($this->dirname, 'user_certify_date');
        $activation_type = XoopsUtils::getXoopsConfig('activation_type', XOOPS_CONF_USER);

        $result = [];
        $dataname = Xoonips_Enum::WORKFLOW_USER;

        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $uid = intval($root->mContext->mRequest->getRequest('uid'));
        $user = $userBean->getUserBasicInfo($uid);
        $actkey = $root->mContext->mRequest->getRequest('actkey');
        if (empty($actkey) || !$user) {
            $controller->executeForward(XOOPS_URL.'/');
        }

        if ($user['actkey'] != $actkey) {
            $controller->executeRedirect(XOOPS_URL.'/', 3, _MD_USER_MESSAGE_ACTKEYNOT);
        }

        if (1 == $user['level']) {
            $controller->executeRedirect(XOOPS_URL.'/user.php', 3, _MD_XOONIPS_MESSAGE_ACTIVATED_NOT_APPROVE);
        }

        if ($user['level'] > 1) {
            $controller->executeRedirect(XOOPS_URL.'/user.php', 3, _MD_USER_MESSAGE_ACONTACT);
        }
        if ($user_certify_date > 0) {
            $time = (time() - $user['user_regdate']) / (24 * 60 * 60);
            if ($time > $user_certify_date) {
                if (!$userBean->deleteUsers($uid)) {
                    $controller->executeRedirect(XOOPS_URL.'/', 3, 'DB error!');
                }
                if (!$userBean->deleteGroupsUsersByUid($uid)) {
                    redirect_header(XOOPS_URL.'/', 3, 'DB error!');
                }
                $controller->executeRedirect(XOOPS_URL.'/', 3, _MD_XOONIPS_MESSAGE_ACTIVATE_TIMEOUT);
            }
        }
        if (!$userBean->activateUser($user)) {
            $controller->executeRedirect(XOOPS_URL.'/', 3, 'Activation failed!');
        }

        $notification = new Xoonips_UserNotification($xoopsDB, $this->dirname, $this->trustDirname);

        $groupUserLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        $moderatorUids = $groupUserLinkBean->getModeratorUserIds();

        if ('on' == $certify_user) {
            // certification request
            $title = $user['uname'];
            $url = XOOPS_URL.'/userinfo.php?uid='.$uid;
            if (Xoonips_Workflow::addItem($title, $this->dirname, $dataname, $uid, $url)) {
                // success to register workflow task
                XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', new XoopsUser($uid));
                $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $dataname, $uid);
                $notification->accountCertifyRequest($uid, $sendToUsers);
                if (2 == $activation_type) {
                    // activate by xoops admin & certify manual
                    $controller->executeRedirect(XOOPS_URL.'/user.php', 5, _MD_XOONIPS_MESSAGE_ACTIVATED_ADMIN_CERTIFY);
                } elseif ($activation_type <= 1) {
                    // activate by xoops by user & certify manual
                    $controller->executeRedirect(XOOPS_URL.'/user.php', 5, _MD_XOONIPS_MESSAGE_ACTIVATED_USER_CERTIFY);
                }
            } else {
                // workflow not available - force certify automatically
                if (!$userBean->certifyUser($user)) {
                    $controller->executeRedirect(XOOPS_URL.'/', 3, 'Activation failed!');
                }
                XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', new XoopsUser($uid));
                XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', new XoopsUser($uid));
                $sendToUsers = $moderatorUids;
                $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $uid));
                $notification->accountCertifiedAuto($uid, $sendToUsers);
            }
        } else {
            // certification automatically
            if (!$userBean->certifyUser($user)) {
                $controller->executeRedirect(XOOPS_URL.'/', 3, 'Activation failed!');
            }
            XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', new XoopsUser($uid));
            XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', new XoopsUser($uid));
            $sendToUsers = $moderatorUids;
            $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $dataname, $uid));
            $notification->accountCertifiedAuto($uid, $sendToUsers);

            if (2 == $activation_type) {
                // activate xoops account by xoops administrator
                $sitename = XoopsUtils::getXoopsConfig('sitename');
                $adminmail = XoopsUtils::getXoopsConfig('adminmail');
                // send e-mail to the registered address
                // notify a completion of certification to the certified user by e-mail
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
                $xoopsMailer->setSubject(_MD_XOONIPS_MESSAGE_ACCOUNT_CERTIFIED_AUTO_NOTIFYSBJ);
                if ($xoopsMailer->send()) {
                    $controller->executeRedirect(XOOPS_URL.'/user.php', 5, _MD_XOONIPS_MESSAGE_CERTIFY_MAILOK);
                } else {
                    $controller->executeRedirect(XOOPS_URL.'/user.php', 5, _MD_XOONIPS_MESSAGE_CERTIFY_MAILNG);
                }
            } else {
                $controller->executeRedirect(XOOPS_URL.'/user.php', 5, _MD_USER_MESSAGE_ACTLOGIN);
            }
        }
        exit();
    }

    public function executeViewInput(&$controller, &$xoopsUser, &$render)
    {
        $render->setTemplateName($this->dirname.'_user_activate.html');
        $this->setAttributes($render);
        $controller->executeView();
        exit();
    }
}
