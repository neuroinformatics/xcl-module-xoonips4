<?php

use Xoonips\Core\Functions;

/**
 * complement factory class.
 */
class Xoonips_ComplementFactory
{
    /**
     * instances cache.
     *
     * @var {Trustdirname}_Complement[]
     */
    private $complementInstances = [];

    /**
     * constructor.
     *
     * @param string $dirname
     * @param string $trustDirname
     */
    private function __construct($dirname, $trustDirname)
    {
        $complementHandler = Functions::getXoonipsHandler('ComplementObject', $dirname);
        if (!$res = $complementHandler->open()) {
            die('fatal error');
        }
        while ($obj = $complementHandler->getNext($res)) {
            $complementId = $obj->get('complement_id');
            $viewTypeId = $obj->get('view_type_id');
            $module = $obj->get('module');
            if (!empty($module)) {
                $className = ucfirst($trustDirname).'_'.$module;
                require_once XOONIPS_TRUST_PATH.'/class/complement/'.$module.'.class.php';
                if (!class_exists($className)) {
                    die('fatal error');
                }
                $this->complementInstances[$viewTypeId] = new $className($complementId, $dirname, $trustDirname);
            }
        }
        $complementHandler->close($res);
    }

    /**
     * get complement factory instance.
     *
     * @param string $dirname
     * @param string $trustDirname
     *
     * @return {Trustdirname}_ComplementFactory
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
     * get complement instance.
     *
     * @param int $id
     *
     * @return {Trustdirname}_Complemnet
     */
    public function getComplement($id)
    {
        return $this->complementInstances[$id];
    }
}
