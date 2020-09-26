<?php

require_once __DIR__.'/DataType.class.php';

class Xoonips_DataTypeChar extends Xoonips_DataType
{
    public function DataTypeChar()
    {
    }

    public function inputCheck(&$errors, $field, $value, $fieldName)
    {
        $ret = true;
        if (is_array($value)) {
            return true;
        } elseif ($field->getLen() > 0 && strlen(trim($value)) > $field->getLen()) {
            $parameters = [];
            $parameters[] = $field->getName();
            $parameters[] = $field->getLen();
            $errors->addError('_MD_'.strtoupper($this->trustDirname).'_ERROR_MAXLENGTH', $fieldName, $parameters);
            $ret = false;
        }

        return $ret;
    }

    public function isLikeSearch()
    {
        return true;
    }

    public function getValueSql($field)
    {
        $value = [];
        $length = $field->getLen();
        $essential = (1 == $field->getEssential()) ? 'NOT NULL' : '';
        $defaultValue = $field->getDefault();
        $default = ' default NULL';
        if ('' != $defaultValue) {
            $default = " default '$defaultValue'";
        } else {
            if (1 == $field->getEssential()) {
                $default = '';
            }
        }
        $value[0] = " char($length) ".$essential.$default;
        $value[1] = '';

        return $value;
    }

    public function valueAttrCheck($field, &$errors)
    {
        $parameters = [];
        $parameters[] = constant('_AM_'.strtoupper($this->trustDirname).'_LABEL_ITEMTYPE_DATA_LENGTH');
        if ('' == $field->getLen()) {
            $errors->addError('_MD_'.strtoupper($this->trustDirname).'_ERROR_REQUIRED', '', $parameters);
        } elseif (0 == $field->getLen()) {
            $errors->addError('_MD_'.strtoupper($this->trustDirname).'_CHECK_INPUT_ERROR_MSG', '', $parameters);
        } else {
            if ('' != $field->getDefault() && strlen($field->getDefault()) > $field->getLen()) {
                $parameters = [];
                $parameters[] = constant('_AM_'.strtoupper($this->trustDirname).'_LABEL_ITEMTYPE_DEFAULT_VALUE');
                $errors->addError('_MD_'.strtoupper($this->trustDirname).'_CHECK_INPUT_ERROR_MSG', '', $parameters);
            }
        }
    }
}
