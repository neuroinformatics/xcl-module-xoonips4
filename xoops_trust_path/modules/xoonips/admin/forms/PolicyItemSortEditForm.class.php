<?php

use Xoonips\Core\Functions;

/**
 * admin policy item sort edit form.
 */
class Xoonips_Admin_PolicyItemSortEditForm extends Xoonips_AbstractActionForm
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
            'sort_id' => array(
                'type' => self::TYPE_INT,
            ),
            'title' => array(
                'type' => self::TYPE_STRING,
                'label' => constant($constpref.'_POLICY_ITEM_SORT_LABEL'),
                'depends' => array(
                    'required' => true,
                ),
            ),
            'fields' => array(
                'type' => self::TYPE_STRINGARRAY,
                'label' => constant($constpref.'_POLICY_ITEM_SORT_FIELD'),
                'depends' => array(
                    'mask' => '/^\d+:\d+:\d+$/',
                ),
            ),
        );
    }

    /**
     * validate sort fields.
     */
    public function validateFields()
    {
        $constpref = '_AD_'.strtoupper($this->mDirname);
        $fields = $this->get('fields');
        $handler = &Functions::getXoonipsHandler('ItemSort', $this->mDirname);
        $allFields = $handler->getSelectableSortFields();
        foreach ($fields as $field) {
            list($tId, $gId, $fId) = $handler->decodeSortField($field);
            if (isset($found[$tId])) {
                // multiple field found in item id
                $this->addErrorMessage(XCube_Utils::formatString(constant($constpref.'_ERROR_INPUTVALUE'), constant($constpref.'_POLICY_ITEM_SORT_FIELD')));
                break;
            }
            $found[$tId] = false;
            foreach ($allFields as $itInfo) {
                foreach ($itInfo['fields'] as $itFields) {
                    if ($itFields['key'] == $field) {
                        $found[$tId] = true;
                        break 2;
                    }
                }
            }
            if ($found[$tId] === false) {
                // not selectable field found
                $this->addErrorMessage(XCube_Utils::formatString(constant($constpref.'_ERROR_INPUTVALUE'), constant($constpref.'_POLICY_ITEM_SORT_FIELD')));
                break;
            }
        }
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
        if (in_array($key, array('sort_id', 'title'))) {
            return $obj['obj']->get($key);
        }

        return $obj[$key];
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
        if (in_array($key, array('sort_id', 'title'))) {
            $obj['obj']->set($key, $value);
        } else {
            $obj[$key] = $value;
        }
    }
}
