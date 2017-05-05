<?php

require_once dirname(dirname(__FILE__)).'/core/Item.class.php';
require_once dirname(dirname(__FILE__)).'/core/ActionBase.class.php';

class Xoonips_RegisterAction extends Xoonips_ActionBase
{
    protected function doInit(&$request, &$response)
    {
        $breadcrumbs = array(
        array(
            'name' => _MD_XOONIPS_ITEM_REGISTER_ITEM_TITLE,
        ),
        );
        global $xoopsUser;
        $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;

        // Uncertified user can't access.
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        if (!$userBean->isCertified($uid)) {
            redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_MODERATOR_NOT_ACTIVATED);
            exit();
        }

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
            $response->setSystemError('item type is not found');

            return false;
        }
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
        $itemtypeId = $request->getParameter('itemtype_id');
        $viewData = array();
        $this->setCommonViewData($viewData, $itemtypeId, $request, $response);
        $item = new Xoonips_Item($itemtypeId, $this->dirname, $this->trustDirname);
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
        $itemtypeId = $request->getParameter('itemtype_id');
        $viewData = array();
        $this->setCommonViewData($viewData, $itemtypeId, $request, $response);
        $item = new Xoonips_Item($itemtypeId, $this->dirname, $this->trustDirname);
        $targetItemId = $request->getParameter('targetItemId');
        $item->setData($_POST, true);
        if ($item->complete($targetItemId) === false) {
            $viewData['relation'] = false;
        }
        $viewData['registryView'] = $item->getRegistryViewWithData();
        $response->setViewData($viewData);
        $response->setForward('complete_success');

        return true;
    }

    protected function doAddFieldGroup(&$request, &$response)
    {
        $itemtypeId = $request->getParameter('itemtype_id');
        $viewData = array();
        $this->setCommonViewData($viewData, $itemtypeId, $request, $response);
        $item = new Xoonips_Item($itemtypeId, $this->dirname, $this->trustDirname);
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
        $itemtypeId = $request->getParameter('itemtype_id');
        $viewData = array();
        $this->setCommonViewData($viewData, $itemtypeId, $request, $response);
        $item = new Xoonips_Item($itemtypeId, $this->dirname, $this->trustDirname);
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
        $itemtypeId = $request->getParameter('itemtype_id');
        $item = new Xoonips_Item($itemtypeId, $this->dirname, $this->trustDirname);
        $item->setData($_POST);
        $viewData['fileUpload'] = $item->fileUpload();
        $response->setViewData($viewData);
        $response->setForward('uploadFile_success');

        return true;
    }

    protected function doDeleteFile(&$request, &$response)
    {
        $itemtypeId = $request->getParameter('itemtype_id');
        $viewData = array();
        $this->setCommonViewData($viewData, $itemtypeId, $request, $response);
        $item = new Xoonips_Item($itemtypeId, $this->dirname, $this->trustDirname);
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
        $itemtypeId = $request->getParameter('itemtype_id');
        $viewData = array();
        $this->setCommonViewData($viewData, $itemtypeId, $request, $response);
        $item = new Xoonips_Item($itemtypeId, $this->dirname, $this->trustDirname);
        $errors = new Xoonips_Errors();
        $item->setData($_POST, true);
        $item->inputCheck($errors);

        if (count($errors->getErrors()) != 0) {
            $viewData['registryView'] = $item->getRegistryViewWithData();
            $viewData['errors'] = $errors->getView($this->dirname);
            $response->setViewData($viewData);
            $response->setErrors($errors);
            $response->setForward('confirm_error');
        } else {
            // item limit check
            global $xoopsUser;
            $uid = $xoopsUser->getVar('uid');
            $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
            $itemUsed = $itemBean->countUserItems($uid);
            $privateItemLimit = $itemBean->getPrivateItemLimit($uid);
            if ($itemUsed > $privateItemLimit['itemNumber'] - 1 && $privateItemLimit['itemNumber'] > 0) {
                $parameters = array();
                $parameters[] = '';
                $errors->addError('_MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT', '', $parameters);
            }
            if (count($errors->getErrors()) != 0) {
                $viewData['registryView'] = $item->getRegistryViewWithData();
                $viewData['errors'] = $errors->getView($this->dirname);
                $response->setViewData($viewData);
                $response->setErrors($errors);
                $response->setForward('confirm_error');
            } else {
                // item type name
                $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
                $itemtypeName = $itemtypeBean->getItemTypeName($itemtypeId);

                // ticket
                $token_ticket = $this->createToken($this->modulePrefix('confirm_register'));

                // view data
                $viewData = array();
                $viewData['itemtype_id'] = $itemtypeId;
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
        if (!$this->validateToken($this->modulePrefix('confirm_register'))) {
            $response->setSystemError('Ticket error');

            return false;
        }
        $itemtypeId = $request->getParameter('itemtype_id');
        $item = new Xoonips_Item($itemtypeId, $this->dirname, $this->trustDirname);
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
        $itemtypeId = $request->getParameter('itemtype_id');
        $viewData = array();
        $this->setCommonViewData($viewData, $itemtypeId, $request, $response);
        $item = new Xoonips_Item($itemtypeId, $this->dirname, $this->trustDirname);
        $item->setData($_POST, true);
        $viewData['registryView'] = $item->getRegistryViewWithData();
        $response->setViewData($viewData);
    }

    private function setCommonViewData(&$viewData, $itemtypeId, &$request, &$response)
    {
        $viewData['itemtype_id'] = $itemtypeId;
        $viewData['next_url'] = 'register.php?op=confirm';
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;

        global $xoopsUser;
        $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;

        // item_limit, storage_limit
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $privateItemLimit = $itemBean->getPrivateItemLimit($uid);
        $viewData['num_of_items_current'] = $itemBean->countUserItems($uid);
        $viewData['storage_of_items_current'] = sprintf('%.02lf', $itemBean->getFilesizePrivate($uid) / 1024 / 1024);
        $viewData['num_of_items_max'] = $privateItemLimit['itemNumber'];
        $viewData['storage_of_items_max'] = sprintf('%.02lf', $privateItemLimit['itemStorage'] / 1024 / 1024);

        $op = $request->getParameter('op');
        if ($op == 'confirm' && $response->getForward() == 'confirm_success') {
            $xoonipsTreeCheckBox = false;
        } else {
            $xoonipsTreeCheckBox = true;
        }
        $xoonipsCheckPrivateHandlerId = 'PrivateIndexCheckedHandler';

        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $groupIndexes = array();
        $privateIndex = false;
        $publicIndex = $indexBean->getPublicIndex();
        $publicGroupIndexes = $indexBean->getPublicGroupIndex();

        if ($uid != XOONIPS_UID_GUEST) {
            $groupIndexes = $indexBean->getGroupIndex($uid);
            $privateIndex = $indexBean->getPrivateIndex($uid);
        }
        $groupIndexes = $indexBean->mergeIndexes($publicGroupIndexes, $groupIndexes);
        $indexes = array();
        $url = false;
        // public index
        if ($publicIndex) {
            $trees = array();
            $indexes[] = $publicIndex;
            $tree = array();
            $tree['index_id'] = $publicIndex['index_id'];
            $trees[] = $tree;
        }
        // group index
        if ($groupIndexes) {
            foreach ($groupIndexes as $index) {
                $indexes[] = $index;
                $tree = array();
                $tree['index_id'] = $index['index_id'];
                $trees[] = $tree;
            }
        }
        // private index
        if ($privateIndex) {
            $privateIndex['title'] = 'Private';
            $indexes[] = $privateIndex;
            $tree = array();
            $tree['index_id'] = $privateIndex['index_id'];
            $trees[] = $tree;
        }

        $viewData['indexes'] = $indexes;
        $viewData['trees'] = $trees;
    }
}
