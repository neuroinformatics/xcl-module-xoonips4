<?php

require_once dirname(__DIR__).'/core/Workflow.class.php';
require_once dirname(__DIR__).'/core/WorkflowClientBase.class.php';

class Xoonips_WorkflowClientGroupItems extends Xoonips_WorkflowClientBase
{
    public $dataname = Xoonips_Enum::WORKFLOW_GROUP_ITEMS;

    public function doCertify($indexItemLinkId, $comment)
    {
        $result = true;
        $indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        if (false === ($info = $indexItemLinkBean->getIndexItemLinkInfoByIndexItemLinkId($indexItemLinkId)) || empty($info)) {
            return false;
        }
        $itemId = $info['item_id'];
        $indexId = $info['index_id'];
        if (false === ($info = $indexBean->getIndex($indexId))) {
            return false;
        }
        $groupId = $info['groupid'];
        if (0 == $groupId || !$indexItemLinkBean->update($indexId, $itemId, XOONIPS_CERTIFIED)) {
            return false;
        }
        if ($result) {
            $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname);
            $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
            $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
            $groupInfo = $groupBean->getGroup($groupId);
            if ($groupInfo['item_number_limit'] > 0 && $itemBean->countGroupItems($groupId) >= $groupInfo['item_number_limit']) {
                $result = false;
                //$result[1] = _MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT;
            }
        }
        if ($result) {
            if ($groupInfo['item_storage_limit'] > 0 && $fileBean->countGroupFileSizes($groupId) >= $groupInfo['item_storage_limit']) {
                $result = false;
                //$result[1] = _MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT;
            }
        }
        if ($result) {
            if ($groupBean->isPublic($groupId)) {
                $itemStatusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $this->dirname, $this->trustDirname);
                if (!$itemStatusBean->updateItemStatus($itemId)) {
                    $result = false;
                }
            }
        }
        if (!$result) {
            $indexItemLinkBean->update($indexId, $itemId, XOONIPS_CERTIFY_REQUIRED);

            return;
        }
        //event log
        $this->log->recordCertifyGroupItemEvent($indexId, $itemId);
        //send to item users
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname);
        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $itemUsersInfo = $itemUsersBean->getItemUsersInfo($itemId);
        $sendToUsers = [];
        foreach ($groupsUsersLinkBean->getAdminUserIds($groupId) as $id) {
            $sendToUsers[] = $id;
        }
        foreach ($itemUsersInfo as $itemUser) {
            $sendToUsers[] = $itemUser['uid'];
        }
        $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $this->dataname, $indexItemLinkId));
        $sendToUsers = array_unique($sendToUsers);
        $this->notification->groupItemCertified($itemId, $indexId, $groupId, $sendToUsers, $comment);
    }

    public function doProgress($indexItemLinkId)
    {
        $indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        if (false === ($info = $indexItemLinkBean->getIndexItemLinkInfoByIndexItemLinkId($indexItemLinkId)) || empty($info)) {
            return false;
        }
        $itemId = $info['item_id'];
        $indexId = $info['index_id'];
        if (false === ($info = $indexBean->getIndex($indexId))) {
            return false;
        }
        $groupId = $info['groupid'];
        if (0 == $groupId) {
            return false;
        }
        $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $this->dataname, $indexItemLinkId);
        $this->notification->groupItemCertifyRequest($itemId, $indexId, $groupId, $sendToUsers);
    }

    public function doRefuse($indexItemLinkId, $comment)
    {
        $indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        if (false === ($info = $indexItemLinkBean->getIndexItemLinkInfoByIndexItemLinkId($indexItemLinkId)) || empty($info)) {
            return false;
        }
        $itemId = $info['item_id'];
        $indexId = $info['index_id'];
        if (false === ($info = $indexBean->getIndex($indexId))) {
            return false;
        }
        $groupId = $info['groupid'];
        if (0 == $groupId || !$indexItemLinkBean->deleteById($indexId, $itemId)) {
            return false;
        }
        //event log
        $this->log->recordRejectGroupItemEvent($indexId, $itemId);
        //send to item user
        $groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname);
        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $itemUsersInfo = $itemUsersBean->getItemUsersInfo($itemId);
        $sendToUsers = [];
        foreach ($groupsUsersLinkBean->getAdminUserIds($groupId) as $id) {
            $sendToUsers[] = $id;
        }
        foreach ($itemUsersInfo as $itemUser) {
            $sendToUsers[] = $itemUser['uid'];
        }
        $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $this->dataname, $indexItemLinkId));
        $sendToUsers = array_unique($sendToUsers);
        $this->notification->groupItemRejected($itemId, $indexId, $groupId, $sendToUsers, $comment);
    }
}
