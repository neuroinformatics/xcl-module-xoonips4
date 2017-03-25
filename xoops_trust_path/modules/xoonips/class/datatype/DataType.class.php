<?php

abstract class Xoonips_DataType
{
    private $id;
    private $name;
    private $module;
    protected $trustDirname;

    abstract protected function getSql($id, $len);
    abstract protected function inputCheck(&$errors, $field, $value, $fieldName);
    abstract protected function getValueSql($field);
    abstract protected function valueAttrCheck($field, &$errors);

    public function setId($v)
    {
        $this->id = $v;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($v)
    {
        $this->name = $v;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setModule($v)
    {
        $this->module = $v;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function setTrustDirname($v)
    {
        $this->trustDirname = $v;
    }

    public function sqlConvert($value)
    {
        return "'$value'";
    }

    public function convertSQLStr($value)
    {
        return Xoonips_Utils::convertSQLStr($value);
    }

    public function convertSQLStrLike($value)
    {
        return Xoonips_Utils::convertSQLStrLike($value);
    }

    public function convertSQLNum($value)
    {
        return Xoonips_Utils::convertSQLNum($value);
    }

    public function isLikeSearch()
    {
        return false;
    }

    public function isNumericSearch()
    {
        return false;
    }
}
