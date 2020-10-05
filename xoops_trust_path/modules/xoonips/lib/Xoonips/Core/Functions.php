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
    public static function getTrustDirname(): string
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
     * @return ?object
     */
    public static function getXoonipsHandler(string $name, string $dirname): ?object
    {
        return XoopsUtils::getModuleHandler($name, $dirname, self::getTrustDirname());
    }

    /**
     * get xoonips config.
     *
     * @param string $dirname
     * @param string $name
     *
     * @return ?string
     */
    public static function getXoonipsConfig(string $dirname, string $name): ?string
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
    public static function setXoonipsConfig(string $dirname, string $name, string $value): bool
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
    public static function getItemListUrl(string $dirname): string
    {
        $compat3 = self::getXoonipsConfig($dirname, 'url_compatible');
        if ('on' == $compat3) {
            return XOONIPS_ITEM_LIST_COMPATIBLE;
        }

        return XOONIPS_ITEM_LIST;
    }
}
