<?php

require_once __DIR__.'/DataType.class.php';

class Xoonips_DataTypeInt extends Xoonips_DataType
{
    public function inputCheck(&$errors, $field, $value, $fieldName)
    {
        $ret = true;
        if (is_array($value)) {
            foreach ($value as $v) {
                if ('' != trim($v) && !is_numeric($v) || strpos($v, '.')) {
                    $parameters = [];
                    $parameters[] = $field->getName();
                    $errors->addError('_MD_'.strtoupper($this->trustDirname).'_MESSAGE_INPUT_INT', $fieldName, $parameters);
                    $ret = false;
                }
            }
        } elseif ('' != trim($value) && !is_numeric($value) || strpos($value, '.')) {
            $parameters = [];
            $parameters[] = $field->getName();
            $errors->addError('_MD_'.strtoupper($this->trustDirname).'_MESSAGE_INPUT_INT', $fieldName, $parameters);
            $ret = false;
        } elseif ($field->getLen() > 0 && strlen(trim($value)) > $field->getLen()) {
            $parameters = [];
            $parameters[] = $field->getName();
            $parameters[] = $field->getLen();
            $errors->addError('_MD_'.strtoupper($this->trustDirname).'_ERROR_MAXLENGTH', $fieldName, $parameters);
            $ret = false;
        }

        return $ret;
    }

    public function convertSQLStr($value)
    {
        return $this->convertSQLNum($value);
    }

    public function getValueSql($field)
    {
        $value = [];
        $length = $field->getLen();
        $essential = (1 == $field->getEssential()) ? 'NOT NULL' : '';
        $defaultValue = ('' != $field->getDefault()) ? $field->getDefault() : 0;
        $default = ' default '."'$defaultValue'";
        $value[0] = " int($length) ".$essential.$default;
        $value[1] = '';

        return $value;
    }

    public function valueAttrCheck($field, &$errors)
    {
        if ('' == $field->getLen()) {
            $parameters = [];
            $parameters[] = constant('_AM_'.strtoupper($this->trustDirname).'_LABEL_ITEMTYPE_DATA_LENGTH');
            $errors->addError('_MD_'.strtoupper($this->trustDirname).'_ERROR_REQUIRED', '', $parameters);
        } elseif ($field->getLen() <= 0) {
            $parameters = [];
            $parameters[] = constant('_AM_'.strtoupper($this->trustDirname).'_LABEL_ITEMTYPE_DATA_LENGTH');
            $errors->addError('_MD_'.strtoupper($this->trustDirname).'_CHECK_INPUT_ERROR_MSG', '', $parameters);
        } else {
            if ('' != $field->getDefault() && (!is_numeric($field->getDefault()) ||
            strlen($field->getDefault()) > $field->getLen())) {
                $parameters = [];
                $parameters[] = constant('_AM_'.strtoupper($this->trustDirname).'_LABEL_ITEMTYPE_DEFAULT_VALUE');
                $errors->addError('_MD_'.strtoupper($this->trustDirname).'_CHECK_INPUT_ERROR_MSG', '', $parameters);
            }
        }
    }

    public function isNumericSearch()
    {
        return true;
    }
}
