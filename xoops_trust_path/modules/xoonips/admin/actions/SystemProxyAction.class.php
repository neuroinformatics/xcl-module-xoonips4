<?php

require_once dirname(__DIR__).'/class/AbstractConfigAction.class.php';

/**
 * admin system proxy action.
 */
class Xoonips_Admin_SystemProxyAction extends Xoonips_Admin_AbstractConfigAction
{
    /**
     * get page url.
     *
     * @return string
     */
    protected function _getUrl()
    {
        return XOOPS_URL.'/modules/'.$this->mAsset->mDirname.'/admin/index.php?action=SystemProxy';
    }

    /**
     * get config keys.
     *
     * @return array
     */
    protected function getConfigKeys()
    {
        return ['proxy_host', 'proxy_port', 'proxy_user', 'proxy_pass'];
    }

    /**
     * get action form.
     *
     * @return {Trustdirname}_AbstractActionForm &
     */
    protected function &_getActionForm()
    {
        return $this->mAsset->getObject('form', 'system', true, 'proxy');
    }

    /**
     * execute view input.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewInput(&$render)
    {
        $dirname = $this->mAsset->mDirname;
        $constpref = '_AD_'.strtoupper($dirname);
        // breadcrumbs
        $breadcrumbs = [
            [
                'name' => constant($constpref.'_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php',
            ],
            [
                'name' => constant($constpref.'_SYSTEM_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=System',
            ],
            [
                'name' => constant($constpref.'_SYSTEM_PROXY_TITLE'),
            ],
        ];
        $render->setTemplateName('system_proxy.html');
        $render->setAttribute('title', constant($constpref.'_SYSTEM_PROXY_TITLE'));
        $render->setAttribute('description', constant($constpref.'_SYSTEM_PROXY_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('constpref', $constpref);
    }
}
