<?php

/**
 * admin system oaipmh form.
 */
class Xoonips_Admin_SystemOaipmhForm extends Xoonips_AbstractActionForm
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
     * get form params.
     *
     * @return array
     */
    protected function _getFormParams()
    {
        $constpref = '_AD_'.strtoupper($this->mDirname);

        return array(
            'repository_name' => array(
                'type' => self::TYPE_STRING,
            ),
            'repository_nijc_code' => array(
                'type' => self::TYPE_STRING,
            ),
            'repository_deletion_track' => array(
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_SYSTEM_OAIPMH_REPOSITORY_DELETION_TRACK_TITLE'),
                'depends' => array(
                    'required' => true,
                    'min' => 1,
                ),
            ),
        );
    }
}
