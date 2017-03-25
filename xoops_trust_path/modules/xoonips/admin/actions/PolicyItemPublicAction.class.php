<?php

require_once dirname(dirname(__FILE__)).'/class/AbstractConfigAction.class.php';

/**
 * admin policy item public action.
 */
class Xoonips_Admin_PolicyItemPublicAction extends Xoonips_Admin_AbstractConfigAction
{
    /**
     * get page url.
     *
     * @return string
     */
    protected function _getUrl()
    {
        return XOOPS_URL.'/modules/'.$this->mAsset->mDirname.'/admin/index.php?action=PolicyItemPublic';
    }

    /**
     * get config keys.
     *
     * @return array
     */
    protected function getConfigKeys()
    {
        return array('certify_item', 'download_file_compression');
    }

    /**
     * get action form.
     *
     * @return {Trustdirname}_AbstractActionForm &
     */
    protected function &_getActionForm()
    {
        return $this->mAsset->getObject('form', 'policy', true, 'itemPublic');
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
        $breadcrumbs = array(
            array(
                'name' => constant($constpref.'_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php',
            ),
            array(
                'name' => constant($constpref.'_POLICY_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=Policy',
            ),
            array(
                'name' => constant($constpref.'_POLICY_ITEM_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=PolicyItem',
            ),
            array(
                'name' => constant($constpref.'_POLICY_ITEM_PUBLIC_TITLE'),
            ),
        );
        $render->setTemplateName('policy_item_public.html');
        $render->setAttribute('title', constant($constpref.'_POLICY_ITEM_PUBLIC_TITLE'));
        $render->setAttribute('description', constant($constpref.'_POLICY_ITEM_PUBLIC_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('constpref', $constpref);
    }
}
