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
        static $keys = ['uid', 'uname', 'name'];
        $ret = [];
        if (XOONIPS_UID_GUEST == XoopsUtils::getUid()) {
            return false;
        }
        $uid = intval($this->mRequest->getRequest('uid'));
        if (0 == $uid) {
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
