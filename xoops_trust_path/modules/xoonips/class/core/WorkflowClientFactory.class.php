<?php

class Xoonips_WorkflowClientFactory
{
    public static function getWorkflow($dataname, $dirname, $trustDirname)
    {
        static $workflows = [];
        $className = ucfirst($trustDirname).'_WorkflowClient'.$dataname;
        if (!isset($workflows[$className])) {
            require_once XOOPS_TRUST_PATH.'/modules/'.$trustDirname.'/class/workflow/'.$dataname.'.class.php';
            if (!class_exists($className)) {
                die('fatal error');
            }
            $workflows[$className] = new $className($dataname, $dirname, $trustDirname);
        }

        return $workflows[$className];
    }
}
