<?php

/**
 * admin policy user form.
 */
class Xoonips_Admin_PolicyUserForm extends Xoonips_AbstractActionForm
{
    /**
     * is admin mode.
     *
     * @return bool
     */
    protected function _isAdminMode()
    {
        return true;
    }

    /**
     * is multiple mode.
     *
     * @return bool
     */
    protected function _isMultipleMode()
    {
        return true;
    }

    /**
     * get form params.
     *
     * @return array
     */
    protected function _getFormParams()
    {
        $constpref = '_AD_'.strtoupper($this->mDirname);

        return [
            'regist' => [
                'activate_user' => [
                    'type' => self::TYPE_INT,
                    'label' => constant($constpref.'_POLICY_USER_REGIST_ACTIVATE_TITLE'),
                    'depends' => [
                        'required' => true,
                        'intRange' => true,
                        'min' => 0,
                        'max' => 2,
                    ],
                ],
                'certify_user' => [
                    'type' => self::TYPE_STRING,
                    'label' => constant($constpref.'_POLICY_USER_REGIST_CERTIFY_TITLE'),
                    'depends' => [
                        'required' => true,
                        'mask' => '/^(?:on|auto)$/',
                    ],
                ],
                'user_certify_date' => [
                    'type' => self::TYPE_INT,
                    'label' => constant($constpref.'_POLICY_USER_REGIST_DATELIMIT_TITLE'),
                    'depends' => [
                        'required' => true,
                        'min' => 0,
                    ],
                ],
            ],
            'initval' => [
                'private_item_number_limit' => [
                    'type' => self::TYPE_INT,
                    'label' => constant($constpref.'_POLICY_USER_INITVAL_MAXITEM_TITLE'),
                    'depends' => [
                        'required' => true,
                        'min' => 0,
                    ],
                ],
                'private_index_number_limit' => [
                    'type' => self::TYPE_INT,
                    'label' => constant($constpref.'_POLICY_USER_INITVAL_MAXINDEX_TITLE'),
                    'depends' => [
                        'required' => true,
                        'min' => 0,
                    ],
                ],
                'private_item_storage_limit' => [
                    'type' => self::TYPE_FLOAT,
                    'label' => constant($constpref.'_POLICY_USER_INITVAL_MAXDISK_TITLE'),
                    'depends' => [
                        'required' => true,
                        'min' => 0,
                    ],
                ],
            ],
        ];
    }
}
