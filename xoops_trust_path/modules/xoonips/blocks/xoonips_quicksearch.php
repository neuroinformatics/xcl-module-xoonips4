<?php

use Xoonips\Core\Functions;

require_once dirname(__DIR__).'/class/core/Request.class.php';

// xoonips quick search block
function b_xoonips_quick_search_show($options)
{
    global $xoopsUser;

    $dirname = empty($options[0]) ? 'xoonips' : $options[0];
    $module_handler = &xoops_gethandler('module');
    $module = &$module_handler->getByDirname($dirname);
    if (!is_object($module)) {
        exit('Access Denied');
    }

    $search_conditions = array();

    // get installed itemtypes
    $handler = Functions::getXoonipsHandler('ItemQuickSearchCondition', $dirname);
    $search_conditions = $handler->getConditions();
    // fetch previous query conditions
    // - keyword
    $request = new Xoonips_Request();
    $keyword = $request->getParameter('keyword');
    // - search_itemtype
    $selected = $request->getParameter('search_condition');
    if (!is_null($selected) && !in_array($selected, array_keys($search_conditions))) {
        $selected = '';
    }

    // assign block template variables
    $block = array(
        'search_conditions' => $search_conditions,
        'keyword' => $keyword,
        'search_conditions_selected' => $selected,
        'op' => 'quick',
        'dirname' => $dirname,
        'submit_url' => XOOPS_URL.'/modules/'.$dirname.'/search.php', );

    return $block;
}
