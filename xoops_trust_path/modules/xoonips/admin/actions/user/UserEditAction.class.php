<?php

use Xoonips\Core\Functions;

require_once XOOPS_MODULE_PATH.'/user/admin/actions/UserEditAction.class.php';
require_once dirname(dirname(dirname(__DIR__))).'/class/core/Workflow.class.php';
require_once dirname(dirname(dirname(__DIR__))).'/class/user/Notification.class.php';

class Xoonips_UserEditAction extends User_UserEditAction
{
    protected $mDirname = '';
    protected $mTrustDirname = '';

    public function setDirname($dirname, $trustDirname)
    {
        $this->mDirname = $dirname;
        $this->mTrustDirname = $trustDirname;
    }

    public function _setupObject()
    {
        parent::_setupObject();
        if (null == $this->mObject && $this->isEnableCreate()) {
            $certify_user = Functions::getXoonipsConfig($this->mDirname, 'certify_user');
            if ('auto' == $certify_user) {
                $this->mObject->set('level', 2);
            }
        }
    }

    public function executeViewInput(&$controller, &$xoopsUser, &$render)
    {
        parent::executeViewInput($controller, $xoopsUser, $render);
        $constpref = '_AD_'.strtoupper($this->mDirname);
        $render->setAttribute('constpref', $constpref);
    }

    public function _doExecute()
    {
        $ret = parent::_doExecute();
        if (true === $ret) {
            $isNew = (!$this->_getId());
            // workflow and notification
            $ret = self::_updateUserWorkflow($this->mObject->get('uid'), $isNew, $this->mDirname, $this->mTrustDirname);
        }

        return $ret;
    }

    /**
     * update user workflow.
     *
     * @param int    $uid
     * @param bool   $isNew
     * @param string $dirname
     * @param string $trustDirname
     *
     * @return bool
     */
    protected static function _updateUserWorkflow($uid, $isNew, $dirname, $trustDirname)
    {
        $root = &XCube_Root::getSingleton();
        $xoopsDB = &$root->mController->getDB();
        $certify_user = Functions::getXoonipsConfig($dirname, 'certify_user');
        $notification = new Xoonips_UserNotification($xoopsDB, $dirname, $trustDirname);
        $groupsUsersLink = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $dirname, $trustDirname);
        $moderatorUids = $groupsUsersLink->getModeratorUserIds();
        $xoopsUserHandler = &xoops_gethandler('user');
        $xoopsUser = &$xoopsUserHandler->get($uid);
        $level = $xoopsUser->get('level');
        $uname = $xoopsUser->get('uname');
        $url = XOOPS_URL.'/userinfo.php?uid='.$uid;
        $firstApprovalUids = Xoonips_Workflow::getCurrentApproverUserIds($dirname, Xoonips_Enum::WORKFLOW_USER, $uid);
        $allApprovalUids = array_unique(array_merge($moderatorUids, Xoonips_Workflow::getAllApproverUserIds($dirname, Xoonips_Enum::WORKFLOW_USER, $uid)));
        if ($isNew) {
            // register new user
            if (1 == $level && 'auto' == $certify_user) {
                // auto certify mode enabled - force update level and certify automatically
                $xoopsUser->set('level', 2);
                if (!$xoopsUserHandler->insert($xoopsUser)) {
                    return false;
                }
                XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', $xoopsUser);
                XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', $xoopsUser);
                $notification->accountCertifiedAuto($uid, $allApprovalUids);
            } elseif (1 == $level) {
                // not certified user - try to use workflow
                if (Xoonips_Workflow::addItem($uname, $dirname, Xoonips_Enum::WORKFLOW_USER, $uid, $url)) {
                    // success to register workflow task - send certify request
                    XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', $xoopsUser);
                    $notification->accountCertifyRequest($uid, $firstApprovalUids);
                } else {
                    // workflow not ready - force update level and certify automatically
                    $xoopsUser->set('level', 2);
                    if (!$xoopsUserHandler->insert($xoopsUser)) {
                        return false;
                    }
                    XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', $xoopsUser);
                    XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', $xoopsUser);
                    $notification->accountCertifiedAuto($uid, $allApprovalUids);
                }
            } elseif (2 == $level) {
                // certified user - certify automatically
                $doNotify = 'auto';
                XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', $xoopsUser);
                XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', $xoopsUser);
                $notification->accountCertifiedAuto($uid, $allApprovalUids);
            }
        } else {
            // existing user
            if (1 == $level && 'auto' == $certify_user) {
                // auto certify mode enabled - force update level and certify automatically
                $xoopsUser->set('level', 2);
                if (!$xoopsUserHandler->insert($xoopsUser)) {
                    return false;
                }
                if (Xoonips_Workflow::isInProgressItem($dirname, Xoonips_Enum::WORKFLOW_USER, $uid)) {
                    // workflow task found - delete in progress task
                    Xoonips_Workflow::deleteItem($dirname, Xoonips_Enum::WORKFLOW_USER, $uid);
                } else {
                    // workflow task not found
                    XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', $xoopsUser);
                }
                XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', $xoopsUser);
                $notification->accountCertifiedAuto($uid, $allApprovalUids);
            } elseif (1 == $level) {
                // not certified user - check current workflow progress
                if (!Xoonips_Workflow::isInProgressItem($dirname, Xoonips_Enum::WORKFLOW_USER, $uid)) {
                    // workflow task not found - try to register workflow task
                    if (Xoonips_Workflow::addItem($uname, $dirname, Xoonips_Enum::WORKFLOW_USER, $uid, $url)) {
                        // success to register workflow task - send certify request
                        XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', $xoopsUser);
                        $notification->accountCertifyRequest($uid, $firstApprovalUids);
                    } else {
                        // workflow not ready - force update level and certify automatically
                        $xoopsUser->set('level', 2);
                        if (!$xoopsUserHandler->insert($xoopsUser)) {
                            return false;
                        }
                        XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', $xoopsUser);
                        XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', $xoopsUser);
                        $notification->accountCertifiedAuto($uid, $allApprovalUids);
                    }
                }
            } elseif (2 == $level) {
                // certified user - certify if workflow exists
                if (Xoonips_Workflow::isInProgressItem($dirname, Xoonips_Enum::WORKFLOW_USER, $uid)) {
                    Xoonips_Workflow::deleteItem($dirname, Xoonips_Enum::WORKFLOW_USER, $uid);
                    XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', $xoopsUser);
                    $notification->accountCertified($uid, $allApprovalUids, '');
                }
            }
        }

        return true;
    }
}
