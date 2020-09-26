<?php

require_once __DIR__.'/DataType.class.php';

class Xoonips_DataTypeBlob extends Xoonips_DataType
{
    public function DataTypeString()
    {
        $this->setId(1);
        $this->setName('String');
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
        $essential = (1 == $field->getEssential()) ? 'NOT NULL' : '';
        $value[0] = ' blob '.$essential;
        $value[1] = '(255)';

        return $value;
    }

    public function valueAttrCheck($field, &$errors)
    {
        return true;
    }
}
