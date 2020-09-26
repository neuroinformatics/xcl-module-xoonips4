<?php

require_once dirname(__DIR__).'/class/AbstractConfigAction.class.php';

/**
 * admin policy index action.
 */
class Xoonips_Admin_PolicyIndexAction extends Xoonips_Admin_AbstractConfigAction
{
    /**
     * get page url.
     *
     * @return string
     */
    protected function _getUrl()
    {
        return XOOPS_URL.'/modules/'.$this->mAsset->mDirname.'/admin/index.php?action=PolicyIndex';
    }

    /**
     * get config keys.
     *
     * @return array
     */
    protected function getConfigKeys()
    {
        return ['index_upload_dir'];
    }

    /**
     * get style sheet.
     *
     * @return string
     */
    protected function _getStylesheet()
    {
        return '/modules/'.$this->mAsset->mDirname.'/admin/index.php/css/admin_style.css';
    }

    /**
     * get action form.
     *
     * @return {Trustdirname}_AbstractActionForm &
     */
    protected function &_getActionForm()
    {
        return $this->mAsset->getObject('form', 'policy', true, 'index');
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
                'name' => constant($constpref.'_POLICY_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=Policy',
            ],
            [
                'name' => constant($constpref.'_POLICY_INDEX_TITLE'),
            ],
        ];
        $render->setTemplateName('policy_index.html');
        $render->setAttribute('title', constant($constpref.'_POLICY_INDEX_TITLE'));
        $render->setAttribute('description', constant($constpref.'_POLICY_INDEX_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('constpref', $constpref);
    }
}
