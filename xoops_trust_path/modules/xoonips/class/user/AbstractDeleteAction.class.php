<?php

require_once dirname(__DIR__).'/user/AbstractEditAction.class.php';

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
