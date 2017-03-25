<?php

require_once XOOPS_MODULE_PATH.'/user/admin/actions/GroupDeleteAction.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/BeanFactory.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/Errors.class.php';
require_once XOONIPS_TRUST_PATH.'/class/Enum.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/User.class.php';

class Xoonips_GroupDeleteAction extends User_GroupDeleteAction
{
    protected $mDirname = '';
    protected $mTrustDirname = '';

    public function setDirname($dirname, $trustDirname)
    {
        $this->mDirname = $dirname;
        $this->mTrustDirname = $trustDirname;
    }

    public function _doExecute()
    {
        $handler = &xoops_gethandler('group');
        $group = &$handler->get($this->mObject->get('groupid'));
        if ($group->get('group_type') != Xoonips_Enum::GROUP_TYPE) {
            return parent::_doExecute();
        }
        $groupbean = Xoonips_BeanFactory::getBean('GroupsBean', $this->mDirname, $this->mTrustDirname);
        $group = $groupbean->getGroup($this->mObject->get('groupid'));
        if ($group['activate'] == Xoonips_Enum::GRP_DELETE_REQUIRED) {
            return USER_FRAME_VIEW_ERROR;
        }
        $transaction = Xoonips_Transaction::getInstance();
        $transaction->start();
        $user = Xoonips_User::getInstance();
        $message = '';
        if (!$user->doGroupDelete($group, $message)) {
            return USER_FRAME_VIEW_ERROR;
        }
        $transaction->commit();

        return USER_FRAME_VIEW_SUCCESS;
    }

    public function executeViewInput(&$controller, &$xoopsUser, &$render)
    {
        parent::executeViewInput($controller, $xoopsUser, $render);
        $deleteRequired = false;
        $isExtended = ($this->mObject->get('group_type') == Xoonips_Enum::GROUP_TYPE);
        if ($isExtended) {
            $groupbean = Xoonips_BeanFactory::getBean('GroupsBean', $this->mDirname, $this->mTrustDirname);
            $group = $groupbean->getGroup($this->mObject->get('groupid'));
            if ($group['activate'] == Xoonips_Enum::GRP_DELETE_REQUIRED) {
                $deleteRequired = true;
            }
        }
        $render->setAttribute('isExtended', $isExtended);
        $render->setAttribute('deleteRequired', $deleteRequired);
        $constpref = '_AD_'.strtoupper($this->mDirname);
        $render->setAttribute('constpref', $constpref);
    }
}
