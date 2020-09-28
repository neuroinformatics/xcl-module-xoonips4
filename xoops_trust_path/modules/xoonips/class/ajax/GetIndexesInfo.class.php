<?php

use Xoonips\Core\Functions;
use Xoonips\Core\XoopsUtils;

require_once dirname(__DIR__).'/core/Request.class.php';

class Xoonips_GetIndexesInfoAjaxMethod extends Xoonips_AbstractAjaxMethod
{
    /**
     * execute ajax call at import screen.
     *
     * @param int $uid
     *
     * @return bool
     */
    private function funcImportItem($uid)
    {
        if (XOONIPS_UID_GUEST == $uid) {
            return false;
        }

        $trees = [];
        // public index
        $trees[] = $this->_getPublicIndexTree($uid);
        // group index and public group index
        $gids = $this->_getGroupIds($uid);
        foreach ($gids as $gid) {
            $trees[] = $this->_getGroupIndexTree($uid, $gid);
        }
        // private index
        $trees[] = $this->_getPrivateIndexTree($uid);

        $ret = [];
        $ret['dirname'] = $this->mDirname;
        $ret['trees'] = $trees;
        $this->mResult = json_encode($ret);

        return true;
    }

    /**
     * execute ajax call at register screen.
     *
     * @param int $uid
     *
     * @return bool
     */
    private function funcRegisterItem($uid)
    {
        if (XOONIPS_UID_GUEST == $uid) {
            return false;
        }
        // get checked_indexes
        $checkedIndexes = [];
        if (isset($_COOKIE['register_checked_indexes'])) {
            $checkedIndexes = explode(':', $_COOKIE['register_checked_indexes']);
        }
        // get opened_indexes
        $openedIndexes = [];
        if (isset($_COOKIE['register_opened_indexes'])) {
            $openedIndexes = explode(':', $_COOKIE['register_opened_indexes']);
        }

        $trees = [];
        // public index
        $trees[] = $this->_getPublicIndexTree($uid, $checkedIndexes, $openedIndexes);
        // group index and public group index
        $gids = $this->_getGroupIds($uid);
        foreach ($gids as $gid) {
            $trees[] = $this->_getGroupIndexTree($uid, $gid, $checkedIndexes, $openedIndexes);
        }
        // private index
        $trees[] = $this->_getPrivateIndexTree($uid, $checkedIndexes, $openedIndexes);

        $ret = [];
        $ret['dirname'] = $this->mDirname;
        $ret['trees'] = $trees;
        $this->mResult = json_encode($ret);

        return true;
    }

    /**
     * execute ajax call at edit screen.
     *
     * return bool
     */
    private function funcEditItem($uid, $xoonipsItemId)
    {
        if (XOONIPS_UID_GUEST == $uid) {
            return false;
        }

        // get checked_indexes
        $checkedIndexes = [];
        if (isset($_COOKIE['edit_checked_indexes'])) {
            $checkedIndexes = explode(':', $_COOKIE['edit_checked_indexes']);
        } else {
            if (!empty($xoonipsItemId)) {
                $indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->mDirname, $this->mTrustDirname);
                $indexes_arr = $indexItemLinkBean->getIndexItemLinkInfo($xoonipsItemId);
                foreach ($indexes_arr as $index_arr) {
                    $checkedIndexes[] = $index_arr['index_id'];
                }
            }
        }
        // get opened_indexes
        $openedIndexes = [];
        if (isset($_COOKIE['edit_opened_indexes'])) {
            $openedIndexes = explode(':', $_COOKIE['edit_opened_indexes']);
        }

        $trees = [];
        // public index
        $trees[] = $this->_getPublicIndexTree($uid, $checkedIndexes, $openedIndexes);
        // group index
        $gids = $this->_getGroupIds($uid);
        foreach ($gids as $gid) {
            $trees[] = $this->_getGroupIndexTree($uid, $gid, $checkedIndexes, $openedIndexes);
        }
        // private index
        $trees[] = $this->_getPrivateIndexTree($uid, $checkedIndexes, $openedIndexes);

        $ret = [];
        $ret['dirname'] = $this->mDirname;
        $ret['trees'] = $trees;
        $this->mResult = json_encode($ret);

        return true;
    }

    /**
     * execute ajax call at edit screen.
     *
     * return bool
     */
    private function maintenaceItemCommon($uid, $publicFlag, $checkedIndexes, $openedIndexes, $searchUserID = 0)
    {
        if (!XoopsUtils::isAdmin($uid, $this->mDirname)) {
            // permission error
            return false;
        }
        if (0 != $searchUserID) {
            $uid = $searchUserID;
        } else {
            $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;
        }

        $trees = [];
        if (1 == $publicFlag) {
            // public index
            $trees[] = $this->_getPublicIndexTree($uid, $checkedIndexes, $openedIndexes);
        } else {
            // group index
            $gids = $this->_getGroupIds($uid);
            foreach ($gids as $gid) {
                $trees[] = $this->_getGroupIndexTree($uid, $gid, $checkedIndexes, $openedIndexes);
            }
            // private index
            $trees[] = $this->_getPrivateIndexTree($uid, $checkedIndexes, $openedIndexes);
        }

        $ret = [];
        $ret['dirname'] = $this->mDirname;
        $ret['trees'] = $trees;
        $this->mResult = json_encode($ret);

        return true;
    }

    /**
     * execute ajax call at TOP and edit index screen.
     *
     * return bool
     */
    public function execute()
    {
        $uid = XoopsUtils::getUid();
        $funcName = $this->mRequest->getRequest('function');
        $xoonipsItemId = intval($this->mRequest->getRequest('item_id'));
        if (0 == $xoonipsItemId) {
            $itemBean = Xoonips_BeanFactory::getBean('ItemBean', $this->mDirname, $this->mTrustDirname);
            $xoonipsItemId = $itemBean->getItemIdBydoi($this->mRequest->getRequest(XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME));
        }
        $checkedIndexes = [];
        $openedIndexes = [];
        switch ($funcName) {
            case 'itemimport':
                return self::funcImportItem($uid);
            case 'register':
                return self::funcRegisterItem($uid);
            case 'editItem':
                return self::funcEditItem($uid, $xoonipsItemId);
            case 'commonItemDelete':
                if (isset($_COOKIE['item_delete_checked_indexes'])) {
                    $checkedIndexes = explode(':', $_COOKIE['item_delete_checked_indexes']);
                }
                if (isset($_COOKIE['item_delete_opened_indexes'])) {
                    $openedIndexes = explode(':', $_COOKIE['item_delete_opened_indexes']);
                }

                return self::maintenaceItemCommon($uid, 0, $checkedIndexes, $openedIndexes, intval($this->mRequest->getRequest('searchUserID')));
            case 'commonItemTransferFrom':
                if (isset($_COOKIE['item_transfer_from_checked_indexes'])) {
                    $checkedIndexes = explode(':', $_COOKIE['item_transfer_from_checked_indexes']);
                }
                if (isset($_COOKIE['item_transfer_from_opened_indexes'])) {
                    $openedIndexes = explode(':', $_COOKIE['item_transfer_from_opened_indexes']);
                }

                return self::maintenaceItemCommon($uid, 0, $checkedIndexes, $openedIndexes, intval($this->mRequest->getRequest('searchUserID')));
            case 'commonItemTransferTo':
                if (isset($_COOKIE['item_transfer_to_checked_indexes'])) {
                    $checkedIndexes = explode(':', $_COOKIE['item_transfer_to_checked_indexes']);
                }
                if (isset($_COOKIE['item_transfer_to_opened_indexes'])) {
                    $openedIndexes = explode(':', $_COOKIE['item_transfer_to_opened_indexes']);
                }

                return self::maintenaceItemCommon($uid, 0, $checkedIndexes, $openedIndexes, intval($this->mRequest->getRequest('searchUserID')));
            case 'commonItemWithDraw':
                if (isset($_COOKIE['item_withdraw_checked_indexes'])) {
                    $checkedIndexes = explode(':', $_COOKIE['item_withdraw_checked_indexes']);
                }
                if (isset($_COOKIE['item_withdraw_opened_indexes'])) {
                    $openedIndexes = explode(':', $_COOKIE['item_withdraw_opened_indexes']);
                }

                return self::maintenaceItemCommon($uid, 1, $checkedIndexes, $openedIndexes);
        }

        // get checked indexes if item_id found.
        if (!empty($xoonipsItemId)) {
            $indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->mDirname, $this->mTrustDirname);
            $links = $indexItemLinkBean->getIndexItemLinkInfo($xoonipsItemId);
            foreach ($links as $link) {
                $checkedIndexes[] = $link['index_id'];
            }
        }
        // get opened_indexes
        if (isset($_COOKIE['opened_indexes'])) {
            $openedIndexes = explode(':', $_COOKIE['opened_indexes']);
        }

        $trees = [];
        // public index
        $trees[] = $this->_getPublicIndexTree($uid, $checkedIndexes, $openedIndexes);
        // group index and public group index
        $gids = $this->_getGroupIds($uid, $xoonipsItemId);
        foreach ($gids as $gid) {
            $trees[] = $this->_getGroupIndexTree($uid, $gid, $checkedIndexes, $openedIndexes);
        }
        // private index
        $trees[] = $this->_getPrivateIndexTree($uid, $checkedIndexes, $openedIndexes);

        $ret = [];
        $ret['dirname'] = $this->mDirname;
        $ret['trees'] = $trees;
        $this->mResult = json_encode($ret);

        return true;
    }

    /**
     * get public index tree.
     *
     * @param int   $uid
     * @param array $checkedIndexes
     * @param array $openedIndexes
     *
     * @return array tree
     */
    private function _getPublicIndexTree($uid, $checkedIndexes = [], $openedIndexes = [])
    {
        $htmls = [];
        $public_index_id = '';
        $indexHandler = Functions::getXoonipsHandler('Index', $this->mDirname);
        $publicIndexes = $indexHandler->getPublicIndexList($uid);
        foreach ($publicIndexes as $index) {
            if (XOONIPS_IID_ROOT == $index['index_id']) {
                continue;
            }
            $html = [];
            // id
            $html['id'] = $index['index_id'];
            // parent
            $parent = $index['parent_index_id'];
            if (XOONIPS_IID_ROOT == $index['parent_index_id']) {
                $parent = '#';
                $public_index_id = $index['index_id'];
                $html['state'] = ['opened' => true];
            }
            $html['parent'] = $parent;
            // text
            isset($GLOBALS['cubeUtilMlang']) && $index['title'] = $GLOBALS['cubeUtilMlang']->obFilter($index['title']);
            $text = sprintf('%s(%s)', $index['title'], $index['num_items']);
            if ('0' == $index['num_items']) {
                $text = $index['title'];
            }
            $html['text'] = $text;
            // selected
            if (!empty($checkedIndexes)) {
                if (in_array($index['index_id'], $checkedIndexes)) {
                    if (!empty($html['state'])) {
                        $html['state'] = ['opened' => true, 'selected' => true];
                    } else {
                        $html['state'] = ['selected' => true];
                    }
                }
            }
            // opened
            if (!empty($openedIndexes)) {
                if (in_array($index['index_id'], $openedIndexes)) {
                    $html['state'] = ['opened' => true];
                }
            }
            $htmls[] = $html;
        }

        $tree = [];
        $tree['index_id'] = $public_index_id;
        $tree['html'] = $htmls;

        return $tree;
    }

    /**
     * get group index tree.
     *
     * @param int   $uid
     * @param int   $gid
     * @param array $checkedIndexes
     * @param array $openedIndexes
     *
     * @return array tree
     */
    private function _getGroupIndexTree($uid, $gid, $checkedIndexes = [], $openedIndexes = [])
    {
        $group_index_id = '';
        $indexHandler = Functions::getXoonipsHandler('Index', $this->mDirname);
        $groupIndexes = $indexHandler->getGroupIndexList($uid, $gid);
        $htmls = [];
        foreach ($groupIndexes as $index) {
            $html = [];
            // id
            $html['id'] = $index['index_id'];
            // parent
            $parent = $index['parent_index_id'];
            if (XOONIPS_IID_ROOT == $index['parent_index_id']) {
                $parent = '#';
                $group_index_id = $index['index_id'];
                $html['state'] = ['opened' => true];
            }
            $html['parent'] = $parent;
            // text
            isset($GLOBALS['cubeUtilMlang']) && $index['title'] = $GLOBALS['cubeUtilMlang']->obFilter($index['title']);
            $text = sprintf('%s(%s)', $index['title'], $index['num_items']);
            if ('0' == $index['num_items']) {
                $text = $index['title'];
            }
            $html['text'] = $text;
            // selected
            if (!empty($checkedIndexes)) {
                if (in_array($index['index_id'], $checkedIndexes)) {
                    if (!empty($html['state'])) {
                        $html['state'] = ['opened' => true, 'selected' => true];
                    } else {
                        $html['state'] = ['selected' => true];
                    }
                }
            }
            // opened
            if (!empty($openedIndexes)) {
                if (in_array($index['index_id'], $openedIndexes)) {
                    $html['state'] = ['opened' => true];
                }
            }
            $htmls[] = $html;
        }

        $tree = [];
        $tree['index_id'] = $group_index_id;
        $tree['html'] = $htmls;

        return $tree;
    }

    /**
     * get private index tree.
     *
     * @param int   $uid
     * @param array $checkedIndexes
     * @param array $openedIndexes
     *
     * @return array tree
     */
    private function _getPrivateIndexTree($uid, $checkedIndexes = [], $openedIndexes = [])
    {
        $opened_indexes = [];
        if (isset($_COOKIE['opened_indexes'])) {
            $opened_indexes = explode(':', $_COOKIE['opened_indexes']);
        }
        $htmls = [];
        $private_index_id = '';
        $indexHandler = Functions::getXoonipsHandler('Index', $this->mDirname);
        $privateIndexes = $indexHandler->getPrivateIndexList($uid);
        foreach ($privateIndexes as $index) {
            $html = [];
            // id
            $html['id'] = $index['index_id'];
            // parent
            $parent = $index['parent_index_id'];
            if (XOONIPS_IID_ROOT == $index['parent_index_id']) {
                $parent = '#';
                $private_index_id = $index['index_id'];
                $index['title'] = 'Private';
                $html['state'] = ['opened' => true];
            }
            $html['parent'] = $parent;
            // text
            isset($GLOBALS['cubeUtilMlang']) && $index['title'] = $GLOBALS['cubeUtilMlang']->obFilter($index['title']);
            $text = sprintf('%s(%s)', $index['title'], $index['num_items']);
            if ('0' == $index['num_items']) {
                $text = $index['title'];
            }
            $html['text'] = $text;
            // selected
            if (!empty($checkedIndexes)) {
                if (in_array($index['index_id'], $checkedIndexes)) {
                    if (!empty($html['state'])) {
                        $html['state'] = ['opened' => true, 'selected' => true];
                    } else {
                        $html['state'] = ['selected' => true];
                    }
                }
            }
            // opened
            if (!empty($openedIndexes)) {
                if (in_array($index['index_id'], $openedIndexes)) {
                    $html['state'] = ['opened' => true];
                }
            }
            $htmls[] = $html;
        }

        $tree = [];
        $tree['index_id'] = $private_index_id;
        $tree['html'] = $htmls;

        return $tree;
    }

    /**
     * get group ids.
     *
     * @param int $uid
     *
     * @return array group ids
     */
    private function _getGroupIds($uid, $xoonipsItemId = null)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->mDirname, $this->mTrustDirname);
        $itemUserBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->mDirname, $this->mTrustDirname);
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->mDirname, $this->mTrustDirname);
        $is_moderator = false;
        if (XOONIPS_UID_GUEST != $uid) {
            $is_moderator = $userBean->isModerator($uid);
        }
        $puid = [];
        $puid[] = $uid;
        // if has item id && moderator
        if (!empty($xoonipsItemId) && $is_moderator) {
            $itemUserInfo = $itemUserBean->getItemUsersInfo($xoonipsItemId);
            if ($itemUserInfo) {
                $puid = [];
                foreach ($itemUserInfo as $obj) {
                    $puid[] = $obj['uid'];
                }
            }
        }

        $gids = [];
        $allGroupIndexes = array_merge($indexBean->getGroupIndex2($puid), $indexBean->getPublicGroupIndex());
        foreach ($allGroupIndexes as $allGroupIndex) {
            if (!empty($allGroupIndex['groupid'])) {
                $gids[] = $allGroupIndex['groupid'];
            }
        }

        return $gids;
    }
}
