<?php

use Xoonips\Core\Functions;

require_once __DIR__.'/BeanBase.class.php';

class Xoonips_BeanFactory
{
    public static function getBean($name, $dirname, $trustDirname = null)
    {
        static $beans = [];
        $trustDirname = Functions::getTrustDirname();
        $className = ucfirst($trustDirname).'_'.$name;
        if (!isset($beans[$className])) {
            require_once XOONIPS_TRUST_PATH.'/class/bean/'.$name.'.class.php';
            if (!class_exists($className)) {
                die('fatal error');
            }
            $beans[$className] = new $className($dirname, $trustDirname);
        }

        return $beans[$className];
    }
}
