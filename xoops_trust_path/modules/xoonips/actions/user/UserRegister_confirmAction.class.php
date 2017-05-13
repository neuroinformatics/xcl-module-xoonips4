<?php

use Xoonips\Core\Functions;

require_once XOOPS_MODULE_PATH.'/user/actions/UserRegister_confirmAction.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/Workflow.class.php';
require_once XOONIPS_TRUST_PATH.'/class/user/Notification.class.php';
require_once XOONIPS_TRUST_PATH.'/class/Enum.class.php';

class Xoonips_UserRegister_confirmAction extends User_UserRegister_confirmAction
{
    protected $mDirname = '';
    protected $mTrustDirname = '';

    public function setDirname($dirname, $trustDirname)
    {
        $this->mDirname = $dirname;
        $this->mTrustDirname = $trustDirname;
    }

    public function execute(&$controller, &$xoopsUser)
    {
        if (XCube_Root::getSingleton()->mContext->mRequest->getRequest('_form_control_cancel') != null) {
            return USER_FRAME_VIEW_CANCEL;
        }

        // get xoonips module configs
        $certify_user = Functions::getXoonipsConfig($this->mDirname, 'certify_user');
        $is_certify_auto = ($certify_user == 'auto');

        $memberHandler = &xoops_gethandler('member');
        $this->mNewUser = &$memberHandler->createUser();
        $this->mRegistForm->update($this->mNewUser);
        $this->mNewUser->set('uorder', $controller->mRoot->mContext->getXoopsConfig('com_order'), true);
        $this->mNewUser->set('umode', $controller->mRoot->mContext->getXoopsConfig('com_mode'), true);

        if ($this->mConfig['activation_type'] == 1) {
            // activate automatically
            if ($is_certify_auto) {
                // certify automatically
                $this->mNewUser->set('level', 2);
            } else {
                // required to certify by moderator
                $this->mNewUser->set('level', 1);
            }
        } else {
            // required to activate by myself or administrator
            $this->mNewUser->set('level', 0);
        }

        if (!$memberHandler->insertUser($this->mNewUser)) {
            $this->mRedirectMessage = _MD_USER_LANG_REGISTERNG;

            return USER_FRAME_VIEW_ERROR;
        }

        $uid = $this->mNewUser->get('uid');
        if (!$memberHandler->addUserToGroup(XOOPS_GROUP_USERS, $uid)) {
            $this->mRedirectMessage = _MD_USER_LANG_REGISTERNG;

            return USER_FRAME_VIEW_ERROR;
        }

        if ($this->mConfig['activation_type'] == 1 && !$is_certify_auto) {
            // activate automatically and certify required by moderator
            $dataname = Xoonips_Enum::WORKFLOW_USER;
            $url = XOOPS_URL.'/userinfo.php?uid='.$uid;
            if (Xoonips_Workflow::addItem($certifyTitle, $this->mDirname, $dataname, $uid, $url)) {
                // success to register workflow task
                global $xoopsDB;
                $notification = new Xoonips_UserNotification($xoopsDB, $this->mDirname, $this->mTrustDirname);
                $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->mDirname, $dataname, $uid);
                $notification->accountCertifyRequest($uid, $sendToUsers);
            } else {
                // workflow not available - force certify automatically
                $this->mNewUser->set('level', 2);
                $memberHandler->insertUser($this->mNewUser);
            }
        }

        $this->_clearRegistForm($controller);

        $this->_processMail($controller);
        $this->_eventNotifyMail($controller);
        XCube_DelegateUtils::call('Legacy.Event.RegistUser.Success', new XCube_Ref($this->mNewUser));

        XCube_DelegateUtils::call('Module.Xoonips.Event.User.CertifyRequest', new XoopsUser($uid));
        if ($this->mNewUser->get('level') == 2) {
            XCube_DelegateUtils::call('Module.Xoonips.Event.User.Certify', new XoopsUser($uid));
        }

        return USER_FRAME_VIEW_SUCCESS;
    }

    public function executeViewSuccess(&$controller, &$xoopsUser, &$render)
    {
        $activationType = $this->mConfig['activation_type'];
        if ($activationType == 1 && $xoopsUser->get('level') == 1) {
            // activate automatically and certify required by moderator
            $render->setTemplateName('user_register_finish.html');
            $render->setAttribute('complete_message', _MD_XOONIPS_MESSAGE_ACTIVATE_BY_USER_CERTIFY_MANUAL);
        } else {
            parent::executeViewSuccess($controller, $xoopsUser, $render);
        }
    }
}
