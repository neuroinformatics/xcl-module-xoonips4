<?php

use Xoonips\Core\Functions;

/**
 * data type factory class.
 */
class Xoonips_DataTypeFactory
{
    /**
     * instances cache.
     *
     * @var {Trustdirname}_DataType[]
     */
    private $dataTypeInstances = [];

    /**
     * constructor.
     *
     * @param string $dirname
     * @param string $trustDirname
     */
    private function __construct($dirname, $trustDirname)
    {
        $dataTypeHandler = Functions::getXoonipsHandler('DataTypeObject', $dirname);
        if (!$res = $dataTypeHandler->open()) {
            die('fatal error');
        }
        while ($obj = $dataTypeHandler->getNext($res)) {
            $dataTypeId = $obj->get('data_type_id');
            $name = $obj->get('name');
            $module = $obj->get('module');
            $className = ucfirst($trustDirname).'_'.$module;
            require_once XOONIPS_TRUST_PATH.'/class/datatype/'.$module.'.class.php';
            if (!class_exists($className)) {
                die('fatal error');
            }
            $dataType = new $className();
            $dataType->setId($dataTypeId);
            $dataType->setName($name);
            $dataType->setModule($module);
            $dataType->setTrustDirname($trustDirname);
            $this->dataTypeInstances[$dataTypeId] = $dataType;
        }
        $dataTypeHandler->close($res);
    }

    /**
     * get data type factory instance.
     *
     * @param string $dirname
     * @param string $trustDirname
     *
     * @return {Trustdirname}_DataTypeFactory
     */
    public static function getInstance($dirname, $trustDirname)
    {
        static $instance = [];
        if (!isset($instance[$dirname])) {
            $instance[$dirname] = new self($dirname, $trustDirname);
        }

        return $instance[$dirname];
    }

    /**
     * get data type instance.
     *
     * @param string $id
     *
     * @return {Trustdirname}_DataType
     */
    public function getDataType($id)
    {
        return $this->dataTypeInstances[$id];
    }
}
