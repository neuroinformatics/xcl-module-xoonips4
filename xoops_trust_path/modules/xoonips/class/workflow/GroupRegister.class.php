<?php

require_once dirname(__DIR__).'/core/Workflow.class.php';
require_once dirname(__DIR__).'/core/WorkflowClientBase.class.php';
require_once dirname(__DIR__).'/core/User.class.php';

class Xoonips_WorkflowClientGroupRegister extends Xoonips_WorkflowClientBase
{
    public $dataname = Xoonips_Enum::WORKFLOW_GROUP_REGISTER;

    public function doCertify($gid, $comment)
    {
        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        $group_handler = &xoops_gethandler('group');
        $xoopsGroup = $group_handler->get($gid);
        if (!$groupBean->groupsCertify($gid)) {
            return false;
        }
        // event log
        $user = Xoonips_User::getInstance();
        $user->doGroupCertified($gid, $xoopsGroup, false, $comment);
    }

    public function doProgress($gid)
    {
        $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $this->dataname, $gid);
        $this->notification->groupCertifyRequest($gid, $sendToUsers);
    }

    public function doRefuse($gid, $comment)
    {
        $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        $group = $groupBean->getGroup($gid);
        $group_handler = &xoops_gethandler('group');
        $xoopsGroup = $group_handler->get($gid);
        $group['index_path'] = '/'.$group['name'];
        $groupUserLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname, $this->trustDirname);
        // send to group admins
        $sendToUsers = array();
        foreach ($groupUserLinkBean->getAdminUserIds($gid) as $id) {
            $sendToUsers[] = $id;
        }
        $groupUserIds = $groupUserLinkBean->getUserIds($gid);
        if (!$groupBean->delete($gid)) {
            return false;
        }
        // user leave log
        foreach ($groupUserIds as $uid) {
            XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Member.Leave', new XoopsUser($uid), $xoopsGroup);
        }
        if (!$groupUserLinkBean->deleteGroupUsers($gid)) {
            return false;
        }

        // event log
        XCube_DelegateUtils::call('Module.Xoonips.Event.Group.Reject', $xoopsGroup);

        // send to group admins and certifyUsers
        $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $this->dataname, $gid));
        $sendToUsers = array_unique($sendToUsers);
        $this->notification->groupRejected($group, $sendToUsers, $comment);
    }
}
