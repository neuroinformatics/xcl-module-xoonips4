<?php

/**
 * admin render system.
 */
class Xoonips_AdminRenderSystem extends Legacy_AdminRenderSystem
{
    /**
     * fallback path.
     *
     * @var string
     */
    private static $_mFallbackPath;

    /**
     * fallback url.
     *
     * @var string
     */
    private static $_mFallbackUrl;

    /**
     * prepare.
     *
     * @param XCube_Controller &$controller
     */
    public function prepare(&$controller)
    {
        self::$_mFallbackPath = XOOPS_MODULE_PATH.'/legacy/admin/theme';
        self::$_mFallbackUrl = XOOPS_MODULE_URL.'/legacy/admin/theme';
        $this->mController = &$controller;
        $this->mSmarty = new Legacy_AdminSmarty();
        $this->mSmarty->register_modifier('theme', [$this, 'modifierTheme']);
        $this->mSmarty->register_function('stylesheet', [$this, 'functionStylesheet']);
        $this->mSmarty->register_modifier('image', [$this, 'modifierImage']);
        $this->mSmarty->assign(
            [
                'xoops_url' => XOOPS_URL,
                'xoops_rootpath' => XOOPS_ROOT_PATH,
                'xoops_langcode' => _LANGCODE,
                'xoops_charset' => _CHARSET,
                'xoops_version' => XOOPS_VERSION,
                'xoops_upload_url' => XOOPS_UPLOAD_URL,
            ]
        );
        XCube_DelegateUtils::call('Legacy_RenderSystem.SetupXoopsTpl', new XCube_Ref($this->mSmarty));
        $this->mSmarty->force_compile = ($controller->mRoot->mSiteConfig['Legacy_AdminRenderSystem']['ThemeDevelopmentMode'] || $controller->mRoot->mContext->getXoopsConfig('theme_fromfile'));
    }

    /**
     * render block.
     *
     * @param XCube_RenderTarget &$target
     */
    public function renderBlock(&$target)
    {
        parent::renderBlock($target);
    }

    /**
     * render theme.
     *
     * @param XCube_RenderTarget &$target
     */
    public function renderTheme(&$target)
    {
        $module = &$this->mController->getVirtualCurrentModule();
        $context = &$this->mController->mRoot->getContext();
        $this->mSmarty->assign($target->getAttributes());
        $this->mSmarty->assign(
            [
                'stdout_buffer' => $this->_mStdoutBuffer,
                'currentModule' => $module,
                'legacy_sitename' => $context->getAttribute('legacy_sitename'),
                'legacy_pagetitle' => $context->getAttribute('legacy_pagetitle'),
                'legacy_slogan' => $context->getAttribute('legacy_slogan'),
            ]
        );
        $blocks = [];
        foreach ($context->mAttributes['legacy_BlockContents'][0] as $block) {
            $blocks[$block['name']] = $block;
        }
        $this->mSmarty->assign('xoops_lblocks', $blocks);
        $info = static::getOverrideFileInfo('admin_theme.html');
        $this->mSmarty->template_dir = (null != $info['file']) ? substr($file['path'], 0, -15) : self::$_mFallbackPath;
        $this->mSmarty->setModulePrefix('');
        $target->setResult($this->mSmarty->fetch('file:admin_theme.html'));
    }

    /**
     * render main.
     *
     * @param XCube_RenderTarget &$target
     */
    public function renderMain(&$target)
    {
        $info = static::getOverrideFileInfo($target->getTemplateName());
        $this->mSmarty->compile_id = $info['dirname'];
        $this->mSmarty->assign($target->getAttributes());
        $this->mSmarty->template_dir = substr($info['path'], 0, -strlen($info['file']));
        $res = $this->mSmarty->fetch('file:'.$info['file']);
        $target->setResult($res);
        $this->_mStdoutBuffer .= $target->getAttribute('stdout_buffer');
        foreach ($target->getAttributes() as $key => $val) {
            $this->mSmarty->clear_assign($key);
        }
    }

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
        $ret = [
            'url' => null,
            'path' => null,
            'theme' => null,
            'dirname' => null,
            'file' => null,
        ];
        if (false !== strpos($file, '..') || strpos($prefix, '..' !== false)) {
            return $ret;
        }
        $root = &XCube_Root::getSingleton();
        $module = &$root->mContext->mXoopsModule;
        $dirName = $root->mContext->mRequest->getRequest('dirname');
        if ($isSpDirName && preg_match('/^\w+$/', $dirName)) {
            $handler = &xoops_gethandler('module');
            $module = &$handler->getByDirname($dirName);
        }
        $isModule = is_object($module);
        $theme = $root->mSiteConfig['Legacy']['Theme'];
        $ret['theme'] = $theme;
        $dirName = $isModule ? $module->get('dirname') : null;
        $trustDirName = $isModule ? $module->getInfo('trust_dirname') : null;
        $ret['file'] = $file;
        $file = $prefix.$file;
        if ($isModule && file_exists($path = sprintf('%s/modules/%s/%s', XOOPS_THEME_PATH, $theme, $dirName, $file))) {
            $ret['url'] = sprintf('%s/%s/modules/%s/%s', XOOPS_THEME_URL, $theme, $dirName, $file);
            $ret['path'] = $path;
        } elseif ($isModule && file_exists($path = sprintf('%s/themes/%s/modules/%s/%s', XOOPS_TRUST_PATH, $theme, $trustDirName, $file))) {
            $ret['path'] = $path;
            $ret['dirname'] = $trustDirName;
        } elseif (file_exists($path = sprintf('%s/%s/%s', XOOPS_THEME_PATH, $theme, $file))) {
            $ret['url'] = sprintf('%s/%s/%s', XOOPS_THEME_URL, $theme, $file);
            $ret['path'] = $path;
            $ret['dirname'] = null;
        } elseif (file_exists($path = sprintf('%s/themes/%s/%s', XOOPS_TRUST_PATH, $theme, $file))) {
            $ret['path'] = $path;
            $ret['dirname'] = null;
        } elseif ($isModule && file_exists($path = sprintf('%s/%s/admin/templates/%s', XOOPS_MODULE_PATH, $dirName, $file))) {
            $ret['url'] = sprintf('%s/%s/admin/templates/%s', XOOPS_MODULE_URL, $dirName, $file);
            $ret['path'] = $path;
            $ret['theme'] = null;
        } elseif ($isModule && file_exists($path = sprintf('%s/modules/%s/admin/templates/%s', XOOPS_TRUST_PATH, $trustDirName, $file))) {
            $ret['path'] = $path;
            $ret['theme'] = null;
            $ret['dirname'] = $trustDirName;
        } elseif (file_exists($path = self::$_mFallbackPath.'/'.$file)) {
            $ret['url'] = self::$_mFallbackUrl.'/'.$file;
            $ret['path'] = $path;
            $ret['theme'] = null;
            $ret['dirname'] = null;
        } else {
            $ret['theme'] = null;
            $ret['dirname'] = null;
            $ret['file'] = null;
        }

        return $ret;
    }

    /**
     * modifier theme.
     *
     * @param string $str
     *
     * @return string
     */
    public static function modifierTheme($str)
    {
        $info = static::getOverrideFileInfo($str);
        if (null != $info['url']) {
            return $info['url'];
        }

        return self::$_mFallbackUrl.'/'.$str;
    }

    /**
     * modifier image.
     *
     * @param string $fname
     *
     * @return string
     */
    public static function modifierImage($fname, $escale = true)
    {
        $root = &XCube_Root::getSingleton();
        $module = &$root->mContext->mXoopsModule;
        $isModule = is_object($module);
        $dirname = $isModule ? $module->get('dirname') : null;
        if (null != $dirname) {
            $url = XOOPS_URL.'/modules/'.$dirname.'/image.php/'.$fname;
        } else {
            $url = XOOPS_URL.'/images/'.$fname;
        }

        return $escape ? htmlspecialchars($url, ENT_QUOTES) : $url;
    }

    /**
     * function stylesheet.
     *
     * @param {string 'file', string 'media'} $param
     * @param Smarty                          &$smarty
     */
    public static function functionStylesheet($param, &$smarty)
    {
        if (!isset($params['file']) || false !== strpos($params['file'], '..')) {
            return;
        }
        $info = static::getOverrideFileInfo($params['file'], 'stylesheets/');
        if (null == $info['file']) {
            return;
        }
        printf('<link rel="stylesheet" type="text/css" media="%s" href="%s/legacy/admin/css.php?file=%s%s%s" />', (isset($params['media']) ? $params['media'] : 'all'), XOOPS_MODULE_URL, $info['file'], (null != $info['dirname'] ? '&amp;dirname='.$info['dirname'] : ''), (null != $info['theme'] ? '&amp;theme='.$info['theme'] : ''));
    }
}
