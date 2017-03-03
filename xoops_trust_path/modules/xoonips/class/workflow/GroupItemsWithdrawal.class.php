<?php

require_once XOONIPS_TRUST_PATH . '/class/core/Workflow.class.php';
require_once XOONIPS_TRUST_PATH . '/class/core/WorkflowClientBase.class.php';
require_once XOONIPS_TRUST_PATH . '/class/core/WorkflowClientFactory.class.php';
require_once XOONIPS_TRUST_PATH . '/class/core/BeanFactory.class.php';

class Xoonips_WorkflowClientGroupItemsWithdrawal extends Xoonips_WorkflowClientBase {

	var $dataname = Xoonips_Enum::WORKFLOW_GROUP_ITEMS_WITHDRAWAL;

	/**
	 *
	 *
	**/
	public function doCertify($indexItemLinkId, $comment) {
		$indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
		$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
		if (($info = $indexItemLinkBean->getIndexItemLinkInfoByIndexItemLinkId($indexItemLinkId)) === false || empty($info))
			return false;
		$itemId = $info['item_id'];
		$indexId = $info['index_id'];
		if (($info = $indexBean->getIndex($indexId)) === false)
			return false;
		$groupId = $info['groupid'];
		// delete xoonip_index_item_link info by index_id and item_id
		if ($groupId == 0 || !$indexItemLinkBean->deleteById($indexId, $itemId))
			return false;
		//update xoonips_oaipmh_item_status
		$openIndexIds = $indexItemLinkBean->getOpenIndexIds($itemId);
		if ($openIndexIds === false) return false;
		if (count($openIndexIds) == 0) {
			$itemStatusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $this->dirname, $this->trustDirname);
			if (!$itemStatusBean->delete($itemId))
				return false;
		}
		//event log
		$this->log->recordCertifyGroupItemWithdrawalEvent($itemId, $indexId);
		//send to item users
		$groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname);
		$itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
		$itemUsersInfo = $itemUsersBean->getItemUsersInfo($itemId);
		$sendToUsers = array();
		foreach ($groupsUsersLinkBean->getAdminUserIds($groupId) as $id) {
			$sendToUsers[] = $id; 
		}
		foreach ($itemUsersInfo as $itemUser) {
			$sendToUsers[] = $itemUser['uid'];
		}
		$sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $this->dataname, $indexItemLinkId));
		$sendToUsers = array_unique($sendToUsers);
		$this->notification->groupItemWithdrawal($itemId, $indexId, $groupId, $sendToUsers, $comment);
	}

	/**
	 *
	 *
	**/
	public function doProgress($indexItemLinkId) {
		$indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
		$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
		if (($info = $indexItemLinkBean->getIndexItemLinkInfoByIndexItemLinkId($indexItemLinkId)) === false || empty($info))
			return false;
		$itemId = $info['item_id'];
		$indexId = $info['index_id'];
		if (($info = $indexBean->getIndex($indexId)) === false)
			return false;
		$groupId = $info['groupid'];
		if ($groupId == 0)
			return;
		$sendToUsers = Xoonips_Workflow::getCurrentApproverUserIds($this->dirname, $this->dataname, $indexItemLinkId);
		$this->notification->groupItemWithdrawalRequest($itemId, $indexId, $groupId, $sendToUsers);
	}

	/**
	 *
	 *
	**/
	public function doRefuse($indexItemLinkId, $comment) {
		$indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
		$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
		if (($info = $indexItemLinkBean->getIndexItemLinkInfoByIndexItemLinkId($indexItemLinkId)) === false || empty($info))
			return false;
		$itemId = $info['item_id'];
		$indexId = $info['index_id'];
		if (($info = $indexBean->getIndex($indexId)) === false)
			return false;
		$groupId = $info['groupid'];
		// update xoonip_index_item_link
		if ($groupId == 0 || !$indexItemLinkBean->update($indexId, $itemId, XOONIPS_CERTIFIED))
			return false;
		$groupId = $indexInfo['groupid'];
		//event log
		$this->log->recordRejectGroupItemWithdrawalEvent($itemId, $indexId);
		//send to item users
		$groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $this->dirname);
		$itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
		$itemUsersInfo = $itemUsersBean->getItemUsersInfo($itemId);
		$sendToUsers = array();
		foreach ($groupsUsersLinkBean->getAdminUserIds($groupId) as $id)
			$sendToUsers[] = $id; 
		foreach ($itemUsersInfo as $itemUser)
			$sendToUsers[] = $itemUser['uid'];
		$sendToUsers = array_merge($sendToUsers, Xoonips_Workflow::getAllApproverUserIds($this->dirname, $this->dataname, $indexItemLinkId));
		$sendToUsers = array_unique($sendToUsers);
		$this->notification->groupItemWithdrawalRejected($itemId, $indexId, $groupId, $sendToUsers, $comment);
	}

}

