<?php

/**
 * admin policy item field delete form.
 */
class Xoonips_Admin_PolicyItemFieldDeleteForm extends Xoonips_AbstractActionForm
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
            'field_id' => array(
                'type' => self::TYPE_INT,
                'label' => constant($constpref.'_LANG_ITEM_FIELD_ID'),
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
        if ($key == 'field_id') {
            $key = 'item_field_detail_id';
        }

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
        if ($key == 'field_id') {
            $key = 'item_field_detail_id';
        }
        $obj->set($key, $value);
    }
}
