<?php

require_once dirname(__DIR__).'/core/Workflow.class.php';
require_once dirname(__DIR__).'/core/WorkflowClientBase.class.php';
require_once dirname(__DIR__).'/core/WorkflowClientFactory.class.php';

class Xoonips_WorkflowClientPublicItemsWithdrawal extends Xoonips_WorkflowClientBase
{
    public $dataname = Xoonips_Enum::WORKFLOW_PUBLIC_ITEMS_WITHDRAWAL;

    public function doCertify($indexItemLinkId, $comment)
    {
        $indexItemLinkBean = null;
        $workflow = Xoonips_WorkflowClientFactory::getWorkflow(Xoonips_Enum::WORKFLOW_PUBLIC_ITEMS, $this->dirname, $this->trustDirname);
        list($itemId, $indexId) = $workflow->getItemAndIndexId($indexItemLinkId, $indexItemLinkBean);
        // delete xoonip_index_item_link info by index_id and item_id
        if ($itemId == 0 || !$indexItemLinkBean->deleteById($indexId, $itemId)) {
            return false;
        }
        //update xoonips_oaipmh_item_status
        $openIndexIds = $indexItemLinkBean->getOpenIndexIds($itemId);
        if ($openIndexIds === false) {
            return false;
        }
        if (count($openIndexIds) == 0) {
            $itemStatusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $this->dirname, $this->trustDirname);
            if (!$itemStatusBean->delete($itemId)) {
                return false;
            }
        }

        //event log
        $this->log->recordCertifyItemWithdrawalEvent($itemId, $indexId);

        //send to item users
        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $itemUsersInfo = $itemUsersBean->getItemUsersInfo($itemId);
        $sendToUsers = array();
        foreach ($itemUsersInfo as $itemUser) {
            $sendToUsers[] = $itemUser['uid'];
        }
        $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $this->dataname, $indexItemLinkId));
        $sendToUsers = array_unique($sendToUsers);
        $this->notification->itemPublicWithdrawal($itemId, $indexId, $sendToUsers, $comment);
    }

    public function doProgress($indexItemLinkId)
    {
        $indexItemLinkBean = null;
        $workflow = Xoonips_WorkflowClientFactory::getWorkflow(Xoonips_Enum::WORKFLOW_PUBLIC_ITEMS, $this->dirname, $this->trustDirname);
        list($itemId, $indexId) = $workflow->getItemAndIndexId($indexItemLinkId, $indexItemLinkBean);
        if ($itemId == 0) {
            return;
        }
        $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $this->dataname, $indexItemLinkId);
        $this->notification->itemPublicWithdrawalRequest($itemId, $indexId, $sendToUsers);
    }

    public function doRefuse($indexItemLinkId, $comment)
    {
        $indexItemLinkBean = null;
        $workflow = Xoonips_WorkflowClientFactory::getWorkflow(Xoonips_Enum::WORKFLOW_PUBLIC_ITEMS, $this->dirname, $this->trustDirname);
        list($itemId, $indexId) = $workflow->getItemAndIndexId($indexItemLinkId, $indexItemLinkBean);
        // update xoonip_index_item_link
        if ($itemId == 0 || !$indexItemLinkBean->update($indexId, $itemId, 2)) {
            return false;
        }

        //event log
        $this->log->recordRejectItemWithdrawalEvent($itemId, $indexId);

        //send to item users
        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $itemUsersInfo = $itemUsersBean->getItemUsersInfo($itemId);
        $sendToUsers = array();
        foreach ($itemUsersInfo as $itemUser) {
            $sendToUsers[] = $itemUser['uid'];
        }
        $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $this->dataname, $indexItemLinkId));
        $sendToUsers = array_unique($sendToUsers);
        $this->notification->itemPublicWithdrawalRejected($itemId, $indexId, $sendToUsers, $comment);
    }
}
