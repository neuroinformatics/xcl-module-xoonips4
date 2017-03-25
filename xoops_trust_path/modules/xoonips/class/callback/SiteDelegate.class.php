<?php

class Xoonips_SiteDelegate
{
    /**
     * 'Site.JQuery.AddFunction' Delegete function.
     *
     * @param jQuery &$jQuery
     */
    public static function jQueryAddFunction(&$jQuery)
    {
        $trustDirname = basename(dirname(dirname(dirname(__FILE__))));
        $dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
        static $isFirst = true;
        if ($isFirst) {
            // load javascript only first module
            //$jQuery->addLibrary('https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js', false);
            //$jQuery->addStylesheet('https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css', false);
            //$jQuery->addLibrary('https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js', false);
            $jQuery->addLibrary('/common/js/jstree/jstree.js');
            $jQuery->addStylesheet('/common/js/jstree/themes/default/style.min.css');
            $jQuery->addLibrary('/common/js/jquery.cookie.js');
            $jQuery->addLibrary('/modules/'.$dirnames[0].'/index.php/js/popin.js');
            $jQuery->addStylesheet('/modules/'.$dirnames[0].'/index.php/css/popin.css');

            $isFirst = false;
        }
        foreach ($dirnames as $dirname) {
            $jQuery->addLibrary('/modules/'.$dirname.'/index.php/js/library.js');
            $jQuery->addStylesheet('/modules/'.$dirname.'/index.php/css/style.css');
        }
    }

    /**
     * 'Site.CheckLogin.Success' Delegete function.
     *
     * @param XoopsUser &$xoopsUser
     */
    public static function checkLoginSuccess(&$xoopsUser)
    {
        $trustDirname = basename(dirname(dirname(dirname(__FILE__))));
        $dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
        foreach ($dirnames as $dirname) {
            $log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
            $log->recordLoginSuccessEvent($xoopsUser->get('uid'));
        }
    }

    /**
     * 'Site.CheckLogin.Fail' Delegete function.
     *
     * @param XoopsUser &$xoopsUser
     */
    public static function checkLoginFail(&$xoopsUser)
    {
        $trustDirname = basename(dirname(dirname(dirname(__FILE__))));
        $dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
        $uname = xoops_getrequest('uname');
        foreach ($dirnames as $dirname) {
            $log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
            $log->recordLoginFailureEvent($uname);
        }
    }

    /**
     * 'Site.Logout.Success' Delegete function.
     *
     * @param XoopsUser &$xoopsUser
     */
    public static function logoutSuccess(&$xoopsUser)
    {
        $trustDirname = basename(dirname(dirname(dirname(__FILE__))));
        $dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
        $uid = $xoopsUser->get('uid');
        foreach ($dirnames as $dirname) {
            $log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
            $log->recordLogoutEvent($uid);
        }
    }

    /**
     * 'User_UserViewAction.GetUserPosts' Delegate function
     * Recount posts of $xoopsUser in the item contribution.
     *
     * @param int       &$posts
     * @param xoopsUser $xoopsUser
     *
     * @see html/modules/user/admin/actions/UserViewAction.class.php
     * @see html/modules/legacy/kernel/Legacy_Controller.class.php
     * @see html/modules/legacy/kernel/Legacy_EventFunctions.class.php
     */
    public static function recountPost(&$posts, $xoopsUser)
    {
        $trustDirname = basename(dirname(dirname(dirname(__FILE__))));
        $dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
        $uid = $xoopsUser->get('uid');
        foreach ($dirnames as $dirname) {
            $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $dirname, $trustDirname);
            $itemBean->getPosts($uid, $posts);
        }
    }
}
