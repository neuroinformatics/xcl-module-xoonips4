<?php

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

require_once XOONIPS_TRUST_PATH.'/class/user/AbstractEditAction.class.php';

class Xoonips_UserAbstractDeleteAction extends Xoonips_UserAbstractEditAction
{
    public function isEnableCreate()
    {
        return false;
    }

    public function _doExecute()
    {
        return $this->mObjectHandler->delete($this->mObject);
    }
}
