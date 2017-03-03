<?php

namespace Xoonips\Core;

/**
 * language manager class.
 */
class LanguageManager
{
    const DEFAULT_LANG = 'english';

    /**
     * dirname.
     *
     * @var string
     */
    private $mDirname;

    /**
     * page type.
     *
     * @var string
     */
    private $mPageType;

    /**
     * prefix.
     *
     * @var string
     */
    private $mPrefix;

    /**
     * constructor.
     *
     * @param string $dirname
     * @param string $pageType
     */
    public function __construct($dirname, $pageType)
    {
        static $types = array(
            'admin' => 'AD',
            'blocks' => 'BL',
            'main' => 'MD',
            'modinfo' => 'MI',
            'install' => 'IN',
        );
        $this->mDirname = $dirname;
        $this->mPageType = $pageType;
        $this->mPrefix = '_'.$types[$pageType].'_'.strtoupper($dirname).'_';
    }

    /**
     * load language resource.
     *
     * @return bool
     */
    public function load()
    {
        $lang = XoopsUtils::getXoopsConfig('language');
        $modulePath = XOOPS_ROOT_PATH.'/modules/'.$this->mDirname;
        $langPath = $modulePath.'/language';
        $langFile = false;
        if (is_dir($langPath)) {
            $langFile = $langPath.'/'.$lang.'/'.$this->mPageType.'.php';
            if (!file_exists($langFile)) {
                $langFile = $langPath.'/'.self::DEFAULT_LANG.'/'.$this->mPageType.'.php';
                if (!file_exists($langFile)) {
                    $langFile = false;
                }
            }
        } else {
            $d3file = $modulePath.'/mytrustdirname.php';
            if (file_exists($d3file)) {
                include $d3file;
                if (isset($mytrustdirname)) {
                    $langPath = XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/language';
                    $langFile = $langPath.'/'.$lang.'/'.$this->mPageType.'.php';
                    if (!file_exists($langFile)) {
                        $langFile = $langPath.'/'.self::DEFAULT_LANG.'/'.$this->mPageType.'.php';
                        if (!file_exists($langFile)) {
                            $langFile = false;
                        }
                    }
                }
            }
        }
        if ($langFile === false) {
            return false;
        }
        $mydirname = $this->mDirname;
        require_once $langFile;

        return true;
    }

    /**
     * get constant message.
     *
     * @param string $label
     *
     * @return string
     */
    public function get($label)
    {
        return constant($this->mPrefix.$label);
    }

    /**
     * define constant message.
     *
     * @param string $label
     * @param string $message
     */
    public function set($label, $message)
    {
        return define($this->mPrefix.$label, $message);
    }

    /**
     * get constant name.
     *
     * @param string $label
     *
     * @return string
     */
    public function getName($label)
    {
        return $this->mPrefix.$label;
    }

    /**
     * check whether constant message is defined.
     *
     * @param string $label
     * @param string $message
     */
    public function exists($label)
    {
        return defined($this->mPrefix.$label);
    }
}
