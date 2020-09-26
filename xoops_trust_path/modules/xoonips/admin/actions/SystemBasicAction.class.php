<?php

require_once dirname(__DIR__).'/class/AbstractConfigAction.class.php';

/**
 * admin system basic action.
 */
class Xoonips_Admin_SystemBasicAction extends Xoonips_Admin_AbstractConfigAction
{
    /**
     * get page url.
     *
     * @return string
     */
    protected function _getUrl()
    {
        return XOOPS_URL.'/modules/'.$this->mAsset->mDirname.'/admin/index.php?action=SystemBasic';
    }

    /**
     * get config keys.
     *
     * @return array
     */
    protected function getConfigKeys()
    {
        return ['moderator_gid', 'upload_dir', 'url_compatible'];
    }

    /**
     * get action form.
     *
     * @return {Trustdirname}_AbstractActionForm &
     */
    protected function &_getActionForm()
    {
        return $this->mAsset->getObject('form', 'system', true, 'basic');
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
                'name' => constant($constpref.'_SYSTEM_BASIC_TITLE'),
            ],
        ];
        $render->setTemplateName('system_basic.html');
        $render->setAttribute('title', constant($constpref.'_SYSTEM_BASIC_TITLE'));
        $render->setAttribute('description', constant($constpref.'_SYSTEM_BASIC_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('constpref', $constpref);
        $render->setAttribute('groups', $this->_getGroups());
    }

    /**
     * get groups.
     *
     * @return array (group_id as key)
     */
    private function _getGroups()
    {
        $memberHandler = &xoops_gethandler('member');
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('groupid', [XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS], 'NOT IN'));
        $groups = &$memberHandler->getGroupList($criteria);

        return $groups;
    }
}
