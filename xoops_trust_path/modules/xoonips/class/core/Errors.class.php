<?php

require_once __DIR__.'/Error.class.php';

class Xoonips_Errors
{
    private $errors = [];

    public function __construct()
    {
    }

    public function setErrors($v)
    {
        $this->errors = $v;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getView($dirname, $isAdmin = false)
    {
        if (0 == count($this->errors)) {
            return '';
        }

        global $xoopsTpl;
        $xoopsTpl->assign('errors', $this->errors);
        $xoopsTpl->assign('dirname', $dirname);
        $xoopsTpl->assign('isAdmin', $isAdmin);

        return $xoopsTpl->fetch('db:'.$dirname.'_error.html');
    }

    public function addError($msgId, $fieldName, $parameters, $isConst = true)
    {
        $error = new Xoonips_Error($msgId, $fieldName, $parameters, $isConst);
        $this->errors[] = $error;
    }

    public function hasError()
    {
        if (count($this->errors) > 0) {
            return true;
        } else {
            return false;
        }
    }
}
