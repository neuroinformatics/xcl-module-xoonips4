<?php

require_once dirname(__DIR__).'/AdminRenderSystem.class.php';

/**
 * user admin render system.
 */
class Xoonips_UserAdminRenderSystem extends Xoonips_AdminRenderSystem
{
    /**
     * get override file info.
     *
     * @param string $file
     * @param string $prefix
     * @param bool   $isSpDirName
     *
     * @return {string 'theme', string 'file', string 'dirname'}
     */
    public static function getOverrideFileInfo($file, $prefix = null, $isSpDirName = false)
    {
        $root = &XCube_Root::getSingleton();
        $module = &$root->mContext->mXoopsModule;
        if (!is_object($module) || 'user' != $module->get('dirname')) {
            return parent::getOverrideFileInfo($file, $prefix, $isSpDirName);
        }
        $ret = [
            'url' => null,
            'path' => null,
            'theme' => null,
            'dirname' => null,
            'file' => null,
        ];
        if (false !== strpos($file, '..') || false !== strpos($prefix, '..')) {
            return $ret;
        }
        $trustDirname = basename(dirname(dirname(dirname(__DIR__))));
        if (file_exists($path = sprintf('%s/modules/%s/admin/templates/user/%s', XOOPS_TRUST_PATH, $trustDirname, $prefix.$file))) {
            $ret['file'] = $file;
            $ret['path'] = $path;
            $ret['dirname'] = 'user';
        } elseif (file_exists($path = sprintf('%s/user/admin/templates/%s', XOOPS_MODULE_PATH, $prefix.$file))) {
            $ret['file'] = $file;
            $ret['path'] = $path;
            $ret['dirname'] = 'user';
        }

        return $ret;
    }
}
