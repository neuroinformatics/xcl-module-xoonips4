<?php

require_once dirname(__FILE__).'/MaintenanceItemCommonAction.class.php';
require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/BeanFactory.class.php';
require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/Transaction.class.php';

class Xoonips_MaintenanceItemWithdrawAction extends Xoonips_MaintenanceItemCommonAction
{
    protected function doInit(&$request, &$response)
    {

        //title
        $title = _AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_TITLE;
        $description = _AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // token ticket
        $token_ticket = $this->createToken($this->modulePrefix('do_item_withdraw_index'));

        // get userinfo
        global $xoopsUser;
        $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $userInfo = $userBean->getUserBasicInfo($uid);
        $uname = $userInfo['uname'];

        // get index
        $req_indexes = array();
        if ($this->validateToken($this->modulePrefix('do_item_withdraw_confirm'))) {
            $req_indexes = $this->getRequestIndexes($request, $uid, '_withdraw');
        }

        // index tree
        $indexes = array();
        $trees = array();
        $index_num = 0;
        $index_num = $this->indexTree($uid, $indexes, $trees, '_withdraw', $req_indexes, 1);

        $viewData = array();
        $viewData['title'] = $title;
        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['description'] = $description;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['index_flg'] = (count($indexes) > 0) ? true : false;
        $viewData['indexes'] = $indexes;
        $viewData['trees'] = $trees;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $viewData['searchUserID'] = $uid;
        $viewData['searchUserName'] = $uname;
        $response->setViewData($viewData);
        $response->setForward('index_success');

        return true;
    }

    protected function doConfirm(&$request, &$response)
    {

        //title
        $title = _AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_CONFIRM_TITLE;
        $description = _AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_CONFIRM_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('do_item_withdraw_index'))) {
            return false;
        } else {
            $token_ticket = $this->createToken($this->modulePrefix('do_item_withdraw_confirm'));
        }

        // get parameter
        $uid = $request->getParameter('searchUserID');

        // get index
        $req_indexes = explode(',', $request->getParameter('checked_indexes'));

        // not choose index
        if (count($req_indexes) == 0) {
            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/maintenance_itemwithdraw.php';
            $viewData['redirect_msg'] = _AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_MSG_FAILURE1;
            $response->setViewData($viewData);
            $response->setForward('confirm_failure');

            return true;
        }

        $indexes = array();
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        foreach ($req_indexes as $index_id) {
            $index = array();
            $indexInfo = $indexBean->getFullPathIndexes($index_id);
            $path = '';
            foreach ($indexInfo as $index) {
                if ($index['parent_index_id'] == 1 && $index['open_level'] == XOONIPS_OL_PRIVATE && $index['uid'] == $uid) {
                    $path .= ' / Private';
                } else {
                    $path .= ' / '.$index['title'];
                }
            }
            $index['id'] = $index_id;
            $index['path'] = $path;
            $indexes[] = $index;
        }

        $viewData = array();
        $viewData['title'] = $title;
        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['description'] = $description;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['req_indexes'] = $indexes;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('confirm_success');

        return true;
    }

    protected function doExecute(&$request, &$response)
    {

        //title
        $title = _AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_EXECUTE_TITLE;
        $description = _AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_MSG_SUCCESS;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('do_item_withdraw_confirm'))) {
            return false;
        }

        // get parameter
        $uid = $request->getParameter('searchUserID');

        // get index
        $req_indexes = $this->getRequestIndexes($request, $uid, '_withdraw');

        // not choose index
        if (count($req_indexes) == 0) {
            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/maintenance_itemwithdraw.php';
            $viewData['redirect_msg'] = _AM_XOONIPS_MAINTENANCE_ITEMWITHDRAW_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('execute_failure');

            return true;
        }

        // get item
        $wdraw_items = array();
        $result_items = array();
        $indexItemBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        foreach ($req_indexes as $index_id) {
            $item_arr = $indexItemBean->getItemIdsByIndexId($index_id);
            $index_path = $indexBean->getFullPathStr($index_id, $uid);
            foreach ($item_arr as $item_id) {
                if (!in_array($item_id, $wdraw_items)) {
                    $wdraw_items[] = $item_id;
                }
                $result_items[] = $this->getItemInfoForResult($item_id, $index_path);
            }
        }

        // withdraw item
        $total_success = 0;
        $total_fail = 0;
        foreach ($wdraw_items as $item_id) {
            if (!$this->withdraw($item_id)) {
                $result = 0;
            } else {
                $result = 1;
            }
            foreach ($result_items as &$result_item) {
                if ($result_item['id'] == $item_id) {
                    $result_item['result'] = $result;
                    if ($result) {
                        ++$total_success;
                    } else {
                        ++$total_fail;
                    }
                }
            }
        }

        $viewData = array();
        $viewData['title'] = $title;
        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['description'] = $description;
        $viewData['items'] = $result_items;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $viewData['user_flg'] = false;
        $viewData['searchUserName'] = '';
        $viewData['total_noagree'] = 0;
        $viewData['total_success'] = $total_success;
        $viewData['total_fail'] = $total_fail;
        $viewData['agree_flg'] = false;
        $viewData['action'] = 'maintenance_itemwithdraw';
        $response->setViewData($viewData);
        $response->setForward('execute_success');
        // reset cookie
        setcookie('item_withdraw_opened_indexes', '', time() - 86400, '/');
        setcookie('item_withdraw_checked_indexes', '', time() - 86400, '/');

        return true;
    }

    private function withdraw($itemId)
    {
        $this->transaction = Xoonips_Transaction::getInstance();

        // omit public index
        $checkedIndexes = '';
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexItemBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $indexes_arr = $indexItemBean->getIndexItemLinkInfo($itemId);
        foreach ($indexes_arr as $index_arr) {
            $iid = $index_arr['index_id'];
            $d_index = $indexBean->getIndex($iid);
            if ($d_index['open_level'] != XOONIPS_OL_PUBLIC) {
                $checkedIndexes .= (strlen($checkedIndexes) == 0) ? $iid : ','.$iid;
            }
        }

        $this->transaction->start();

        $ret = $this->forceEditIndex($itemId, $checkedIndexes);

        $this->transaction->commit();

        return $ret;
    }
}
