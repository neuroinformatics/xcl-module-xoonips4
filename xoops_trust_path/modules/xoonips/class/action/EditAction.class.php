<?php

use Xoonips\Core\FileUtils;
use Xoonips\Core\Functions;
use Xoonips\Core\XoopsUtils;

require_once dirname(__DIR__).'/core/Item.class.php';
require_once dirname(__DIR__).'/core/ItemField.class.php';
require_once dirname(__DIR__).'/core/ActionBase.class.php';
require_once dirname(dirname(__DIR__)).'/include/itemtypetemplate.inc.php';
require_once dirname(__DIR__).'/core/ViewTypeFactory.class.php';

class Xoonips_EditAction extends Xoonips_ActionBase
{
    protected function doInit(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $itemTypeId = $this->getItemtypeIdByItemId($itemId);
        $this->checkAccessPermission($itemId, $uid, 'itemEdit');

        $viewData = [];
        $this->setCommonViewData($viewData, $itemId, $itemTypeId, $request, $response);
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        $viewData['editryView'] = $item->getEditView($itemId);
        $response->setViewData($viewData);
        $response->setForward('init_success');
        // reset cookie
        setcookie('edit_opened_indexes', '', time() - 86400);
        setcookie('edit_checked_indexes', '', time() - 86400);
        setcookie('edit_index_tree_selected_tab', '', time() - 86400);
        foreach ($_COOKIE as $key => $value) {
            if (preg_match('/_item_edit_index_tree_div_/', $key)) {
                setcookie($key, '', time() - 86400);
            }
        }

        return true;
    }

    protected function doComplete(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $itemTypeId = $this->getItemtypeIdByItemId($itemId);
        $this->checkAccessPermission($itemId, $uid, 'itemEdit');

        $viewData = [];
        $this->setCommonViewData($viewData, $itemId, $itemTypeId, $request, $response);
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        // targetItemId is editing item group annd field id. ex. '10:1:11'
        $targetItemId = $request->getParameter('targetItemId');
        $item->setData($_POST, true);
        if (false === $item->complete($targetItemId)) {
            $viewData['relation'] = false;
        }
        $viewData['editryView'] = $item->getEditViewWithData();
        $response->setViewData($viewData);
        $response->setForward('complete_success');

        return true;
    }

    protected function doAddFieldGroup(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $itemTypeId = $this->getItemtypeIdByItemId($itemId);
        $this->checkAccessPermission($itemId, $uid, 'itemEdit');

        $viewData = [];
        $this->setCommonViewData($viewData, $itemId, $itemTypeId, $request, $response);
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        // targetItemId is editing item group annd field id. ex. '10:1:11'
        $targetItemId = $request->getParameter('targetItemId');
        $item->setData($_POST, true);
        $item->addFieldGroup($targetItemId);
        $viewData['editryView'] = $item->getEditViewWithData();
        $response->setViewData($viewData);
        $response->setForward('addFieldGroup_success');

        return true;
    }

    protected function doDeleteFieldGroup(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $itemTypeId = $this->getItemtypeIdByItemId($itemId);
        $this->checkAccessPermission($itemId, $uid, 'itemEdit');

        $viewData = [];
        $this->setCommonViewData($viewData, $itemId, $itemTypeId, $request, $response);
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        // targetItemId is editing item group annd field id. ex. '10:1:11'
        $targetItemId = $request->getParameter('targetItemId');
        $item->setData($_POST, true);
        $item->deleteFieldGroup($targetItemId);
        $viewData['editryView'] = $item->getEditViewWithData();
        $response->setViewData($viewData);
        $response->setForward('deleteFieldGroup_success');

        return true;
    }

    protected function doUploadFile(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $itemTypeId = $this->getItemtypeIdByItemId($itemId);
        $this->checkAccessPermission($itemId, $uid, 'itemEdit');

        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        $item->setData($_POST);
        $viewData['fileUpload'] = $item->fileUpload();
        $response->setViewData($viewData);
        $response->setForward('uploadFile_success');

        return true;
    }

    protected function doDeleteFile(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $itemTypeId = $this->getItemtypeIdByItemId($itemId);
        $this->checkAccessPermission($itemId, $uid, 'itemEdit');

        $viewData = [];
        $this->setCommonViewData($viewData, $itemId, $itemTypeId, $request, $response);
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        // targetItemId is editing item group annd field id. ex. '10:1:11'
        $targetItemId = $request->getParameter('targetItemId');
        $item->setData($_POST, true);
        $item->delFile($targetItemId, intval($request->getParameter('fileId')));
        $viewData['editryView'] = $item->getEditViewWithData();
        $response->setViewData($viewData);
        $response->setForward('deleteFile_success');

        return true;
    }

    protected function doSearchUser(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $this->checkAccessPermission($itemId, $uid, 'itemUsersEdit');

        $this->doCommon($request, $response);
        $response->setForward('searchUser_success');

        return true;
    }

    protected function doDeleteUser(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $this->checkAccessPermission($itemId, $uid, 'itemUsersEdit');

        $this->doCommon($request, $response);
        $response->setForward('deleteUser_success');

        return true;
    }

    protected function doSearchRelatedItem(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $this->checkAccessPermission($itemId, $uid, 'itemEdit');

        $this->doCommon($request, $response);
        $response->setForward('searchRelatedItem_success');

        return true;
    }

    protected function doDeleteRelatedItem(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $this->checkAccessPermission($itemId, $uid, 'itemEdit');

        $this->doCommon($request, $response);
        $response->setForward('deleteRelatedItem_success');

        return true;
    }

    protected function doBack(&$request, &$response)
    {
        $this->doCommon($request, $response);
        $response->setForward('back_success');

        return true;
    }

    protected function doConfirm(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $itemTypeId = $this->getItemtypeIdByItemId($itemId);
        $this->checkAccessPermission($itemId, $uid, 'itemEdit');

        $viewData = [];
        $this->setCommonViewData($viewData, $itemId, $itemTypeId, $request, $response);
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        $errors = new Xoonips_Errors();
        $insertInfo = [];
        $item->setData($_POST, true);
        $item->editCheck($errors, $itemId);
        if (0 != count($errors->getErrors())) {
            $viewData['editryView'] = $item->getEditViewWithData();
            $viewData['errors'] = $errors->getView($this->dirname);
            $response->setViewData($viewData);
            $response->setErrors($errors);
            $response->setForward('confirm_error');
        } else {
            // ticket
            $token_ticket = $this->createToken($this->modulePrefix('confirm_edit'));

            // view data
            $viewData = [];
            $viewData['item_id'] = $itemId;
            $viewData['itemtype_id'] = $itemTypeId;
            $viewData['itemtype_name'] = $this->getItemtypeName($itemTypeId);
            $viewData['token_titcket'] = $token_ticket;

            //$item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
            $item->setData($_POST, true);
            $viewData['confirmView'] = $item->getConfirmView(4);
            $response->setViewData($viewData);
            $response->setForward('confirm_success');
        }

        return true;
    }

    protected function doSave(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $itemTypeId = $this->getItemtypeIdByItemId($itemId);
        $this->checkAccessPermission($itemId, $uid, 'itemEdit');

        if (!$this->validateToken($this->modulePrefix('confirm_edit'))) {
            $response->setSystemError('Ticket error');

            return false;
        }

        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        $item->setData($_POST, true);
        $this->startTransaction();
        $certify_msg = '';
        $ret = $item->doEdit($itemId, $certify_msg, $this->log);
        if ($ret) {
            $viewData['callbackid'] = 'detail.php?item_id='.$itemId;
            if (!empty($certify_msg)) {
                $viewData['callbackvalue'] = $certify_msg;
            } else {
                $viewData['callbackvalue'] = _MD_XOONIPS_MSG_DBUPDATED;
            }
            $response->setViewData($viewData);
            $response->setForward('save_success');

            return true;
        } else {
            if (!empty($certify_msg)) {
                $response->setSystemError($certify_msg);
            } else {
                $response->setSystemError(_MD_XOONIPS_ERROR_DBUPDATE_FAILED);
            }

            return false;
        }
    }

    protected function doFinish(&$request, &$response)
    {
        $viewData['url'] = $request->getParameter('url');
        $viewData['redirect_msg'] = $request->getParameter('redirect_msg');
        $response->setViewData($viewData);
        $response->setForward('finish_success');

        return true;
    }

    protected function doEditIndex(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $this->checkAccessPermission($itemId, $uid, 'itemIndexEdit');

        // get user's index
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $index_id = $indexBean->getItemlistLinkIndex($uid);

        // breadcrumbs
        $breadcrumbs = [
            [
                'name' => _MD_XOONIPS_ITEM_LISTING_ITEM,
                'url' => Functions::getItemListUrl($this->dirname).'?index_id='.$index_id,
            ],
            [
                'name' => _MD_XOONIPS_ITEM_DETAIL_ITEM_TITLE,
                'url' => 'detail.php?item_id='.$itemId,
            ],
            [
                'name' => _MD_XOONIPS_INDEX_EDIT,
            ],
        ];

        $viewData = [];
        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['item_id'] = $itemId;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;

        // get can veiw indexes
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $ids = $indexBean->getCanVeiwIndexes($itemId, $uid);

        $this->getItemInfoByIndexEdit($itemId, implode(',', $ids), $viewData);

        $response->setViewData($viewData);
        $response->setForward('editIndex_success');

        return true;
    }

    protected function doConfirmIndex(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $this->checkAccessPermission($itemId, $uid, 'itemIndexEdit');

        // user's index id
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexId = $indexBean->getItemlistLinkIndex($uid);

        // checked index ids
        $checkedIndexes = $request->getParameter('checked_indexes');

        // index change information
        $edit_index_msgs = [];
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $indexChangeInfos = $itemBean->getIndexChangeInfo($itemId, $checkedIndexes);
        foreach ($indexChangeInfos as $info) {
            foreach ($info as $msg) {
                $edit_index_msgs[] = $msg;
            }
        }

        // ticket
        $token_ticket = $this->createToken($this->modulePrefix('item_index_edit_confirm'));

        // breadcrumbs
        $breadcrumbs = [
            [
                'name' => _MD_XOONIPS_ITEM_LISTING_ITEM,
                'url' => Functions::getItemListUrl($this->dirname).'?index_id='.$indexId,
            ],
            [
                'name' => _MD_XOONIPS_ITEM_DETAIL_ITEM_TITLE,
                'url' => 'detail.php?item_id='.$itemId,
            ],
            [
                'name' => _MD_XOONIPS_ITEM_INDEX_EDIT_CONFIRM_TITLE,
            ],
        ];

        // set view data
        $viewData['item_id'] = $itemId;
        $viewData['checked_indexes'] = $checkedIndexes;
        $viewData['edit_index_msgs'] = $edit_index_msgs;
        $viewData['save_visible'] = (isset($indexChangeInfos[0]) || isset($indexChangeInfos[1])) ? true : false;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;

        $response->setViewData($viewData);
        $response->setForward('confirmIndex_success');

        return true;
    }

    protected function doSaveIndex(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $itemTypeId = $this->getItemtypeIdByItemId($itemId);
        $this->checkAccessPermission($itemId, $uid, 'itemIndexEdit');

        if (!$this->validateToken($this->modulePrefix('item_index_edit_confirm'))) {
            $response->setSystemError('Ticket error');

            return false;
        }

        $checkedIndexes = $request->getParameter('checked_indexes');
        $certify_msg = '';
        $this->startTransaction();
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        $ret = $item->doIndexEdit($itemId, $checkedIndexes, $certify_msg);
        if ($ret) {
            $viewData['url'] = 'detail.php?item_id='.$itemId;
            if (!empty($certify_msg)) {
                $viewData['redirect_msg'] = $certify_msg;
            } else {
                $viewData['redirect_msg'] = _MD_XOONIPS_MSG_DBUPDATED;
            }
            $response->setViewData($viewData);
            $response->setForward('saveIndex_success');

            return true;
        } else {
            if (!empty($certify_msg)) {
                $response->setSystemError($certify_msg);
            } else {
                $response->setSystemError(_MD_XOONIPS_ERROR_DBREGISTRY_FAILED);
            }

            return false;
        }
    }

    protected function doEditOwners(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $this->checkAccessPermission($itemId, $uid, 'itemUsersEdit');

        // get item users
        $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $itemUsersInfo = $itemUsersBean->getItemUsersInfo($itemId);
        $uids = [];
        foreach ($itemUsersInfo as $userInfo) {
            $uids[] = $userInfo['uid'];
        }

        $viewData = [];
        $this->setCommonViewDataByEditOwners($itemId, implode(',', $uids), $viewData);

        $response->setViewData($viewData);
        $response->setForward('editOwners_success');

        return true;
    }

    protected function doSearchOwners(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $this->checkAccessPermission($itemId, $uid, 'itemUsersEdit');

        $uids = $request->getParameter($this->dirname.'CreateUser');

        // item limit check
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $errors = new Xoonips_Errors();
        $uid_arr = explode(',', $uids);
        $uids = '';
        foreach ($uid_arr as $uid) {
            $itemUsed = $itemBean->countUserItems($uid);
            $privateItemLimit = $itemBean->getPrivateItemLimit($uid);
            if ($itemUsed > $privateItemLimit['itemNumber'] - 1 && $privateItemLimit['itemNumber'] > 0) {
                $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
                $user = $userBean->getUserBasicInfo($uid);
                $parameters = [];
                $parameters[] = $user['uname'];
                $errors->addError('_MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT2', '', $parameters);
            } else {
                $uids .= (0 == strlen($uids)) ? $uid : ','.$uid;
            }
        }
        $viewData = [];
        $this->setCommonViewDataByEditOwners($itemId, $uids, $viewData);
        if (0 != count($errors->getErrors())) {
            $viewData['errors'] = $errors->getView($this->dirname);
        }
        $response->setViewData($viewData);
        if (0 != count($errors->getErrors())) {
            $response->setErrors($errors);
        }
        $response->setForward('searchOwners_success');

        return true;
    }

    protected function doDeleteOwners(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $this->checkAccessPermission($itemId, $uid, 'itemUsersEdit');

        $uids = $request->getParameter($this->dirname.'CreateUser');

        $viewData = [];
        $this->setCommonViewDataByEditOwners($itemId, $uids, $viewData);

        $response->setViewData($viewData);
        $response->setForward('deleteOwners_success');

        return true;
    }

    protected function doSaveOwners(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $this->checkAccessPermission($itemId, $uid, 'itemUsersEdit');

        $uids = $request->getParameter($this->dirname.'CreateUser');

        if (!$this->validateToken($this->modulePrefix('item_owners_edit'))) {
            $response->setSystemError('Ticket error');

            return false;
        }
        $deleteuser_err = '';
        $userIds = explode(',', $uids);
        $selectUids = [];
        foreach ($userIds as $userId) {
            $selectUids[] = $userId;
        }
        $ret = $this->updateItemUsers($itemId, $selectUids, $deleteuser_err);
        if ($ret) {
            $viewData['url'] = "detail.php?item_id=$itemId";
            if (!empty($deleteuser_err)) {
                $viewData['redirect_msg'] = $deleteuser_err;
            } else {
                $viewData['redirect_msg'] = _MD_XOONIPS_MSG_DBUPDATED;
            }
            $response->setViewData($viewData);
            $response->setForward('saveOwners_success');

            return true;
        } else {
            $response->setSystemError(_MD_XOONIPS_ERROR_DBREGISTRY_FAILED);

            return false;
        }
    }

    protected function doDeleteConfirm(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $itemTypeId = $this->getItemtypeIdByItemId($itemId);
        $this->checkAccessPermission($itemId, $uid, 'itemDelete');

        $breadcrumbs = [
            [
                'name' => _MD_XOONIPS_ITEM_DETAIL_ITEM_TITLE,
                'url' => 'detail.php?item_id='.$itemId,
            ],
            [
                'name' => _MD_XOONIPS_ITEM_DELETE_ITEM_CONFIRM,
            ],
        ];

        // ticket
        $token_ticket = $this->createToken($this->modulePrefix('item_delete_confirm'));

        // get item infomation
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        $viewData = [];
        $viewData['confirmView'] = $item->getDetailView($itemId);
        $viewData['item_id'] = $itemId;
        $viewData['itemtype_name'] = $this->getItemtypeName($itemTypeId);
        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['token_titcket'] = $token_ticket;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('deleteConfirm_success');

        return true;
    }

    protected function doDelete(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $itemTypeId = $this->getItemtypeIdByItemId($itemId);
        $this->checkAccessPermission($itemId, $uid, 'itemDelete');

        if (!$this->validateToken($this->modulePrefix('item_delete_confirm'))) {
            $response->setSystemError('Ticket error');

            return false;
        }

        // get item users
        $usersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
        $users = $usersBean->getItemUsersInfo($itemId);
        $viewData['url'] = XOOPS_URL.'/';
        if ($users) {
            if (count($users) > 1) {
                $viewData['redirect_msg'] = _MD_XOONIPS_ITEM_CANNOT_DELETE_ITEM;
                $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/detail.php?item_id='.$itemId;
                $response->setViewData($viewData);
                $response->setForward('delete_success');

                return true;
            }
        }
        // start transaction
        $transaction = Xoonips_Transaction::getInstance();
        $transaction->start();

        // delete xoonips_item
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        $certify_msg = '';
        $ret = $item->doDelete($itemId, $certify_msg, $this->log);
        if ($ret) {
            $transaction->commit();

            // delete temp files
            $tmp = Functions::getXoonipsConfig($this->dirname, 'upload_dir');
            $item_dir = $tmp.'/item/'.$itemId;
            if (is_dir($item_dir)) {
                FileUtils::deleteDirectory($item_dir);
            }

            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/';
            $viewData['redirect_msg'] = _MD_XOONIPS_MSG_DBDELETED;
            $response->setViewData($viewData);
            $response->setForward('delete_success');

            return true;
        } else {
            $transaction->rollback();

            if (!empty($certify_msg)) {
                $response->setSystemError($certify_msg);
            } else {
                $response->setSystemError(_MD_XOONIPS_ERROR_DBDELETE_FAILED);
            }

            return false;
        }
    }

    private function setCommonViewDataByEditOwners($itemId, $uids, &$viewData)
    {
        $uid = XoopsUtils::getUid();

        // get item information
        $this->getItemInfoByOwnersEdit($itemId, $uids, $viewData);

        // get user's index id
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexId = $indexBean->getItemlistLinkIndex($uid);

        // breadcrumbs
        $breadcrumbs = [
            [
                'name' => _MD_XOONIPS_ITEM_LISTING_ITEM,
                'url' => Functions::getItemListUrl($this->dirname).'?index_id='.$indexId,
            ],
            [
                'name' => _MD_XOONIPS_ITEM_DETAIL_ITEM_TITLE,
                'url' => 'detail.php?item_id='.$itemId,
            ],
            [
                'name' => _MD_XOONIPS_ITEM_ITEMUSERS_EDIT_TITLE,
            ],
        ];

        // ticket
        $token_ticket = $this->createToken($this->modulePrefix('item_owners_edit'));

        // set view data
        $viewData['item_id'] = $itemId;
        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
    }

    // get item info
    private function getItemInfoByOwnersEdit($itemId, $uids, &$viewData)
    {
        $itemTypeId = $this->getItemtypeIdByItemId($itemId);

        // get item info
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $itemInfo = $itemBean->getItem2($itemId);
        $item_html = $itemBean->getItemListHtml($itemInfo);

        // get create user detail
        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        $detailInfo = $detailBean->getCreateUserDetail($itemTypeId);
        $detail_name = $detailInfo['name'];

        $field = new Xoonips_ItemField();
        $field->setId($detailInfo['item_field_detail_id']);
        $field->setFieldGroupId($detailInfo['group_id']);

        $viewTypeCreateUser = Xoonips_ViewTypeFactory::getInstance($this->dirname, $this->trustDirname)->getViewType(12);
        $users_html = $viewTypeCreateUser->getItemOwnersEditView($field, $uids, 1);

        $viewData['item_html'] = $item_html;
        $viewData['users_html'] = $users_html;
        $viewData['detail_name'] = $detail_name;
        $viewData['item_id'] = $itemId;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
    }

    // do update
    private function updateItemUsers($itemId, $uids, &$messages)
    {
        $itemTypeId = $this->getItemtypeIdByItemId($itemId);

        // get create user detail
        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        $detailInfo = $detailBean->getCreateUserDetail($itemTypeId);
        $detailName = $detailInfo['name'];

        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);

        if (!$item->insertChangelogUsersEdit($itemId, $uids, $detailName)) {
            return false;
        }
        if (!$item->updateItemUsersPrivateIndex($itemId, $uids)) {
            return false;
        }
        if (!$item->updateXoonipsItemUsers($itemId, $uids, $itemTypeId, $messages, $this->log)) {
            return false;
        }

        return true;
    }

    // get item info by index edit
    private function getItemInfoByIndexEdit($itemId, $indexes, &$viewData)
    {
        // get item info
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $viewTypeBean = Xoonips_BeanFactory::getBean('ViewTypeBean', $this->dirname, $this->trustDirname);

        $itemInfo = $itemBean->getItem2($itemId);
        $item_html = $itemBean->getItemListHtml($itemInfo);

        //$viewTypeIndex = new ViewTypeIndex();
        $viewTypeIndex = Xoonips_ViewTypeFactory::getInstance($this->dirname, $this->trustDirname)->getViewType($viewTypeBean->selectByName('index'));
        $index_html = $viewTypeIndex->getItemIndexEditView($indexes);

        $viewData['item_html'] = $item_html;
        $viewData['index_html'] = $index_html;
        $viewData['item_id'] = $itemId;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
    }

    private function getItemtypeIdByItemId($iid)
    {
        $bean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        $basic = $bean->getItemBasicInfo($iid);

        return !empty($basic) ? intval($basic['item_type_id']) : 0;
    }

    private function doCommon(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemId = intval($request->getParameter('item_id'));
        $itemTypeId = $this->getItemtypeIdByItemId($itemId);
        $viewData = [];
        $this->setCommonViewData($viewData, $itemId, $itemTypeId, $request, $response);
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        $item->setData($_POST, true);
        $viewData['editryView'] = $item->getEditViewWithData();
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
    }

    private function setCommonViewData(&$viewData, $itemId, $itemTypeId, &$request, &$response)
    {
        $uid = XoopsUtils::getUid();

        $viewData['item_id'] = $itemId;
        $viewData['itemtype_id'] = $itemTypeId;
        $viewData['next_url'] = 'edit.php?op=editry';
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;

        // item_limit, storage_limit
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $privateItemLimit = $itemBean->getPrivateItemLimit($uid);
        $viewData['num_of_items_current'] = $itemBean->countUserItems($uid);
        $viewData['storage_of_items_current'] = sprintf('%.02lf', $itemBean->getFilesizePrivate($uid) / 1024 / 1024);
        $viewData['num_of_items_max'] = $privateItemLimit['itemNumber'];
        $viewData['storage_of_items_max'] = sprintf('%.02lf', $privateItemLimit['itemStorage'] / 1024 / 1024);

        $op = $request->getParameter('op');
        if ('confirm' == $op && 'confirm_success' == $response->getForward()) {
            $xoonipsTreeCheckBox = false;
        } else {
            $xoonipsTreeCheckBox = true;
        }
        $xoonipsCheckPrivateHandlerId = 'PrivateIndexCheckedHandler';
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $groupIndexes = [];
        $privateIndex = false;
        $publicIndex = $indexBean->getPublicIndex();
        $publicGroupIndexes = $indexBean->getPublicGroupIndex();

        if (XOONIPS_UID_GUEST != $uid) {
            $groupIndexes = $indexBean->getGroupIndex($uid);
            $privateIndex = $indexBean->getPrivateIndex($uid);
        }
        $groupIndexes = $indexBean->mergeIndexes($publicGroupIndexes, $groupIndexes);
        $indexes = [];
        $url = false;
        // public index
        if ($publicIndex) {
            $trees = [];
            $indexes[] = $publicIndex;
            $tree = [];
            $tree['index_id'] = $publicIndex['index_id'];
            $trees[] = $tree;
        }
        // group index
        if ($groupIndexes) {
            foreach ($groupIndexes as $index) {
                $indexes[] = $index;
                $tree = [];
                $tree['index_id'] = $index['index_id'];
                $trees[] = $tree;
            }
        }
        // private index
        if ($privateIndex) {
            $privateIndex['title'] = 'Private';
            $indexes[] = $privateIndex;
            $tree = [];
            $tree['index_id'] = $privateIndex['index_id'];
            $trees[] = $tree;
        }

        $viewData['indexes'] = $indexes;
        $viewData['trees'] = $trees;
    }

    /**
     * get item type name by item type id.
     *
     * @param int $itemTypeId
     *
     * @return string
     */
    private function getItemtypeName($itemTypeId)
    {
        $bean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $name = $bean->getItemTypeName($itemTypeId);

        return !empty($name) ? $name : '';
    }

    /**
     * access permission check.
     *
     * @param int    $itemId
     * @param string $type
     */
    private function checkAccessPermission($itemId, $uid, $type)
    {
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $hasPerm = false;
        switch ($type) {
        case 'itemEdit':
            $hasPerm = $itemBean->canItemEdit($itemId, $uid);
            break;
        case 'itemDelete':
            $hasPerm = $itemBean->canItemDelete($itemId, $uid);
            break;
        case 'itemUsersEdit':
            $hasPerm = $itemBean->canItemUsersEdit($itemId, $uid);
            break;
        case 'itemIndexEdit':
            $hasPerm = $itemBean->canItemIndexEdit($itemId, $uid);
            break;
        }
        if (!$hasPerm) {
            redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_ITEM_CANNOT_ACCESS_ITEM);
            exit();
        }
    }
}
