<?php

namespace Xoonips\Core;

/**
 * functions.
 */
class Functions
{
    /**
     * get trust dirname.
     *
     * @return string
     */
    public static function getTrustDirname()
    {
        static $trustDirname = false;
        if (false === $trustDirname) {
            $trustDirname = basename(dirname(dirname(dirname(__DIR__))));
        }

        return $trustDirname;
    }

    /**
     * get xoonips module handler.
     *
     * @param string $name
     * @param string $dirname
     *
     * @return Handler\AbstractHandler
     */
    public static function getXoonipsHandler($name, $dirname)
    {
        return XoopsUtils::getModuleHandler($name, $dirname, self::getTrustDirname());
    }

    /**
     * get xoonips config.
     *
     * @param string $dirname
     * @param string $name
     *
     * @return string
     */
    public static function getXoonipsConfig($dirname, $name)
    {
        $cHandler = self::getXoonipsHandler('ConfigObject', $dirname);

        return $cHandler->getConfig($name);
    }

    /**
     * set xoonips config.
     *
     * @param string $dirname
     * @param string $name
     * @param string $value
     *
     * @return bool
     */
    public static function setXoonipsConfig($dirname, $name, $value)
    {
        $cHandler = self::getXoonipsHandler('ConfigObject', $dirname);

        return $cHandler->setConfig($name, $value);
    }

    /**
     * get item list url.
     *
     * @param string $dirname
     *
     * @return string
     **/
    public static function getItemListUrl($dirname)
    {
        $url = null;
        if ('on' == self::getXoonipsConfig($dirname, 'url_compatible')) {
            $url = XOONIPS_ITEM_LIST_COMPATIBLE;
        } elseif ('off' == self::getXoonipsConfig($dirname, 'url_compatible')) {
            $url = XOONIPS_ITEM_LIST;
        }

        return $url;
    }
}
