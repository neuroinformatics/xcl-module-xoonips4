<?php

use Xoonips\Core\Functions;
use Xoonips\Core\StringUtils;

require_once dirname(__DIR__).'/class/core/Item.class.php';

function xoonips_search($keywords, $andor, $limit, $offset, $userid)
{
    global $xoopsDB;
    $dirname = 'xoonips';
    $module_handler = &xoops_gethandler('module');
    $module = &$module_handler->getByDirname($dirname);
    if (!is_object($module)) {
        exit('Access Denied');
    }
    $trustDirname = $module->getVar('trust_dirname');

    $ret = [];
    $keyword = '';
    if (is_array($keywords)) {
        $pos = 0;
        foreach ($keywords as $val) {
            if (0 == $pos) {
                $keyword .= $val;
            } else {
                $keyword .= ' '.$andor.' '.$val;
            }
            $pos = $pos + 1;
        }
    }

    if ('' == trim($keyword)) {
        return $ret;
    }

    $isExact = false;
    if ('exact' == $andor) {
        $isExact = true;
    }
    $chandler = Functions::getXoonipsHandler('ItemQuickSearchCondition', $dirname);
    $cobj = &$chandler->get(1);
    $fieldIds = $chandler->getItemFieldIds($cobj);
    if (0 == count($fieldIds)) {
        return $ret;
    }
    $post_data = [];
    foreach ($fieldIds as $fieldId) {
        $groupId = 0;
        $itemtypeId = 0;
        $post_data[$itemtypeId][$groupId.Xoonips_Enum::ITEM_ID_SEPARATOR.$itemtypeId.Xoonips_Enum::ITEM_ID_SEPARATOR.$fieldId] = $keyword;
    }
    $searchSqlArr = [];
    foreach ($post_data as $key => $data) {
        $itemtype = new Xoonips_Item($key, $dirname, $trustDirname);
        $itemtype->setData($data);
        $searchSqlArr[] = $itemtype->doSearch(Xoonips_Enum::OP_TYPE_QUICKSEARCH, $isExact);
    }
    $searchSqlStr = implode(' UNION ALL ', $searchSqlArr);
    $sql = "SELECT DISTINCT item_id FROM ( $searchSqlStr ) AS temp ORDER BY item_id";
    $result = $xoopsDB->queryF($sql, $limit, $offset);
    if (!$result) {
        return $ret;
    }
    $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $dirname, $trustDirname);
    $itemBasicBean = Xoonips_BeanFactory::getBean('ItemBean', $dirname, $trustDirname);
    $itemTitleBean = Xoonips_BeanFactory::getBean('ItemTitleBean', $dirname, $trustDirname);
    $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $dirname, $trustDirname);
    while ($row = $xoopsDB->fetchArray($result)) {
        $data = [];
        $item_id = $row['item_id'];
        $basicInfo = $itemBasicBean->getItemBasicInfo($item_id);
        $typeInfo = $itemTypeBean->getItemType($basicInfo['item_type_id']);
        $data['image'] = 'images/'.$typeInfo['icon'];
        $data['link'] = "detail.php?item_id=$item_id";
        $titleInfo = $itemTitleBean->getItemTitleInfo($item_id);
        $data['title'] = StringUtils::htmlSpecialChars($titleInfo[0]['title']);
        $data['time'] = $basicInfo['creation_date'];
        $userInfo = $itemUsersBean->getItemUsersInfo($item_id);
        $data['uid'] = $userInfo[0]['uid'];
        $data['context'] = '';
        $ret[] = $data;
    }

    return $ret;
}
