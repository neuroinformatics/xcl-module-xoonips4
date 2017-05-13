<?php

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
    if ($uid != XOONIPS_UID_GUEST) {
        $is_moderator = $userBean->isModerator($uid);
    }

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

    // get indexes
    $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $dirname, $trustDirname);
    $publicIndex = false;
    $publicGroupIndexes = array();
    $groupIndexes = array();
    $privateIndexes = array();

    if ($uid == XOONIPS_UID_GUEST) {
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
        $url = XOOPS_URL.'/modules/'.$dirname.'/list.php';
    }

    if (empty($xoonipsTreeCheckBox)) {
        $checkbox = false;
    } else {
        $checkbox = true;
    }

    $trees = array();
    $indexes = array();
    // public index
    if ($publicIndex && count($publicIndex) > 0) {
        $indexes[] = $publicIndex;
        $tree = array();
        $tree['index_id'] = $publicIndex['index_id'];
        $trees[] = $tree;
    }

    // group index and public group index
    if ($checkbox != true) {
        $groupIndexes = $indexBean->mergeIndexes($publicGroupIndexes, $groupIndexes);
    // if has checkbox
    } else {
        // if not moderator and not item user
        if (!$is_moderator && $xoonipsItemId != null && !$itemUserBean->isLink($xoonipsItemId, $uid)) {
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
            $tree = array();
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
            $tree = array();
            $tree['index_id'] = $index['index_id'];
            $trees[] = $tree;
        }
    }
    // assign block template variables
    foreach ($indexes as $key => $value) {
        $indexes[$key]['title'] = $value['title'];
    }
    $block = array(
        'isKHTML' => (strstr($_SERVER['HTTP_USER_AGENT'], 'KHTML') !== false),
        'checkbox' => $checkbox,
        'indexes' => $indexes,
        'trees' => $trees,
        'dirname' => $dirname, );

    return $block;
}
