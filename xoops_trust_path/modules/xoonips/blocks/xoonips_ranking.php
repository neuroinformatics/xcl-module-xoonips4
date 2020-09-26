<?php

use Xoonips\Core\Functions;
use Xoonips\Core\StringUtils;

require_once dirname(__DIR__).'/class/core/Transaction.class.php';

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
        if ('b_xoonips_ranking_show' == $func) {
            $side = $b->getVar('side', 'n');
            if (XOOPS_SIDEBLOCK_LEFT == $side || XOOPS_SIDEBLOCK_RIGHT == $side) {
                $maxlen = 16;
                break;
            } elseif (XOOPS_CENTERBLOCK_LEFT == $side || XOOPS_CENTERBLOCK_RIGHT == $side) {
                $maxlen = 24;
                break;
            }
        }
    }

    // get configs
    $config = [];
    $config['num_rows'] = XOONIPS_BLOCK_RANKING_NUM_ROWS;
    $config['visible'] = explode(',', XOONIPS_BLOCK_RANKING_VISIBLE);
    $config['order'] = explode(',', XOONIPS_BLOCK_RANKING_ORDER);
    $config['days_enabled'] = XOONIPS_BLOCK_RANKING_DAYS_ENABLED;
    $config['days'] = XOONIPS_BLOCK_RANKING_DAYS;

    // update ranking
    $last_update = Functions::getXoonipsConfig($dirname, 'ranking_last_update');
    if ($last_update < time() - 86400) {
        $timeout = lock($dirname);
        if (false != $timeout) {
            $term = getTerm($config);
            // update viewed ranking
            $rankingViewedItemHandler = Functions::getXoonipsHandler('RankingViewedItemObject', $dirname);
            $rankingViewedItemHandler->update($term);
            // update downloaded ranking
            $rankingDownloadedItemHandler = Functions::getXoonipsHandler('RankingDownloadedItemObject', $dirname);
            $rankingDownloadedItemHandler->update($term);
            unlock($timeout, $dirname);
        }
    }

    $visible = false;
    foreach ($config['visible'] as $v) {
        if (0 != $v) {
            $visible = true;
        }
    }
    if (!$visible) {
        return;
    }

    // - set ranking number label
    $rank_tmp = explode(',', _MB_XOONIPS_RANKING_RANK_STR);
    $rank_str = [];
    for ($i = 0; $i < $config['num_rows']; ++$i) {
        $rank_str[] = ($i + 1).$rank_tmp[min($i, count($rank_tmp) - 1)];
    }

    $block['rankings'] = [];
    $itemHandler = Functions::getXoonipsHandler('Item', $dirname);

    // ranking block
    // ranking viewed item
    if ($config['visible'][0]) {
        $items = [];
        $itemObjs = $itemHandler->getMostViewedItems($config['num_rows']);
        $i = 0;
        foreach ($itemObjs as $itemObj) {
            $title = StringUtils::htmlSpecialChars($itemObj['title']);
            $title = StringUtils::truncate($title, $maxlen, $etc);
            $items[] = [
                'title' => $title,
                'url' => $itemObj['url'],
                'num' => $itemObj['count'],
                'rank_str' => $rank_str[$i],
            ];
            ++$i;
        }

        $block['rankings'][$config['order'][0]] = [
            'items' => $items,
            'title' => _MB_XOONIPS_RANKING_VIEWED_ITEM,
        ];
        unset($items);
    }

    // ranking downloaded item
    if ($config['visible'][1]) {
        $items = [];
        $itemObjs = $itemHandler->getMostDownloadedItems($config['num_rows']);
        $i = 0;
        foreach ($itemObjs as $itemObj) {
            $title = StringUtils::htmlSpecialChars($itemObj['title']);
            $title = StringUtils::truncate($title, $maxlen, $etc);
            $items[] = [
                'title' => $title,
                'url' => $itemObj['url'],
                'num' => $itemObj['count'],
                'rank_str' => $rank_str[$i],
            ];
            ++$i;
        }
        $block['rankings'][$config['order'][1]] = [
            'items' => $items,
            'title' => _MB_XOONIPS_RANKING_DOWNLOADED_ITEM,
        ];
        unset($items);
    }

    ksort($block['rankings']);

    return $block;
}

/**
 * lock rankings table update.
 *
 * @param string dirname
 *
 * @return int
 */
function lock($dirname)
{
    $now = time();
    $timeout = $now + 180;
    if (setConfig('ranking_lock_timeout', $timeout, $dirname)) {
        // transaction
        $transaction = Xoonips_Transaction::getInstance();
        $transaction->start();
        // lock exclusively
        global $xoopsDB;
        $configTable = $xoopsDB->prefix($dirname.'_config');
        $xoopsDB->queryF('SELECT value FROM '.$configTable.' WHERE name=\'ranking_last_update\' FOR UPDATE');

        return $timeout;
    }

    return false;
}

/**
 * unlock rankings table update.
 *
 * @param int timeout
 * @param string dirname
 *
 * @return bool
 */
function unlock($timeout, $dirname)
{
    // transaction
    $transaction = Xoonips_Transaction::getInstance();
    $transaction->commit();
    $oldTimeout = Functions::getXoonipsConfig($dirname, 'ranking_lock_timeout');
    if ($oldTimeout != $timeout) {
        return false;
    }
    if (!setConfig('ranking_lock_timeout', 0, $dirname)) {
        return false;
    }
    if (!setConfig('ranking_last_update', time(), $dirname)) {
        return false;
    }

    return true;
}

/**
 * set config (force).
 *
 * @param string name
 * @param string value
 * @param string dirname
 *
 * @return bool
 */
function setConfig($name, $value, $dirname)
{
    $configHandler = Functions::getXoonipsHandler('ConfigObject', $dirname);

    return $configHandler->setConfig($name, $value, true);
}

/**
 * get term.
 *
 * @param array config
 *
 * @return int term
 */
function getTerm($config)
{
    $now = time();
    // set default 7 days.
    $term = $now - 604800;
    if ('on' == $config['days_enabled']) {
        if (is_numeric($config['days'])) {
            $term = time() - (int) $config['days'] * 86400;
        }
    }

    return $term;
}
