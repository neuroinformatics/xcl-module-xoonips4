<?php

/**
 * admin policy item quick search delete form.
 */
class Xoonips_Admin_PolicyItemQuickSearchDeleteForm extends Xoonips_AbstractActionForm
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
            'condition_id' => array(
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_POLICY_ITEM_QUICKSEARCH_CRITERIA_ID'),
                'depends' => array(
                    'required' => true,
                    'min' => 2,
                ),
            ),
        );
    }

    /**
     * get object value.
     *
     * @param mixed  &$obj
     * @param string $key
     *
     * @return mixed
     */
    protected function _getObjectValue(&$obj, $key)
    {
        return $obj->get($key);
    }

    /**
     * set object value.
     *
     * @param mixed  &$obj
     * @param string $key
     * @param mixed  $value
     */
    protected function _setObjectValue(&$obj, $key, $value)
    {
        $obj->set($key, $value);
    }
}
