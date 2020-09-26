<?php

namespace Xoonips\Core;

/**
 * xoops utility class.
 */
class XoopsUtils
{
    const UID_GUEST = 0;

    /**
     * configs cache.
     *
     * @var array
     */
    private static $mConfigs = [];

    /**
     * user membership cache.
     *
     * @var array
     */
    private static $mGroupIds = [];

    /**
     * get trust dirname by dirname.
     *
     * @param string $dirname
     *
     * @return string
     */
    public static function getTrustDirnameByDirname($dirname)
    {
        static $cache = [];
        if (array_key_exists($dirname, $cache)) {
            return $cache[$dirname];
        }
        $handler = &xoops_gethandler('module');
        $module = &$handler->getByDirname($dirname);
        if (is_object($module)) {
            $ret = $module->get('trust_dirname');
            if (false !== $ret) {
                $cache[$dirname] = $ret;

                return $ret;
            }
        }

        return null;
    }

    /**
     * get dirname list by trust dirname.
     *
     * @param string $trustDirname
     *
     * @return array
     */
    public static function getDirnameListByTrustDirname($trustDirname)
    {
        $ret = [];
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('isactive', 0, '>'));
        $criteria->add(new \Criteria('trust_dirname', $trustDirname));
        $criteria->addSort('weight', 'ASC');
        $criteria->addSort('mid', 'ASC');
        $handler = &xoops_gethandler('module');
        $objs = &$handler->getObjects($criteria);
        foreach ($objs as $obj) {
            $ret[] = $obj->get('dirname');
        }

        return $ret;
    }

    /**
     * get module handler.
     *
     * @param string $name
     * @param string $dirname
     * @param string $trustDirname
     *
     * @return Handler\AbstractHandler &
     */
    public static function &getModuleHandler($name, $dirname, $trustDirname = null)
    {
        static $cache = [];
        $key = $dirname.':'.$name;
        if (!array_key_exists($key, $cache)) {
            $db = &\XoopsDatabaseFactory::getDatabaseConnection();
            if (is_null($trustDirname)) {
                $trustDirname = self::getTrustDirnameByDirname($dirname);
            }
            if (isset($trustDirname)) {
                $className = ucfirst($trustDirname).'\\Handler\\'.ucfirst($name).'Handler';
                if (!class_exists($className)) {
                    $fpath = XOOPS_TRUST_PATH.'/modules/'.$trustDirname.'/class/handler/'.ucfirst($name).'.class.php';
                    require_once $fpath;
                    $className = ucfirst($trustDirname).'_'.ucfirst($name).'Handler';
                }
                $cache[$key] = new $className($db, $dirname);
            } else {
                $cache[$key] = &xoops_getmodulehandler($name, $dirname);
            }
        }

        return $cache[$key];
    }

    /**
     * get environment variable.
     *
     * @param string $key
     *
     * @return string
     */
    public static function getEnv($key)
    {
        return @getenv($key);
    }

    /**
     * get xoops config.
     *
     * @param string $key
     * @param string $catId
     *
     * @return mixed
     */
    public static function getXoopsConfig($key, $catId = XOOPS_CONF)
    {
        $cat = 'xoops:'.$catId;
        if (!array_key_exists($cat, self::$mConfigs)) {
            $configHandler = &xoops_gethandler('config');
            self::$mConfigs[$cat] = $configHandler->getConfigsByCat($catId);
            if (defined('XOOPS_CUBE_LEGACY')) {
                switch ($catId) {
                case XOOPS_CONF:
                    static $maps = [
                        'user' => ['avatar_minposts', 'maxuname', 'sslloginlink', 'sslpost_name', 'use_ssl', 'usercookie'],
                        'legacyRender' => ['banners'],
                    ];
                    foreach ($maps as $dirname => $mkeys) {
                        foreach ($mkeys as $mkey) {
                            self::$mConfigs[$cat][$mkey] = self::getModuleConfig($dirname, $mkey);
                        }
                    }
                    break;
                case XOOPS_CONF_USER:
                case XOOPS_CONF_METAFOOTER:
                    static $dirnames = [XOOPS_CONF_USER => 'user', XOOPS_CONF_METAFOOTER => 'legacyRender'];
                    $dirname = $dirnames[$catId];
                    if (!array_key_exists($dirname, self::$mConfigs)) {
                        self::$mConfigs[$dirname] = $configHandler->getConfigsByDirname($dirname);
                    }
                    self::$mConfigs[$cat] = self::$mConfig[$dirname];
                    break;
                }
            }
        }

        return array_key_exists($key, self::$mConfigs[$cat]) ? self::$mConfigs[$cat][$key] : null;
    }

    /**
     * get module config.
     *
     * @param string $dirname
     * @param string $key
     *
     * @return mixed
     */
    public static function getModuleConfig($dirname, $key)
    {
        if (!array_key_exists($dirname, self::$mConfigs)) {
            $configHandler = &xoops_gethandler('config');
            self::$mConfigs[$dirname] = $configHandler->getConfigsByDirname($dirname);
        }
        if (is_null(self::$mConfigs[$dirname])) {
            // dirname not found
            return null;
        }

        return array_key_exists($key, self::$mConfigs[$dirname]) ? self::$mConfigs[$dirname][$key] : null;
    }

    /**
     * set module config.
     *
     * @param string $dirname
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public static function setModuleConfig($dirname, $key, $value)
    {
        $configHandler = &xoops_gethandler('config');
        $moduleHandler = &xoops_gethandler('module');
        $moduleObj = &$moduleHandler->getByDirname($dirname);
        $mid = $moduleObj->get('mid');
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('conf_modid', $mid));
        $criteria->add(new \Criteria('conf_name', $key));
        $configObjs = $configHandler->getConfigs($criteria);
        if (1 != count($configObjs)) {
            return false;
        }
        $configObj = array_shift($configObjs);
        $configObj->set('conf_value', $value);

        if (!$configHandler->insertConfig($configObj)) {
            return false;
        }
        if (array_key_exists($dirname, self::$mConfigs)) {
            self::$mConfigs[$dirname][$key] = $value;
        }

        return true;
    }

    /**
     * format page title.
     *
     * @param string $moduleName
     * @param string $pageTitle
     * @param string $action
     *
     * @return string
     **/
    public static function formatPagetitle($moduleName, $pageTitle, $action)
    {
        $format = self::getModuleConfig('legacyRender', 'pagetitle');
        if (is_null($format)) {
            $format = '{modulename} {action} [pagetitle]:[/pagetitle] {pagetitle}';
        }
        $search = ['{modulename}', '{pagetitle}', '{action}'];
        $replace = [$moduleName, $pageTitle, $action];
        $ret = str_replace($search, $replace, $format);
        $ret = preg_replace('/\[modulename\](.*)\[\/modulename\]/U', (empty($moduleName) ? '' : '$1'), $ret);
        $ret = preg_replace('/\[pagetitle\](.*)\[\/pagetitle\]/U', (empty($pageTitle) ? '' : '$1'), $ret);
        $ret = preg_replace('/\[action\](.*)\[\/action\]/U', (empty($action) ? '' : '$1'), $ret);

        return $ret;
    }

    /**
     * render uri.
     *
     * @param string $dirname
     * @param string $dataname
     * @param int    $dataId
     * @param string $action
     * @param string $query
     *
     * @return string
     **/
    public static function renderUri($dirname, $dataname = null, $dataId = 0, $action = null, $query = null)
    {
        $uri = null;
        if (true == self::getXoopsConfig('cool_uri')) {
            if (isset($dataname)) {
                if ($dataId > 0) {
                    if (isset($action)) {
                        $uri = sprintf('/%s/%s/%d/%s', $dirname, $dataname, $dataId, $action);
                    } else {
                        $uri = sprintf('/%s/%s/%d', $dirname, $dataname, $dataId);
                    }
                } else {
                    if (isset($action)) {
                        $uri = sprintf('/%s/%s/%s', $dirname, $dataname, $action);
                    } else {
                        $uri = sprintf('/%s/%s', $dirname, $dataname);
                    }
                }
            } else {
                if ($dataId > 0) {
                    if (isset($action)) {
                        die();
                    } else {
                        $uri = sprintf('/%s/%d', $dirname, $dataId);
                    }
                } else {
                    if (isset($action)) {
                        die();
                    } else {
                        $uri = '/'.$dirname;
                    }
                }
            }
            $uri = (isset($query)) ? XOOPS_URL.$uri.'?'.$query : XOOPS_URL.$uri;
        } else {
            $trustDirname = self::getTrustDirnameByDirname($dirname);
            \XCube_DelegateUtils::call('Module.'.$trustDirname.'.Global.Event.GetNormalUri', new \XCube_Ref($uri), $dirname, $dataname, $dataId, $action, $query);
            $uri = XOOPS_MODULE_URL.$uri;
        }

        return $uri;
    }

    /**
     * get module version.
     *
     * @param string $dirname
     *
     * @return float version
     */
    public static function getModuleVersion($dirname)
    {
        $moduleHandler = &xoops_gethandler('module');
        $moduleObj = &$moduleHandler->getByDirname($dirname);
        if (!is_object($moduleObj)) {
            return false;
        }

        return $moduleObj->get('version');
    }

    /**
     * get current user id.
     *
     * @return int user id
     */
    public static function getUid()
    {
        global $xoopsUser;

        return is_object($xoopsUser) ? intval($xoopsUser->get('uid')) : self::UID_GUEST;
    }

    /**
     * get user name.
     *
     * @param int $uid
     *
     * @return string | null
     */
    public static function getUserName($uid)
    {
        $name = null;
        XCube_DelegateUtils::call('Legacy_User.GetUserName', new XCube_Ref($name), $uid);
        if (!$name) {
            $handler = &xoops_gethandler('member');
            $user = &$handler->getUser(intval($uid));
            if ($user) {
                $name = $user->get('uname');
            }
        }

        return $name;
    }

    /**
     * check whether user exists.
     *
     * @param int    $userId
     * @param string $dirname
     *
     * @return bool
     */
    public static function userExists($userId)
    {
        $memberHandler = &xoops_gethandler('member');
        $userObj = &$memberHandler->getUser($userId);

        return is_object($userObj);
    }

    /**
     * check whether user is administraotr.
     *
     * @param int    $uid
     * @param string $dirname
     *
     * @return bool
     */
    public static function isAdmin($uid, $dirname)
    {
        return self::isSiteAdmin($uid) || self::isModuleAdmin($uid, $dirname);
    }

    /**
     * check whether user is site administraotr.
     *
     * @param int $uid
     *
     * @return bool
     */
    public static function isSiteAdmin($uid)
    {
        if (self::UID_GUEST == $uid) {
            return false;
        }
        if (!array_key_exists($uid, self::$mGroupIds)) {
            $memberHandler = &xoops_gethandler('member');
            self::$mGroupIds[$uid] = $memberHandler->getGroupsByUser($uid, false);
        }

        return in_array(XOOPS_GROUP_ADMIN, self::$mGroupIds[$uid]);
    }

    /**
     * check whether user is site administraotr.
     *
     * @param int    $uid
     * @param string $dirname
     *
     * @return bool
     */
    public static function isModuleAdmin($uid, $dirname)
    {
        if (self::UID_GUEST == $uid) {
            return false;
        }
        $moduleHandler = &xoops_gethandler('module');
        $moduleObj = &$moduleHandler->getByDirname($dirname);
        if (!is_object($moduleObj)) {
            return false;
        }
        $mid = $moduleObj->get('mid');
        if (!array_key_exists($uid, self::$mGroupIds)) {
            $memberHandler = &xoops_gethandler('member');
            self::$mGroupIds[$uid] = $memberHandler->getGroupsByUser($uid, false);
        }
        $gpermHandler = &xoops_gethandler('groupperm');

        return $gpermHandler->checkRight('module_admin', $mid, self::$mGroupIds[$uid], 1, true);
    }
}
