<?php

require_once XOONIPS_TRUST_PATH.'/class/core/Workflow.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/WorkflowClientBase.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/BeanFactory.class.php';
require_once dirname(dirname(__FILE__)).'/core/Item.class.php';

class Xoonips_WorkflowClientPublicItems extends Xoonips_WorkflowClientBase
{
    public $dataname = Xoonips_Enum::WORKFLOW_PUBLIC_ITEMS;

    public function doCertify($indexItemLinkId, $comment)
    {
        $indexItemLinkBean = null;
        list($itemId, $indexId) = $this->getItemAndIndexId($indexItemLinkId, $indexItemLinkBean);
        if ($itemId == 0 || !$indexItemLinkBean->update($indexId, $itemId, XOONIPS_CERTIFIED)) {
            return;
        }
        $itemStatusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $this->dirname, $this->trustDirname);
        if (!$itemStatusBean->updateItemStatus($itemId)) {
            $indexItemLinkBean->update($indexId, $itemId, XOONIPS_CERTIFY_REQUIRED);

            return;
        }

        //event log
        $this->log->recordCertifyItemEvent($itemId, $indexId);

        //send to item user
        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $itemUsersInfo = $itemUsersBean->getItemUsersInfo($itemId);
        $sendToUsers = array();
        foreach ($itemUsersInfo as $itemUser) {
            $sendToUsers[] = $itemUser['uid'];
        }
        $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $this->dataname, $indexItemLinkId));
        $sendToUsers = array_unique($sendToUsers);
        $this->notification->itemCertified($itemId, $indexId, $sendToUsers, $comment);
    }

    public function doProgress($indexItemLinkId)
    {
        $indexItemLinkBean = null;
        list($itemId, $indexId) = $this->getItemAndIndexId($indexItemLinkId, $indexItemLinkBean);
        if ($itemId == 0) {
            return;
        }
        $sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $this->dataname, $indexItemLinkId);
        $this->notification->itemCertifyRequest($itemId, $indexId, $sendToUsers);
    }

    public function doRefuse($indexItemLinkId, $comment)
    {
        $indexItemLinkBean = null;
        list($itemId, $indexId) = $this->getItemAndIndexId($indexItemLinkId, $indexItemLinkBean);
        if ($itemId == 0 || !$indexItemLinkBean->deleteById($indexId, $itemId)) {
            return;
        }

        //event log
        $this->log->recordRejectItemEvent($itemId, $indexId);

        //send to item user
        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $itemUsersInfo = $itemUsersBean->getItemUsersInfo($itemId);
        $sendToUsers = array();
        foreach ($itemUsersInfo as $itemUser) {
            $sendToUsers[] = $itemUser['uid'];
        }
        $sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $this->dataname, $indexItemLinkId));
        $sendToUsers = array_unique($sendToUsers);
        $this->notification->itemRejected($itemId, $indexId, $sendToUsers, $comment);
    }

    public function getItemAndIndexId($indexItemLinkId, &$indexItemLinkBean)
    {
        $indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $indexItemLinkInfo = $indexItemLinkBean->getIndexItemLinkInfoByIndexItemLinkId($indexItemLinkId);
        if (!$indexItemLinkInfo) {
            return array(0, 0);
        }

        return array($indexItemLinkInfo['item_id'], $indexItemLinkInfo['index_id']);
    }
}
