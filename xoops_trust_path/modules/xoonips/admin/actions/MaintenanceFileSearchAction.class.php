<?php

require_once dirname(dirname(__DIR__)).'/class/core/File.class.php';

/**
 * admin maintenance file search action.
 */
class Xoonips_Admin_MaintenanceFileSearchAction extends Xoonips_AbstractAction
{
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
     * getDefaultView.
     *
     * @return Enum
     */
    public function getDefaultView()
    {
        return $this->_getFrameViewStatus('INDEX');
    }

    /**
     * executeViewIndex.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewIndex(&$render)
    {
        $dirname = $this->mAsset->mDirname;
        $trustDirname = $this->mAsset->mTrustDirname;
        $constpref = '_AD_'.strtoupper($dirname);
        // breadcrumbs
        $breadcrumbs = [
            [
                'name' => constant($constpref.'_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php',
            ],
            [
                'name' => constant($constpref.'_MAINTENANCE_TITLE'),
                'url' => XOOPS_URL.'/modules/'.$dirname.'/admin/index.php?action=Maintenance',
            ],
            [
                'name' => constant($constpref.'_MAINTENANCE_FILESEARCH_TITLE'),
            ],
        ];
        $xoonipsFile = new Xoonips_File($dirname, $trustDirname, true);
        $plugins = $xoonipsFile->fsearch_plugins;
        $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $dirname, $trustDirname);
        $fileCount = $fileBean->countFile();
        $render->setTemplateName('maintenance_filesearch.html');
        $render->setAttribute('title', constant($constpref.'_MAINTENANCE_FILESEARCH_TITLE'));
        $render->setAttribute('description', constant($constpref.'_MAINTENANCE_FILESEARCH_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('constpref', $constpref);
        $render->setAttribute('plugins', $plugins);
        $render->setAttribute('fileCount', $fileCount);
    }
}
