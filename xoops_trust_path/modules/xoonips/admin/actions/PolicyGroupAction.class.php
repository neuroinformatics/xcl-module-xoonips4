<?php

use Xoonips\Core\Functions;

require_once dirname(dirname(__DIR__)).'/class/AbstractEditAction.class.php';

/**
 * admin policy group action.
 */
class Xoonips_Admin_PolicyGroupAction extends Xoonips_AbstractEditAction
{
    /**
     * config keys.
     *
     * @var array
     */
    private $_mConfigKeys = [
        'general' => [
            'group_making',
            'group_making_certify',
            'group_publish_certify',
        ],
        'initval' => [
            'group_item_number_limit',
            'group_index_number_limit',
            'group_item_storage_limit',
        ],
    ];

    /**
     * is admin.
     *
     * @return bool
     */
    protected function _isAdmin()
    {
        return true;
    }

    /**
     * get page url.
     *
     * @return string
     */
    protected function _getUrl()
    {
        return XOOPS_URL.'/modules/'.$this->mAsset->mDirname.'/admin/index.php?action=PolicyGroup';
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
        return $this->mAsset->getObject('form', 'policy', true, 'group');
    }

    /**
     * setup object.
     */
    protected function _setupObject()
    {
        $this->mObject = $this->getGroupPolicies();
    }

    /**
     * save object.
     *
     * @return bool
     */
    protected function _saveObject()
    {
        return $this->setGroupPolicies($this->mObject);
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
                'name' => constant($constpref.'_POLICY_GROUP_TITLE'),
            ],
        ];
        $render->setTemplateName('policy_group.html');
        $render->setAttribute('title', constant($constpref.'_POLICY_GROUP_TITLE'));
        $render->setAttribute('description', constant($constpref.'_POLICY_GROUP_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('constpref', $constpref);
    }

    /**
     * get group policies.
     *
     * @return array
     */
    protected function getGroupPolicies()
    {
        $ret = [];
        $ret['mode'] = '';
        foreach ($this->_mConfigKeys as $mode => $keys) {
            foreach ($keys as $key) {
                $value = Functions::getXoonipsConfig($this->mAsset->mDirname, $key);
                if ('group_item_storage_limit' == $key) {
                    $value /= (1024 * 1024);
                }
                $ret[$key] = $value;
            }
        }

        return $ret;
    }

    /**
     * set group policies.
     *
     * @param array $configs
     *
     * @return bool
     */
    protected function setGroupPolicies($policies)
    {
        foreach ($this->_mConfigKeys as $mode => $keys) {
            if ($mode == $policies['mode']) {
                foreach ($keys as $key) {
                    $value = $policies[$key];
                    if ('group_item_storage_limit' == $key) {
                        $value *= (1024 * 1024);
                    }
                    if (!Functions::setXoonipsConfig($this->mAsset->mDirname, $key, $value)) {
                        return false;
                    }
                }

                return true;
            }
        }

        return false;
    }
}
