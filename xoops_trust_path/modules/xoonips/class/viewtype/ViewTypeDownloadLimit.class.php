<?php

require_once __DIR__.'/ViewTypeDownloadNotify.class.php';

class Xoonips_ViewTypeDownloadLimit extends Xoonips_ViewTypeDownloadNotify
{
    protected function getList()
    {
        $ret = [];
        $ret[1] = _MI_XOONIPS_INSTALL_DOWNLOAD_LIMIT_LOGIN_USER;
        $ret[0] = _MI_XOONIPS_INSTALL_DOWNLOAD_LIMIT_EVERYONE;

        return $ret;
    }
}
