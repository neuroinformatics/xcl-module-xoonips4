<?php

require_once XOOPS_MODULE_PATH.'/user/admin/actions/GroupDeleteAction.class.php';
require_once dirname(dirname(dirname(__DIR__))).'/class/core/Errors.class.php';
require_once dirname(dirname(dirname(__DIR__))).'/class/core/User.class.php';

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
        if (Xoonips_Enum::GROUP_TYPE != $group->get('group_type')) {
            return parent::_doExecute();
        }
        $groupbean = Xoonips_BeanFactory::getBean('GroupsBean', $this->mDirname, $this->mTrustDirname);
        $group = $groupbean->getGroup($this->mObject->get('groupid'));
        if (Xoonips_Enum::GRP_DELETE_REQUIRED == $group['activate']) {
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
        $isExtended = (Xoonips_Enum::GROUP_TYPE == $this->mObject->get('group_type'));
        if ($isExtended) {
            $groupbean = Xoonips_BeanFactory::getBean('GroupsBean', $this->mDirname, $this->mTrustDirname);
            $group = $groupbean->getGroup($this->mObject->get('groupid'));
            if (Xoonips_Enum::GRP_DELETE_REQUIRED == $group['activate']) {
                $deleteRequired = true;
            }
        }
        $render->setAttribute('isExtended', $isExtended);
        $render->setAttribute('deleteRequired', $deleteRequired);
        $constpref = '_AD_'.strtoupper($this->mDirname);
        $render->setAttribute('constpref', $constpref);
    }
}
