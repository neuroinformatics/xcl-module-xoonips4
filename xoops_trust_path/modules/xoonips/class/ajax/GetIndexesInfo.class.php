<?php

use Xoonips\Core\Functions;

require_once dirname(__DIR__).'/core/Request.class.php';

class Xoonips_GetIndexesInfoAjaxMethod extends Xoonips_AbstractAjaxMethod
{
    /**
     * execute ajax call at import screen.
     *
     * return bool
     */
    private function itemImport($dirname, $trustDirname, $xoopsUser, $indexHandler)
    {
        $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;

        $trees = array();
        // public index
        $trees[] = $this->_getPublicIndexTree($indexHandler, $uid);

        // group index and public group index
        $gids = $this->_getGroupIds($dirname, $trustDirname, $uid, $indexHandler);
        foreach ($gids as $gid) {
            $trees[] = $this->_getGroupIndexTree($indexHandler, $uid, $gid);
        }

        // private index
        $trees[] = $this->_getPrivateIndexTree($indexHandler, $uid);

        $ret = array();
        $ret['dirname'] = $dirname;
        $ret['trees'] = $trees;
        $this->mResult = json_encode($ret);

        return true;
    }

    /**
     * execute ajax call at register screen.
     *
     * return bool
     */
    private function register($dirname, $trustDirname, $xoopsUser, $indexHandler)
    {
        $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;

        // get checked_indexes
        $register_checked_indexes = array();
        if (isset($_COOKIE['register_checked_indexes'])) {
            $register_checked_indexes = explode(':', $_COOKIE['register_checked_indexes']);
        }

        // get opened_indexes
        $register_opened_indexes = array();
        if (isset($_COOKIE['register_opened_indexes'])) {
            $register_opened_indexes = explode(':', $_COOKIE['register_opened_indexes']);
        }

        $trees = array();
        // public index
        $trees[] = $this->_getPublicIndexTree($indexHandler, $uid, $register_checked_indexes, $register_opened_indexes);

        // group index and public group index
        $gids = $this->_getGroupIds($dirname, $trustDirname, $uid, $indexHandler);
        foreach ($gids as $gid) {
            $trees[] = $this->_getGroupIndexTree($indexHandler, $uid, $gid, $register_checked_indexes, $register_opened_indexes);
        }

        // private index
        $trees[] = $this->_getPrivateIndexTree($indexHandler, $uid, $register_checked_indexes, $register_opened_indexes);

        $ret = array();
        $ret['dirname'] = $dirname;
        $ret['trees'] = $trees;
        $this->mResult = json_encode($ret);

        return true;
    }

    /**
     * execute ajax call at edit screen.
     *
     * return bool
     */
    private function editItem($dirname, $trustDirname, $xoopsUser, $indexHandler, $xoonipsItemId)
    {
        $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;

        // get checked_indexes
        $checkedIndexes = array();
        if (isset($_COOKIE['edit_checked_indexes'])) {
            $checkedIndexes = explode(':', $_COOKIE['edit_checked_indexes']);
        } else {
            if (!empty($xoonipsItemId)) {
                $indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $dirname, $trustDirname);
                $indexes_arr = $indexItemLinkBean->getIndexItemLinkInfo($xoonipsItemId);
                foreach ($indexes_arr as $index_arr) {
                    $checkedIndexes[] = $index_arr['index_id'];
                }
            }
        }

        // get opened_indexes
        $edit_opened_indexes = array();
        if (isset($_COOKIE['edit_opened_indexes'])) {
            $edit_opened_indexes = explode(':', $_COOKIE['edit_opened_indexes']);
        }

        $trees = array();
        // public index
        $trees[] = $this->_getPublicIndexTree($indexHandler, $uid, $checkedIndexes, $edit_opened_indexes);

        // group index
        $gids = $this->_getGroupIds($dirname, $trustDirname, $uid, $indexHandler);
        foreach ($gids as $gid) {
            $trees[] = $this->_getGroupIndexTree($indexHandler, $uid, $gid, $checkedIndexes, $edit_opened_indexes);
        }

        // private index
        $trees[] = $this->_getPrivateIndexTree($indexHandler, $uid, $checkedIndexes, $edit_opened_indexes);

        $ret = array();
        $ret['dirname'] = $dirname;
        $ret['trees'] = $trees;
        $this->mResult = json_encode($ret);

        return true;
    }

    /**
     * execute ajax call at edit screen.
     *
     * return bool
     */
    private function maintenaceItemCommon($dirname, $trustDirname, $xoopsUser, $indexHandler, $public_flg, $checkedIndexes, $openedIndexes, $searchUserID = 0)
    {
        if (0 != $searchUserID) {
            $uid = $searchUserID;
        } else {
            $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;
        }

        $trees = array();
        if (1 == $public_flg) {
            // public index
            $trees[] = $this->_getPublicIndexTree($indexHandler, $uid, $checkedIndexes, $openedIndexes);
        } else {
            // group index
            $gids = $this->_getGroupIds($dirname, $trustDirname, $uid, $indexHandler);
            foreach ($gids as $gid) {
                $trees[] = $this->_getGroupIndexTree($indexHandler, $uid, $gid, $checkedIndexes, $openedIndexes);
            }
            // private index
            $trees[] = $this->_getPrivateIndexTree($indexHandler, $uid, $checkedIndexes, $openedIndexes);
        }

        $ret = array();
        $ret['dirname'] = $dirname;
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
        $dirname = empty($options[0]) ? 'xoonips' : $options[0];
        $module_handler = &xoops_gethandler('module');
        $module = &$module_handler->getByDirname($dirname);
        if (!is_object($module)) {
            exit('Access Denied');
        }
        $trustDirname = $module->getVar('trust_dirname');

        global $xoopsUser;
        $indexHandler = Functions::getXoonipsHandler('Index', $dirname);

        $request = new Xoonips_Request();
        $itemBean = Xoonips_BeanFactory::getBean('ItemBean', $dirname, $trustDirname);
        $xoonipsItemId = $request->getParameter('item_id');
        if (empty($xoonipsItemId)) {
            $xoonipsItemId = $itemBean->getItemIdBydoi($request->getParameter(XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME));
        }
        $functionName = $request->getParameter('function');
        $checkedIndexes = array();
        $openedIndexes = array();
        if ('itemimport' == $functionName) {
            return self::itemImport($dirname, $trustDirname, $xoopsUser, $indexHandler);
        } elseif ('register' == $functionName) {
            return self::register($dirname, $trustDirname, $xoopsUser, $indexHandler);
        } elseif ('editItem' == $functionName) {
            return self::editItem($dirname, $trustDirname, $xoopsUser, $indexHandler, $xoonipsItemId);
        } elseif ('commonItemDelete' == $functionName) {
            if (isset($_COOKIE['item_delete_checked_indexes'])) {
                $checkedIndexes = explode(':', $_COOKIE['item_delete_checked_indexes']);
            }
            if (isset($_COOKIE['item_delete_opened_indexes'])) {
                $openedIndexes = explode(':', $_COOKIE['item_delete_opened_indexes']);
            }

            return self::maintenaceItemCommon($dirname, $trustDirname, $xoopsUser, $indexHandler, 0, $checkedIndexes, $openedIndexes, $request->getParameter('searchUserID'));
        } elseif ('commonItemTransferFrom' == $functionName) {
            if (isset($_COOKIE['item_transfer_from_checked_indexes'])) {
                $checkedIndexes = explode(':', $_COOKIE['item_transfer_from_checked_indexes']);
            }
            if (isset($_COOKIE['item_transfer_from_opened_indexes'])) {
                $openedIndexes = explode(':', $_COOKIE['item_transfer_from_opened_indexes']);
            }

            return self::maintenaceItemCommon($dirname, $trustDirname, $xoopsUser, $indexHandler, 0, $checkedIndexes, $openedIndexes, $request->getParameter('searchUserID'));
        } elseif ('commonItemTransferTo' == $functionName) {
            if (isset($_COOKIE['item_transfer_to_checked_indexes'])) {
                $checkedIndexes = explode(':', $_COOKIE['item_transfer_to_checked_indexes']);
            }
            if (isset($_COOKIE['item_transfer_to_opened_indexes'])) {
                $openedIndexes = explode(':', $_COOKIE['item_transfer_to_opened_indexes']);
            }

            return self::maintenaceItemCommon($dirname, $trustDirname, $xoopsUser, $indexHandler, 0, $checkedIndexes, $openedIndexes, $request->getParameter('searchUserID'));
        } elseif ('commonItemWithDraw' == $functionName) {
            if (isset($_COOKIE['item_withdraw_checked_indexes'])) {
                $checkedIndexes = explode(':', $_COOKIE['item_withdraw_checked_indexes']);
            }
            if (isset($_COOKIE['item_withdraw_opened_indexes'])) {
                $openedIndexes = explode(':', $_COOKIE['item_withdraw_opened_indexes']);
            }

            return self::maintenaceItemCommon($dirname, $trustDirname, $xoopsUser, $indexHandler, 1, $checkedIndexes, $openedIndexes);
        }

        $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;

        // get opened_indexes
        $opened_indexes = array();
        if (isset($_COOKIE['opened_indexes'])) {
            $opened_indexes = explode(':', $_COOKIE['opened_indexes']);
        }
        // get checked indexes if item_id found.
        $checked_indexes = array();
        if (!empty($xoonipsItemId)) {
            $indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $dirname, $trustDirname);
            $links = $indexItemLinkBean->getIndexItemLinkInfo($xoonipsItemId);
            foreach ($links as $link) {
                $checked_indexes[] = $link['index_id'];
            }
        }

        $trees = array();
        // public index
        $trees[] = $this->_getPublicIndexTree($indexHandler, $uid, $checked_indexes, $opened_indexes);

        // group index and public group index
        $gids = $this->_getGroupIds($dirname, $trustDirname, $uid, $indexHandler, $xoonipsItemId);
        foreach ($gids as $gid) {
            $trees[] = $this->_getGroupIndexTree($indexHandler, $uid, $gid, $checked_indexes, $opened_indexes);
        }

        // private index
        $trees[] = $this->_getPrivateIndexTree($indexHandler, $uid, $checked_indexes, $opened_indexes);

        $ret = array();
        $ret['dirname'] = $dirname;
        $ret['trees'] = $trees;
        $this->mResult = json_encode($ret);

        return true;
    }

    /**
     * get public index tree.
     *
     * param $opj indexHandler
     * param $int uid
     * param $array checkedIndexes
     * param $array openedIndexes
     *
     * return $obj tree
     */
    private function _getPublicIndexTree($indexHandler, $uid, $checkedIndexes = array(), $openedIndexes = array())
    {
        $htmls = array();
        $public_index_id = '';
        $publicIndexes = $indexHandler->getPublicIndexList($uid);
        foreach ($publicIndexes as $index) {
            if (XOONIPS_IID_ROOT == $index['index_id']) {
                continue;
            }
            $html = array();
            // id
            $html['id'] = $index['index_id'];
            // parent
            $parent = $index['parent_index_id'];
            if (XOONIPS_IID_ROOT == $index['parent_index_id']) {
                $parent = '#';
                $public_index_id = $index['index_id'];
                $html['state'] = array('opened' => true);
            }
            $html['parent'] = $parent;
            // text
            $text = sprintf('%s(%s)', $index['title'], $index['num_items']);
            if ('0' == $index['num_items']) {
                $text = $index['title'];
            }
            $html['text'] = $text;
            // selected
            if (!empty($checkedIndexes)) {
                if (in_array($index['index_id'], $checkedIndexes)) {
                    if (!empty($html['state'])) {
                        $html['state'] = array('opened' => true, 'selected' => true);
                    } else {
                        $html['state'] = array('selected' => true);
                    }
                }
            }
            // opened
            if (!empty($openedIndexes)) {
                if (in_array($index['index_id'], $openedIndexes)) {
                    $html['state'] = array('opened' => true);
                }
            }
            $htmls[] = $html;
        }

        $tree = array();
        $tree['index_id'] = $public_index_id;
        $tree['html'] = $htmls;

        return $tree;
    }

    /**
     * get group ids.
     *
     * param $string dirname
     * param $string trustDirname
     * param $int uid
     * param $opj indexHandler
     * return $array $gids
     */
    private function _getGroupIds($dirname, $trustDirname, $uid, $indexHandler, $xoonipsItemId = null)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $dirname);
        $itemUserBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $dirname, $trustDirname);
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $dirname, $trustDirname);
        $is_moderator = false;
        if (XOONIPS_UID_GUEST != $uid) {
            $is_moderator = $userBean->isModerator($uid);
        }
        $puid = array();
        $puid[] = $uid;
        // if has item id && moderator
        if (!empty($xoonipsItemId) && $is_moderator) {
            $itemUserInfo = $itemUserBean->getItemUsersInfo($xoonipsItemId);
            if ($itemUserInfo) {
                $puid = array();
                foreach ($itemUserInfo as $obj) {
                    $puid[] = $obj['uid'];
                }
            }
        }

        $gids = array();
        $allGroupIndexes = array_merge($indexBean->getGroupIndex2($puid), $indexBean->getPublicGroupIndex());
        foreach ($allGroupIndexes as $allGroupIndex) {
            if (!empty($allGroupIndex['groupid'])) {
                $gids[] = $allGroupIndex['groupid'];
            }
        }

        return $gids;
    }

    /**
     * get group index tree.
     *
     * param $opj indexHandler
     * param $int uid
     * param $int gid
     * param $array checkedIndexes
     * param $array openedIndexes
     *
     * return $obj tree
     */
    private function _getGroupIndexTree($indexHandler, $uid, $gid, $checkedIndexes = array(), $openedIndexes = array())
    {
        $opened_indexes = array();
        if (isset($_COOKIE['opened_indexes'])) {
            $opened_indexes = explode(':', $_COOKIE['opened_indexes']);
        }
        $group_index_id = '';
        $groupIndexes = $indexHandler->getGroupIndexList($uid, $gid);
        $htmls = array();
        foreach ($groupIndexes as $index) {
            $html = array();
            // id
            $html['id'] = $index['index_id'];
            // parent
            $parent = $index['parent_index_id'];
            if (XOONIPS_IID_ROOT == $index['parent_index_id']) {
                $parent = '#';
                $group_index_id = $index['index_id'];
                $html['state'] = array('opened' => true);
            }
            $html['parent'] = $parent;
            // text
            $text = sprintf('%s(%s)', $index['title'], $index['num_items']);
            if ('0' == $index['num_items']) {
                $text = $index['title'];
            }
            $html['text'] = $text;
            // selected
            if (!empty($checkedIndexes)) {
                if (in_array($index['index_id'], $checkedIndexes)) {
                    if (!empty($html['state'])) {
                        $html['state'] = array('opened' => true, 'selected' => true);
                    } else {
                        $html['state'] = array('selected' => true);
                    }
                }
            }
            // opened
            if (!empty($openedIndexes)) {
                if (in_array($index['index_id'], $openedIndexes)) {
                    $html['state'] = array('opened' => true);
                }
            }
            $htmls[] = $html;
        }

        $tree = array();
        $tree['index_id'] = $group_index_id;
        $tree['html'] = $htmls;

        return $tree;
    }

    /**
     * get private index tree.
     *
     * param $opj indexHandler
     * param $int uid
     * param $array checkedIndexes
     * param $array openedIndexes
     *
     * return $obj tree
     */
    private function _getPrivateIndexTree($indexHandler, $uid, $checkedIndexes = array(), $openedIndexes = array())
    {
        $opened_indexes = array();
        if (isset($_COOKIE['opened_indexes'])) {
            $opened_indexes = explode(':', $_COOKIE['opened_indexes']);
        }
        $htmls = array();
        $private_index_id = '';
        $privateIndexes = $indexHandler->getPrivateIndexList($uid);
        foreach ($privateIndexes as $index) {
            $html = array();
            // id
            $html['id'] = $index['index_id'];
            // parent
            $parent = $index['parent_index_id'];
            if (XOONIPS_IID_ROOT == $index['parent_index_id']) {
                $parent = '#';
                $private_index_id = $index['index_id'];
                $index['title'] = 'Private';
                $html['state'] = array('opened' => true);
            }
            $html['parent'] = $parent;
            // text
            $text = sprintf('%s(%s)', $index['title'], $index['num_items']);
            if ('0' == $index['num_items']) {
                $text = $index['title'];
            }
            $html['text'] = $text;
            // selected
            if (!empty($checkedIndexes)) {
                if (in_array($index['index_id'], $checkedIndexes)) {
                    if (!empty($html['state'])) {
                        $html['state'] = array('opened' => true, 'selected' => true);
                    } else {
                        $html['state'] = array('selected' => true);
                    }
                }
            }
            // opened
            if (!empty($openedIndexes)) {
                if (in_array($index['index_id'], $openedIndexes)) {
                    $html['state'] = array('opened' => true);
                }
            }
            $htmls[] = $html;
        }

        $tree = array();
        $tree['index_id'] = $private_index_id;
        $tree['html'] = $htmls;

        return $tree;
    }
}
