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
     * @return ?string
     */
    public static function getTrustDirnameByDirname(string $dirname): ?string
    {
        static $cache = [];
        if (array_key_exists($dirname, $cache)) {
            return $cache[$dirname];
        }
        $cache[$dirname] = null;
        $moduleHandler = xoops_gethandler('module');
        $moduleObj = $moduleHandler->getByDirname($dirname);
        if (is_object($moduleObj)) {
            $trustDirname = trim((string) $moduleObj->get('trust_dirname'));
            if ('' !== $trustDirname) {
                $cache[$dirname] = $trustDirname;
            }
        }

        return $cache[$dirname];
    }

    /**
     * get dirname list by trust dirname.
     *
     * @param string $trustDirname
     *
     * @return array
     */
    public static function getDirnameListByTrustDirname(string $trustDirname): array
    {
        static $cache = [];
        if (array_key_exists($trustDirname, $cache)) {
            return $cache[$trustDirname];
        }
        $cache[$trustDirname] = [];
        $ret = [];
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('isactive', 0, '>'));
        $criteria->add(new \Criteria('trust_dirname', $trustDirname));
        $criteria->addSort('weight', 'ASC');
        $criteria->addSort('mid', 'ASC');
        $moduleHandler = xoops_gethandler('module');
        $moduleObjs = $moduleHandler->getObjects($criteria);
        foreach ($moduleObjs as $moduleObj) {
            $ret[] = $moduleObj->get('dirname');
        }
        $cache[$trustDirname] = $ret;

        return $ret;
    }

    /**
     * get module handler.
     *
     * @param string  $name
     * @param string  $dirname
     * @param ?string $trustDirname
     *
     * @return ?object
     */
    public static function getModuleHandler(string $name, string $dirname, ?string $trustDirname = null): ?object
    {
        static $cache = [];
        $key = $dirname.':'.$name;
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }
        $cache[$key] = null;
        if (null === $trustDirname) {
            $trustDirname = self::getTrustDirnameByDirname($dirname);
        }
        if (null !== $trustDirname) {
            $className = ucfirst($trustDirname).'\\Handler\\'.ucfirst($name).'Handler';
            if (!class_exists($className)) {
                $className = ucfirst($trustDirname).'_'.ucfirst($name).'Handler';
                $fpath = XOOPS_TRUST_PATH.'/modules/'.$trustDirname.'/class/handler/'.ucfirst($name).'.class.php';
                require_once $fpath;
            }
            if (class_exists($className)) {
                $db = \XoopsDatabaseFactory::getDatabaseConnection();
                $cache[$key] = new $className($db, $dirname);
            }
        } else {
            $cache[$key] = xoops_getmodulehandler($name, $dirname);
        }

        return $cache[$key];
    }

    /**
     * get environment variable.
     *
     * @param string $key
     *
     * @return ?string
     */
    public static function getEnv(string $key): ?string
    {
        $ret = @getenv($key);

        return false !== $ret ? $ret : null;
    }

    /**
     * get xoops config.
     *
     * @param string $key
     * @param int    $catId
     *
     * @return mixed
     */
    public static function getXoopsConfig(string $key, int $catId = XOOPS_CONF)
    {
        $cat = 'xoops:'.$catId;
        if (!array_key_exists($cat, self::$mConfigs)) {
            $configHandler = xoops_gethandler('config');
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
                    foreach (array_keys(self::$mConfigs[$dirname]) as $mKey) {
                        if (!array_key_exists($mKey, self::$mConfigs[$cat])) {
                            self::$mConfigs[$cat][$mKey] = self::$mConfigs[$dirname][$mKey];
                        }
                    }
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
    public static function getModuleConfig(string $dirname, string $key)
    {
        if (!array_key_exists($dirname, self::$mConfigs)) {
            $configHandler = xoops_gethandler('config');
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
    public static function setModuleConfig(string $dirname, string $key, $value): bool
    {
        $configHandler = xoops_gethandler('config');
        $moduleHandler = xoops_gethandler('module');
        $moduleObj = $moduleHandler->getByDirname($dirname);
        if (!is_object($moduleObj)) {
            return false;
        }
        $mid = (int) $moduleObj->get('mid');
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
    public static function formatPagetitle(string $moduleName, string $pageTitle, string $action): string
    {
        $format = self::getModuleConfig('legacyRender', 'pagetitle');
        if (null === $format) {
            $format = '{modulename} {action} [pagetitle]:[/pagetitle] {pagetitle}';
        }
        $search = ['{modulename}', '{pagetitle}', '{action}'];
        $replace = [$moduleName, $pageTitle, $action];
        $ret = str_replace($search, $replace, $format);
        $ret = preg_replace('/\[modulename\](.*)\[\/modulename\]/U', ('' === $moduleName ? '' : '$1'), $ret);
        $ret = preg_replace('/\[pagetitle\](.*)\[\/pagetitle\]/U', ('' === $pageTitle ? '' : '$1'), $ret);
        $ret = preg_replace('/\[action\](.*)\[\/action\]/U', ('' === $action ? '' : '$1'), $ret);

        return $ret;
    }

    /**
     * get module version.
     *
     * @param string $dirname
     *
     * @return ?int version
     */
    public static function getModuleVersion(string $dirname): ?int
    {
        $moduleHandler = xoops_gethandler('module');
        $moduleObj = $moduleHandler->getByDirname($dirname);
        if (!is_object($moduleObj)) {
            return null;
        }

        return (int) $moduleObj->get('version');
    }

    /**
     * get current user id.
     *
     * @return int user id
     */
    public static function getUid(): int
    {
        global $xoopsUser;

        return is_object($xoopsUser) ? (int) $xoopsUser->get('uid') : self::UID_GUEST;
    }

    /**
     * get user name.
     *
     * @param int $uid
     *
     * @return ?string
     */
    public static function getUserName(int $uid): ?string
    {
        $name = null;
        if (defined('XOOPS_CUBE_LEGACY')) {
            \XCube_DelegateUtils::call('Legacy_User.GetUserName', new \XCube_Ref($name), $uid);
        }
        if (null === $name) {
            $memberHandler = xoops_gethandler('member');
            $userObj = $memberHandler->getUser($uid);
            if (is_object($userObj)) {
                $name = $userObj->get('uname');
            }
        }

        return $name;
    }

    /**
     * check whether user exists.
     *
     * @param int $uid
     *
     * @return bool
     */
    public static function userExists(int $uid): bool
    {
        $memberHandler = xoops_gethandler('member');
        $userObj = $memberHandler->getUser($uid);

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
    public static function isAdmin(int $uid, string $dirname): bool
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
    public static function isSiteAdmin(int $uid): bool
    {
        if (self::UID_GUEST === $uid) {
            return false;
        }
        if (!array_key_exists($uid, self::$mGroupIds)) {
            $memberHandler = xoops_gethandler('member');
            self::$mGroupIds[$uid] = array_map('intval', $memberHandler->getGroupsByUser($uid, false));
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
    public static function isModuleAdmin(int $uid, string $dirname): bool
    {
        if (self::UID_GUEST == $uid) {
            return false;
        }
        $moduleHandler = xoops_gethandler('module');
        $moduleObj = $moduleHandler->getByDirname($dirname);
        if (!is_object($moduleObj)) {
            return false;
        }
        $mid = (int) $moduleObj->get('mid');
        if (!array_key_exists($uid, self::$mGroupIds)) {
            $memberHandler = xoops_gethandler('member');
            self::$mGroupIds[$uid] = array_map('intval', $memberHandler->getGroupsByUser($uid, false));
        }
        $gpermHandler = xoops_gethandler('groupperm');

        return $gpermHandler->checkRight('module_admin', $mid, self::$mGroupIds[$uid], 1, true);
    }
}
