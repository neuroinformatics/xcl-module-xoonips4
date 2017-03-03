<?php

require_once dirname(dirname(__FILE__)) . '/core/ActionBase.class.php';

class Xoonips_EditIndexAction extends Xoonips_ActionBase {

	protected function doInit(&$request, &$response) {
		global $xoopsUser;
		$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
		$viewData = array();
		$indexId = $request->getParameter('index_id');
		$uid = $xoopsUser->getVar('uid');
		if ($indexId == null) {
			$index = $indexBean->getPrivateIndex($uid);
			$indexId = $index['index_id'];
		} else {
			$index = $indexBean->getIndex($indexId);
			if (!$indexBean->checkWriteRight($indexId, $uid)) {
				// User doesn't have the right to write.
				$response->setSystemError(_NOPERM);
				return false;
			}
		}

		//if private index
		if ($index['open_level'] == XOONIPS_OL_PRIVATE) {
			$breadcrumbsName = _MD_XOONIPS_INDEX_PANKUZU_EDIT_PRIVATE_INDEX_KEYWORD;
			$limitLabel = _MD_XOONIPS_INDEX_NUMBER_OF_PRIVATE_INDEX_LABEL;
			$indexNumberLimit = Xoonips_Utils::getXooNIpsConfig($this->dirname, 'private_index_number_limit');
			$indexCount =  $indexBean->countUserIndexes($index['uid']);
			//if group index
		} else if ($index['open_level'] == XOONIPS_OL_GROUP_ONLY) {
			$breadcrumbsName = _MD_XOONIPS_INDEX_PANKUZU_EDIT_GROUP_INDEX_KEYWORD;
			$limitLabel = _MD_XOONIPS_INDEX_NUMBER_OF_GROUP_INDEX_LABEL;
			$groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname);
			$group = $groupBean->getGroup($index['groupid']);
			$indexNumberLimit = $group['index_number_limit'];
			$indexCount =  $indexBean->countGroupIndexes($index['groupid']);
			// if public index
		} else {
			$breadcrumbsName = _MD_XOONIPS_INDEX_PANKUZU_EDIT_PUBLIC_INDEX_KEYWORD;
		}
		$breadcrumbs = array(
			array(
		    	'name' => $breadcrumbsName
			)
		);
		$viewData['xoops_breadcrumbs'] = $breadcrumbs;
		// if not public index
		if ($index['open_level'] != XOONIPS_OL_PUBLIC) {
			$viewData['limitLabel'] = $limitLabel;
			$viewData['indexNumberLimit'] = $indexNumberLimit;
			$viewData['indexCount'] = $indexCount;
		}
		$childIndexes = $indexBean->getChildIndexes($indexId);
		$viewData['childCount'] = count($childIndexes);
		$viewData['open_level'] = $index['open_level'];
		$viewData['xid'] = $indexId;

		$fullPathIndexes = $indexBean->getFullPathIndexes($indexId);
		if ($fullPathIndexes[0]['uid'] == $uid) {
			$fullPathIndexes[0]['html_title'] = 'Private';
		}
		$viewData['index_path'] = $fullPathIndexes;
		foreach ($childIndexes as $key => $value) {
			$childIndexes[$key]['title'] = $value['title'];
			$childIndexes[$key]['isLocked'] = $indexBean->isLocked($value['index_id']);
		}
		$viewData['child_indexes'] = $childIndexes;
		$viewData['error_message'] = false;
		$token_ticket = $this->createToken($this->modulePrefix('edit_index'));
		$viewData['token_ticket'] = $token_ticket;
		$viewData['dirname'] = $this->dirname;
		$viewData['mytrustdirname'] = $this->trustDirname;
		$response->setViewData($viewData);
		$response->setForward('init_success');
		return true;
	}

	protected function doSave(&$request, &$response) {
		if (!$this->validateToken($this->modulePrefix('edit_index'))) {
			$response->setSystemError('Ticket error');
			return false;
		}
		$indexId = $request->getParameter('index_id');
		$indexIds = $request->getParameter('indexIds');
		$weights = $request->getParameter('weights');
		$this->startTransaction();
		foreach ($weights as $key => $value) {
			if (($key + 1) != $value) {
				// update weight
				$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
				$ret = $indexBean->updateWeight($indexIds[$key], $key + 1);
				$this->log->recordMoveIndexEvent($indexIds[$key]);
				if (!$ret) {
					$response->setSystemError('DB error!');
					return false;
				}
			}
		}
		$viewData['url'] = "editindex.php?index_id=$indexId";
		$viewData['redirect_msg'] = _MD_XOONIPS_MSG_DBUPDATED;
		$viewData['dirname'] = $this->dirname;
		$viewData['mytrustdirname'] = $this->trustDirname;
		$response->setViewData($viewData);
		$response->setForward('save_success');
		return true;
	}

	protected function doIndexEdit(&$request, &$response) {
		global $xoopsUser;
		$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
		$viewData = array();
		$indexId = $request->getParameter('index_id');
		$parentIndexId = $request->getParameter('parent_index_id');
		$uid = $xoopsUser->getVar('uid');

		// if update mode
		if ($indexId != null) {
			// check write right
			if (!$indexBean->checkWriteRight($indexId, $uid)) {
				// User doesn't have the right to write.
				$response->setSystemError(_NOPERM);
				return false;
			}
			$index = $indexBean->getIndex($indexId);
			if ($index == false) {
				$response->setSystemError(_MD_XOONIPS_ERROR_DELETE_NOTEXIST_INDEX);
				return false;
			}

			if ($indexBean->isLocked($indexId)) {
				$response->setSystemError(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_INDEX);
				return false;
			}
			// if registry mode
		} else {
			// check write right
			if (!$indexBean->checkWriteRight($parentIndexId, $uid)) {
				// User doesn't have the right to write.
				$response->setSystemError(_NOPERM);
				return false;
			}
			$index = false;
			if (!$this->checkIndexLimit($response, $parentIndexId)) {
				return false;
			}
			if ($indexBean->isLocked($parentIndexId)) {
				$response->setSystemError(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_INDEX);
				return false;
			}
		}
		$viewData['index'] = $index;
		$viewData['parent_index_id'] = $parentIndexId;
		$token_ticket = $this->createToken($this->modulePrefix('update_index'));
		$viewData['token_ticket'] = $token_ticket;
		$viewData['dirname'] = $this->dirname;
		$viewData['mytrustdirname'] = $this->trustDirname;
		$response->setViewData($viewData);
		$response->setForward('indexEdit_success');
		return true;
	}

	protected function doUpdate(&$request, $response) {
		global $xoopsUser;
		global $xoopsDB;
		$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
		$errors = new Xoonips_Errors();
		$viewData = array();
		$index = array();
		$indexId = $request->getParameter('index_id');
		$parentIndexId = $request->getParameter('parent_index_id');
		$title = $request->getParameter('title');
		if (_CHARSET != 'UTF-8') {
			$title = mb_convert_encoding($title, _CHARSET, 'utf-8');
		}
		if (!$this->validateToken($this->modulePrefix('update_index'))) {
			$response->setSystemError('Ticket error');
			return false;
		}

		// if not input
		if (trim($title) == '') {
			$errors = new Xoonips_Errors();
			$parameters[] = _MD_XOONIPS_INDEX_TITLE;
			$errors->addError('_MD_XOONIPS_ERROR_REQUIRED', 'title', $parameters);
			$index['index_id'] = $indexId;
			$index['title'] = $title;
			$viewData['index'] = $index;
			$viewData['parent_index_id'] = $parentIndexId;
			$viewData['errors'] = $errors->getView($this->dirname);
			$token_ticket = $this->createToken($this->modulePrefix('update_index'));
			$viewData['token_ticket'] = $token_ticket;
			$response->setViewData($viewData);
			$response->setForward('update_error');
			return true;
		}

		// if update mode
		if ($indexId != null) {
			$index = $indexBean->getIndex($indexId);
			if ($index == false) {
				$response->setSystemError(_MD_XOONIPS_ERROR_DELETE_NOTEXIST_INDEX);
				return false;
			}
			if ($index['open_level'] != XOONIPS_OL_PRIVATE && $index['title'] != $title && $indexBean->isLocked($indexId)) {
				$response->setSystemError(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_INDEX);
				return false;
			}
			// if same title index
			if($indexBean->hasSameNameIndex($index['parent_index_id'], $title, $indexId)) {
				$viewData['warnings'] = sprintf(_MD_XOONIPS_INDEX_TITLE_CONFLICT, $title);
			}
			$oldTitle = $index['title'];
			$index['title'] = $title;
			$notification_context = $this->notification->beforeUserIndexRenamed($indexId);
			$indexBean->updateIndex($index);
			if ($oldTitle != $title) {
				// write log
				$this->log->recordUpdateIndexEvent($indexId);

				// event
				$this->notification->afterUserIndexRenamed($notification_context);
			}
			$viewData['callbackvalue'] = _MD_XOONIPS_MSG_DBUPDATED;
			// if registry mode
		} else {
			$index = $indexBean->getIndex($parentIndexId);
			if ($index == false) {
				$response->setSystemError(_MD_XOONIPS_ERROR_DELETE_NOTEXIST_INDEX);
				return false;
			}

			if (!$this->checkIndexLimit($response, $parentIndexId)) {
				return false;
			}

			// if same title index
			if ($indexBean->hasSameNameIndex($parentIndexId, $title)) {
				$viewData['warnings'] = sprintf(_MD_XOONIPS_INDEX_TITLE_CONFLICT, $title);
			}
			$index['title'] = $title;
			$index['parent_index_id'] = $parentIndexId;
			$indexBean->insertIndex($index);
			$newIndexId = $xoopsDB->getInsertId();
			// write log
			$this->log->recordInsertIndexEvent($newIndexId);
			$viewData['callbackvalue'] = _MD_XOONIPS_MSG_DBREGISTERED;
		}
		$viewData['callbackid'] = "editindex.php?index_id=$parentIndexId";
		$viewData['dirname'] = $this->dirname;
		$viewData['mytrustdirname'] = $this->trustDirname;
		$response->setViewData($viewData);
		$response->setForward('update_success');
		return true;
	}

	protected function doIndexMove(&$request, &$response) {
		global $xoopsUser;
		$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
		$errors = new Xoonips_Errors();
		$viewData = array();
		$indexId = $request->getParameter('index_id');
		$uid = $xoopsUser->getVar('uid');

		$index = $indexBean->getIndex($indexId);
		if (!$index) {
			$response->setSystemError(_MD_XOONIPS_ERROR_DELETE_NOTEXIST_INDEX);
			return false;
		}

		if (!$indexBean->checkWriteRight($indexId, $uid)) {
			// User doesn't have the right to write.
			$response->setSystemError(_NOPERM);
			return false;
		}

		if ($indexBean->isLocked($indexId)) {
			$response->setSystemError(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_INDEX);
			return false;
		}

		// if private index
		if ($index['open_level'] == XOONIPS_OL_PRIVATE) {
			$rootIndex = $indexBean->getPrivateIndex($uid);
			// if group index
		} elseif ($index['open_level'] == XOONIPS_OL_GROUP_ONLY) {
			$rootIndex = $indexBean->getRootIndex($indexId);
			// if public index
		} else {
			$rootIndex = $indexBean->getPublicIndex();
		}
		$indexPath = $indexBean->getIndexPath($rootIndex['index_id'], $indexId);
		$viewData['index'] = $index;
		$viewData['index_path']= $indexPath;
		$token_ticket = $this->createToken($this->modulePrefix('move_index'));
		$viewData['token_ticket'] = $token_ticket;
		$viewData['dirname'] = $this->dirname;
		$viewData['mytrustdirname'] = $this->trustDirname;
		$response->setViewData($viewData);
		$response->setForward('indexMove_success');
		return true;
	}

	protected function doMove(&$request, &$response) {
		global $xoopsUser;
		$indexId = $request->getParameter('index_id');
		$moveto = $request->getParameter('moveto');
		$uid=$xoopsUser->getVar('uid');
		$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);

		if (!$this->validateToken($this->modulePrefix('move_index'))) {
			$response->setSystemError('Ticket error');
			return false;
		}

		// if locked
		if ($indexBean->isLocked($indexId)) {
			$response->setSystemError(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_INDEX);
			return false;
		}

		$index = $indexBean->getIndex($indexId);
		$parentIndexId = $index['parent_index_id'];

		// if move to child index
		if ($indexBean->isChild($indexId, $moveto)) {
			$response->setSystemError(_MD_XOONIPS_INDEX_BAD_MOVE);
			return false;
		}

		$notification_context = $this->notification->beforeUserIndexMoved($indexId);
		if (!$indexBean->moveto($indexId, $moveto)) {
			return false;
		}

		// write event log
		$this->log->recordMoveIndexEvent($indexId);
			
		// notificate move to item's owner
		$this->notification->afterUserIndexMoved($notification_context);

		$viewData['callbackid'] = "editindex.php?index_id=$parentIndexId";
		$viewData['callbackvalue'] = _MD_XOONIPS_MSG_DBUPDATED;
		$viewData['dirname'] = $this->dirname;
		$viewData['mytrustdirname'] = $this->trustDirname;
		$response->setViewData($viewData);
		$response->setForward('move_success');
		return true;
	}

	protected function doIndexDelete(&$request, &$response) {
		global $xoopsUser;
		$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
		$errors = new Xoonips_Errors();
		$viewData = array();
		$indexId = $request->getParameter('index_id');
		$uid = $xoopsUser->getVar('uid');
		$index = $indexBean->getIndex($indexId);
		if (!$index) {
			$response->setSystemError(_MD_XOONIPS_ERROR_DELETE_NOTEXIST_INDEX);
			return false;
		}

		if (!$indexBean->checkWriteRight($indexId, $uid)) {
			// User doesn't have the right to write.
			$response->setSystemError(_NOPERM);
			return false;
		}

		if ($indexBean->isLocked($indexId)) {
			$response->setSystemError(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_INDEX);
			return false;
		}

		$viewData['index'] = $index;
		$token_ticket = $this->createToken($this->modulePrefix('delete_index'));
		$viewData['token_ticket'] = $token_ticket;
		$viewData['dirname'] = $this->dirname;
		$viewData['mytrustdirname'] = $this->trustDirname;
		$response->setViewData($viewData);
		$response->setForward('indexDelete_success');
		return true;
	}

	protected function doDelete(&$request, &$response) {
		global $xoopsUser;
		$viewData = array();
		$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
		$uid = $xoopsUser->getVar('uid');
		$indexId = $request->getParameter('index_id');

		if (!$this->validateToken($this->modulePrefix('delete_index'))) {
			$response->setSystemError('Ticket error');
			return false;
		}

		$index = $indexBean->getIndex($indexId);
		if (!$index) {
			$response->setSystemError(_MD_XOONIPS_ERROR_DELETE_NOTEXIST_INDEX);
			return false;
		}
		if ($indexBean->isLocked($indexId)) {
			$response->setSystemError(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_INDEX);
			return false;
		}


		$indexes[] = $index;
		$childIndexes = $indexBean->getAllChildIndexes($indexId);
		$parentIndexId = $index['parent_index_id'];
		$indexIds[] = $index['index_id'];
		foreach ($childIndexes as $idx) {
			$indexIds[] = $idx['index_id'];
		}

		$notification_contexts = array();
		foreach ($indexIds as $xid) {
			$notification_contexts[$xid] = $this->notification->beforeUserIndexDeleted($xid);
		}
		// get linked item's ids
		$itemIds = $indexBean->getItemIds($indexIds);

		// delete index
		$indexBean->deleteIndexItemLink($indexIds);

		// delete index item link
		$indexBean->deleteIndex($indexIds);

		// if private index
		if ($index['open_level'] == XOONIPS_OL_PRIVATE) {
			// get root index
			$privateIndex = $indexBean->getPrivateIndex($uid);

			foreach ($itemIds as $itemId) {
				// if item is not linked in private index after index delete
				if (!$indexBean->isLinkedItem($privateIndex['index_id'], $itemId)) {
					// link to parent index
					$indexBean->insertIndexItemLink($index['parent_index_id'], $itemId);
				}
			}
			// if public index
		} elseif ($index['open_level'] == XOONIPS_OL_PUBLIC) {
			foreach ($itemIds as $itemId) {
				// if item is not linked in public ,public group
				if (!$indexBean->isLinked2PublicItem($itemId)) {
					$itemStatusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $this->dirname, $this->trustDirname);
					$itemStatusBean->delete($itemId);
				}
			}
			// if group index
		} else {
			// if public group
			$groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname);
			if($groupBean->isPublic($index['groupid'])) {
				foreach ($itemIds as $itemId) {
					// if item is not linked in public ,public group
					if (!$indexBean->isLinked2PublicItem($itemId)) {
						$itemStatusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $this->dirname, $this->trustDirname);
						$itemStatusBean->delete($itemId);
					}
				}
			}
		}

		// record events(delete index)
		foreach ($indexIds as $xid) {
			$this->log->recordDeleteIndexEvent($xid);
			$this->notification->afterUserIndexDeleted($notification_contexts[$xid], $parentIndexId);
		}
		$viewData['callbackid'] = "editindex.php?index_id=$parentIndexId";
		$viewData['callbackvalue'] = _MD_XOONIPS_MSG_DBDELETED;
		$viewData['dirname'] = $this->dirname;
		$viewData['mytrustdirname'] = $this->trustDirname;
		$response->setViewData($viewData);
		$response->setForward('delete_success');
		return true;
	}

	protected function doFinish(&$request, &$response) {
		$viewData['url'] = $request->getParameter('url');
		$viewData['redirect_msg'] = $request->getParameter('redirect_msg');
		$viewData['dirname'] = $this->dirname;
		$viewData['mytrustdirname'] = $this->trustDirname;
		$response->setViewData($viewData);
		$response->setForward('finish_success');
		return true;
	}

	private function checkIndexLimit(&$response, $parentIndexId) {
		global $xoopsUser;
		$uid = $xoopsUser->getVar('uid');
		$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
		$rootIndex = $indexBean->getRootIndex($parentIndexId);
		if ($rootIndex['open_level'] == XOONIPS_OL_PUBLIC) {
			return true;
			// if private index
		} elseif ($rootIndex['open_level'] == XOONIPS_OL_PRIVATE) {
			$indexNumberLimit = Xoonips_Utils::getXooNIpsConfig($this->dirname, 'private_index_number_limit');
			$indexCount =  $indexBean->countUserIndexes($rootIndex['uid']);
			//if group index
		} elseif ($rootIndex['open_level'] == XOONIPS_OL_GROUP_ONLY) {
			$groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname);
			$group = $groupBean->getGroup($rootIndex['groupid']);
			$indexNumberLimit = $group['index_number_limit'];
			$indexCount =  $indexBean->countGroupIndexes($rootIndex['groupid']);
		}

		// if over index number limit
		if ($indexNumberLimit != 0 && $indexCount >= $indexNumberLimit) {
			$response->setSystemError(_MD_XOONIPS_INDEX_TOO_MANY_INDEXES);
			return false;
		}
		return true;
	}
}
