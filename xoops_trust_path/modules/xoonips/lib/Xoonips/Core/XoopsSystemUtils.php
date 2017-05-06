<?php

namespace Xoonips\Core;

/**
 * xoops system utility class.
 */
class XoopsSystemUtils
{
    const BLOCK_SIDE_HIDE = -1;
    const BLOCK_SIDE_LEFT = 0;
    const BLOCK_SIDE_RIGHT = 1;
    const BLOCK_SIDE_BOTH = 2;
    const BLOCK_SIDE_CENTER_LEFT = 3;
    const BLOCK_SIDE_CENTER_RIGHT = 4;
    const BLOCK_SIDE_CENTER_CENTER = 5;
    const BLOCK_SIDE_CENTER_ALL = 6;

    const BLOCK_PAGE_TOP = -1;
    const BLOCK_PAGE_ALL = 0;

    /**
     * fix invalid xoops group permissions
     *  - refer: http://www.xugj.org/modules/d3forum/index.php?topic_id=791.
     *
     * @return bool false if failure
     */
    public static function fixGroupPermissions()
    {
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        // get invalid group ids
        $table = $db->prefix('group_permission');
        $table2 = $db->prefix('groups');
        $sql = sprintf('SELECT DISTINCT `gperm_groupid` FROM `%1$s` LEFT JOIN `%2$s` ON `%1$s`.`gperm_groupid`=`%2$s`.`groupid` WHERE `%1$s`.`gperm_modid`=1 AND `%2$s`.`groupid` IS NULL', $table, $table2);
        $result = $db->query($sql);
        if (!$result) {
            return false;
        }
        $gids = array();
        while ($myrow = $db->fetchArray($result)) {
            $gids[] = $myrow['gperm_groupid'];
        }
        $db->freeRecordSet($result);
        // remove all invalid group id entries
        if (count($gids) != 0) {
            $sql = sprintf('DELETE FROM `%s` WHERE `gperm_groupid` IN (%s) AND `gperm_modid`=1', $table, implode(',', $gids));
            $result = $db->query($sql);
            if (!$result) {
                return false;
            }
        }

        return true;
    }

    /**
     * fix invalid module configs.
     *
     * @return bool false if failure
     */
    public static function fixModuleConfigs()
    {
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        // get invalid module ids
        $table = $db->prefix('config');
        $table2 = $db->prefix('modules');
        $sql = sprintf('SELECT DISTINCT `%1$s`.`conf_modid` FROM `%s` LEFT JOIN `%2$s` ON `%1$s`.`conf_modid`=`%2$s`.`mid` WHERE `%1$s`.`conf_modid` != 0 AND `%2$s`.`mid` IS NULL', $table, $table2);
        $result = $db->query($sql);
        if (!$result) {
            return false;
        }
        $mids = array();
        while ($myrow = $db->fetchArray($result)) {
            $mids[] = $myrow['conf_modid'];
        }
        $db->freeRecordSet($result);
        // remove all invalid config entries
        $configHandler = &xoops_gethandler('config');
        foreach ($mids as $mid) {
            $configs = &$configHandler->getConfigs(new \Criteria('conf_modid', $mid));
            foreach ($configs as $config) {
                if ($configHandler->deleteConfig($config) === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * set module admin rights.
     *
     * @param int   $mid
     * @param array $gids
     *
     * @return bool
     */
    public static function setModuleAdminRights($mid, $gids)
    {
        return self::_setRights('module_admin', $mid, $gids);
    }

    /**
     * set module read rights.
     *
     * @param int   $mid
     * @param array $gids
     *
     * @return bool
     */
    public static function setModuleReadRights($mid, $gids)
    {
        return self::_setRights('module_read', $mid, $gids);
    }

    /**
     * set block read rights.
     *
     * @param int   $bid
     * @param array $gids
     *
     * @return bool
     */
    public static function setBlockReadRights($bid, $gids)
    {
        return self::_setRights('block_read', $bid, $gids);
    }

    /**
     * get block id.
     *
     * @param string $dirname
     * @param string $show_func
     *
     * @return int
     */
    public static function getBlockId($dirname, $show_func)
    {
        $ret = false;
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        $table = $db->prefix('newblocks');
        $sql = sprintf('SELECT bid FROM `%s` WHERE `dirname`=%s AND `show_func`=%s', $table, $db->quoteString($dirname), $db->quoteString($show_func));
        if ($res = $db->query($sql)) {
            if ($row = $db->fetchArray($res)) {
                $ret = intval($row['bid']);
            }
            $db->freeRecordSet($res);
        }

        return $ret;
    }

    /**
     * set block information.
     *
     * @param int   $bid
     * @param int   $side
     *                      -1: hide
     *                      0:  sideblock - left
     *                      1:  sideblock - right
     *                      2:  sideblock - left and right
     *                      3:  centerblock - left
     *                      4:  centerblock - right
     *                      5:  centerblock - center
     *                      6:  centerblock - left, right, center
     * @param int   $weight
     * @param array $pages
     *                      -1:  top page
     *                      0:   all pages
     *                      >=1: module id
     *
     * @return bool
     */
    public static function setBlockInfo($bid, $side, $weight, $pages)
    {
        if (self::_setBlockPosition($bid, $side, $weight) === false) {
            return false;
        }
        if ($pages !== false) {
            if (self::_setBlockShowPages($bid, $pages) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * set start module.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public static function setStartupPageModule($dirname)
    {
        if (empty($dirname)) {
            // top page
            $dirname = '--';
        }
        $configHandler = &xoops_gethandler('config');
        $criteria = new \CriteriaCompo(new \Criteria('conf_modid', 0));
        $criteria->add(new \Criteria('conf_catid', XOOPS_CONF));
        $criteria->add(new \Criteria('conf_name', 'startpage'));
        $configObjs = &$configHandler->getConfigs($criteria);
        if (count($configObjs) != 1) {
            return false;
        }
        list($configObj) = $configObjs;
        $configObj->setConfValueForInput($dirname);

        return $configHandler->insertConfig($configObj);
    }

    /**
     * enable xoops notificaiton.
     *
     * @param int    $mid
     * @param string $category
     * @param string $event
     *
     * @return bool false if failure
     */
    public static function enableNotification($mid, $category, $event)
    {
        $configHandler = &xoops_gethandler('config');
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('conf_name', 'notification_events'));
        $criteria->add(new \Criteria('conf_modid', $mid));
        $criteria->add(new \Criteria('conf_catid', 0));
        $configItems = $configHandler->getConfigs($criteria);
        if (count($configItems) != 1) {
            return false;
        } else {
            list($configItem) = $configItems;
            $optionValue = $category.'-'.$event;
            $optionValues = $configItem->getConfValueForOutput();
            if (!in_array($optionValue, $optionValues)) {
                $optionValues[] = $optionValue;
                $configItem->setConfValueForInput($optionValues);
                $configItemHandler = &xoops_gethandler('config_item');
                $configItemHandler->insert($configItem);
            }
        }

        return true;
    }

    /**
     * subscribe user to xoops notificaiton.
     *
     * @param int    $mid
     * @param int    $uid
     * @param string $category
     * @param string $event
     *
     * @return bool false if failure
     */
    public static function subscribeNotification($mid, $uid, $category, $event)
    {
        $notificationHandler = &xoops_gethandler('notification');
        $notificationHandler->subscribe($category, 0, $event, null, $mid, $uid);

        return true;
    }

    /**
     * set block position.
     *
     * @param int $bid
     * @param int $side
     *                    -1: hide
     *                    0:  sideblock - left
     *                    1:  sideblock - right
     *                    2:  sideblock - left and right
     *                    3:  centerblock - left
     *                    4:  centerblock - right
     *                    5:  centerblock - center
     *                    6:  centerblock - left, right, center
     * @param int $weight
     *
     * @return bool
     */
    private static function _setBlockPosition($bid, $side, $weight)
    {
        $visible = ($side < 0) ? 0 : 1;
        $side = ($visible == 0) ? 0 : $side;
        $blockHandler = &xoops_gethandler('block');
        $blockObj = &$blockHandler->get($bid);
        if (!is_object($blockObj)) {
            return false;
        }
        $blockObj->set('visible', $visible);
        $blockObj->set('side', $side);
        if ($weight !== false) {
            $blockObj->set('weight', $weight);
        }

        return $blockHandler->insert($blockObj);
    }

    /**
     * set block show pages.
     *
     * @param int   $bid
     * @param array $pages
     *                     -1:  top page
     *                     0:   all pages
     *                     >=1: module id
     *
     * @return bool
     */
    private static function _setBlockShowPages($bid, $pages)
    {
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        if (in_array(self::BLOCK_PAGE_ALL, $pages)) {
            // set only 0 if '0:all pages' found in pages.
            $pages = array(self::BLOCK_PAGE_ALL);
        }
        $table = $db->prefix('block_module_link');
        $sql = sprintf('SELECT `module_id` FROM `%s` WHERE `block_id`=%u AND `module_id` IN (%s)', $table, $bid, implode(',', $pages));
        $result = $db->query($sql);
        if (!$result) {
            return false;
        }
        $existPages = array();
        while ($myrow = $db->fetchArray($result)) {
            $existPages[] = $myrow['module_id'];
        }
        $db->freeRecordSet($result);
        $addPages = array_diff($pages, $existPages);
        foreach ($addPages as $page) {
            $sql = sprintf('INSERT INTO `%s` (`block_id`,`module_id`) VALUES ( %u, %d )', $table, $bid, $page);
            if (!$result = $db->query($sql)) {
                return false;
            }
        }
        $delPages = array_diff($existPages, $pages);
        if (!empty($delPages)) {
            $sql = sprintf('DELETE FROM `%s` WHERE `block_id`=%u AND `module_id` IN (%s)', $table, $bid, implode(',', $delPages));
            if (!$result = $db->query($sql)) {
                return false;
            }
        }

        return true;
    }

    /**
     * set xoops rights.
     *
     * @param string $name
     * @param int    $iid
     * @param array  $gids
     *
     * @return bool
     */
    private static function _setRights($name, $iid, $gids)
    {
        $gpermHandler = &xoops_gethandler('groupperm');
        $memberHandler = &xoops_gethandler('member');
        $groupNames = &$memberHandler->getGroupList();
        $allGids = array_keys($groupNames);
        $delGids = array_diff($allGids, $gids);
        foreach ($gids as $gid) {
            if (!$gpermHandler->addRight($name, $iid, $gid)) {
                return false;
            }
        }
        foreach ($delGids as $gid) {
            if (!$gpermHandler->removeRight($name, $iid, $gid)) {
                return false;
            }
        }

        return true;
    }
}
