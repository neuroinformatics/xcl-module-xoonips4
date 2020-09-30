<?php

use Xoonips\Core\Functions;
use Xoonips\Core\XoopsUtils;

require_once dirname(__DIR__).'/core/ActionBase.class.php';
require_once dirname(dirname(__DIR__)).'/include/itemtypetemplate.inc.php';

class Xoonips_ItemSelectSubAction extends Xoonips_ActionBase
{
    protected function doInit(&$request, &$response)
    {
        // this action is called from ajax ItemSelect
        $callbackId = $request->getParameter('callbackid');
        $this->createIndexTree($request, $response);
        $response->setViewDataByKey('callbackid', $callbackId);
        $response->setViewDataByKey('dirname', $this->dirname);
        $response->setForward('init_success');

        return true;
    }

    protected function doSearch(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $itemTitleBean = Xoonips_BeanFactory::getBean('ItemTitleBean', $this->dirname, $this->trustDirname);
        $indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $this->createIndexTree($request, $response);
        $title = $request->getParameter('title');
        $indexId = intval($request->getParameter('indexId'));

        if (0 != $indexId) {
            $itemIds = $indexBean->getCanViewItemIds($indexId, $uid);
        } else {
            $itemIds = $itemTitleBean->searchItemIdByTitle(trim($title));
            $itemBean->filterCanViewItem($itemIds, $uid);
        }

        // get order by select
        $defalut_orderby = '0';
        $sortHandler = Functions::getXoonipsHandler('ItemSort', $this->dirname);
        $sortTitles = $sortHandler->getSortTitles();
        $sortIds = array_keys($sortTitles);
        if (!empty($sortIds)) {
            $defalut_orderby = $sortIds[0];
        }
        $page = '' != $request->getParameter('page') ? $request->getParameter('page') : 1;
        $orderby = '' != $request->getParameter('orderby') ? $request->getParameter('orderby') : $defalut_orderby;
        $order_dir = '' != $request->getParameter('order_dir') ? $request->getParameter('order_dir') : XOONIPS_ASC;
        $itemcount = '' != $request->getParameter('itemcount') ? $request->getParameter('itemcount') : 2;

        $cri = ['start' => ($page - 1) * $itemcount,
            'rows' => $itemcount,
            'orderby' => $orderby,
            'orderdir' => $order_dir, ];

        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $itemList = $itemBean->getItemsList($itemIds, $cri);
        $items = false;
        if ($itemList) {
            foreach ($itemList as $itemId) {
                $itemInfo = $itemBean->getItem2($itemId);
                $item['item_id'] = $itemId;
                $item['html'] = $itemBean->getItemListHtml($itemInfo);
                $items[] = $item;
            }
        }

        $num_of_items = count($itemIds);
        $item_no_label = false;
        $page_no_label = '';
        if (0 == $num_of_items) {
            $item_no_label = '0 - 0 of 0 Items';
        } else {
            $_pMin = min(($page - 1) * $itemcount + 1, $num_of_items);
            $_pMax = min($page * $itemcount, $num_of_items);
            $page_no_label = $_pMin.' - '.$_pMax.' of '.$num_of_items.' Items';
        }

        $response->setViewDataByKey('order_by_select', $sortTitles);
        $response->setViewDataByKey('item_count_select', ['2', '5', '10']);

        //centering current page number(5th of $pages)
        $response->setViewDataByKey('pages', $this->getSelectablePageNumber($page, ceil($num_of_items / $itemcount)));
        $response->setViewDataByKey('maxpage', ceil($num_of_items / $itemcount));
        $response->setViewDataByKey('orderby', $orderby);
        $response->setViewDataByKey('order_dir', $order_dir);
        $response->setViewDataByKey('page', $page);
        $response->setViewDataByKey('itemcount', intval($itemcount));
        $response->setViewDataByKey('num_of_items', $num_of_items);
        $response->setViewDataByKey('page_no_label', $page_no_label);
        $response->setViewDataByKey('item_no_label', $item_no_label);
        $response->setViewDataByKey('select_index_id', $indexId);

        $response->setViewDataByKey('items', $items);
        $response->setViewDataByKey('title', $title);
        $response->setViewDataByKey('dirname', $this->dirname);
        $response->setForward('search_success');

        return true;
    }

    private function createIndexTree(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $groupIndexes = [];
        $privateIndex = false;
        $publicIndex = $indexBean->getPublicIndex();
        $publicGroupIndexes = $indexBean->getPublicGroupIndex();
        if (XOONIPS_UID_GUEST != $uid) {
            $groupIndexes = $indexBean->getGroupIndex($uid);
            $privateIndex = $indexBean->getPrivateIndex($uid);
        }
        $groupIndexes = $indexBean->mergeIndexes($publicGroupIndexes, $groupIndexes);
        $indexes = [];
        $trees = [];
        // public index
        if ($publicIndex) {
            $indexes[] = $publicIndex;
            $tree = [];
            $tree['index_id'] = $publicIndex['index_id'];
            $trees[] = $tree;
        }
        // group index
        if ($groupIndexes) {
            foreach ($groupIndexes as $index) {
                $indexes[] = $index;
                $tree = [];
                $tree['index_id'] = $index['index_id'];
                $trees[] = $tree;
            }
        }
        // private index
        if ($privateIndex) {
            $privateIndex['title'] = 'Private';
            $indexes[] = $privateIndex;
            $tree = [];
            $tree['index_id'] = $privateIndex['index_id'];
            $trees[] = $tree;
        }
        foreach ($indexes as $key => $value) {
            $indexes[$key]['title'] = $value['title'];
        }
        $response->setViewDataByKey('indexes', $indexes);
        $response->setViewDataByKey('trees', $trees);
    }

    private function getSelectablePageNumber($page, $maxpage)
    {
        //centering current page number(5th of $pages)
        $pages = [min(max(1, $page - 4), max(1, $maxpage - 9))];
        for ($i = 1; $i < 10 && $pages[$i - 1] < $maxpage; ++$i) {
            $pages[$i] = $pages[$i - 1] + 1;
        }

        return $pages;
    }
}
