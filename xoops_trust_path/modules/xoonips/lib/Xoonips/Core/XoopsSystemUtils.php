<?php

namespace Xoonips\Core;

/**
 * xoops system utility class.
 */
class XoopsSystemUtils
{
    /**
     * fix invalid xoops group permissions
     *  - refer: http://www.xugj.org/modules/d3forum/index.php?topic_id=791.
     *
     * @return bool false if failure
     */
    public static function fixGroupPermissions()
    {
        global $xoopsDB;
        // get invalid group ids
        $table = $xoopsDB->prefix('group_permission');
        $table2 = $xoopsDB->prefix('groups');
        $sql = sprintf('SELECT DISTINCT `gperm_groupid` FROM `%s` LEFT JOIN `%s` ON `%s`.`gperm_groupid`=`%s`.`groupid` WHERE `gperm_modid`=1 AND `groupid` IS NULL', $table, $table2, $table, $table2);
        $result = $xoopsDB->query($sql);
        if (!$result) {
            return false;
        }
        $gids = array();
        while ($myrow = $xoopsDB->fetchArray($result)) {
            $gids[] = $myrow['gperm_groupid'];
        }
        $xoopsDB->freeRecordSet($result);
        // remove all invalid group id entries
        if (count($gids) != 0) {
            $sql = sprintf('DELETE FROM `%s` WHERE `gperm_groupid` IN (%s) AND `gperm_modid`=1', $table, implode(',', $gids));
            $result = $xoopsDB->query($sql);
            if (!$result) {
                return false;
            }
        }

        return true;
    }

    /**
     * set xoops module admin right.
     *
     * @param int  $mid
     * @param int  $gid
     * @param bool $right
     *
     * @return bool
     */
    public static function setModuleAdminRight($mid, $gid, $right)
    {
        return self::_setRight('module_admin', $mid, $gid, $right);
    }

    /**
     * set module read right.
     *
     * @param int  $mid
     * @param int  $gid
     * @param bool $right
     *
     * @return bool
     */
    public static function setModuleReadRight($mid, $gid, $right)
    {
        return self::_setRight('module_read', $mid, $gid, $right);
    }

    /**
     * set block read right.
     *
     * @param int  $bid
     * @param int  $gid
     * @param bool $right
     *
     * @return bool
     */
    public static function setBlockReadRight($bid, $gid, $right)
    {
        return self::_setRight('block_read', $bid, $gid, $right);
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
     * set block position.
     *
     * @param int  $bid
     * @param bool $visible
     * @param int  $side
     *                      0: sideblock - left
     *                      1: sideblock - right
     *                      2: sideblock - left and right
     *                      3: centerblock - left
     *                      4: centerblock - right
     *                      5: centerblock - center
     *                      6: centerblock - left, right, center
     * @param int  $weight
     *
     * @return bool
     */
    public static function setBlockPosition($bid, $visible, $side, $weight)
    {
        $blockHandler = &xoops_gethandler('block');
        $blockObj = &$blockHandler->get($bid);
        if (!is_object($blockObj)) {
            return false;
        }
        $blockObj->set('visible', $visible ? 1 : 0);
        $blockObj->set('side', $side);
        $blockObj->set('weight', $weight);

        return $blockHandler->insert($blockObj);
    }

    /**
     * set block show page.
     *
     * @param int $bid
     * @param int $mid
     *                 -1 : top page
     *                 0 : all pages
     *                 >=1 : module id
     *
     * @return bool
     */
    public static function setBlockShowPage($bid, $mid, $is_show)
    {
        $db = &\XoopsDatabaseFactory::getDatabaseConnection();
        $table = $db->prefix('block_module_link');
        $sql = sprintf('UPDATE `%s` SET `module_id`=%d WHERE `block_id`=%u', $table, $mid, $bid);

        return $db->query($sql);
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
     * set xoops right.
     *
     * @param string $name
     * @param int    $iid
     * @param int    $gid
     * @param bool   $right
     *
     * @return bool
     */
    private static function _setRight($name, $iid, $gid, $right)
    {
        $gpermHandler = &xoops_gethandler('groupperm');
        if ($right) {
            $ret = $gpermHandler->addRight($name, $iid, $gid);
        } else {
            $ret = $gpermHandler->removeRight($name, $iid, $gid);
        }

        return $ret;
    }
}
