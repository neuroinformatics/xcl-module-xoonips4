<?php

require_once dirname(__DIR__).'/core/AbstractActionBase.class.php';
require_once XOOPS_ROOT_PATH.'/core/XCube_PageNavigator.class.php';

class Xoonips_UserSelectSubAction extends Xoonips_AbstractActionBase
{
    protected function doInit(&$request, &$response)
    {
        // this action is called from ajax UsesSelect
        $callbackID = $request->getParameter('callbackid');
        $mode = $request->getParameter('mode');
        if ('single' != $mode) {
            $mode = 'mult';
        }
        $viewData = [];
        $viewData['callbackid'] = $callbackID;
        $viewData['mode'] = $mode;
        $this->setCommon($viewData);
        $response->setViewData($viewData);
        $response->setForward('init_success');

        return true;
    }

    protected function doSearch(&$request, &$response)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);

        //get parameter
        $uname = $request->getParameter('uname');
        $name = $request->getParameter('name');
        $page = $request->getParameter('page');
        $callbackID = $request->getParameter('callbackid');
        $mode = $request->getParameter('mode');
        if ('single' != $mode) {
            $mode = 'mult';
        }
        $sortkey = '';
        $order = '';
        if ('' == $page) {
            $page = 1;
        } else {
            $uname = $request->getParameter('hiduname');
            $name = $request->getParameter('hidname');
            $sortkey = $request->getParameter('hidkey');
            $order = $request->getParameter('hidorder');
        }

        //set user limit
        $limit = 5;

        $userlist = $userBean->getUserBasicInfoByName(trim($uname), trim($name));
        $counts = count($userlist);
        $pageNavi = $this->naviList($counts, $limit, $page);
        $viewData = [];
        if ('' != $sortkey || '' != $order) {
            $sortlist = $this->getSortList($userlist, $sortkey);

            if ('DESC' == $order) {
                array_multisort($sortlist, SORT_DESC, $userlist);
            } else {
                array_multisort($sortlist, $userlist);
            }
            $viewData['sortkey'] = $sortkey;
            $viewData['order'] = $order;
        }
        $userlist = $this->pageList($userlist, $page, $limit);
        $viewData['uname'] = $uname;
        $viewData['name'] = $name;
        $viewData['userlist'] = $userlist;
        $viewData['pageNavi'] = $pageNavi;
        $viewData['callbackid'] = $callbackID;
        $viewData['mode'] = $mode;
        $this->setCommon($viewData);
        $response->setViewData($viewData);
        $response->setForward('search_success');

        return true;
    }

    protected function doSort(&$request, &$response)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);

        //get parameter
        $sortkey = $request->getParameter('sortkey');
        $order = $request->getParameter('order');
        $uname = $request->getParameter('hiduname');
        $name = $request->getParameter('hidname');
        $callbackID = $request->getParameter('callbackid');
        $mode = $request->getParameter('mode');
        if ('single' != $mode) {
            $mode = 'mult';
        }

        //set page,user limit
        $page = 1;
        $limit = 5;

        $userlist = $userBean->getUserBasicInfoByName(trim($uname), trim($name));
        $counts = count($userlist);
        $pageNavi = $this->naviList($counts, $limit, $page);
        $sortlist = $this->getSortList($userlist, $sortkey);

        if ('DESC' == $order) {
            array_multisort($sortlist, SORT_DESC, $userlist);
        } else {
            array_multisort($sortlist, $userlist);
        }
        $userlist = $this->pageList($userlist, $page, $limit);
        $viewData = [];
        $viewData['userlist'] = $userlist;
        $viewData['pageNavi'] = $pageNavi;
        $viewData['sortkey'] = $sortkey;
        $viewData['order'] = $order;
        $viewData['uname'] = $uname;
        $viewData['name'] = $name;
        $viewData['callbackid'] = $callbackID;
        $viewData['mode'] = $mode;
        $this->setCommon($viewData);
        $response->setViewData($viewData);
        $response->setForward('sort_success');

        return true;
    }

    /**
     * Every page information setting.
     *
     * @return array
     */
    private function pageList($list, $page, $per)
    {
        $pagelists = array_chunk($list, $per);
        $ret = [];
        $cnt = 1;
        foreach ($pagelists as $list) {
            if ($cnt == $page) {
                $ret = $list;
                break;
            }
            ++$cnt;
        }

        return $ret;
    }

    /**
     * navi information setting.
     *
     * @return array
     */
    private function naviList($counts, $limit, $page)
    {
        $pageNavi = new XCube_PageNavigator('', XCUBE_PAGENAVI_START);
        $pageNavi->setTotalItems($counts);
        $pageNavi->setPerpage($limit);
        $pageNavi->setStart(($page - 1) * $limit);

        return $pageNavi;
    }

    /**
     * Get array_multisort's list.
     *
     * @return array
     */
    private function getSortList($userlist, $sortkey)
    {
        $sortlist = [];
        foreach ($userlist as $row) {
            $sortlist[] = $row[$sortkey];
        }

        return $sortlist;
    }

    private function setCommon(&$viewData)
    {
        $viewData['dirname'] = $this->dirname;
        $viewData['USERSELECT_TITLE'] = constant('_MD_'.strtoupper($this->trustDirname).'_USERSELECT_TITLE');
        $viewData['USER_UNAME_LABEL'] = constant('_MD_'.strtoupper($this->trustDirname).'_USER_UNAME_LABEL');
        $viewData['USER_NAME_LABEL'] = constant('_MD_'.strtoupper($this->trustDirname).'_USER_NAME_LABEL');
        $viewData['LABEL_SEARCH'] = constant('_MD_'.strtoupper($this->trustDirname).'_LABEL_SEARCH');
        $viewData['LABEL_SELECT'] = constant('_MD_'.strtoupper($this->trustDirname).'_LABEL_SELECT');
        $viewData['LABEL_CANCEL'] = constant('_MD_'.strtoupper($this->trustDirname).'_LABEL_CANCEL');
    }
}
