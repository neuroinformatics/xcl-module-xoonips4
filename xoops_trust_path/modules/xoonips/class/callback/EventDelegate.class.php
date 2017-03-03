<?php

require_once dirname(dirname(__FILE__)) . '/core/Notification.class.php';

class Xoonips_EventDelegate {

	/**
	 * 'Module.Xoonips.Event.User.CertifyRequest' Delegete function
	 *
	 * @param xoopsUser $xoopsUser
	 */
	public static function userCertifyRequest($xoopsUser) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$uid = $xoopsUser->get('uid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordRequestInsertAccountEvent($uid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.User.Certify' Delegete function
	 *
	 * @param xoopsUser $xoopsUser
	 */
	public static function userCertify($xoopsUser) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$uid = $xoopsUser->get('uid');
		$success = true;
		foreach ($dirnames as $dirname) {
			$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $dirname, $trustDirname);
			$index_id = $indexBean->insertPrivateIndex($uid);
			if ($index_id === false) {
				$sucess = false;
				continue;
			}
			// certification automatically
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordCertifyAccountEvent($uid);
		}
		if (!$success)
			redirect_header(XOOPS_URL . '/', 3, 'Activation failed!');
	}

	/**
	 * 'Module.Xoonips.Event.User.Reject' Delegete function
	 *
	 * @param xoopsUser $xoopsUser
	 */
	public static function userReject($xoopsUser) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$uid = $xoopsUser->get('uid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordUncertifyAccountEvent($uid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.User.Substitute.Begin' Delegete function
	 *
	 * @param xoopsUser $xoopsUser original user
	 * @param xoopsUser $xoopsUser_target target user
	 */
	public static function userSuBegin($xoopsUser, $xoopsUser_target) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$uid = $xoopsUser->get('uid');
		$tuid = $xoopsUser_target->get('uid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordStartSuEvent($uid, $tuid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.User.Substitute.End' Delegete function
	 *
	 * @param xoopsUser $xoopsUser original user
	 * @param xoopsUser $xoopsUser_target target user
	 */
	public static function userSuEnd($xoopsUser, $xoopsUser_target) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$uid = $xoopsUser->get('uid');
		$tuid = $xoopsUser_target->get('uid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordEndSuEvent($uid, $tuid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.User.Delete' Delegete function
	 *
	 * @param xoopsUser $xoopsUser
	 */
	public static function userDelete($xoopsUser){
		// FIXME: this delegate is different policy with others!
		// this should handle only logging, and should not operates
		// user information deleting action!
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		global $xoopsDB;
		$uid = $xoopsUser->get('uid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$notification = new Xoonips_Notification($xoopsDB, $dirname, $trustDirname);
			$itemBean = Xoonips_BeanFactory::getBean('ItemBean', $dirname, $trustDirname);
			$itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $dirname, $trustDirname);
			$itemTitleBean = Xoonips_BeanFactory::getBean('ItemTitleBean', $dirname, $trustDirname);
			$itemKeywordBean = Xoonips_BeanFactory::getBean('ItemKeywordBean', $dirname, $trustDirname);
			$itemRelatedToBean = Xoonips_BeanFactory::getBean('ItemRelatedToBean', $dirname, $trustDirname);
			$itemExtendBean = Xoonips_BeanFactory::getBean('ItemExtendBean', $dirname, $trustDirname);
			$itemFileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $dirname, $trustDirname);
			$changeLogBean = Xoonips_BeanFactory::getBean('ItemChangeLogBean', $dirname, $trustDirname);
			$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $dirname, $trustDirname);
			$indexItemLink = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $dirname, $trustDirname);
			// delete items
			$itemIdSingle = array();
			$itemIdSingle = $itemUsersBean->getItemsWithOwner($uid);
			if ($itemIdSingle != false && count($itemIdSingle) != 0) {
				$itemExtendTable = $itemExtendBean->getItemExtendTable();
				foreach ($itemIdSingle as $itemSingle) {
					if (!$itemBean->delete($itemSingle['item_id'])
							|| !$itemUsersBean->delete($itemSingle['item_id'])
							|| !$itemTitleBean->delete($itemSingle['item_id'])
							|| !$itemKeywordBean->delete($itemSingle['item_id'])
							|| !$itemRelatedToBean->deleteBoth($itemSingle['item_id'])
							|| !$itemFileBean->delete($itemSingle['item_id'])
							|| !$changeLogBean->delete($itemSingle['item_id'])) {
						redirect_header(XOOPS_URL . '/', 3, 'delete error!');
					} else {
						$log->recordDeleteItemEvent($itemSingle['item_id']);
					}
					//delete item_extend
					if (count($itemExtendTable) != 0) {
						foreach ($itemExtendTable as $tableName) {
							if (!$itemExtendBean->delete($itemSingle['item_id'], $tableName)) {
								redirect_header(XOOPS_URL . '/', 3, 'delete itemExtend error!');
							}
						}
					}
				}
			}
			// delete item users
			$itemIdMore = array();
			$itemIdMore = $itemUsersBean->getItemsWithOwners($uid);
			if ($itemIdMore != false && count($itemIdMore) != 0) {
				if (!$itemUsersBean->deleteAllByUid($uid)) {
					redirect_header(XOOPS_URL . '/', 3, 'delete groupItemUsers error!');
				} else {
					foreach ($itemIdMore as $itemMore) {
						$itemUsersInfo = $itemUsersBean->getItemUsersInfo($itemMore['item_id']);
						$itemUsersId = array();
						foreach ($itemUsersInfo as $itemUser) {
							$itemUsersId[] = $itemUser['uid'];
						}
						//log :57
						$log->recordDeleteItemUserEvent($itemMore['item_id'], $uid);
						//event
						if ($itemUsersId != false && count($itemUsersId) != 0) {
							$notification->userDeleteItemUser($itemMore['item_id'], $userInfo['uname'], $itemUsersId);
						}
					}
				}
			}
			//delete index
			$indexIds = array();
			$indexIds = $indexBean->getPrivateIndexes($uid);
			if (!$indexBean->deleteIndexByUid($uid)) {
				redirect_header(XOOPS_URL . '/', 3, 'delete index error!');
			}
			//delete index_item_link
			if ($indexIds != false && count($indexIds) != 0) {
				foreach ($indexIds as $indexId) {
					if (!$indexItemLink->deleteByIndexId($indexId['index_id'])) {
						redirect_header(XOOPS_URL . '/', 3, 'delete indexItemLink error!');
					} else {
						$log->recordDeleteIndexEvent($indexId['index_id']);
					}
				}
			}

			// user delete log
			$log->recordDeleteAccountEvent($uid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.Member.Join' Delegete function
	 *
	 * @param xoopsUser $xoopsUser
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupMemberJoin($xoopsUser, $xoopsGroup){
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$uid = $xoopsUser->get('uid');
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordInsertGroupMemberEvent($uid, $gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.Member.JoinRequest' Delegete function
	 *
	 * @param xoopsUser $xoopsUser
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupMemberJoinRequest($xoopsUser, $xoopsGroup) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$uid = $xoopsUser->get('uid');
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordRequestJoinGroupEvent($uid, $gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.Member.JoinCertify' Delegete function
	 *
	 * @param xoopsUser $xoopsUser
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupMemberJoinCertify($xoopsUser, $xoopsGroup) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$uid = $xoopsUser->get('uid');
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordCertifyJoinGroupEvent($uid, $gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.Member.JoinReject' Delegete function
	 *
	 * @param xoopsUser $xoopsUser
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupMemberJoinReject($xoopsUser, $xoopsGroup) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$uid = $xoopsUser->get('uid');
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordRejectJoinGroupEvent($uid, $gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.Member.Leave' Delegete function
	 *
	 * @param xoopsUser $xoopsUser
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupMemberLeave($xoopsUser, $xoopsGroup) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$uid = $xoopsUser->get('uid');
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordDeleteGroupMemberEvent($uid, $gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.Member.LeaveRequest' Delegete function
	 *
	 * @param xoopsUser $xoopsUser
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupMemberLeaveRequest($xoopsUser, $xoopsGroup) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$uid = $xoopsUser->get('uid');
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordRequestLeaveGroupEvent($uid, $gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.Member.LeaveCertify' Delegete function
	 *
	 * @param xoopsUser $xoopsUser
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupMemberLeaveCertify($xoopsUser, $xoopsGroup) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$uid = $xoopsUser->get('uid');
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordCertifyLeaveGroupEvent($uid, $gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.Member.LeaveReject' Delegete function
	 *
	 * @param xoopsUser $xoopsUser
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupMemberLeaveReject($xoopsUser, $xoopsGroup) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$uid = $xoopsUser->get('uid');
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordRejectLeaveGroupEvent($uid, $gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.CertifyRequest' Delegete function
	 *
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupCertifyRequest($xoopsGroup){
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordRequestGroupEvent($gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.Certify' Delegete function
	 *
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupCertify($xoopsGroup){
		// FIXME: this delegate is different policy with others!
		// this should handle only logging, and should not operates
		// group information creating action!
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$gid = $xoopsGroup->get('groupid');
		$success = true;
		foreach ($dirnames as $dirname) {
			$groupbean = Xoonips_BeanFactory::getBean('GroupsBean', $dirname);
			$group = $groupbean->getGroup($gid);
			$index = array(
				'parent_index_id' => 1,
				'groupid' => $gid,
				'open_level' => 2,
				'title' => $group['name']
			);
			$indexbean = Xoonips_BeanFactory::getBean('IndexBean', $dirname, $trustDirname);
			$index_id = $indexbean->insertGroupIndex($index);
			//insert index
			if (!$index_id) {
				$success = false;
				continue;
			}
			//update group index
			if (!$groupbean->updateGroupIndex($gid, $index_id)) {
				$success = false;
				continue;
			}
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordCertifyGroupEvent($gid);
		}
		if (!$success)
			redirect_header(XOOPS_URL . '/', 3, 'DB error!');
	}

	/**
	 * 'Module.Xoonips.Event.Group.Reject' Delegete function
	 *
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupReject($xoopsGroup){
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordRejectGroupEvent($gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.Delete.Request' Delegete function
	 *
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupDeleteRequest($xoopsGroup) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordRequestDeleteGroupEvent($gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.Delete.Certify' Delegete function
	 *
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupDeleteCertify($xoopsGroup){
		// FIXME: this delegate is different policy with others!
		// this should handle only logging, and should not operates
		// group information deleting action!
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$gid = $xoopsGroup->get('groupid');
		$success = true;
		foreach ($dirnames as $dirname) {
			$indexbean = Xoonips_BeanFactory::getBean('IndexBean', $dirname, $trustDirname);
			$indexDelete = $indexbean->deleteGroupIndex($gid);
			//delete index
			if (!$indexDelete) {
				$success = false;
				continue;
			}
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordDeleteGroupEvent($gid);
		}
		if (!$success)
			redirect_header(XOOPS_URL . '/', 3, 'delete error!');
	}

	/**
	 * 'Module.Xoonips.Event.Group.DeleteReject' Delegete function
	 *
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupDeleteReject($xoopsGroup){
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordRejectDeleteGroupEvent($gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.Edit' Delegete function
	 *
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupEdit($xoopsGroup) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$groupbean = Xoonips_BeanFactory::getBean('GroupsBean', $dirname);
			$group = $groupbean->getGroup($gid);
			$index = array(
				'parent_index_id' => 1,
				'groupid' => $gid,
				'open_level' => 2,
				'title' => $group['name']
			);
			$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $dirname, $trustDirname);
			//update index
			if (!$indexBean->updateRootGroupIndex($index)) {
				$response->setSystemError('DB error!');
				return false;
			}
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordUpdateGroupEvent($gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.OpenRequest' Delegete function
	 *
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupOpenRequest($xoopsGroup){
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordRequestGroupOpenEvent($gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.OpenCertify' Delegete function
	 *
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupOpenCertify($xoopsGroup){
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordCertifyGroupOpenEvent($gid);
			$statusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $dirname, $trustDirname);
			$statusBean->insertByGroupOpen($gid);
			$statusBean->updateByGroupOpen($gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.OpenReject' Delegete function
	 *
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupOpenReject($xoopsGroup){
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordRejectGroupOpenEvent($gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.CloseRequest' Delegete function
	 *
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupCloseRequest($xoopsGroup){
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordRequestGroupCloseEvent($gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.CloseCertify' Delegete function
	 *
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupCloseCertify($xoopsGroup){
		// FIXME: this delegate is different policy with others!
		// this should handle only logging, and should not operates
		// public item information modification!
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordCertifyGroupCloseEvent($gid);
			$statusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $dirname, $trustDirname);
			$statusBean->deleteByGroupOpen($gid);
		}
	}

	/**
	 * 'Module.Xoonips.Event.Group.CloseReject' Delegete function
	 *
	 * @param xoopsUser $xoopsUser
	 * @param xoopsGroup $xoopsGroup
	 */
	public static function groupCloseReject($xoopsGroup){
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		$gid = $xoopsGroup->get('groupid');
		foreach ($dirnames as $dirname) {
			$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
			$log->recordRejectGroupCloseEvent($gid);
		}
	}

}

