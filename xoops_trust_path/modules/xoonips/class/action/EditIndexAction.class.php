<?php

use Xoonips\Core\Functions;
use Xoonips\Core\XoopsUtils;

require_once dirname(__DIR__).'/core/ActionBase.class.php';

class Xoonips_EditIndexAction extends Xoonips_ActionBase
{
    protected function doInit(&$request, &$response)
    {
        // index list
        $uid = XoopsUtils::getUid();
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexId = intval($request->getParameter('index_id'));
        if (0 == $indexId) {
            $index = $indexBean->getPrivateIndex($uid);
            $indexId = $index['index_id'];
        } else {
            $index = $indexBean->getIndex($indexId);
        }
        if (empty($index) || !$indexBean->checkWriteRight($indexId, $uid)) {
            // User doesn't have the right to write.
            $response->setSystemError(_NOPERM);

            return false;
        }

        //if private index
        if (XOONIPS_OL_PRIVATE == $index['open_level']) {
            $breadcrumbsName = _MD_XOONIPS_INDEX_PANKUZU_EDIT_PRIVATE_INDEX_KEYWORD;
            $limitLabel = _MD_XOONIPS_INDEX_NUMBER_OF_PRIVATE_INDEX_LABEL;
            $indexNumberLimit = Functions::getXoonipsConfig($this->dirname, 'private_index_number_limit');
            $indexCount = $indexBean->countUserIndexes($index['uid']);
        //if group index
        } elseif (XOONIPS_OL_GROUP_ONLY == $index['open_level']) {
            $breadcrumbsName = _MD_XOONIPS_INDEX_PANKUZU_EDIT_GROUP_INDEX_KEYWORD;
            $limitLabel = _MD_XOONIPS_INDEX_NUMBER_OF_GROUP_INDEX_LABEL;
            $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname);
            $group = $groupBean->getGroup($index['groupid']);
            $indexNumberLimit = $group['index_number_limit'];
            $indexCount = $indexBean->countGroupIndexes($index['groupid']);
        // if public index
        } else {
            $breadcrumbsName = _MD_XOONIPS_INDEX_PANKUZU_EDIT_PUBLIC_INDEX_KEYWORD;
        }

        $breadcrumbs = [
            [
                'name' => $breadcrumbsName,
            ],
        ];

        $viewData = [];
        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        // if not public index
        if (XOONIPS_OL_PUBLIC != $index['open_level']) {
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
        $viewData['itemListUrl'] = Functions::getItemListUrl($this->dirname);
        $response->setViewData($viewData);
        $response->setForward('init_success');

        return true;
    }

    protected function doSave(&$request, &$response)
    {
        // change order of child indexes
        $uid = XoopsUtils::getUid();
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexId = intval($request->getParameter('index_id'));
        $index = $indexBean->getIndex($indexId);
        if (empty($index) || !$indexBean->checkWriteRight($indexId, $uid)) {
            // User doesn't have the right to write.
            $response->setSystemError(_NOPERM);

            return false;
        }

        if (!$this->validateToken($this->modulePrefix('edit_index'))) {
            $response->setSystemError('Ticket error');

            return false;
        }
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

        $viewData = [];
        $viewData['url'] = "editindex.php?index_id=$indexId";
        $viewData['redirect_msg'] = _MD_XOONIPS_MSG_DBUPDATED;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('save_success');

        return true;
    }

    protected function doIndexEdit(&$request, &$response)
    {
        // now index edit mode is implemented into indexdescription.php
        // this action is only used for new creation
        $uid = XoopsUtils::getUid();
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $parentIndexId = intval($request->getParameter('parent_index_id'));
        $parentIndex = $indexBean->getIndex($parentIndexId);
        if (empty($parentIndex) || !$indexBean->checkWriteRight($parentIndexId, $uid)) {
            // User doesn't have the right to write.
            $response->setSystemError(_NOPERM);

            return false;
        }
        if (!$this->checkIndexLimit($response, $parentIndexId)) {
            return false;
        }
        if ($indexBean->isLocked($parentIndexId)) {
            $response->setSystemError(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_INDEX);

            return false;
        }
        $index = [
            'index_id' => 0,
            'title' => '',
        ];

        $viewData = [];
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

    protected function doUpdate(&$request, $response)
    {
        // now index edit mode is implemented into indexdescription.php
        // this action is only used for new creation
        $uid = XoopsUtils::getUid();
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $parentIndexId = intval($request->getParameter('parent_index_id'));
        $parentIndex = $indexBean->getIndex($parentIndexId);
        if (empty($parentIndex) || !$indexBean->checkWriteRight($parentIndexId, $uid)) {
            // User doesn't have the right to write.
            $response->setSystemError(_NOPERM);

            return false;
        }
        if (!$this->checkIndexLimit($response, $parentIndexId)) {
            return false;
        }
        if ($indexBean->isLocked($parentIndexId)) {
            $response->setSystemError(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_INDEX);

            return false;
        }

        if (!$this->validateToken($this->modulePrefix('update_index'))) {
            $response->setSystemError('Ticket error');

            return false;
        }

        global $xoopsDB;
        $errors = new Xoonips_Errors();
        $index = [
            'index_id' => 0,
            'parent_index_id' => $parentIndexId,
            'uid' => $parentIndexId['uid'],
            'group_id' => $parentIndex['group_id'],
            'open_level' => $parentIndex['open_level'],
            'title' => trim($request->getParameter('title')),
        ];

        $viewData = [];
        // if not input
        if ('' == $index['title']) {
            $errors = new Xoonips_Errors();
            $parameters[] = _MD_XOONIPS_INDEX_TITLE;
            $errors->addError('_MD_XOONIPS_ERROR_REQUIRED', 'title', $parameters);
            $viewData['index'] = $index;
            $viewData['parent_index_id'] = $parentIndexId;
            $viewData['errors'] = $errors->getView($this->dirname);
            $token_ticket = $this->createToken($this->modulePrefix('update_index'));
            $viewData['token_ticket'] = $token_ticket;
            $response->setViewData($viewData);
            $response->setForward('update_error');

            return true;
        }

        // if same title index
        if ($indexBean->hasSameNameIndex($parentIndexId, $title)) {
            $viewData['warnings'] = sprintf(_MD_XOONIPS_INDEX_TITLE_CONFLICT, $title);
        }
        $indexBean->insertIndex($index);
        $newIndexId = $xoopsDB->getInsertId();
        // write log
        $this->log->recordInsertIndexEvent($newIndexId);
        $viewData['callbackvalue'] = _MD_XOONIPS_MSG_DBREGISTERED;

        $viewData['callbackid'] = 'editindex.php?index_id='.$parentIndexId;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('update_success');

        return true;
    }

    protected function doIndexMove(&$request, &$response)
    {
        // edit form for index move
        $uid = XoopsUtils::getUid();
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexId = intval($request->getParameter('index_id'));
        $index = $indexBean->getIndex($indexId);
        if (empty($index) || !$indexBean->checkWriteRight($indexId, $uid)) {
            // User doesn't have the right to write.
            $response->setSystemError(_NOPERM);

            return false;
        }
        if ($indexBean->isLocked($indexId)) {
            $response->setSystemError(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_INDEX);

            return false;
        }

        // if private index
        if (XOONIPS_OL_PRIVATE == $index['open_level']) {
            $rootIndex = $indexBean->getPrivateIndex($uid);
        // if group index
        } elseif (XOONIPS_OL_GROUP_ONLY == $index['open_level']) {
            $rootIndex = $indexBean->getRootIndex($indexId);
        // if public index
        } else {
            $rootIndex = $indexBean->getPublicIndex();
        }

        $viewData = [];
        $viewData['index'] = $index;
        $viewData['index_path'] = $indexBean->getIndexPath($rootIndex['index_id'], $indexId);
        $token_ticket = $this->createToken($this->modulePrefix('move_index'));
        $viewData['token_ticket'] = $token_ticket;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('indexMove_success');

        return true;
    }

    protected function doMove(&$request, &$response)
    {
        // move index to new parent index
        $uid = XoopsUtils::getUid();
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexId = intval($request->getParameter('index_id'));
        $index = $indexBean->getIndex($indexId);
        if (empty($index) || !$indexBean->checkWriteRight($indexId, $uid)) {
            // User doesn't have the right to write.
            $response->setSystemError(_NOPERM);

            return false;
        }
        if ($indexBean->isLocked($indexId)) {
            $response->setSystemError(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_INDEX);

            return false;
        }
        $parentIndexId = intval($request->getParameter('moveto'));
        $parentIndex = $indexBean->getIndex($parentIndexId);
        if (empty($parentIndex) || !$indexBean->checkWriteRight($parentIndexId, $uid)) {
            // User doesn't have the right to write.
            $response->setSystemError(_NOPERM);

            return false;
        }
        if ($indexBean->isChild($indexId, $parentIndexId)) {
            // deny to move to child index
            $response->setSystemError(_MD_XOONIPS_INDEX_BAD_MOVE);

            return false;
        }
        if ($index['open_level'] != $parentIndex['open_level'] || $index['uid'] != $parentIndex['uid'] || $index['groupid'] != $parentIndex['groupid']) {
            // deny to move to deferent index of index type
            $response->setSystemError(_NOPERM);

            return false;
        }

        if (!$this->validateToken($this->modulePrefix('move_index'))) {
            $response->setSystemError('Ticket error');

            return false;
        }

        $prevParentIndexId = $index['parent_index_id'];
        $notification_context = $this->notification->beforeUserIndexMoved($indexId);
        if (!$indexBean->moveto($indexId, $parentIndexId)) {
            return false;
        }

        // write event log
        $this->log->recordMoveIndexEvent($indexId);

        // notificate move to item's owner
        $this->notification->afterUserIndexMoved($notification_context);

        $viewData = [];
        $viewData['callbackid'] = 'editindex.php?index_id='.$prevParentIndexId;
        $viewData['callbackvalue'] = _MD_XOONIPS_MSG_DBUPDATED;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('move_success');

        return true;
    }

    protected function doIndexDelete(&$request, &$response)
    {
        // index delete form
        $uid = XoopsUtils::getUid();
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexId = intval($request->getParameter('index_id'));
        $index = $indexBean->getIndex($indexId);
        if (empty($index) || !$indexBean->checkWriteRight($indexId, $uid)) {
            // User doesn't have the right to write.
            $response->setSystemError(_NOPERM);

            return false;
        }
        if ($indexBean->isLocked($indexId)) {
            $response->setSystemError(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_INDEX);

            return false;
        }
        if (1 == $index['parent_index_id']) {
            // deny delete to root index
            $response->setSystemError(_NOPERM);

            return false;
        }

        $viewData = [];
        $viewData['index'] = $index;
        $token_ticket = $this->createToken($this->modulePrefix('delete_index'));
        $viewData['token_ticket'] = $token_ticket;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('indexDelete_success');

        return true;
    }

    protected function doDelete(&$request, &$response)
    {
        // delete index
        $uid = XoopsUtils::getUid();
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexId = intval($request->getParameter('index_id'));
        $index = $indexBean->getIndex($indexId);
        if (empty($index) || !$indexBean->checkWriteRight($indexId, $uid)) {
            // User doesn't have the right to write.
            $response->setSystemError(_NOPERM);

            return false;
        }
        if ($indexBean->isLocked($indexId)) {
            $response->setSystemError(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_INDEX);

            return false;
        }
        if (1 == $index['parent_index_id']) {
            // deny delete of root index
            $response->setSystemError(_NOPERM);

            return false;
        }

        if (!$this->validateToken($this->modulePrefix('delete_index'))) {
            $response->setSystemError('Ticket error');

            return false;
        }

        $indexes[] = $index;
        $childIndexes = $indexBean->getAllChildIndexes($indexId);
        $parentIndexId = $index['parent_index_id'];
        $indexIds[] = $index['index_id'];
        foreach ($childIndexes as $idx) {
            $indexIds[] = $idx['index_id'];
        }

        $notification_contexts = [];
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
        if (XOONIPS_OL_PRIVATE == $index['open_level']) {
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
        } elseif (XOONIPS_OL_PUBLIC == $index['open_level']) {
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
            if ($groupBean->isPublic($index['groupid'])) {
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

        $viewData = [];
        $viewData['callbackid'] = "editindex.php?index_id=$parentIndexId";
        $viewData['callbackvalue'] = _MD_XOONIPS_MSG_DBDELETED;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('delete_success');

        return true;
    }

    protected function doFinish(&$request, &$response)
    {
        $viewData = [];
        $viewData['url'] = $request->getParameter('url');
        $viewData['redirect_msg'] = $request->getParameter('redirect_msg');
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('finish_success');

        return true;
    }

    private function checkIndexLimit(&$response, $parentIndexId)
    {
        $uid = XoopsUtils::getUid();
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $rootIndex = $indexBean->getRootIndex($parentIndexId);
        if (XOONIPS_OL_PUBLIC == $rootIndex['open_level']) {
            return true;
        // if private index
        } elseif (XOONIPS_OL_PRIVATE == $rootIndex['open_level']) {
            $indexNumberLimit = Functions::getXoonipsConfig($this->dirname, 'private_index_number_limit');
            $indexCount = $indexBean->countUserIndexes($rootIndex['uid']);
        //if group index
        } elseif (XOONIPS_OL_GROUP_ONLY == $rootIndex['open_level']) {
            $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname);
            $group = $groupBean->getGroup($rootIndex['groupid']);
            $indexNumberLimit = $group['index_number_limit'];
            $indexCount = $indexBean->countGroupIndexes($rootIndex['groupid']);
        }

        // if over index number limit
        if (0 != $indexNumberLimit && $indexCount >= $indexNumberLimit) {
            $response->setSystemError(_MD_XOONIPS_INDEX_TOO_MANY_INDEXES);

            return false;
        }

        return true;
    }
}
