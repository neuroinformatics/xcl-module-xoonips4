<?php

require_once __DIR__.'/ViewType.class.php';

class Xoonips_ViewTypeId extends Xoonips_ViewTypeText
{
    public function inputCheck(&$errors, $field, $value, $fieldName)
    {
        if (strlen($value) > 0) {
            $matches = [];
            $res = preg_match('/'.XOONIPS_CONFIG_DOI_FIELD_PARAM_PATTERN.'/', $value, $matches);
            if (strlen($value) > XOONIPS_CONFIG_DOI_FIELD_PARAM_MAXLEN || 0 == $res || $matches[0] != $value) {
                $parameters = [];
                $parameters[] = XOONIPS_CONFIG_DOI_FIELD_PARAM_MAXLEN;
                $errors->addError('_MD_XOONIPS_ITEM_DOI_INVALID_ID', '', $parameters);
            } else {
                $itemBasicBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
                if ($itemBasicBean->checkExistdoi(0, $value)) {
                    $parameters = [];
                    $parameters[] = '';
                    $errors->addError('_MD_XOONIPS_ITEM_DOI_DUPLICATE_ID', '', $parameters);
                }
            }
        }
    }

    public function editCheck(&$errors, $field, $value, $fieldName, $itemid)
    {
        if (strlen($value) > 0) {
            $matches = [];
            $res = preg_match('/'.XOONIPS_CONFIG_DOI_FIELD_PARAM_PATTERN.'/', $value, $matches);
            if (strlen($value) > XOONIPS_CONFIG_DOI_FIELD_PARAM_MAXLEN || 0 == $res || $matches[0] != $value) {
                $parameters = [];
                $parameters[] = XOONIPS_CONFIG_DOI_FIELD_PARAM_MAXLEN;
                $errors->addError('_MD_XOONIPS_ITEM_DOI_INVALID_ID', '', $parameters);
            } else {
                $itemBasicBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
                if ($itemBasicBean->checkExistdoi($itemid, $value)) {
                    $parameters = [];
                    $parameters[] = '';
                    $errors->addError('_MD_XOONIPS_ITEM_DOI_DUPLICATE_ID', '', $parameters);
                }
            }
        }
    }

    /**
     * must Create item_extend table.
     *
     * @param
     *
     * @return bool
     */
    public function mustCreateItemExtendTable()
    {
        return false;
    }
}
