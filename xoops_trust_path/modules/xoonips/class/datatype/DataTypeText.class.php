<?php

require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/class/datatype/DataType.class.php';

class Xoonips_DataTypeText extends Xoonips_DataType
{
    public function DataTypeText()
    {
        $this->setId(1);
        $this->setName('String');
    }

    public function getSql($id, $len)
    {
        echo "create table tbl_$id length=$len";
    }

    public function inputCheck(&$errors, $field, $value, $fieldName)
    {
        $ret = true;
        if (is_array($value)) {
            return true;
        } elseif ($field->getLen() > 0 && strlen(trim($value)) > $field->getLen()) {
            $parameters = array();
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
        $value = array();
        $essential = ($field->getEssential() == 1) ? 'NOT NULL' : '';
        $value[0] = ' text '.$essential;
        $value[1] = '(255)';

        return $value;
    }

    public function valueAttrCheck($field, &$errors)
    {
        return true;
    }
}
