<?php

use Xoonips\Core\Functions;
use Xoonips\Core\StringUtils;

// xoonips ranking block
function b_xoonips_ranking_show($options)
{
    $dirname = empty($options[0]) ? 'xoonips' : $options[0];
    $module_handler = &xoops_gethandler('module');
    $module = &$module_handler->getByDirname($dirname);
    if (!is_object($module)) {
        exit('Access Denied');
    }

    $etc = '...';
    // decide maximum string length by block position
    if (defined('XOOPS_CUBE_LEGACY')) {
        // get xoonips module id
        $mid = $module->getVar('mid', 's');
        // get block array
        $block_arr = &XoopsBlock::getByModule($mid);
    } else {
        global $block_arr;
    }
    $maxlen = 56;
    // default
    foreach ($block_arr as $b) {
        $func = $b->getVar('show_func', 'n');
        if ($func == 'b_xoonips_ranking_show') {
            $side = $b->getVar('side', 'n');
            if ($side == XOOPS_SIDEBLOCK_LEFT || $side == XOOPS_SIDEBLOCK_RIGHT) {
                $maxlen = 16;
                break;
            } elseif ($side == XOOPS_CENTERBLOCK_LEFT || $side == XOOPS_CENTERBLOCK_RIGHT) {
                $maxlen = 24;
                break;
            }
        }
    }

    // get configs
    $config = array();
    $config['num_rows'] = XOONIPS_BLOCK_RANKING_NUM_ROWS;
    $config['visible'] = explode(',', XOONIPS_BLOCK_RANKING_VISIBLE);
    $config['order'] = explode(',', XOONIPS_BLOCK_RANKING_ORDER);
    $config['days_enabled'] = XOONIPS_BLOCK_RANKING_DAYS_ENABLED;
    $config['days'] = XOONIPS_BLOCK_RANKING_DAYS;

    $visible = false;
    foreach ($config['visible'] as $v) {
        if ($v != 0) {
            $visible = true;
        }
    }
    if (!$visible) {
        return;
    }

    $term = 0;
    if ($config['days_enabled'] == 'on') {
        if (is_numeric($config['days'])) {
            $term = time() - (int) $config['days'] * 86400;
        }
    }

    // - set ranking number label
    $rank_tmp = explode(',', _MB_XOONIPS_RANKING_RANK_STR);
    $rank_str = array();
    for ($i = 0; $i < $config['num_rows']; ++$i) {
        $rank_str[] = ($i + 1).$rank_tmp[min($i, count($rank_tmp) - 1)];
    }

    $block['rankings'] = array();

    // ranking block
    // ranking viewed item
    if ($config['visible'][0]) {
        $items = array();
        $itemHandler = &Functions::getXoonipsHandler('Item', $dirname);
        $itemObjs = $itemHandler->getMostViewedItems($config['num_rows'], $term);
        $i = 0;
        foreach ($itemObjs as $itemObj) {
            $title = StringUtils::htmlSpecialChars($itemObj['title']);
            $title = StringUtils::truncate($title, $maxlen, $etc);
            $items[] = array(
                'title' => $title,
                'url' => $itemObj['url'],
                'num' => $itemObj['view_count'],
                'rank_str' => $rank_str[$i],
            );
            ++$i;
        }

        $block['rankings'][$config['order'][0]] = array(
            'items' => $items,
            'title' => _MB_XOONIPS_RANKING_VIEWED_ITEM,
        );
        unset($items);
    }

    // ranking downloaded item
    if ($config['visible'][1]) {
        $items = array();
        $itemFileHandler = &Functions::getXoonipsHandler('ItemFile', $dirname);
        $itemFileObjs = $itemFileHandler->getMostDownloadedItems($config['num_rows'], $term);
        $i = 0;
        foreach ($itemFileObjs as $itemFileObj) {
            $title = StringUtils::htmlSpecialChars($itemFileObj['title']);
            $title = StringUtils::truncate($title, $maxlen, $etc);
            $items[] = array(
                'title' => $title,
                'url' => $itemFileObj['url'],
                'num' => $itemFileObj['download_count'],
                'rank_str' => $rank_str[$i],
            );
            ++$i;
        }
        $block['rankings'][$config['order'][1]] = array(
            'items' => $items,
            'title' => _MB_XOONIPS_RANKING_DOWNLOADED_ITEM,
        );
        unset($items);
    }

    ksort($block['rankings']);

    return $block;
}
