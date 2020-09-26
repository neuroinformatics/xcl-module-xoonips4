<?php

use Xoonips\Core\XoopsUtils;

require_once dirname(__DIR__).'/class/core/User.class.php';
require_once dirname(__DIR__).'/class/user/ActionBase.class.php';

class Xoonips_UserSearchAction extends Xoonips_UserActionBase
{
    private $breadcrumbs = [
        [
            'name' => _MD_XOONIPS_USERSEARCH_TITLE,
        ],
    ];
    private $breadcrumbs_userlist = [
        [
            'name' => _MD_XOONIPS_USERSEARCH_TITLE,
            'url' => 'user.php?op=userSearch',
        ],
        [
            'name' => _MD_XOONIPS_LANG_USERLIST,
        ],
    ];
    //removerd $user->getDetailHead()
    private $detailHead = [
        [
            'name' => _MD_XOONIPS_USER_UNAME_LABEL,
            'column_name' => 'uname',
            'list_sort_key' => 1,
        ],
        [
            'name' => _MD_XOONIPS_USER_NAME_LABEL,
            'column_name' => 'name',
            'list_sort_key' => 1,
        ],
        [
            'name' => _MD_XOONIPS_LANG_EMAIL,
            'column_name' => 'email',
            'list_sort_key' => 1,
        ],
    ];

    // set page size
    private $perPage = 20;

    /**
     * Moderator.
     *
     * @return bool
     */
    private function isModerator()
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
        $ret = false;
        //isModerator not installed Moderator Group
        if ($userBean->isGroupMember(1, XoopsUtils::getUid())) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * User can delete itself.
     *
     * @return bool
     */
    private function isSelfDelete()
    {
        $self_delete = XoopsUtils::getXoopsConfig('self_delete', XOOPS_CONF_USER);

        return null === $self_delete ? false : (1 == $self_delete);
    }

    /**
     * Every page's url setting.
     *
     * @param array $list
     *                    string $url
     *                    int $perPage
     *
     * @return array
     */
    private function pagenavi($list, $url, $perPage)
    {
        $cnt = 1;
        $pageIndex = [];
        (int) $pageCount = count($list) / $perPage + 1;
        do {
            $page = $cnt;
            $pageIndex[$cnt] = "$url"."page=$page";
            ++$cnt;
        } while ($cnt < $pageCount);

        return $pageIndex;
    }

    /**
     * Every page's user information setting.
     *
     * @param array $userslist
     *                         int $page
     *                         int $perPage
     *
     * @return array
     */
    private function pageUserslist($userslist, $page, $perPage)
    {
        $pagelists = array_chunk($userslist, $perPage);
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
     * Get array_multisort's list.
     *
     * @param array $userslist
     *                         string $sortkey
     *
     * @return array
     */
    private function getSortList($userslist, $sortkey)
    {
        $sortlist = [];
        foreach ($userslist as $row) {
            if (!isset($row[$sortkey])) {
                $sortlist[] = null;
            } else {
                $sortlist[] = $row[$sortkey];
            }
        }

        return $sortlist;
    }

    /**
     * initialization view.
     *
     * @param object $request
     *                        object $response
     *
     * @return bool
     */
    protected function doInit(&$request, &$response)
    {
        $viewData['xoops_breadcrumbs'] = $this->breadcrumbs;
        $viewData['select_tab'] = 1;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('init_success');

        return true;
    }

    /**
     * do search.
     *
     * @param object $request
     *                        object $response
     *
     * @return bool
     */
    protected function doSearch(&$request, &$response)
    {
        $user = Xoonips_User::getInstance();
        $viewData = [];
        $errors = new Xoonips_Errors();
        $user->setData($_POST);
        if (!isset($_POST['sortkey'])) {
            $sortkey = '';
        } else {
            $sortkey = $_POST['sortkey'];
        }

        if (!isset($_POST['newOrder'])) {
            $order = '';
        } else {
            $order = $_POST['newOrder'];
        }
        $page = $request->getParameter('page');
        if ('' == $page) {
            $page = 1;
        }
        if (0 != count($errors->getErrors())) {
            $viewData['xoops_breadcrumbs'] = $this->breadcrumbs;
            $viewData['select_tab'] = 1;
            $viewData['errors'] = $errors->getView($this->dirname);
            $response->setViewData($viewData);
            $response->setForward('inputError');
        } else {
            $userslist = $user->doSearch();
            if (false === $userslist) {
                $response->setSystemError('search error!');

                return false;
            }
            //sequence
            if ('' != $sortkey) {
                $sortlist = $this->getSortList($userslist, $sortkey);
                if ('DESC' == $order) {
                    array_multisort($sortlist, SORT_DESC, $userslist);
                } else {
                    array_multisort($sortlist, $userslist);
                }
            }
            $viewData['xoops_breadcrumbs'] = $this->breadcrumbs_userlist;
            $viewData['isSelfDelete'] = $this->isSelfDelete();
            $viewData['isModerator'] = $this->isModerator(); //site admin group
            unset($_POST['sortkey']);
            unset($_POST['Id']);
            unset($_POST['newOrder']);
            $viewData['hiddenData'] = $_POST;
            $viewData['userId'] = XoopsUtils::getUid();
            $viewData['userslist'] = $this->pageUserslist($userslist, $page, $this->perPage);
            $viewData['head'] = $this->detailHead;
            $viewData['pagenavi'] = $this->pagenavi($userslist, 'user.php?op=userSearch&action=search&', $this->perPage);
            $viewData['sortkey'] = $sortkey;
            $viewData['order'] = $order;
            $viewData['page'] = $page;
            $viewData['dirname'] = $this->dirname;
            $viewData['mytrustdirname'] = $this->trustDirname;
            $response->setForward('success');
            $response->setViewData($viewData);
        }

        return true;
    }

    /**
     * sequence.
     *
     * @param object $request
     *                        object $response
     *
     * @return bool
     */
    protected function doSort(&$request, &$response)
    {
        $user = Xoonips_User::getInstance();
        $user->setData($_POST);
        $sortkey = $request->getParameter('key');
        $order = $request->getParameter('order');
        $detailId = $request->getParameter('detailId');
        $userslist = $user->doSearch();
        $head = $this->detailHead;
        $sortlist = $this->getSortList($userslist, $sortkey);
        if ('DESC' == $order) {
            array_multisort($sortlist, SORT_DESC, $userslist);
        } else {
            array_multisort($sortlist, $userslist);
        }
        //set page
        $page = 1;
        unset($_POST['sortkey']);
        unset($_POST['Id']);
        unset($_POST['newOrder']);
        $viewData['hiddenData'] = $_POST;
        $viewData['userslist'] = $this->pageUserslist($userslist, $page, $this->perPage);
        $viewData['pagenavi'] = $this->pagenavi($userslist, 'user.php?op=userSearch&action=search&', $this->perPage);
        $viewData['head'] = $head;
        $viewData['xoops_breadcrumbs'] = $this->breadcrumbs_userlist;
        $viewData['isSelfDelete'] = $this->isSelfDelete();
        $viewData['isModerator'] = $this->isModerator();
        $viewData['userId'] = XoopsUtils::getUid();
        $viewData['detailId'] = $detailId;
        $viewData['sortkey'] = $sortkey;
        $viewData['order'] = $order;
        $viewData['page'] = $page;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setForward('success');
        $response->setViewData($viewData);

        return true;
    }
}
