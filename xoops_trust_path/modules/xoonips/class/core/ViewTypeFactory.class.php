<?php

use Xoonips\Core\Functions;

require_once __DIR__.'/Search.class.php';

/**
 * view type factory class.
 */
class Xoonips_ViewTypeFactory
{
    /**
     * instances cache.
     *
     * @var {Trustdirname}_ViewType[]
     */
    private $viewTypeInstances = [];

    /**
     * constructor.
     *
     * @param string $dirname
     * @param string $trustDirname
     */
    private function __construct($dirname, $trustDirname)
    {
        $search = &Xoonips_Search::getInstance();

        // get view type data
        $viewTypeHandler = Functions::getXoonipsHandler('ViewTypeObject', $dirname);
        if (!$res = $viewTypeHandler->open()) {
            die('fatal error');
        }
        while ($obj = $viewTypeHandler->getNext($res)) {
            $module = $obj->get('module');
            $className = ucfirst($trustDirname).'_'.$module;
            require_once XOONIPS_TRUST_PATH.'/class/viewtype/'.$module.'.class.php';
            if (!class_exists($className)) {
                die('fatal error');
            }
            $viewType = new $className($dirname, $trustDirname);
            $viewTypeId = $obj->get('view_type_id');
            $viewType->setId($viewTypeId);
            $viewType->setName($obj->get('name'));
            $viewType->setPreslect($obj->get('preselect'));
            $viewType->setModule($module);
            $viewType->setMulti($obj->get('multi'));
            $viewType->setTemplate();
            $viewType->setSearch($search);
            $this->viewTypeInstances[$viewTypeId] = $viewType;
        }
        $viewTypeHandler->close($res);
    }

    /**
     * get view typefactory instance.
     *
     * @param string $dirname
     * @param string $trustDirname
     *
     * @return {Trustdirname}_ViewTypeFactory
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
     * get view type instance.
     *
     * @param string $id
     *
     * @return {Trustdirname}_ViewType
     */
    public function getViewType($id)
    {
        return $this->viewTypeInstances[$id];
    }
}
