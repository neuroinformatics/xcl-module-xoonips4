<?php

use Xoonips\Core\Functions;
use Xoonips\Core\XoopsUtils;

require_once dirname(dirname(__DIR__)).'/class/AbstractEditAction.class.php';

/**
 * admin policy user action.
 */
class Xoonips_Admin_PolicyUserAction extends Xoonips_AbstractEditAction
{
    /**
     * config keys.
     *
     * @var array
     */
    private $_mConfigKeys = [
        'regist' => [
            'certify_user',
            'user_certify_date',
        ],
        'initval' => [
            'private_item_number_limit',
            'private_index_number_limit',
            'private_item_storage_limit',
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
        return XOOPS_URL.'/modules/'.$this->mAsset->mDirname.'/admin/index.php?action=PolicyUser';
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
        return $this->mAsset->getObject('form', 'policy', true, 'user');
    }

    /**
     * setup object.
     */
    protected function _setupObject()
    {
        $this->mObject = $this->getUserPolicies();
    }

    /**
     * save object.
     *
     * @return bool
     */
    protected function _saveObject()
    {
        return $this->setUserPolicies($this->mObject);
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
                'name' => constant($constpref.'_POLICY_USER_TITLE'),
            ],
        ];
        $render->setTemplateName('policy_user.html');
        $render->setAttribute('title', constant($constpref.'_POLICY_USER_TITLE'));
        $render->setAttribute('description', constant($constpref.'_POLICY_USER_DESC'));
        $render->setAttribute('xoops_breadcrumbs', $breadcrumbs);
        $render->setAttribute('actionForm', $this->mActionForm);
        $render->setAttribute('constpref', $constpref);
    }

    /**
     * get user policies.
     *
     * @return array
     */
    protected function getUserPolicies()
    {
        $ret = [];
        $ret['mode'] = '';
        $ret['activate_user'] = XoopsUtils::getModuleConfig('user', 'activation_type');
        foreach ($this->_mConfigKeys as $mode => $keys) {
            foreach ($keys as $key) {
                $value = Functions::getXoonipsConfig($this->mAsset->mDirname, $key);
                if ('private_item_storage_limit' == $key) {
                    $value /= (1024 * 1024);
                }
                $ret[$key] = $value;
            }
        }

        return $ret;
    }

    /**
     * set user policies.
     *
     * @param array $configs
     *
     * @return bool
     */
    protected function setUserPolicies($policies)
    {
        foreach ($this->_mConfigKeys as $mode => $keys) {
            if ($mode == $policies['mode']) {
                if ('regist' == $mode) {
                    if (!XoopsUtils::setModuleConfig('user', 'activation_type', $policies['activate_user'])) {
                        return false;
                    }
                }
                foreach ($keys as $key) {
                    $value = $policies[$key];
                    if ('private_item_storage_limit' == $key) {
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
