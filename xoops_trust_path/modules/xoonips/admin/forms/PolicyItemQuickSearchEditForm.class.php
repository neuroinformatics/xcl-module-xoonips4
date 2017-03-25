<?php

/**
 * admin policy item quick search edit form.
 */
class Xoonips_Admin_PolicyItemQuickSearchEditForm extends Xoonips_AbstractActionForm
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
                ),
            ),
            'condition_name' => array(
                'type' => self::TYPE_STRING,
                'label' => constant($constpref.'_POLICY_ITEM_QUICKSEARCH_LABEL'),
                'depends' => array(
                    'required' => true,
                ),
            ),
            'itemFieldIds' => array(
                'type' => self::TYPE_INTARRAY,
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
        $ret = null;
        switch ($key) {
        case 'condition_id':
            $ret = $obj['obj']->get($key);
            break;
        case 'condition_name':
            $ret = $obj['obj']->get('title');
            break;
        case 'itemFieldIds':
            $ret = $obj[$key];
            break;
        }

        return $ret;
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
        switch ($key) {
        case 'condition_id':
            $obj['obj']->set($key, $value);
            break;
        case 'condition_name':
            $obj['obj']->set('title', $value);
            break;
        case 'itemFieldIds':
            $obj[$key] = $value;
            break;
        }
    }
}
