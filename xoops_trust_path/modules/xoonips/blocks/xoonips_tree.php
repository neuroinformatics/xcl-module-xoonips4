<?php

use Xoonips\Core\Functions;

require_once dirname(__DIR__).'/class/core/Request.class.php';

// xoonips index tree block
function b_xoonips_tree_show($options)
{
    global $xoopsUser;
    global $xoonipsTreeCheckBox, $xoonipsURL, $xoonipsEditIndex;
    global $xoonipsCheckPrivateHandlerId, $xoonipsItemId;

    $dirname = empty($options[0]) ? 'xoonips' : $options[0];
    $module_handler = &xoops_gethandler('module');
    $module = &$module_handler->getByDirname($dirname);
    if (!is_object($module)) {
        exit('Access Denied');
    }
    $trustDirname = $module->getVar('trust_dirname');

    $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;
    $puid[] = $uid;
    $request = new Xoonips_Request();

    // get user informations
    $userBean = Xoonips_BeanFactory::getBean('UsersBean', $dirname);
    $itemUserBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $dirname, $trustDirname);
    $is_moderator = false;
    if (XOONIPS_UID_GUEST != $uid) {
        $is_moderator = $userBean->isModerator($uid);
    }

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

    // get indexes
    $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $dirname, $trustDirname);
    $publicIndex = false;
    $publicGroupIndexes = [];
    $groupIndexes = [];
    $privateIndexes = [];

    if (XOONIPS_UID_GUEST == $uid) {
        $publicIndex = $indexBean->getPublicIndex();
        $publicGroupIndexes = $indexBean->getPublicGroupIndex();
    } else {
        // login users
        if (!empty($xoonipsEditIndex)) {
            // edit index - show editable indexes
            if ($is_moderator) {
                $publicIndex = $indexBean->getPublicIndex();
            }
            $groupIndexes = $indexBean->getGroupIndex2($puid);
            $privateIndexes = $indexBean->getPrivateIndex2($puid);
        } else {
            $publicIndex = $indexBean->getPublicIndex();
            $publicGroupIndexes = $indexBean->getPublicGroupIndex();
            $groupIndexes = $indexBean->getGroupIndex2($puid);
            $privateIndexes = $indexBean->getPrivateIndex2($puid);
        }
    }
    if (isset($xoonipsURL)) {
        $url = urlencode($xoonipsURL);
    } else {
        $url = XOOPS_URL.'/modules/'.$dirname.'/'.Functions::getItemListUrl($dirname);
    }

    if (empty($xoonipsTreeCheckBox)) {
        $checkbox = false;
    } else {
        $checkbox = true;
    }

    $trees = [];
    $indexes = [];
    // public index
    if ($publicIndex && count($publicIndex) > 0) {
        $indexes[] = $publicIndex;
        $tree = [];
        $tree['index_id'] = $publicIndex['index_id'];
        $trees[] = $tree;
    }

    // group index and public group index
    if (true != $checkbox) {
        $groupIndexes = $indexBean->mergeIndexes($publicGroupIndexes, $groupIndexes);
    // if has checkbox
    } else {
        // if not moderator and not item user
        if (!$is_moderator && null != $xoonipsItemId && !$itemUserBean->isLink($xoonipsItemId, $uid)) {
            // only admin group index
            foreach ($groupIndexes as $key => $index) {
                if (!$userBean->isGroupManager($index['groupid'], $uid)) {
                    unset($groupIndexes[$key]);
                }
            }
        }
    }
    // if edit mode
    if (!empty($xoonipsEditIndex)) {
        $indexBean->filteEditableGroupIndex($groupIndexes, $uid);
    }
    if ($groupIndexes) {
        foreach ($groupIndexes as $index) {
            $indexes[] = $index;
            $tree = [];
            $tree['index_id'] = $index['index_id'];
            $trees[] = $tree;
        }
    }
    // private index
    if ($privateIndexes) {
        foreach ($privateIndexes as $index) {
            if ($index['uid'] == $uid) {
                $index['title'] = 'Private';
            }
            $indexes[] = $index;
            $tree = [];
            $tree['index_id'] = $index['index_id'];
            $trees[] = $tree;
        }
    }
    // assign block template variables
    foreach ($indexes as $key => $value) {
        $indexes[$key]['title'] = $value['title'];
    }
    $block = [
        'checkbox' => $checkbox,
        'indexes' => $indexes,
        'trees' => $trees,
        'dirname' => $dirname,
        'itemListUrl' => Functions::getItemListUrl($dirname), ];

    return $block;
}
