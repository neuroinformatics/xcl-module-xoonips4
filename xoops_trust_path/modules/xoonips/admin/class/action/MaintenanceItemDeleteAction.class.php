<?php

use Xoonips\Core\FileUtils;
use Xoonips\Core\Functions;

require_once __DIR__.'/MaintenanceItemCommonAction.class.php';
require_once dirname(dirname(dirname(__DIR__))).'/class/core/Transaction.class.php';

class Xoonips_MaintenanceItemDeleteAction extends Xoonips_MaintenanceItemCommonAction
{
    protected function doInit(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_MAINTENANCE_ITEMDELETE_TITLE;
        $description = _AM_XOONIPS_MAINTENANCE_ITEMDELETE_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        //get common viewdata
        $viewData = [];
        $viewData['title'] = $title;
        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['description'] = $description;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;

        $response->setViewData($viewData);
        $response->setForward('init_success');
        // reset cookie
        setcookie('item_delete_opened_indexes', '', time() - 86400, '/');
        setcookie('item_delete_checked_indexes', '', time() - 86400, '/');
        setcookie('item_delete_index_tree_selected_tab', '', time() - 86400, '/');

        return true;
    }

    protected function doIndex(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_MAINTENANCE_ITEMDELETE_TITLE;
        $description = _AM_XOONIPS_MAINTENANCE_ITEMDELETE_INDEX_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // token ticket
        $token_ticket = $this->createToken($this->modulePrefix('do_item_delete_index'));

        // get parameter
        $uid = intval($request->getParameter('searchUserID'));

        // get userinfo
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $userInfo = $userBean->getUserBasicInfo($uid);
        $uname = $userInfo['uname'];

        // get index
        $req_indexes = [];
        if ($this->validateToken($this->modulePrefix('do_item_delete_confirm'))) {
            $req_indexes = $this->getRequestIndexes($request, $uid, '_del');
        }

        // index tree
        $indexes = [];
        $trees = [];
        $index_num = 0;
        $index_num = $this->indexTree($uid, $indexes, $trees, '_del', $req_indexes);

        $viewData = [];
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
        $title = _AM_XOONIPS_MAINTENANCE_ITEMDELETE_CONFIRM_TITLE;
        $description = _AM_XOONIPS_MAINTENANCE_ITEMDELETE_CONFIRM_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('do_item_delete_index'))) {
            return false;
        } else {
            $token_ticket = $this->createToken($this->modulePrefix('do_item_delete_confirm'));
        }

        // get parameter
        $uid = intval($request->getParameter('searchUserID'));
        $uname = $request->getParameter('searchUserName');

        // get index
        $req_indexes = array_map('intval', explode(',', $request->getParameter('checked_indexes')));

        // not choose index
        if (0 == count($req_indexes)) {
            $req_indexes_url = $this->getRequestIndexesURL($request, $uid, '_del');
            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/maintenance_itemdelete.php'
            .'?op=index&searchUserID='.$uid.$req_indexes_url;
            $viewData['redirect_msg'] = _AM_XOONIPS_MAINTENANCE_ITEMDELETE_MSG_FAILURE1;
            $response->setViewData($viewData);
            $response->setForward('confirm_failure');

            return true;
        }

        $indexes = [];
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        foreach ($req_indexes as $index_id) {
            $index = [];
            $indexInfo = $indexBean->getFullPathIndexes($index_id);
            $path = '';
            foreach ($indexInfo as $index) {
                if (1 == $index['parent_index_id'] && XOONIPS_OL_PRIVATE == $index['open_level'] && $index['uid'] == $uid) {
                    $path .= ' / Private';
                } else {
                    $path .= ' / '.$index['title'];
                }
            }
            $index['id'] = $index_id;
            $index['path'] = $path;
            $indexes[] = $index;
        }

        $viewData = [];
        $viewData['title'] = $title;
        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['description'] = $description;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['req_indexes'] = $indexes;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $viewData['searchUserID'] = $uid;
        $viewData['searchUserName'] = $uname;
        $response->setViewData($viewData);
        $response->setForward('confirm_success');

        return true;
    }

    protected function doExecute(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_MAINTENANCE_ITEMDELETE_EXECUTE_TITLE;
        $description = _AM_XOONIPS_MAINTENANCE_ITEMDELETE_MSG_SUCCESS;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('do_item_delete_confirm'))) {
            return false;
        }

        // get parameter
        $uid = intval($request->getParameter('searchUserID'));
        $uname = $request->getParameter('searchUserName');

        // get index
        $req_indexes = $this->getRequestIndexes($request, $uid, '_del');

        // not choose index
        if (0 == count($req_indexes)) {
            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/maintenance_itemdelete.php';
            $viewData['redirect_msg'] = _AM_XOONIPS_MAINTENANCE_ITEMDELETE_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('execute_failure');

            return true;
        }

        // get item
        $del_items = [];
        $result_items = [];
        $indexItemBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        foreach ($req_indexes as $index_id) {
            $item_arr = $indexItemBean->getItemIdsByIndexId($index_id);
            $index_path = $indexBean->getFullPathStr($index_id, $uid);
            foreach ($item_arr as $item_id) {
                if (!in_array($item_id, $del_items)) {
                    $del_items[] = $item_id;
                }
                $result_items[] = $this->getItemInfoForResult($item_id, $index_path);
            }
        }

        // delete item
        $total_success = 0;
        $total_fail = 0;
        foreach ($del_items as $item_id) {
            if ($this->delete($item_id) < 0) {
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

        $viewData = [];
        $viewData['title'] = $title;
        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['description'] = $description;
        $viewData['items'] = $result_items;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $viewData['user_flg'] = true;
        $viewData['searchUserName'] = $uname;
        $viewData['total_noagree'] = 0;
        $viewData['total_success'] = $total_success;
        $viewData['total_fail'] = $total_fail;
        $viewData['agree_flg'] = false;
        $viewData['action'] = 'maintenance_itemdelete';
        $response->setViewData($viewData);
        $response->setForward('execute_success');

        return true;
    }

    /**
     * Table and upload file delete related item_id.
     *
     * @param type $item_id
     *
     * @return int 0:Success,
     *             -1:each table delete fail ,
     *             -2:extend table delete fail
     */
    private function delete($item_id)
    {
        $this->transaction = Xoonips_Transaction::getInstance();

        $this->transaction->start();

        if (false == $this->delete_each($item_id)) {
            $this->transaction->rollback();

            return -1;
        }

        if (false == $this->delete_extend($item_id)) {
            $this->transaction->rollback();

            return -2;
        }

        $this->transaction->commit();

        // delete temp files
        $tmp = Functions::getXoonipsConfig($this->dirname, 'upload_dir');
        $item_dir = $tmp.'/item/'.$item_id;
        if (is_dir($item_dir)) {
            FileUtils::deleteDirectory($item_dir);
        }

        return 0;
    }
}
