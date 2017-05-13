<?php

use Xoonips\Core\Functions;

require_once dirname(dirname(__FILE__)).'/core/ActionBase.class.php';
require_once dirname(dirname(__FILE__)).'/core/Item.class.php';
require_once dirname(dirname(dirname(__FILE__))).'/include/itemtypetemplate.inc.php';
require_once dirname(dirname(__FILE__)).'/XmlItemExport.class.php';

class Xoonips_ListAction extends Xoonips_ActionBase
{
    protected function doInit(&$request, &$response)
    {
        $root = &XCube_Root::getSingleton();
        $textFilter = &$root->getTextFilter();

        global $xoopsUser;
        $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;

        // get order by select
        $defalut_orderby = '0';
        $sortHandler = Xoonips_Utils::getModuleHandler('ItemSort', $this->dirname);
        $sortTitles = $sortHandler->getSortTitles();
        $sortIds = array_keys($sortTitles);
        if (!empty($sortIds)) {
            $defalut_orderby = $sortIds[0];
        }
        $sess_orderby = isset($_SESSION[$this->dirname.'_order_by']) ? $_SESSION[$this->dirname.'_order_by'] : $defalut_orderby;
        $sess_orderdir = isset($_SESSION[$this->dirname.'_order_dir']) ? $_SESSION[$this->dirname.'_order_dir'] : XOONIPS_ASC;
        $request_vars = array(
            'op' => array('s', ''),
            'isPrint' => array('s', ''),
            'page' => array('i', 1),
            'orderby' => array('s', $sess_orderby),
            'order_dir' => array('i', $sess_orderdir),
            'itemcount' => array('i', 20),
            'selected' => array('i', array()),
            'num_of_items' => array('i', null),
            'index_id' => array('i', null),
        );
        foreach ($request_vars as $key => $meta) {
            list($type, $default) = $meta;
            $$key = $request->getParameter($key);
            if ($$key == '') {
                $$key = $default;
            }
        }

        // if has not index id then set public index id
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        if (!isset($index_id)) {
            $public_idx = $indexBean->getPublicIndex();
            $index_id = $public_idx['index_id'];
        }

        // check can view index id
        if (!$indexBean->canView($index_id, $uid)) {
            redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
            exit();
        }

        $_SESSION[$this->dirname.'_order_by'] = $orderby;
        $_SESSION[$this->dirname.'_order_dir'] = $order_dir;

        $cri = array(
            'start' => ($page - 1) * $itemcount,
            'rows' => $itemcount,
            'orderby' => $orderby,
            'orderdir' => $order_dir,
        );

        $export_enabled = true;

        // index info
        $detailed_title = '';
        $detailed_description = '';
        $icon = '';
        $indexInfo = $indexBean->getIndex($index_id);
        if ($indexInfo) {
            $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
            if (!$userBean->isModerator($uid) && $indexInfo['open_level'] == XOONIPS_OL_PUBLIC) {
                $export_enabled = false;
            }

            $detailed_title = $indexInfo['detailed_title'];
            $detailed_description = $textFilter->toShowTarea($indexInfo['detailed_description'], 0, 1, 1, 1, 1);
            $icon = sprintf('%s/modules/%s/image.php/index/%u/%s', XOOPS_URL, $this->dirname, $index_id, $indexInfo['icon']);
            $index_upload_dir = Functions::getXoonipsConfig($this->dirname, 'index_upload_dir');
            $file_path = $index_upload_dir.'/index/'.$index_id;
            if (file_exists($file_path)) {
                $response->setViewDataByKey('icon', $icon);
            }
        }

        // can view items
        $itemIds = $indexBean->getCanViewItemIds($index_id, $uid);
        $num_of_items = count($itemIds);

        // retrieve items
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $itemIds = $itemBean->getItemsList($itemIds, $cri);

        $item_htmls = array();
        if ($itemIds) {
            foreach ($itemIds as $itemId) {
                $itemInfo = $itemBean->getItem2($itemId);
                $item_html = array();
                $item_html['item_id'] = $itemId;
                $item_html['html'] = $itemBean->getItemListHtml($itemInfo);
                $item_htmls[] = $item_html;
            }
        }

        // add index list
        $my_indexes = array();
        $childIndexes = $indexBean->getChildIndexes($index_id);
        if (count($childIndexes) > 0) {
            foreach ($childIndexes as $index) {
                $cid = $index['index_id'];
                $cicnt = count($indexBean->getChildIndexes($cid));
                $cnt = $indexBean->countCanViewItem($cid, $uid);
                $my_index = array(
                    'index_id' => $cid,
                    'title' => $index['title'],
                    'child_index_num' => $cicnt,
                    'child_item_num' => $cnt,
                );
                $index_tpl = new XoopsTpl();
                $index_tpl->assign('index', $my_index);
                $index_tpl->assign('dirname', $this->dirname);
                $my_indexes[] = $index_tpl->fetch('db:'.$this->dirname.'_list_index_block.html');
            }
        }
        $export_select = (count($my_indexes) > 0) ? 1 : 0;

        // get index full path
        $fullpathInfo = $indexBean->getFullPathIndexes($index_id);
        $fullPathIndexes = array();
        foreach ($fullpathInfo as $index) {
            if ($index['parent_index_id'] == 1 && $index['open_level'] == XOONIPS_OL_PRIVATE) {
                $index['html_title'] = 'Private';
            }
            $fullPathIndexes[] = $index;
        }

        // get page_no_label
        if ($num_of_items == 0) {
            $page_no_label = _MD_XOONIPS_ITEM_NO_ITEM_LISTED;
        } else {
            $_pMin = min(($page - 1) * $itemcount + 1, $num_of_items);
            $_pMax = min($page * $itemcount, $num_of_items);
            if ($_pMin == 1 && $_pMax == $num_of_items && $num_of_items == 1) {
                $page_no_label = '';
            } else {
                $page_no_label = $_pMin.' - '.$_pMax.' of '.$num_of_items.' Items';
            }
        }

        // breadcrumbs
        $breadcrumbs = array(
            array(
                'name' => _MD_XOONIPS_ITEM_LISTING_ITEM,
            ),
        );

        // check that index is editable
        $response->setViewDataByKey('edit_index', $indexBean->checkWriteRight($index_id, $uid));

        //centering current page number(5th of $pages)
        $response->setViewDataByKey('pages', $this->getSelectablePageNumber($page, ceil($num_of_items / $itemcount)));

        $response->setViewDataByKey('xoops_breadcrumbs', $breadcrumbs);
        $response->setViewDataByKey('detailed_title', $detailed_title);
        $response->setViewDataByKey('detailed_description', $detailed_description);
        $response->setViewDataByKey('item_htmls', $item_htmls);
        $response->setViewDataByKey('my_indexes', $my_indexes);
        $response->setViewDataByKey('index_path', $fullPathIndexes);
        $response->setViewDataByKey('maxpage', ceil($num_of_items / $itemcount));
        $response->setViewDataByKey('orderby', $orderby);
        $response->setViewDataByKey('order_dir', $order_dir);
        $response->setViewDataByKey('page', $page);
        $response->setViewDataByKey('itemcount', intval($itemcount));
        $response->setViewDataByKey('num_of_items', $num_of_items);
        $response->setViewDataByKey('page_no_label', $page_no_label);
        $response->setViewDataByKey('index_id', $index_id);
        $response->setViewDataByKey('order_by_select', $sortTitles);
        $response->setViewDataByKey('item_count_select', array('20', '50', '100'));
        $response->setViewDataByKey('dirname', $this->dirname);
        if ($isPrint == 'print') {
            $response->setViewDataByKey('isPrintPage', true);
        } else {
            $response->setViewDataByKey('isPrintPage', false);
        }

        // assign export_enable variable if permitted
        if (count($itemIds) > 0 && $export_enabled) {
            $response->setViewDataByKey('export_enabled', 1);
        }
        $response->setViewDataByKey('export_select', $export_select);

        $response->setForward('init_success');

        return true;
    }

    private function getSelectablePageNumber($page, $maxpage)
    {
        //centering current page number(5th of $pages)
        $pages = array(min(max(1, $page - 4), max(1, $maxpage - 9)));
        for ($i = 1; $i < 10 && $pages[$i - 1] < $maxpage; ++$i) {
            $pages[$i] = $pages[$i - 1] + 1;
        }

        return $pages;
    }

    protected function doExport(&$request, &$response)
    {
        global $xoopsUser;
        $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;

        // get request
        $index_id = $request->getParameter('index_id');
        $subindex = $request->getParameter('subindex') ? 1 : 0;

        // if has not index id
        if (!$index_id) {
            redirect_header(XOOPS_URL.'/', 3, 'ERROR ');
            exit();
        }

        // check can view index id
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        if (!$indexBean->canView($index_id, $uid)) {
            redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
            exit();
        }

        // index info
        $indexInfo = $indexBean->getIndex($index_id);

        // can view items
        $itemIds = $indexBean->getCanViewItemIds($index_id, $uid);

        // do export
        $xmlexport = new XmlItemExport();
        $xmlexport->export_zip($itemIds, $index_id);
    }

    protected function doExportselect(&$request, &$response)
    {
        // get request
        $index_id = $request->getParameter('index_id');

        // if has not index id
        if (!isset($index_id)) {
            redirect_header(XOOPS_URL.'/', 3, 'ERROR ');
            exit();
        }

        // breadcrumbs
        $breadcrumbs = array(
            array(
                'name' => _MD_XOONIPS_ITEM_LISTING_ITEM,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/list.php?index_id='.$index_id,
            ),
            array(
                'name' => _MD_XOONIPS_ITEM_EXPORT_SELECT,
            ),
        );

        $response->setViewDataByKey('xoops_breadcrumbs', $breadcrumbs);
        $response->setViewDataByKey('index_id', $index_id);
        $response->setViewDataByKey('dirname', $this->dirname);
        $response->setForward('exportselect_success');

        return true;
    }
}
