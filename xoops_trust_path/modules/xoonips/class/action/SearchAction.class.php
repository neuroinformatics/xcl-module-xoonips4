<?php

use Xoonips\Core\Functions;

require_once dirname(__DIR__).'/core/ActionBase.class.php';
require_once dirname(__DIR__).'/core/Item.class.php';
require_once dirname(dirname(__DIR__)).'/include/itemtypetemplate.inc.php';
require_once dirname(__DIR__).'/XmlItemExport.class.php';

class Xoonips_SearchAction extends Xoonips_ActionBase
{
    protected function doInit(&$request, &$response)
    {
        return $this->doQuick($request, $response);
    }

    protected function doQuick(&$request, &$response)
    {
        // fetch keyword
        $keyword = $request->getParameter('keyword');
        if (is_null($keyword)) {
            // init quick search
            $this->doQuickInit($request, $response);
            $response->setForward('quick_init_success');
        } else {
            // do quick search
            $this->doQuickSearch($request, $response);
            $response->setForward('quick_search_success');
        }

        return true;
    }

    protected function doAdvanced(&$request, &$response)
    {
        // fetch search_parameter
        $search_parameter = $request->getParameter('search_parameter');
        $search_itemtype = $request->getParameter('search_itemtype');
        $search_subtype = $request->getParameter('search_subtype');

        if (!is_null($search_subtype) && $search_subtype != '') {
            // do advanced search by subtype
            $this->doAdvancedSearchBySubType($request, $response);
            $response->setForward('advanced_search_success');

            return true;
        }

        if (!is_null($search_itemtype) && $search_itemtype != '') {
            // do advanced search by itemtype
            $this->doAdvancedSearchByItemType($request, $response);
            $response->setForward('advanced_search_success');

            return true;
        }

        if (is_null($search_parameter)) {
            // init advanced search
            $this->doAdvancedInit($request, $response);
            $response->setForward('advanced_init_success');

            return true;
        } else {
            // do advanced search
            $this->doAdvancedSearch($request, $response);
            $response->setForward('advanced_search_success');

            return true;
        }
    }

    private function doQuickInit(&$request, &$response)
    {
        // fetch previous query conditions
        $keyword = $request->getParameter('keyword');
        $search_condition = $request->getParameter('search_condition');

        // get installed quick search conditions
        $chandler = Functions::getXoonipsHandler('ItemQuickSearchCondition', $this->dirname);
        $search_conditions = $chandler->getConditions();
        if (!in_array($search_condition, array_keys($search_conditions))) {
            $search_condition = '';
        }

        // breadcrumbs
        $breadcrumbs = array(
            array('name' => _MD_XOONIPS_QUICK_SEARCH_TITLE),
        );

        $viewData['user_tab_chk'] = 1;

        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['select_tab'] = 2;
        $viewData['lang_search'] = _MD_XOONIPS_QUICK_SEARCH_BUTTON_LABEL;
        $viewData['search_conditions'] = $search_conditions;
        $viewData['keyword'] = $keyword;
        $viewData['search_conditions_selected'] = $search_condition;
        $viewData['dirname'] = $this->dirname;
        $response->setViewData($viewData);
    }

    private function doQuickSearch(&$request, &$response)
    {
        $keyword = $request->getParameter('keyword');
        $search_condition = $request->getParameter('search_condition');
        $iids = $this->_doQuickSearchBody($keyword, $search_condition);
        // get items of current page
        $this->getItemsOfCurrentPage($iids, $response, $request);
        $response->setViewDataByKey('op', 'quick');
        $response->setViewDataByKey('search_condition', $search_condition);
        $response->setViewDataByKey('keyword', $keyword);
        $response->setViewDataByKey('dirname', $this->dirname);

        // event log
        $this->log->recordQuickSearchEvent($search_condition, $keyword);
    }

    private function doAdvancedInit(&$request, &$response)
    {
        // get itemtypes
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $itemtypes = $itemtypeBean->getItemTypeList();

        // making itemtype resouces
        $index = 1;
        $blocks = array();
        foreach ($itemtypes as $itemtype) {
            $item = new Xoonips_Item($itemtype['item_type_id'], $this->dirname, $this->trustDirname);
            $blocks[] = array(
                'itemtype_name' => $itemtype['name'],
                'itemtype_id' => $itemtype['item_type_id'],
                'itemtype_view' => $item->getSearchView(),
            );
            ++$index;
        }

        // breadcrumbs
        $breadcrumbs = array(array('name' => _MD_XOONIPS_ITEM_ADVANCED_SEARCH_TITLE));

        // set view data
        $viewData['user_tab_chk'] = 1;
        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['select_tab'] = 3;
        $viewData['itemselect_url'] = 'search.php';
        $viewData['blocks'] = $blocks;
        $viewData['dirname'] = $this->dirname;
        $response->setViewData($viewData);
    }

    private function doAdvancedSearch(&$request, &$response)
    {
        $post_data = $_POST;
        if (count($post_data) == 0) {
            $post_data = $_GET;
        }
        $iids = $this->_doAdvancedSearchBody($request, $post_data, $search_data);
        // get items of current page
        $this->getItemsOfCurrentPage($iids, $response, $request);
        $response->setViewDataByKey('op', 'advanced');
        $response->setViewDataByKey('search_data', $post_data);
        $response->setViewDataByKey('dirname', $this->dirname);
        $this->log->recordAdvancedSearchEvent($search_data);
    }

    private function doAdvancedSearchByItemType(&$request, &$response)
    {
        $search_itemtype = $request->getParameter('search_itemtype');
        $iids = $this->_doAdvancedSearchByItemTypeBody($search_itemtype);
        // get items of current page
        $this->getItemsOfCurrentPage($iids, $response, $request);
        $response->setViewDataByKey('op', 'advanced');
        $response->setViewDataByKey('search_itemtype', $search_itemtype);
        $response->setViewDataByKey('dirname', $this->dirname);
        $this->log->recordAdvancedSearchEvent('itemtype='.$search_itemtype);
    }

    private function doAdvancedSearchBySubType(&$request, &$response)
    {
        $search_itemtype = $request->getParameter('search_itemtype');
        $search_subtype = $request->getParameter('search_subtype');
        $iids = $this->_doAdvancedSearchBySubTypeBody($search_itemtype, $search_subtype);
        // get items of current page
        $this->getItemsOfCurrentPage($iids, $response, $request);
        $response->setViewDataByKey('op', 'advanced');
        $response->setViewDataByKey('search_itemtype', $search_itemtype);
        $response->setViewDataByKey('search_subtype', $search_subtype);
        $this->log->recordAdvancedSearchEvent('itemtype='.$search_itemtype.' itemsubtype='.$search_subtype);
    }

    private function getItemsOfCurrentPage($iids, &$response, &$request)
    {
        global $xoopsUser;
        $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;

        // get order by select
        $default_orderby = '0';
        $sortHandler = Functions::getXoonipsHandler('ItemSort', $this->dirname);
        $sortTitles = $sortHandler->getSortTitles();
        $sortIds = array_keys($sortTitles);
        if (!empty($sortIds)) {
            $defalut_orderby = $sortIds[0];
        }
        $sess_orderby = isset($_SESSION[$this->dirname.'_order_by']) ? $_SESSION[$this->dirname.'_order_by'] : $default_orderby;
        $sess_orderdir = isset($_SESSION[$this->dirname.'_order_dir']) ? $_SESSION[$this->dirname.'_order_dir'] : XOONIPS_ASC;
        $request_vars = array(
            'op' => array('s', ''),
            'print' => array('s', ''),
            'page' => array('i', 1),
            'itemcount' => array('i', 20),
            'orderby' => array('s', $sess_orderby),
            'order_dir' => array('i', $sess_orderdir),
            'search_itemtype' => array('i', ''),
            'search_subtype' => array('s', ''),
            'keyword' => array('s', ''),
            'search_condition' => array('i', ''),
        );
        foreach ($request_vars as $key => $meta) {
            list($type, $default) = $meta;
            $$key = $request->getParameter($key);
            if ($$key == '') {
                $$key = $default;
            }
        }
        $_SESSION[$this->dirname.'_order_by'] = $orderby;
        $_SESSION[$this->dirname.'_order_dir'] = $order_dir;
        $cri = array('start' => ($page - 1) * $itemcount,
              'rows' => $itemcount,
              'orderby' => $orderby,
              'orderdir' => $order_dir, );
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $itemBean->filterCanViewItem($iids, $uid);
        $num_of_items = count($iids);

        $dis_iids = $itemBean->getItemsList($iids, $cri);

        $export_enabled = true;

        $item_htmls = array();
        if ($dis_iids) {
            foreach ($dis_iids as $dis_iid) {
                $itemInfo = $itemBean->getItem2($dis_iid);
                $item_html['item_id'] = $dis_iid;
                $item_html['html'] = $itemBean->getItemListHtml($itemInfo);
                $item_htmls[] = $item_html;

                if (!$itemBean->canItemExport($dis_iid, $uid)) {
                    $export_enabled = false;
                }
            }
        }
        $response->setViewDataByKey('item_htmls', $item_htmls);

        $breadcrumbs = array(
            array(
                'name' => _MD_XOONIPS_ITEM_SEARCH_RESULT,
            ),
        );

        $response->setViewDataByKey('xoops_breadcrumbs', $breadcrumbs);
        $response->setViewDataByKey('order_by_select', $sortTitles);
        $response->setViewDataByKey('item_count_select', array('20', '50', '100'));

        //centering current page number(5th of $pages)
        $response->setViewDataByKey('pages', $this->getSelectablePageNumber($page, ceil($num_of_items / $itemcount)));

        $item_no_label = false;
        $page_no_label = '';
        if ($num_of_items == 0) {
            $item_no_label = '0 - 0 of 0 Items';
        } else {
            $_pMin = min(($page - 1) * $itemcount + 1, $num_of_items);
            $_pMax = min($page * $itemcount, $num_of_items);
            $page_no_label = $_pMin.' - '.$_pMax.' of '.$num_of_items.' Items';
        }

        $response->setViewDataByKey('item_no_label', $item_no_label);
        $response->setViewDataByKey('maxpage', ceil($num_of_items / $itemcount));
        $response->setViewDataByKey('orderby', $orderby);
        $response->setViewDataByKey('order_dir', $order_dir);
        $response->setViewDataByKey('page', $page);
        $response->setViewDataByKey('itemcount', intval($itemcount));
        $response->setViewDataByKey('num_of_items', $num_of_items);
        $response->setViewDataByKey('page_no_label', $page_no_label);

        ($print == 'print') ? $printPage = true : $printPage = false;
        $response->setViewDataByKey('isPrintPage', $printPage);

        // assign export_enable variable if permitted
        if ($export_enabled) {
            $response->setViewDataByKey('export_enabled', 1);
        }
    }

    private function getCheckedItemtype(&$request)
    {
        $checked = array();
        $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $itemtypes = $itemTypeBean->getItemTypeList();
        if (!$itemtypes) {
            return $checked;
        }
        foreach ($itemtypes as $itemtype) {
            $itemtypeId = $itemtype['item_type_id'];
            $checkValue = $request->getParameter($itemtypeId);
            if ($checkValue == 'on') {
                $checked[$itemtypeId] = true;
            } else {
                $checked[$itemtypeId] = false;
            }
        }

        return $checked;
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
        // fetch search_parameter
        $search_parameter = $request->getParameter('search_parameter');
        $search_itemtype = $request->getParameter('search_itemtype');
        $search_subtype = $request->getParameter('search_subtype');
        $keyword = $request->getParameter('keyword');

        $items = array();
        if (!is_null($search_subtype) && $search_subtype != '') {
            // do advanced search by subtype
            $items = $this->doAdvancedSearchBySubTypeExport($request, $response);
        } elseif (!is_null($search_itemtype) && $search_itemtype != '') {
            // do advanced search by itemtype
            $items = $this->doAdvancedSearchByItemTypeExport($request, $response);
        } elseif (!is_null($search_parameter)) {
            // do advanced search
            $items = $this->doAdvancedSearchExport($request, $response);
        } elseif (!is_null($keyword)) {
            // do quick search
            $items = $this->doQuickSearchExport($request, $response);
        } else {
            return false;
        }

        // do export
        $xmlexport = new XmlItemExport();
        $xmlexport->export_zip($items);
    }

    private function doAdvancedSearchByItemTypeExport(&$request, &$response)
    {
        $search_itemtype = $request->getParameter('search_itemtype');

        return $this->_doAdvancedSearchByItemTypeBody($search_itemtype);
    }

    private function doAdnvacedSearchBySubTypeExport(&$request, &$response)
    {
        $search_itemtype = $request->getParameter('search_itemtype');
        $search_subtype = $request->getParameter('search_subtype');

        return $this->_doAdvancedSearchBySubTypeBody($search_itemtype, $search_subtype);
    }

    private function doAdvancedSearchExport(&$request, &$response)
    {
        $post_data = $_POST;
        if (count($post_data) == 0) {
            $post_data = $_GET;
        }

        return $this->_doAdvancedSearchBody($request, $post_data, $search_data);
    }

    private function doQuickSearchExport(&$request, &$response)
    {
        // fetch previous query conditions
        $keyword = $request->getParameter('keyword');
        $search_condition = $request->getParameter('search_condition');

        return $this->_doQuickSearchBody($keyword, $search_condition);
    }

    private function _doAdvancedSearchByItemTypeBody($search_itemtype)
    {
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);

        return $itemBean->getItemtypeSearch($search_itemtype);
    }

    private function _doAdvancedSearchBySubTypeBody($search_itemtype, $search_subtype)
    {
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $iids = $itemBean->getItemsubtypeSearch($search_itemtype, $search_subtype);

        return $iids;
    }

    private function _doAdvancedSearchBody(&$request, $post_data, &$search_data)
    {
        $checkedValue = $this->getCheckedItemtype($request);
        $search_data = array();
        $search_var = array();
        foreach ($post_data as $key => $value) {
            $idArray = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $key);
            if (count($idArray) == 3) {
                if ($checkedValue[$idArray[1]] === true) {
                    if (is_array($value)) {
                        if (trim($value[0]) != ' ' || trim($value[1]) != '') {
                            $search_data[$key] = $key.'='.$value[0].','.$value[1];
                            $search_var[$idArray[1]][$key] = $value;
                        }
                    } else {
                        if (trim($value) != '') {
                            $search_data[$key] = $key.'='.trim($value);
                            $search_var[$idArray[1]][$key] = $value;
                        }
                    }
                }
            }
        }

        $iids = array();
        if (count($search_var) > 0) {
            $searchSqlArr = array();
            foreach ($search_var as $key => $data) {
                $item = new Xoonips_Item($key, $this->dirname, $this->trustDirname);
                $item->setData($data);
                $searchSqlArr[] = $item->doSearch(Xoonips_Enum::OP_TYPE_SEARCH);
            }
            $searchSqlStr = implode(' UNION ALL ', $searchSqlArr);
            $sql = "SELECT DISTINCT item_id FROM ( $searchSqlStr ) AS temp";
            global $xoopsDB;
            $result = $xoopsDB->query($sql);
            if ($result) {
                while ($row = $xoopsDB->fetchArray($result)) {
                    $iids[] = $row['item_id'];
                }
            }
        }

        return $iids;
    }

    private function _doQuickSearchBody($keyword, $search_condition)
    {
        $iids = array();
        if (trim($keyword) != '') {
            $chandler = Functions::getXoonipsHandler('ItemQuickSearchCondition', $this->dirname);
            $cobj = &$chandler->get($search_condition);
            if (is_object($cobj)) {
                $post_data = array();
                $fieldIds = $chandler->getItemFieldIds($cobj);
                foreach ($fieldIds as $fieldId) {
                    if (!$chandler->existItemFieldId($fieldId)) {
                        continue;
                    }
                    $itemtypeId = 0;
                    $groupId = 0;
                    $post_data[$itemtypeId][$groupId.Xoonips_Enum::ITEM_ID_SEPARATOR.$itemtypeId.Xoonips_Enum::ITEM_ID_SEPARATOR.$fieldId] = $keyword;
                }
                $searchSqlArr = array();
                foreach ($post_data as $key => $data) {
                    if ($itemtypeId == 0) {
                        $key = 0;
                    }
                    $item = new Xoonips_Item($key, $this->dirname, $this->trustDirname);
                    $item->setData($data);
                    $searchSqlArr[] = $item->doSearch(Xoonips_Enum::OP_TYPE_QUICKSEARCH);
                }
                $searchSqlStr = implode(' UNION ALL ', $searchSqlArr);
                $sql = "SELECT DISTINCT item_id FROM ( $searchSqlStr ) AS temp";
                global $xoopsDB;
                $result = $xoopsDB->query($sql);
                if ($result) {
                    while ($row = $xoopsDB->fetchArray($result)) {
                        $iids[] = $row['item_id'];
                    }
                }
            }
        }

        return $iids;
    }
}
