<?php

use Xoonips\Core\XoopsUtils;

class Xoonips_GetUserInfoAjaxMethod extends Xoonips_AbstractAjaxMethod
{
    /**
     * execute.
     *
     * return bool
     */
    public function execute()
    {
        static $keys = array('uid', 'uname', 'name');
        $ret = array();
        if (XoopsUtils::getUid() == XOONIPS_UID_GUEST) {
            return false;
        }
        $uid = intval($this->mRequest->getRequest('uid'));
        if ($uid == 0) {
            return false;
        }
        $userHandler = &xoops_gethandler('user');
        $userObj = &$userHandler->get($uid);
        if (!is_object($userObj)) {
            return false;
        }
        foreach ($keys as $key) {
            $value = $userObj->get($key);
            if (_CHARSET != 'UTF-8') {
                $value = mb_convert_encoding($value, 'UTF-8', _CHARSET);
            }
            $ret[$key] = $value;
        }
        $this->mResult = json_encode($ret);

        return true;
    }
}
