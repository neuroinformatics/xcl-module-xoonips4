<?php

require_once dirname(__FILE__).'/MaintenanceItemCommonAction.class.php';
require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/BeanFactory.class.php';
require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/Transaction.class.php';

class Xoonips_MaintenanceItemTransferAction extends Xoonips_MaintenanceItemCommonAction
{
    protected function doInit(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_TITLE;
        $description = _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        //get common viewdata
        $viewData = array();
        $viewData['title'] = $title;
        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['description'] = $description;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;

        $response->setViewData($viewData);
        $response->setForward('init_success');
        // reset cookie
        setcookie('item_transfer_from_opened_indexes', '', time() - 86400, '/');
        setcookie('item_transfer_from_checked_indexes', '', time() - 86400, '/');
        setcookie('item_transfer_index_from_tree_selected_tab', '', time() - 86400);
        setcookie('item_transfer_to_opened_indexes', '', time() - 86400, '/');
        setcookie('item_transfer_to_checked_indexes', '', time() - 86400, '/');
        setcookie('item_transfer_index_to_tree_selected_tab', '', time() - 86400);

        return true;
    }

    protected function doIndex(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_TITLE;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // token ticket
        $token_ticket = $this->createToken($this->modulePrefix('do_item_transfer_index'));

        // get parameter
        $uid_from = $request->getParameter('searchUserID_from');
        $uid_to = $request->getParameter('searchUserID_to');

        // get userinfo
        $uname_from = '';
        $uname_to = '';
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $userInfo_from = $userBean->getUserBasicInfo($uid_from);
        $uname_from = $userInfo_from['uname'];
        if (is_numeric($uid_to)) {
            $userInfo_to = $userBean->getUserBasicInfo($uid_to);
            $uname_to = $userInfo_to['uname'];
        }

        // get index
        $req_indexes_from = $this->getRequestIndexes($request, $uid_from, '_from');
        $req_indexes_to = $this->getRequestIndexes($request, $uid_to, '_to', false, false);

        // index tree
        $indexes_from = array();
        $trees_from = array();
        $index_from_num = 0;
        $index_from_num = $this->indexTree($uid_from, $indexes_from, $trees_from, '_from', $req_indexes_from);

        $indexes_to = array();
        $trees_to = array();
        $index_to_num = 0;
        if (is_numeric($uid_to)) {
            $index_to_num = $this->indexTree($uid_to, $indexes_to, $trees_to, '_to', $req_indexes_to);
        }

        //desc
        if (is_numeric($uid_to)) {
            $description = _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_INDEX_DESC2;
        } else {
            $description = _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_INDEX_DESC1;
        }

        $viewData = array();
        $viewData['title'] = $title;
        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['description'] = $description;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['index_from_flg'] = (is_numeric($uid_from)) ? true : false;
        $viewData['indexes_from'] = $indexes_from;
        $viewData['trees_from'] = $trees_from;
        $viewData['searchUserID_from'] = $uid_from;
        $viewData['searchUserName_from'] = $uname_from;
        $viewData['index_to_flg'] = (is_numeric($uid_to)) ? true : false;
        $viewData['indexes_to'] = $indexes_to;
        $viewData['trees_to'] = $trees_to;
        $viewData['searchUserID_to'] = $uid_to;
        $viewData['searchUserName_to'] = $uname_to;
        $viewData['dirname'] = $this->dirname;
        $response->setViewData($viewData);
        $response->setForward('index_success');

        return true;
    }

    protected function doConfirm(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_CONFIRM_TITLE;
        $description = _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_CONFIRM_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('do_item_transfer_index'))) {
            return false;
        } else {
            $token_ticket = $this->createToken($this->modulePrefix('do_item_transfer_confirm'));
        }

        // get parameter
        $uid_from = $request->getParameter('searchUserID_from');
        $uname_from = $request->getParameter('searchUserName_from');
        $uid_to = $request->getParameter('searchUserID_to');
        $uname_to = $request->getParameter('searchUserName_to');

        // get index
        $req_indexes_from = explode(',', $request->getParameter('checked_indexes_from'));
        $req_indexes_to = explode(',', $request->getParameter('checked_indexes_to'));

        // error check
        $index_chk = true;
        if ($uid_from == $uid_to) {
            $index_chk = false;
            $viewData['redirect_msg'] = _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_MSG_FAILURE3;
        } elseif (count($req_indexes_from) == 0) {
            $index_chk = false;
            $viewData['redirect_msg'] = _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_MSG_FAILURE1;
        } elseif (count($req_indexes_to) == 0) {
            $index_chk = false;
            $viewData['redirect_msg'] = _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_MSG_FAILURE2;
        }
        if (!$index_chk) {
            $req_indexes_url = $this->getRequestIndexesURL($request, $uid_from, '_from');
            $req_indexes_url .= $this->getRequestIndexesURL($request, $uid_from, '_to');
            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/maintenance_itemtransfer.php?op=index&searchUserID_from='.$uid_from
            .'&searchUserID_to='.$uid_to.$req_indexes_url;
            $response->setViewData($viewData);
            $response->setForward('confirm_failure');

            return true;
        }

        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexes_from = array();
        foreach ($req_indexes_from as $index_id) {
            $index = array();
            $indexInfo = $indexBean->getFullPathIndexes($index_id);
            $path = '';
            foreach ($indexInfo as $index) {
                if ($index['parent_index_id'] == 1 && $index['open_level'] == XOONIPS_OL_PRIVATE && $index['uid'] == $uid_from) {
                    $path .= ' / Private';
                } else {
                    $path .= ' / '.$index['title'];
                }
            }
            $index['id'] = $index_id;
            $index['path'] = $path;
            $indexes_from[] = $index;
        }

        $indexes_to = array();
        foreach ($req_indexes_to as $index_id) {
            $index = array();
            $indexInfo = $indexBean->getFullPathIndexes($index_id);
            $path = '';
            foreach ($indexInfo as $index) {
                if ($index['parent_index_id'] == 1 && $index['open_level'] == XOONIPS_OL_PRIVATE && $index['uid'] == $uid_to) {
                    $path .= ' / Private';
                } else {
                    $path .= ' / '.$index['title'];
                }
            }
            $index['id'] = $index_id;
            $index['path'] = $path;
            $indexes_to[] = $index;
        }

        $viewData = array();
        $viewData['title'] = $title;
        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['description'] = $description;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['req_indexes_from'] = $indexes_from;
        $viewData['req_indexes_to'] = $indexes_to;
        $viewData['dirname'] = $this->dirname;
        $viewData['searchUserID_from'] = $uid_from;
        $viewData['searchUserName_from'] = $uname_from;
        $viewData['searchUserID_to'] = $uid_to;
        $viewData['searchUserName_to'] = $uname_to;
        $response->setViewData($viewData);
        $response->setForward('confirm_success');

        return true;
    }

    protected function doExecute(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_EXECUTE_TITLE;
        $description = _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_MSG_SUCCESS;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('do_item_transfer_confirm'))) {
            return false;
        }

        // get parameter
        $uid_from = $request->getParameter('searchUserID_from');
        $uname_from = $request->getParameter('searchUserName_from');
        $uid_to = $request->getParameter('searchUserID_to');
        $uname_to = $request->getParameter('searchUserName_to');

        // get index
        $req_indexes_from = $this->getRequestIndexes($request, $uid_from, '_from');
        $req_indexes_to = $this->getRequestIndexes($request, $uid_to, '_to', false, false);

        // not choose index
        if (count($req_indexes_from) == 0 || count($req_indexes_to) == 0) {
            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/maintenance_itemtransfer.php';
            $viewData['redirect_msg'] = _AM_XOONIPS_MAINTENANCE_ITEMTRANSFER_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('execute_failure');

            return true;
        }

        // get item
        $indexItemBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $from_items = array();
        $result_items = array();
        foreach ($req_indexes_from as $index_id) {
            $item_arr = $indexItemBean->getItemIdsByIndexId($index_id);
            $index_path = $indexBean->getFullPathStr($index_id, $uid_from);
            foreach ($item_arr as $item_id) {
                if (!in_array($item_id, $from_items)) {
                    $from_items[] = $item_id;
                }
                $result_items[] = $this->getItemInfoForResult($item_id, $index_path);
            }
        }

        // transfer item
        $total_success = 0;
        $total_fail = 0;
        foreach ($from_items as $item_id) {
            if ($this->transfer($item_id, $uid_to, $req_indexes_to) < 0) {
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
        $viewData['user_flg'] = true;
        $viewData['searchUserName'] = $uname_from;
        $viewData['total_noagree'] = 0;
        $viewData['total_success'] = $total_success;
        $viewData['total_fail'] = $total_fail;
        $viewData['agree_flg'] = false;
        $viewData['action'] = 'maintenance_itemtransfer';
        $response->setViewData($viewData);
        $response->setForward('execute_success');

        return true;
    }

    /**
     * Table and upload file transfer related item_id.
     *
     * @param int   $item_id
     * @param int   $uid
     * @param array $req_indexes
     *
     * @return int 0:Success,
     *             -1:edit indexes fail.
     *             -2:chage uid fail
     */
    private function transfer($item_id, $uid, $req_indexes)
    {
        $this->transaction = Xoonips_Transaction::getInstance();

        $new_indexes = '';
        foreach ($req_indexes as $iid) {
            $new_indexes .= (strlen($new_indexes) == 0) ? $iid : ','.$iid;
        }

        // get public indexes
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexItemBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $indexes_arr = $indexItemBean->getIndexItemLinkInfo($item_id);
        foreach ($indexes_arr as $index_arr) {
            $iid = $index_arr['index_id'];
            $d_index = $indexBean->getIndex($iid);
            if ($d_index['open_level'] == XOONIPS_OL_PUBLIC) {
                $new_indexes .= (strlen($new_indexes) == 0) ? $iid : ','.$iid;
            }
        }

        $this->transaction->start();

        // change uid
        $uids = array($uid);
        if (!$this->updateItemUsers($item_id, $uids, $messages)) {
            return -2;
        }

        // edit indexes
        if (!$this->forceEditIndex($item_id, $new_indexes)) {
            return -1;
        }

        $this->transaction->commit();

        return 0;
    }

    private function updateItemUsers($itemId, $uids, &$messages)
    {
        // get item basic
        $bean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        $result = $bean->getItemBasicInfo($itemId);
        $itemtypeId = $result['item_type_id'];

        // get create user detail
        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        $detailInfo = $detailBean->getCreateUserDetail($itemtypeId);
        $detailName = $detailInfo['name'];

        $item = new Xoonips_Item($itemtypeId, $this->dirname, $this->trustDirname);

        if (!$item->insertChangelogUsersEdit($itemId, $uids, $detailName)) {
            return false;
        }
        if (!$item->updateItemUsersPrivateIndex($itemId, $uids)) {
            return false;
        }
        if (!$item->updateXoonipsItemUsers($itemId, $uids, $itemtypeId, $messages, $this->log)) {
            return false;
        }

        return true;
    }
}
