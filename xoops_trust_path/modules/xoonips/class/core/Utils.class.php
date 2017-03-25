<?php

class Xoonips_Utils
{
    /**
     * get trust dirname by dirname.
     *
     * @param string $dirname
     *
     * @return string/null
     */
    public static function getTrustDirnameByDirname($dirname)
    {
        static $cache = array();
        if (!isset($cache[$dirname])) {
            $cache[$dirname] = false;
            $handler = &xoops_gethandler('module');
            $module = &$handler->getByDirname($dirname);
            if ($module && $module->get('trust_dirname')) {
                $cache[$dirname] = $module->get('trust_dirname');
            }
        }

        return $cache[$dirname] === false ? null : $cache[$dirname];
    }

    /**
     * get xoops handler.
     *
     * @param string $name
     * @param bool   $optional
     *
     * @return XoopsObjectHandler&
     */
    public static function &getXoopsHandler($name, $optional = false)
    {
        return xoops_gethandler($name, $optional);
    }

    /**
     * get trust module handler.
     *
     * @param string $name
     * @param string $dirname
     * @param string $trustDirname
     *
     * @return XoopsObjectHandleer
     */
    public static function getTrustModuleHandler($name, $dirname, $trustDirname)
    {
        $mydirname = $dirname;
        $mytrustdirname = $trustDirname;
        require_once XOOPS_TRUST_PATH.'/modules/'.$trustDirname.'/class/handler/'.ucfirst($name).'.class.php';
        $className = ucfirst($trustDirname).'_'.ucfirst($name).'Handler';
        $root = &XCube_Root::getSingleton();
        $instance = new $className($root->mController->getDB(), $dirname);

        return $instance;
    }

    /**
     * get module handler.
     *
     * @param string $name
     * @param string $dirname
     *
     * @return XoopsObjectHandleer
     */
    public static function getModuleHandler($name, $dirname)
    {
        if ($trustDirname = self::getTrustDirnameByDirname($dirname)) {
            return self::getTrustModuleHandler($name, $dirname, $trustDirname);
        }

        return xoops_getmodulehandler($name, $dirname);
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
     * get module config.
     *
     * @param string $dirname
     * @param string $key
     *
     * @return mixed
     */
    public static function getModuleConfig($dirname, $key)
    {
        $handler = &self::getXoopsHandler('config');
        $configArr = $handler->getConfigsByDirname($dirname);

        return $configArr[$key];
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
        $configHandler = &self::getXoopsHandler('config');
        $moduleHandler = &self::getXoopsHandler('module');
        $moduleObj = &$moduleHandler->getByDirname($dirname);
        $mid = $moduleObj->get('mid');
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('conf_modid', $mid));
        $criteria->add(new Criteria('conf_name', $key));
        $configObjs = $configHandler->getConfigs($criteria);
        if (count($configObjs) != 1) {
            return false;
        }
        $configObj = array_shift($configObjs);
        $configObj->set('conf_value', $value);

        return $configHandler->insertConfig($configObj);
    }

    /**
     * get xoonips config.
     *
     * @param string $dirnmae
     * @param string $key
     *
     * @return mixed
     */
    public static function getXooNIpsConfig($dirname, $key)
    {
        $handler = self::getModuleHandler('config', $dirname);

        return $handler->getConfig($key);
    }

    /**
     * set xoonips config.
     *
     * @param string $dirnmae
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public static function setXooNIpsConfig($dirname, $key, $value)
    {
        $handler = self::getModuleHandler('config', $dirname);

        return $handler->setConfig($key, $value);
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
     * check whether user is admin.
     *
     * @param int    $userId
     * @param string $dirname
     *
     * @return bool
     */
    public static function isAdmin($userId, $dirname = false)
    {
        $memberHandler = &xoops_gethandler('member');
        $groupIds = &$memberHandler->getGroupsByUser($userId);
        if (in_array(XOOPS_GROUP_ADMIN, $groupIds)) {
            return true;
        }
        if ($dirname !== false) {
            $userObj = &$memberHandler->getUser($userId);
            $moduleHandler = &xoops_gethandler('module');
            $moduleObj = &$moduleHandler->getByDirname($dirname);
            $moduleId = $moduleObj->get('mid');
            if ($userObj->isAdmin($moduleId)) {
                return true;
            }
        }

        return false;
    }

    public static function convertSQLStr($v)
    {
        if (is_null($v)) {
            $ret = 'NULL';
        } elseif ($v === '') {
            $ret = "''";
        } else {
            $v = addslashes($v);
            $ret = "'$v'";
        }

        return $ret;
    }

    public static function convertSQLStrLike($v)
    {
        if (is_null($v)) {
            $ret = 'NULL';
        } elseif ($v === '') {
            $ret = '';
        } else {
            $v = addslashes($v);
            $v = str_replace('_', '\\_', $v);
            $v = str_replace('%', '\\%', $v);
            $ret = "$v";
        }

        return $ret;
    }

    public static function convertSQLNum($v)
    {
        if (is_numeric($v)) {
            if (is_float($v)) {
                $ret = floatval($v);
            } else {
                $ret = intval($v);
            }
        } else {
            $ret = 'NULL';
        }

        return $ret;
    }

    public static function convertMsgSign($dirname, $trustDirname)
    {
        $myxoopsConfig = self::getXoopsConfigs(XOOPS_CONF);
        $config_values = array(
            'sitename' => $myxoopsConfig['sitename'],
            'adminmail' => $myxoopsConfig['adminmail'],
            'siteurl' => XOOPS_URL.'/',
            'message_sign' => self::getXooNIpsConfig($dirname, 'message_sign'),
        );

        return $config_values;
    }

    /**
     * delete files not related to any sessions and any items.
     */
    public static function cleanup($dirname)
    {
        global $xoopsDB;
        $fileTable = $xoopsDB->prefix($dirname.'_item_file');
        $sessionTable = $xoopsDB->prefix('session');
        $searchTextTable = $xoopsDB->prefix($dirname.'_search_text');
        // remove file if no-related sessions and files
        $sql = "select file_id from $fileTable as tf left join $sessionTable as ts on tf.sess_id=ts.sess_id where tf.item_id is NULL and ts.sess_id is NULL";
        $result = $xoopsDB->query($sql);
        while (list($file_id) = $xoopsDB->fetchRow($result)) {
            $path = getUploadFilePath($file_id);
            if (is_file($path)) {
                unlink($path);
            }
            $xoopsDB->queryF("delete from $searchTextTable where file_id=$file_id");
            $xoopsDB->queryF("delete from $fileTable where file_id=$file_id");
        }
    }

    /**
     * get creative commons license.
     *
     * @param int  $cc_commercial_use
     * @param int  $cc_modification
     * @param bool $compact_icon
     *
     * @return string rendlerd creative commons licnese
     */
    public static function getCcLicense($cc_commercial_use, $cc_modification, $compact_icon = false)
    {
        $cc_version = '40';
        static $cc_condition_map = array(

            '00' => 'BY\-NC\-ND',
            '01' => 'BY\-NC\-SA',
            '02' => 'BY\-NC',
            '10' => 'BY\-ND',
            '11' => 'BY\-SA',
            '12' => 'BY',
        );
        static $cc_cache = array();
        $condition = sprintf('%u%u', $cc_commercial_use, $cc_modification);
        if (!isset($cc_condition_map[$condition])) {
            // unknown condtion
            return false;
        }
        $condition = $cc_condition_map[$condition];

        if (isset($cc_cache[$condition])) {
            return $cc_cache[$condition];
        }
        if ($compact_icon) {
            $reg = sprintf('/\bCC\-%s\-%s\_compact\.html\b/', $condition, $cc_version);
        } else {
            $reg = sprintf('/\bCC\-%s\-%s\.html\b/', $condition, $cc_version);
        }
        $fpath = self::ccTemplateDir(XOONIPS_TRUST_DIRNAME);
        $fileNames = scandir($fpath);
        if (!$fileNames) {
            return false;
        }
        $fname = '';
        foreach ($fileNames as $fileName) {
            if (preg_match($reg, $fileName, $matches) == 1) {
                $fname = $fileName;
                break;
            }
        }
        if ($fname == '') {
            return false;
        }
        $fpath = self::ccTemplateDir(XOONIPS_TRUST_DIRNAME).$fname;
        // file not found
        if (!file_exists($fpath)) {
            return false;
        }
        $cc_html = @file_get_contents($fpath);
        // failed to read file
        if ($cc_html === false) {
            return false;
        }
        $cc_cache[$condition] = $cc_html;

        return $cc_html;
    }

    /**
     * Finds whether a USER can export.
     * It regards $xoopsUser as USER.
     *
     * @param string $dirname
     * @param string $trustDirname
     *
     * @return bool true if export is permitted for USER
     */
    public static function isUserExportEnabled($dirname, $trustDirname)
    {
        global $xoopsUser;

        if (!$xoopsUser) {
            return false; //guest can not export
        }

        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $dirname);
        if ($userBean->isModerator($xoopsUser->getVar('uid'))) {
            return true; //moderator can always export
        }

        $export_enabled = self::getXooNIpsConfig($dirname, 'export_enabled');

        return $export_enabled == 'on';
    }

    /**
     * deny guest access and redirect.
     *
     * @param string $url redurect URL(default is modules/user.php)
     * @param string $msg message of redirect(default is _MD_XOONIPS_ITEM_FORBIDDEN)
     */
    public static function denyGuestAccess($url = null, $msg = null)
    {
        global $xoopsUser;
        global $xoopsModule;
        $msg = constant('_MD_'.strtoupper($xoopsModule->getVar('trust_dirname')).'_ITEM_FORBIDDEN');
        if (!$xoopsUser) {
            redirect_header(is_null($url) ? XOOPS_URL.'/user.php' : $url, 3, $msg);
        }
    }

    /**
     * get xoops configs for compatibility with XOOPS Cube Legacy 2.1.
     *
     * @return array xoops configs
     */
    public static function getXoopsConfigs($category)
    {
        static $cache_configs = array();
        if (isset($cache_configs[$category])) {
            return $cache_configs[$category];
        }
        $config_handler = &xoops_gethandler('config');
        $configs = $config_handler->getConfigsByCat($category);
        switch ($category) {
            case XOOPS_CONF:
                $tmp = &$config_handler->getConfigsByDirname('legacyRender');
                $configs['banners'] = $tmp['banners'];
                $tmp = &$config_handler->getConfigsByDirname(XCUBE_CORE_USER_MODULE_NAME);
                $configs['usercookie'] = $tmp['usercookie'];
                $configs['maxuname'] = $tmp['maxuname'];
                $configs['sslloginlink'] = $tmp['sslloginlink'];
                $configs['sslpost_name'] = $tmp['sslpost_name'];
                $configs['use_ssl'] = $tmp['use_ssl'];
                break;
            case XOOPS_CONF_USER:
                $configs = $config_handler->getConfigsByDirname(XCUBE_CORE_USER_MODULE_NAME);
                break;
        }
        $cache_configs[$category] = &$configs;

        return $cache_configs[$category];
    }

    public static function loadModinfoMessage($dirname)
    {
        self::loadLanguage($dirname, 'modinfo');
    }

    public static function loadLanguage($dirname, $name)
    {
        $root = &XCube_Root::getSingleton();
        $mLanguageName = $root->mLanguageManager->getLocale();
        $fileName = XOOPS_MODULE_PATH.'/'.$dirname.'/language/'.$mLanguageName.'/'.$name.'.php';
        if (!self::loadFile($fileName)) {
            $fileName = XOOPS_TRUST_PATH.'/modules/'.$dirname.'/language/'.$mLanguageName.'/'.$name.'.php';
            if (!self::loadFile($fileName)) {
                $fileName = XOOPS_TRUST_PATH.'/modules/'.$dirname.'/language/english/'.$name.'.php';
                self::loadFile($fileName);
            }
        }
    }

    private static function loadFile($filename)
    {
        if (file_exists($filename)) {
            global $xoopsDB, $xoopsTpl, $xoopsRequestUri, $xoopsModule, $xoopsModuleConfig,
                   $xoopsModuleUpdate, $xoopsUser, $xoopsUserIsAdmin, $xoopsTheme,
                   $xoopsConfig, $xoopsOption, $xoopsCachedTemplate, $xoopsLogger, $xoopsDebugger;

            require_once $filename;

            return true;
        }

        return false;
    }

    /**
     * get mail_template directory name on current language.
     *
     * @param string $dirname      module directory name
     * @param string $trustDirname module trust directory name
     *
     * @return string accessible mail_template directory name
     */
    public static function mailTemplateDir($dirname = null, $trustDirname = null)
    {
        if (is_null($dirname)) {
            $dirname = 'xoonips';
        }
        $resource = 'mail_template/';
        $basepath = empty($trustDirname) ? XOOPS_ROOT_PATH : XOOPS_TRUST_PATH;
        $dirname = empty($trustDirname) ? $dirname : $trustDirname;
        $root = &XCube_Root::getSingleton();
        $lang = $root->mLanguageManager->getLanguage();
        $langpath = $basepath.'/modules/'.$dirname.'/language/'.$lang.'/'.$resource;

        return $langpath;
    }

    public static function ccTemplateDir($trustDirname = null)
    {
        if (is_null($trustDirname)) {
            $trustDirname = XOONIPS_TRUST_DIRNAME;
        }
        $resource = 'cc/';
        $root = &XCube_Root::getSingleton();
        $lang = $root->mLanguageManager->getLanguage();
        $langpath = XOOPS_TRUST_PATH.'/modules/'.$trustDirname.'/language/'.$lang.'/'.$resource;

        return $langpath;
    }

    public static function getDirname()
    {
        global $xoopsModule;

        return strtolower($xoopsModule->getVar('dirname'));
    }

    public static function getTrustDirname()
    {
        global $xoopsModule;

        return $xoopsModule->getVar('trust_dirname');
    }
}
