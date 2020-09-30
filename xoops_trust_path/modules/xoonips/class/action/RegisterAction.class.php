<?php

use Xoonips\Core\Functions;
use Xoonips\Core\XoopsUtils;

require_once dirname(__DIR__).'/core/Item.class.php';
require_once dirname(__DIR__).'/core/ActionBase.class.php';

class Xoonips_RegisterAction extends Xoonips_ActionBase
{
    protected function doInit(&$request, &$response)
    {
        $breadcrumbs = [
            [
                'name' => _MD_XOONIPS_ITEM_REGISTER_ITEM_TITLE,
            ],
        ];

        $viewData = [];
        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('init_success');

        return true;
    }

    protected function doSelectItemtype(&$request, &$response)
    {
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $itemtypelist = $itemtypeBean->getItemTypeList();
        if (!$itemtypelist) {
            $response->setSystemError('no item types found');

            return false;
        }

        $viewData = [];
        $viewData['next_url'] = 'register.php';
        $viewData['itemtypelist'] = $itemtypelist;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('selectItemtype_success');

        return true;
    }

    protected function doRegister(&$request, &$response)
    {
        $itemTypeId = intval($request->getParameter('itemtype_id'));
        if (!$this->existsReleasedItemType($itemTypeId)) {
            $response->setSystemError('no item type found');

            return false;
        }

        $viewData = [];
        $this->setCommonViewData($viewData, $itemTypeId, $request, $response);
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        $viewData['registryView'] = $item->getRegistryView();
        $response->setViewData($viewData);
        $response->setForward('register_success');
        // reset cookie
        setcookie('register_opened_indexes', '', time() - 86400);
        setcookie('register_checked_indexes', '', time() - 86400);
        setcookie('register_index_tree_selected_tab', '', time() - 86400);
        foreach ($_COOKIE as $key => $value) {
            if (preg_match('/_item_regist_index_tree_div_/', $key)) {
                setcookie($key, '', time() - 86400);
            }
        }

        return true;
    }

    protected function doComplete(&$request, &$response)
    {
        $itemTypeId = intval($request->getParameter('itemtype_id'));
        if (!$this->existsReleasedItemType($itemTypeId)) {
            $response->setSystemError('no item type found');

            return false;
        }

        $viewData = [];
        $this->setCommonViewData($viewData, $itemTypeId, $request, $response);
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        // targetItemId is editing item group annd field id. ex. '10:1:11'
        $targetItemId = $request->getParameter('targetItemId');
        $item->setData($_POST, true);
        if (false === $item->complete($targetItemId)) {
            $viewData['relation'] = false;
        }
        $viewData['registryView'] = $item->getRegistryViewWithData();
        $response->setViewData($viewData);
        $response->setForward('complete_success');

        return true;
    }

    protected function doAddFieldGroup(&$request, &$response)
    {
        $itemTypeId = intval($request->getParameter('itemtype_id'));
        if (!$this->existsReleasedItemType($itemTypeId)) {
            $response->setSystemError('no item type found');

            return false;
        }

        $viewData = [];
        $this->setCommonViewData($viewData, $itemTypeId, $request, $response);
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        // targetItemId is editing item group annd field id. ex. '10:1:11'
        $targetItemId = $request->getParameter('targetItemId');
        $item->setData($_POST, true);
        $item->addFieldGroup($targetItemId);
        $viewData['registryView'] = $item->getRegistryViewWithData();
        $response->setViewData($viewData);
        $response->setForward('addFieldGroup_success');

        return true;
    }

    protected function doDeleteFieldGroup(&$request, &$response)
    {
        $itemTypeId = intval($request->getParameter('itemtype_id'));
        if (!$this->existsReleasedItemType($itemTypeId)) {
            $response->setSystemError('no item type found');

            return false;
        }

        $viewData = [];
        $this->setCommonViewData($viewData, $itemTypeId, $request, $response);
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        // targetItemId is editing item group annd field id. ex. '10:1:11'
        $targetItemId = $request->getParameter('targetItemId');
        $item->setData($_POST, true);
        $item->deleteFieldGroup($targetItemId);
        $viewData['registryView'] = $item->getRegistryViewWithData();
        $response->setViewData($viewData);
        $response->setForward('deleteFieldGroup_success');

        return true;
    }

    protected function doUploadFile(&$request, &$response)
    {
        $itemTypeId = intval($request->getParameter('itemtype_id'));
        if (!$this->existsReleasedItemType($itemTypeId)) {
            $response->setSystemError('item type is not found');

            return false;
        }

        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        $item->setData($_POST);
        $viewData['fileUpload'] = $item->fileUpload();
        $response->setViewData($viewData);
        $response->setForward('uploadFile_success');

        return true;
    }

    protected function doDeleteFile(&$request, &$response)
    {
        $itemTypeId = intval($request->getParameter('itemtype_id'));
        if (!$this->existsReleasedItemType($itemTypeId)) {
            $response->setSystemError('no item type found');

            return false;
        }

        $viewData = [];
        $this->setCommonViewData($viewData, $itemTypeId, $request, $response);
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        // targetItemId is editing item group annd field id. ex. '10:1:11'
        $targetItemId = $request->getParameter('targetItemId');
        $item->setData($_POST, true);
        $item->delFile($targetItemId, $request->getParameter('fileId'));
        $viewData['registryView'] = $item->getRegistryViewWithData();
        $response->setViewData($viewData);
        $response->setForward('deleteFile_success');

        return true;
    }

    protected function doSearchUser(&$request, &$response)
    {
        $this->doCommon($request, $response);
        $response->setForward('searchUser_success');

        return true;
    }

    protected function doDeleteUser(&$request, &$response)
    {
        $this->doCommon($request, $response);
        $response->setForward('deleteUser_success');

        return true;
    }

    protected function doSearchRelatedItem(&$request, &$response)
    {
        $this->doCommon($request, $response);
        $response->setForward('searchRelatedItem_success');

        return true;
    }

    protected function doDeleteRelatedItem(&$request, &$response)
    {
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
        $itemTypeId = intval($request->getParameter('itemtype_id'));
        if (!$this->existsReleasedItemType($itemTypeId)) {
            $response->setSystemError('no item type found');

            return false;
        }

        $viewData = [];
        $this->setCommonViewData($viewData, $itemTypeId, $request, $response);
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        $errors = new Xoonips_Errors();
        $item->setData($_POST, true);
        $item->inputCheck($errors);

        if (0 != count($errors->getErrors())) {
            $viewData['registryView'] = $item->getRegistryViewWithData();
            $viewData['errors'] = $errors->getView($this->dirname);
            $response->setViewData($viewData);
            $response->setErrors($errors);
            $response->setForward('confirm_error');
        } else {
            // item limit check
            $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
            $itemUsed = $itemBean->countUserItems($uid);
            $privateItemLimit = $itemBean->getPrivateItemLimit($uid);
            if ($itemUsed > $privateItemLimit['itemNumber'] - 1 && $privateItemLimit['itemNumber'] > 0) {
                $parameters = [];
                $parameters[] = '';
                $errors->addError('_MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT', '', $parameters);
            }
            if (0 != count($errors->getErrors())) {
                $viewData['registryView'] = $item->getRegistryViewWithData();
                $viewData['errors'] = $errors->getView($this->dirname);
                $response->setViewData($viewData);
                $response->setErrors($errors);
                $response->setForward('confirm_error');
            } else {
                // item type name
                $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
                $itemtypeName = $itemtypeBean->getItemTypeName($itemTypeId);

                // ticket
                $token_ticket = $this->createToken($this->modulePrefix('confirm_register'));

                // view data
                $viewData = [];
                $viewData['itemtype_id'] = $itemTypeId;
                $viewData['itemtype_name'] = $itemtypeName;
                $viewData['token_titcket'] = $token_ticket;
                $item->setData($_POST, true);
                $viewData['confirmView'] = $item->getConfirmView();
                $response->setViewData($viewData);
                $response->setForward('confirm_success');

                return true;
            }
        }

        return true;
    }

    protected function doSave(&$request, &$response)
    {
        $itemTypeId = intval($request->getParameter('itemtype_id'));
        if (!$this->existsReleasedItemType($itemTypeId)) {
            $response->setSystemError('no item type found');

            return false;
        }

        if (!$this->validateToken($this->modulePrefix('confirm_register'))) {
            $response->setSystemError('Ticket error');

            return false;
        }

        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        $item->setData($_POST, true);
        $this->startTransaction();
        $certify_msg = '';
        $ret = $item->doSave($certify_msg, $this->log);
        if ($ret) {
            $viewData['callbackid'] = 'register.php?op=init';
            if (!empty($certify_msg)) {
                $viewData['callbackvalue'] = $certify_msg;
            } else {
                $viewData['callbackvalue'] = _MD_XOONIPS_MSG_DBREGISTERED;
            }
            $viewData['dirname'] = $this->dirname;
            $viewData['mytrustdirname'] = $this->trustDirname;
            $response->setViewData($viewData);
            $response->setForward('save_success');

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

    protected function doFinish(&$request, &$response)
    {
        $viewData['url'] = $request->getParameter('url');
        $viewData['redirect_msg'] = $request->getParameter('redirect_msg');
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('finish_success');

        return true;
    }

    private function doCommon(&$request, &$response)
    {
        $itemTypeId = intval($request->getParameter('itemtype_id'));
        if (!$this->existsReleasedItemType($itemTypeId)) {
            $response->setSystemError('no item type found');

            return false;
        }

        $viewData = [];
        $this->setCommonViewData($viewData, $itemTypeId, $request, $response);
        $item = new Xoonips_Item($itemTypeId, $this->dirname, $this->trustDirname);
        $item->setData($_POST, true);
        $viewData['registryView'] = $item->getRegistryViewWithData();
        $response->setViewData($viewData);
    }

    private function setCommonViewData(&$viewData, $itemTypeId, &$request, &$response)
    {
        $uid = XoopsUtils::getUid();

        $viewData['itemtype_id'] = $itemTypeId;
        $viewData['next_url'] = 'register.php?op=confirm';
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
     * check whether released item type exists.
     *
     * @param int $itemTypeId
     *
     * @return bool
     */
    private function existsReleasedItemType($itemTypeId)
    {
        $ItemTypeHandler = Functions::getXoonipsHandler('ItemTypeObject', $this->dirname);
        $criteria = new CriteriaCompo(new Criteria('item_type_id', $itemTypeId));
        $criteria->add(new Criteria('released', 1));

        return 1 == $ItemTypeHandler->getCount($criteria);
    }
}
