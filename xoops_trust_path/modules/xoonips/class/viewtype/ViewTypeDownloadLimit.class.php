<?php

require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/class/viewtype/ViewTypeDownloadNotify.class.php';

class Xoonips_ViewTypeDownloadLimit extends Xoonips_ViewTypeDownloadNotify
{
    protected function getList()
    {
        $ret = array();
        $ret[1] = _MI_XOONIPS_INSTALL_DOWNLOAD_LIMIT_LOGIN_USER;
        $ret[0] = _MI_XOONIPS_INSTALL_DOWNLOAD_LIMIT_EVERYONE;

        return $ret;
    }
}
