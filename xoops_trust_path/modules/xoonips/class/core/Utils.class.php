<?php

use Xoonips\Core\Functions;

class Xoonips_Utils
{
    public static function convertSQLStr($v)
    {
        global $xoopsDB;
        if (is_null($v)) {
            $ret = 'NULL';
        } else {
            $ret = $xoopsDB->quoteString($v);
        }

        return $ret;
    }

    public static function convertSQLStrLike($v)
    {
        if (is_null($v)) {
            $ret = 'NULL';
        } elseif ('' === $v) {
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
        $sql = 'SELECT `file_id` FROM `'.$fileTable.'` AS `tf` LEFT JOIN `'.$sessionTable.'` AS `ts` ON `tf`.`sess_id`=`ts`.`sess_id` WHERE `tf`.`item_id` IS NULL AND `ts`.`sess_id` IS NULL';
        $result = $xoopsDB->query($sql);
        while (list($file_id) = $xoopsDB->fetchRow($result)) {
            $path = getUploadFilePath($file_id);
            if (is_file($path)) {
                unlink($path);
            }
            $xoopsDB->queryF('DELETE FROM `'.$searchTextTable.'` WHERE `file_id`='.intval($file_id));
            $xoopsDB->queryF('DELETE FROM `'.$fileTable.'` WHERE `file_id`='.intval($file_id));
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
        $trustDirname = Functions::getTrustDirname();
        $cc_version = '40';
        static $cc_condition_map = [
            '00' => 'BY\-NC\-ND',
            '01' => 'BY\-NC\-SA',
            '02' => 'BY\-NC',
            '10' => 'BY\-ND',
            '11' => 'BY\-SA',
            '12' => 'BY',
        ];
        static $cc_cache = [];
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
        $fpath = self::ccTemplateDir($trustDirname);
        $fileNames = scandir($fpath);
        if (!$fileNames) {
            return false;
        }
        $fname = '';
        foreach ($fileNames as $fileName) {
            if (1 == preg_match($reg, $fileName, $matches)) {
                $fname = $fileName;
                break;
            }
        }
        if ('' == $fname) {
            return false;
        }
        $fpath = self::ccTemplateDir($trustDirname).$fname;
        // file not found
        if (!file_exists($fpath)) {
            return false;
        }
        $cc_html = @file_get_contents($fpath);
        // failed to read file
        if (false === $cc_html) {
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

        $export_enabled = Functions::getXoonipsConfig($dirname, 'export_enabled');

        return 'on' == $export_enabled;
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

    public static function ccTemplateDir($trustDirname)
    {
        $resource = 'cc/';
        $root = &XCube_Root::getSingleton();
        $lang = $root->mLanguageManager->getLanguage();
        $langpath = XOOPS_TRUST_PATH.'/modules/'.$trustDirname.'/language/'.$lang.'/'.$resource;

        return $langpath;
    }
}
