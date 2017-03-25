<?php

define('ETID_LOGIN_FAILURE', 1);
define('ETID_LOGIN_SUCCESS', 2);
define('ETID_LOGOUT', 3);
define('ETID_INSERT_ITEM', 4);
define('ETID_UPDATE_ITEM', 5);
define('ETID_DELETE_ITEM', 6);
define('ETID_VIEW_ITEM', 7);
define('ETID_DOWNLOAD_FILE', 8);
define('ETID_REQUEST_PUBLIC_ITEM', 9);
define('ETID_INSERT_INDEX', 10);
define('ETID_UPDATE_INDEX', 11);
define('ETID_DELETE_INDEX', 12);
define('ETID_CERTIFY_PUBLIC_ITEM', 13);
define('ETID_REJECT_PUBLIC_ITEM', 14);
define('ETID_REQUEST_ACCOUNT', 15);
define('ETID_CERTIFY_ACCOUNT', 16);
define('ETID_REQUEST_GROUP', 17);
define('ETID_UPDATE_GROUP', 18);
define('ETID_CERTIFY_DELETE_GROUP', 19);
define('ETID_INSERT_GROUP_MEMBER', 20);
define('ETID_DELETE_GROUP_MEMBER', 21);
define('ETID_VIEW_TOP_PAGE', 22);
define('ETID_QUICK_SEARCH', 23);
define('ETID_ADVANCED_SEARCH', 24);
define('ETID_START_SU', 25);
define('ETID_END_SU', 26);
define('ETID_ADD_ITEM_OWNER', 28);
define('ETID_CERTIFY_GROUP_ITEM', 30);
define('ETID_REJECT_GROUP_ITEM', 31);
define('ETID_CERTIFY_GROUP_OPEN', 32);
define('ETID_DELETE_ACCOUNT', 33);
define('ETID_UNCERTIFY_ACCOUNT', 34);
define('ETID_CERTIFY_GROUP', 35);
define('ETID_REJECT_GROUP', 36);
define('ETID_REQUEST_DELETE_GROUP', 37);
define('ETID_REJECT_DELETE_GROUP', 38);
define('ETID_REQUEST_GROUP_OPEN', 39);
define('ETID_REJECT_GROUP_OPEN', 40);
define('ETID_REQUEST_GROUP_CLOSE', 41);
define('ETID_CERTIFY_GROUP_CLOSE', 42);
define('ETID_REJECT_GROUP_CLOSE', 43);
define('ETID_REQUEST_JOIN_GROUP', 44);
define('ETID_CERTIFY_JOIN_GROUP', 45);
define('ETID_REJECT_JOIN_GROUP', 46);
define('ETID_REQUEST_LEAVE_GROUP', 47);
define('ETID_CERTIFY_LEAVE_GROUP', 48);
define('ETID_REJECT_LEAVE_GROUP', 49);
define('ETID_REQUEST_PUBLIC_ITEM_WITHDRAWAL', 50);
define('ETID_CERTIFY_PUBLIC_ITEM_WITHDRAWAL', 51);
define('ETID_REJECT_PUBLIC_ITEM_WITHDRAWAL', 52);
define('ETID_REQUEST_GROUP_ITEM', 53);
define('ETID_REQUEST_GROUP_ITEM_WITHDRAWAL', 54);
define('ETID_CERTIFY_GROUP_ITEM_WITHDRAWAL', 55);
define('ETID_REJECT_GROUP_ITEM_WITHDRAWAL', 56);
define('ETID_DELETE_ITEM_OWNER', 57);
define('ETID_MOVE_INDEX', 58);
define('ETID_MAX', 59);

require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/BeanBase.class.php';

/**
 * @brief operate xoonips_event_log table
 */
class Xoonips_EventLogBean extends Xoonips_BeanBase
{
    /**
     * Constructor.
     **/
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct($dirname, $trustDirname);
        $this->setTableName('event_log', true);
    }

    private function create()
    {
        $ret = array();
        $ret['event_id'] = null;
        $ret['event_type_id'] = null;
        $ret['timestamp'] = time();
        $ret['exec_uid'] = null;
        $ret['remote_host'] = $this->getRemoteHost();
        $ret['index_id'] = null;
        $ret['item_id'] = null;
        $ret['file_id'] = null;
        $ret['uid'] = null;
        $ret['groupid'] = null;
        $ret['search_keyword'] = null;
        $ret['additional_info'] = null;

        return $ret;
    }

    private function insert($obj)
    {
        $ret = true;
        $sql = "INSERT INTO $this->table (event_type_id,timestamp,exec_uid,remote_host,";
        $sql = $sql.'index_id,item_id,file_id,uid,groupid,search_keyword,additional_info)';

        $sql = $sql.' VALUES('.$obj['event_type_id'].','.$obj['timestamp'].',';
        $sql = $sql.$obj['exec_uid'].','.Xoonips_Utils::convertSQLStr($obj['remote_host']).',';
        $sql = $sql.Xoonips_Utils::convertSQLNum($obj['index_id']).',';
        $sql = $sql.Xoonips_Utils::convertSQLNum($obj['item_id']).',';
        $sql = $sql.Xoonips_Utils::convertSQLNum($obj['file_id']).',';
        $sql = $sql.Xoonips_Utils::convertSQLNum($obj['uid']).',';
        $sql = $sql.Xoonips_Utils::convertSQLNum($obj['groupid']).',';
        $sql = $sql.Xoonips_Utils::convertSQLStr($obj['search_keyword']).',';
        $sql = $sql.Xoonips_Utils::convertSQLStr($obj['additional_info']).')';
        $result = $this->execute($sql);
        if (!$result) {
            return false;
        }

        return $ret;
    }

    /**
     * get remote host.
     *
     * @return string remote host name or address
     */
    private function getRemoteHost()
    {
        $remote_host = '';
        if (isset($_SERVER['REMOTE_HOST'])) {
            $remote_host = $_SERVER['REMOTE_HOST'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $remote_host = $_SERVER['REMOTE_ADDR'];
        }
        // check proxy environment
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $remote_host = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_VIA'])) {
            $remote_host = $_SERVER['HTTP_VIA'];
        }
        // - gethostbyaddr is too slow if dns lookup failure
        //if (preg_match('/^\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}$/', $remote_host)) {
        //	if (function_exists('gethostbyaddr')) {
        //		$remote_host = gethostbyaddr($remote_host);
        //	}
        //}
        return $remote_host;
    }

    /**
     * get execution user id.
     *
     * @return int execution user id
     */
    private function getExecUid()
    {
        $exec_uid = XOONIPS_UID_GUEST;
        if (isset($GLOBALS['xoopsUser']) && is_object($GLOBALS['xoopsUser'])) {
            $exec_uid = intval($GLOBALS['xoopsUser']->getVar('uid'));
        }

        return $exec_uid;
    }

    /**
     * record login failure event (ETID_LOGIN_FAILURE: 1).
     *
     * @param string $uname trying login uname
     *
     * @return bool false if failure
     */
    public function recordLoginFailureEvent($uname)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_LOGIN_FAILURE;
        $obj['exec_uid'] = XOONIPS_UID_GUEST;
        $obj['additional_info'] = $uname;

        return $this->insert($obj);
    }

    /**
     * record login success event (ETID_LOGIN_SUCCESS: 2).
     *
     * @param int $uid login user id
     *
     * @return bool false if failure
     */
    public function recordLoginSuccessEvent($uid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_LOGIN_SUCCESS;
        $obj['exec_uid'] = $uid;

        return $this->insert($obj);
    }

    /**
     * record logout event (ETID_LOGOUT: 3).
     *
     * @param int $uid       logout user id
     * @param int $timestamp logout timestamp for session GC
     *
     * @return bool false if failure
     */
    public function recordLogoutEvent($uid, $timestamp = null)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_LOGOUT;
        $obj['exec_uid'] = $uid;
        if (!is_null($timestamp)) {
            // override timestamp for session GC
            $obj['timestamp'] = $timestamp;
        }
        // force insertion
        return $this->insert($obj);
    }

    /**
     * record insert item event (ETID_INSERT_ITEM: 4).
     *
     * @param int $item_id inserted item id
     *
     * @return bool false if failure
     */
    public function recordInsertItemEvent($item_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_INSERT_ITEM;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;

        return $this->insert($obj);
    }

    /**
     * record update item event (ETID_UPDATE_ITEM: 5).
     *
     * @param int $item_id updated item id
     *
     * @return bool false if failure
     */
    public function recordUpdateItemEvent($item_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_UPDATE_ITEM;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;

        return $this->insert($obj);
    }

    /**
     * record delete item event (ETID_DELETE_ITEM: 6).
     *
     * @param int $item_id deleted item id
     *
     * @return bool false if failure
     */
    public function recordDeleteItemEvent($item_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_DELETE_ITEM;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;

        return $this->insert($obj, true);
    }

    /**
     * record view item event (ETID_VIEW_ITEM: 7).
     *
     * @param int $item_id viewed item id
     *
     * @return bool false if failure
     */
    public function recordViewItemEvent($item_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_VIEW_ITEM;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;
        // force insertion
        return $this->insert($obj);
    }

    /**
     * record download file event (ETID_DOWNLOAD_FILE: 8).
     *
     * @param int $item_id downloaded item id
     * @param int $file_id downloaded file id
     *
     * @return bool false if failure
     */
    public function recordDownloadFileEvent($item_id, $file_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_DOWNLOAD_FILE;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;
        $obj['file_id'] = $file_id;
        // force insertion
        return $this->insert($obj);
    }

    /**
     * record request public item event (ETID_REQUEST_PUBLIC_ITEM: 9).
     *
     * @param int $item_id  requested item id
     * @param int $index_id requested index id
     *
     * @return bool false if failure
     */
    public function recordRequestCertifyItemEvent($item_id, $index_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REQUEST_PUBLIC_ITEM;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;
        $obj['index_id'] = $index_id;

        return $this->insert($obj);
    }

    /**
     * record insert index event (ETID_INSERT_INDEX: 10).
     *
     * @param int $index_id inserted index id
     *
     * @return bool false if failure
     */
    public function recordInsertIndexEvent($index_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_INSERT_INDEX;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['index_id'] = $index_id;

        return $this->insert($obj);
    }

    /**
     * record update index event (ETID_UPDATE_INDEX: 11).
     *
     * @param int $index_id updated index id
     *
     * @return bool false if failure
     */
    public function recordUpdateIndexEvent($index_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_UPDATE_INDEX;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['index_id'] = $index_id;

        return $this->insert($obj);
    }

    /**
     * record delete index event (ETID_DELETE_INDEX: 12).
     *
     * @param int $index_id deleted index id
     *
     * @return bool false if failure
     */
    public function recordDeleteIndexEvent($index_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_DELETE_INDEX;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['index_id'] = $index_id;

        return $this->insert($obj);
    }

    /**
     * record certify public item event (ETID_CERTIFY_PUBLIC_ITEM: 13).
     *
     * @param int $item_id  certified item id
     * @param int $index_id certified index id
     *
     * @return bool false if failure
     */
    public function recordCertifyItemEvent($item_id, $index_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_CERTIFY_PUBLIC_ITEM;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;
        $obj['index_id'] = $index_id;

        return $this->insert($obj);
    }

    /**
     * record reject public item event (ETID_REJECT_PUBLIC_ITEM: 14).
     *
     * @param int $item_id  rejected item id
     * @param int $index_id rejected index id
     *
     * @return bool false if failure
     */
    public function recordRejectItemEvent($item_id, $index_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REJECT_PUBLIC_ITEM;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;
        $obj['index_id'] = $index_id;

        return $this->insert($obj);
    }

    /**
     * record request account event (ETID_REQUEST_ACCOUNT: 15).
     *
     * @param int $uid requested user id
     *
     * @return bool false if failure
     */
    public function recordRequestInsertAccountEvent($uid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REQUEST_ACCOUNT;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['uid'] = $uid;

        return $this->insert($obj);
    }

    /**
     * record certify account event (ETID_CERTIFY_ACCOUNT: 16).
     *
     * @param int $uid certified user id
     *
     * @return bool false if failure
     */
    public function recordCertifyAccountEvent($uid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_CERTIFY_ACCOUNT;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['uid'] = $uid;

        return $this->insert($obj);
    }

    /**
     * record request group event (ETID_REQUEST_GROUP: 17).
     *
     * @param int $gid requested group id
     *
     * @return bool false if failure
     */
    public function recordRequestGroupEvent($gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REQUEST_GROUP;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record update group event (ETID_UPDATE_GROUP: 18).
     *
     * @param int $gid updated group id
     *
     * @return bool false if failure
     */
    public function recordUpdateGroupEvent($gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_UPDATE_GROUP;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record certify delete group event (ETID_CERTIFY_DELETE_GROUP: 19).
     *
     * @param int $gid deleted group id
     *
     * @return bool false if failure
     */
    public function recordDeleteGroupEvent($gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_CERTIFY_DELETE_GROUP;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record insert group member event (ETID_INSERT_GROUP_MEMBER: 20).
     *
     * @param int $uid subscribed user id
     * @param int $gid subscribed group id
     *
     * @return bool false if failure
     */
    public function recordInsertGroupMemberEvent($uid, $gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_INSERT_GROUP_MEMBER;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['uid'] = $uid;
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record delete group member event (ETID_DELETE_GROUP_MEMBER: 21).
     *
     * @param int $uid unsubscribed user id
     * @param int $gid unsubscribed group id
     *
     * @return bool false if failure
     */
    public function recordDeleteGroupMemberEvent($uid, $gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_DELETE_GROUP_MEMBER;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['uid'] = $uid;
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record view top page event (ETID_VIEW_TOP_PAGE: 22).
     *
     * @return bool false if failure
     */
    public function recordViewTopPageEvent()
    {
        // get start page script name
        global $xoopsRequestUri;
        $moduleHandler = xoops_gethandler('module');
        $myxoopsConfig = Xoonips_Utils::getXoopsConfigs(XOOPS_CONF);
        $startpage_url = XOOPS_URL.'/index.php';
        if (isset($myxoopsConfig['startpage']) && $myxoopsConfig['startpage'] != '' && $myxoopsConfig['startpage'] != '--') {
            $module = $moduleHandler->get($myxoopsConfig['startpage']);
            $dirname = $module->getVar('dirname');
            $startpage_url = XOOPS_URL.'/modules/'.$dirname.'/index.php';
        }
        $startpage_script = '';
        if (preg_match('/^(\\S+):\\/\\/([^\\/]+)((\\/[^\\/]+)*\\/index.php)$/', $startpage_url, $matches)) {
            $startpage_script = $matches[3];
        }
        // get current script name
        $current_script = (isset($_SERVER['SCRIPT_NAME'])) ? $_SERVER['SCRIPT_NAME'] : '';
        $query_string = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : '';
        // compare start page script name with current script name
        if ($startpage_script != $current_script || $query_string != '') {
            // current url is not top page
            return false;
        }

        // record event
        $obj = $this->create();
        $obj['event_type_id'] = ETID_VIEW_TOP_PAGE;
        $obj['exec_uid'] = $this->getExecUid();
        // force insertion
        return $this->insert($obj);
    }

    /**
     * record xoonips search event (ETID_QUICK_SEARCH: 23).
     *
     * @param string $search_itemtype 'all' or itemtype name
     * @param string $keyword         searched keyword
     * @param int    $repository_url  repository url to search
     *
     * @return bool false if failure
     */
    public function recordQuickSearchEvent($search_itemtype, $keyword, $repository_url = '')
    {
        $search_keyword = 'condition_id='.urlencode($search_itemtype).'&keyword='.urlencode($keyword).(empty($repository_url) ? '' : '&repository_url='.urlencode($repository_url));
        $obj = $this->create();
        $obj['event_type_id'] = ETID_QUICK_SEARCH;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['search_keyword'] = $search_keyword;
        // force insertion
        $result = $this->insert($obj, true);

        return $result;
    }

    /**
     * record advanced search event (ETID_ADVANCED_SEARCH: 24).
     *
     * @param array $keywords searched keywords
     *
     * @return bool false if failure
     */
    public function recordAdvancedSearchEvent($keywords)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_ADVANCED_SEARCH;
        $obj['exec_uid'] = $this->getExecUid();
        if (is_array($keywords)) {
            $obj['search_keyword'] = implode('&', $keywords);
        } else {
            $obj['search_keyword'] = $keywords;
        }
        // force insertion
        return $this->insert($obj, true);
    }

    /**
     * record start su event (ETID_START_SU: 25).
     *
     * @param int $original_uid original user id
     * @param int $target_uid   switched user id
     *
     * @return bool false if failure
     */
    public function recordStartSuEvent($original_uid, $target_uid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_START_SU;
        $obj['exec_uid'] = $original_uid;
        $obj['uid'] = $target_uid;

        return $this->insert($obj);
    }

    /**
     * record end su event (ETID_END_SU: 26).
     *
     * @param int $original_uid original user id
     * @param int $target_uid   switched user id
     * @param int $timestamp    end su timestamp for session GC
     *
     * @return bool false if failure
     */
    public function recordEndSuEvent($original_uid, $target_uid, $timestamp = null)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_END_SU;
        $obj['exec_uid'] = $original_uid;
        $obj['uid'] = $target_uid;
        if (!is_null($timestamp)) {
            // override timestamp for session GC
            $obj['timestamp'] = $timestamp;
        }
        // force insertion
        return $this->insert($obj, true);
    }

    /**
     * record add item user event (ETID_ADD_ITEM_OWNER: 28).
     *
     * @param int $item_id  transferred item id
     * @param int $index_id transferred index id
     * @param int $to_uid   transferred to user id
     *
     * @return bool false if failure
     */
    public function recordAddItemUserEvent($item_id, $uid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_ADD_ITEM_OWNER;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;
        $obj['uid'] = $uid;

        return $this->insert($obj);
    }

    /**
     * record certify group item event (ETID_CERTIFY_GROUP_ITEM: 30).
     *
     * @param int $index_id certified group id
     *
     * @return bool false if failure
     */
    public function recordCertifyGroupItemEvent($index_id, $item_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_CERTIFY_GROUP_ITEM;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['index_id'] = intval($index_id);
        $obj['item_id'] = intval($item_id);

        return $this->insert($obj);
    }

    /**
     * record reject group item event (ETID_REJECT_GROUP_ITEM: 31).
     *
     * @param int $index_id rejected group id
     *
     * @return bool false if failure
     */
    public function recordRejectGroupItemEvent($index_id, $item_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REJECT_GROUP_ITEM;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['index_id'] = intval($index_id);
        $obj['item_id'] = intval($item_id);

        return $this->insert($obj);
    }

    /**
     * record certify group open event (ETID_CERTIFY_GROUP_OPEN: 32).
     *
     * @param int $gid certified group id
     *
     * @return bool false if failure
     */
    public function recordCertifyGroupOpenEvent($gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_CERTIFY_GROUP_OPEN;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['groupid'] = intval($gid);

        return $this->insert($obj);
    }

    /**
     * record delete account event (ETID_DELETE_ACCOUNT: 33).
     *
     * @param int $uid user id to be deleted
     *
     * @return bool false if failure
     */
    public function recordDeleteAccountEvent($uid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_DELETE_ACCOUNT;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['uid'] = intval($uid);

        return $this->insert($obj);
    }

    /**
     * record uncertify account event (ETID_UNCERTIFY_ACCOUNT: 34).
     *
     * @param int $uid uncertified user id
     *
     * @return bool false if failure
     */
    public function recordUncertifyAccountEvent($uid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_UNCERTIFY_ACCOUNT;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['uid'] = intval($uid);

        return $this->insert($obj);
    }

    /**
     * record certify group event (ETID_CERTIFY_GROUP: 35).
     *
     * @param int $gid certified group id
     *
     * @return bool false if failure
     */
    public function recordCertifyGroupEvent($gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_CERTIFY_GROUP;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record reject group event (ETID_REJECT_GROUP: 36).
     *
     * @param int $gid rejected group id
     *
     * @return bool false if failure
     */
    public function recordRejectGroupEvent($gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REJECT_GROUP;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record request delete group event (ETID_REQUEST_DELETE_GROUP: 37).
     *
     * @param int $gid requested group id
     *
     * @return bool false if failure
     */
    public function recordRequestDeleteGroupEvent($gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REQUEST_DELETE_GROUP;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record reject delete group event (ETID_REJECT_DELETE_GROUP: 38).
     *
     * @param int $gid rejected group id
     *
     * @return bool false if failure
     */
    public function recordRejectDeleteGroupEvent($gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REJECT_DELETE_GROUP;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record request group open event (ETID_REQUEST_GROUP_OPEN: 39).
     *
     * @param int $gid requested group id
     *
     * @return bool false if failure
     */
    public function recordRequestGroupOpenEvent($gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REQUEST_GROUP_OPEN;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record reject group open event (ETID_REJECT_GROUP_OPEN: 40).
     *
     * @param int $gid rejected group id
     *
     * @return bool false if failure
     */
    public function recordRejectGroupOpenEvent($gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REJECT_GROUP_OPEN;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record request group close event (ETID_REQUEST_GROUP_CLOSE: 41).
     *
     * @param int $gid requested group id
     *
     * @return bool false if failure
     */
    public function recordRequestGroupCloseEvent($gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REQUEST_GROUP_CLOSE;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record certify group close event (ETID_CERTIFY_GROUP_CLOSE: 42).
     *
     * @param int $gid certified group id
     *
     * @return bool false if failure
     */
    public function recordCertifyGroupCloseEvent($gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_CERTIFY_GROUP_CLOSE;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record reject group close (ETID_REJECT_GROUP_CLOSE: 43).
     *
     * @param int $gid rejected group id
     *
     * @return bool false if failure
     */
    public function recordRejectGroupCloseEvent($gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REJECT_GROUP_CLOSE;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record request join group event (ETID_REQUEST_JOIN_GROUP: 44).
     *
     * @param int $uid requested joined user id
     * @param int $gid requested joined group id
     *
     * @return bool false if failure
     */
    public function recordRequestJoinGroupEvent($uid, $gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REQUEST_JOIN_GROUP;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['uid'] = $uid;
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record certify join group event (ETID_CERTIFY_JOIN_GROUP: 45).
     *
     * @param int $uid certified joined user id
     * @param int $gid certified joined group id
     *
     * @return bool false if failure
     */
    public function recordCertifyJoinGroupEvent($uid, $gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_CERTIFY_JOIN_GROUP;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['uid'] = $uid;
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record reject join group event (ETID_REJECT_JOIN_GROUP: 46).
     *
     * @param int $uid rejected joined user id
     * @param int $gid rejected joined group id
     *
     * @return bool false if failure
     */
    public function recordRejectJoinGroupEvent($uid, $gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REJECT_JOIN_GROUP;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['uid'] = $uid;
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record request leave group event (ETID_REQUEST_LEAVE_GROUP: 47).
     *
     * @param int $uid requested leaved user id
     * @param int $gid requested leaved group id
     *
     * @return bool false if failure
     */
    public function recordRequestLeaveGroupEvent($uid, $gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REQUEST_LEAVE_GROUP;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['uid'] = $uid;
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record certify leave group event (ETID_CERTIFY_LEAVE_GROUP: 48).
     *
     * @param int $uid certified leaved user id
     * @param int $gid certified leaved group id
     *
     * @return bool false if failure
     */
    public function recordCertifyLeaveGroupEvent($uid, $gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_CERTIFY_LEAVE_GROUP;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['uid'] = $uid;
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record reject leave group event (ETID_REJECT_LEAVE_GROUP: 49).
     *
     * @param int $uid rejected leaveed user id
     * @param int $gid rejected leaveed group id
     *
     * @return bool false if failure
     */
    public function recordRejectLeaveGroupEvent($uid, $gid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REJECT_LEAVE_GROUP;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['uid'] = $uid;
        $obj['groupid'] = $gid;

        return $this->insert($obj);
    }

    /**
     * record request public item withdrawal event (ETID_REQUEST_PUBLIC_ITEM_WITHDRAWAL: 50).
     *
     * @param int $item_id  requested item withdrawal id
     * @param int $index_id requested index withdrawal id
     *
     * @return bool false if failure
     */
    public function recordRequestItemWithdrawalEvent($item_id, $index_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REQUEST_PUBLIC_ITEM_WITHDRAWAL;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;
        $obj['index_id'] = $index_id;

        return $this->insert($obj);
    }

    /**
     * record certify public item withdrawal event (ETID_CERTIFY_PUBLIC_ITEM_WITHDRAWAL: 51).
     *
     * @param int $item_id  certified item withdrawal id
     * @param int $index_id certified index withdrawal id
     *
     * @return bool false if failure
     */
    public function recordCertifyItemWithdrawalEvent($item_id, $index_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_CERTIFY_PUBLIC_ITEM_WITHDRAWAL;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;
        $obj['index_id'] = $index_id;

        return $this->insert($obj);
    }

    /**
     * record reject public item withdrawal event (ETID_REJECT_PUBLIC_ITEM_WITHDRAWAL: 52).
     *
     * @param int $item_id  rejected item withdrawal id
     * @param int $index_id rejected index withdrawal id
     *
     * @return bool false if failure
     */
    public function recordRejectItemWithdrawalEvent($item_id, $index_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REJECT_PUBLIC_ITEM_WITHDRAWAL;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;
        $obj['index_id'] = $index_id;

        return $this->insert($obj);
    }

    /**
     * record request group item event (ETID_REQUEST_GROUP_ITEM: 53).
     *
     * @param int $item_id  requested item id
     * @param int $index_id requested index id
     *
     * @return bool false if failure
     */
    public function recordRequestGroupItemEvent($item_id, $index_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REQUEST_GROUP_ITEM;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;
        $obj['index_id'] = $index_id;

        return $this->insert($obj);
    }

    /**
     * record request group item withdrawal event (ETID_REQUEST_GROUP_ITEM_WITHDRAWAL: 54).
     *
     * @param int $item_id  requested item withdrawal id
     * @param int $index_id requested index withdrawal id
     *
     * @return bool false if failure
     */
    public function recordRequestGroupItemWithdrawalEvent($item_id, $index_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REQUEST_GROUP_ITEM_WITHDRAWAL;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;
        $obj['index_id'] = $index_id;

        return $this->insert($obj);
    }

    /**
     * record certify group item withdrawal event (ETID_CERTIFY_GROUP_ITEM_WITHDRAWAL: 55).
     *
     * @param int $item_id  certified item withdrawal id
     * @param int $index_id certified index withdrawal id
     *
     * @return bool false if failure
     */
    public function recordCertifyGroupItemWithdrawalEvent($item_id, $index_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_CERTIFY_GROUP_ITEM_WITHDRAWAL;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;
        $obj['index_id'] = $index_id;

        return $this->insert($obj);
    }

    /**
     * record reject group item withdrawal event (ETID_REJECT_GROUP_ITEM_WITHDRAWAL: 56).
     *
     * @param int $item_id  rejected item withdrawal id
     * @param int $index_id rejected index withdrawal id
     *
     * @return bool false if failure
     */
    public function recordRejectGroupItemWithdrawalEvent($item_id, $index_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_REJECT_GROUP_ITEM_WITHDRAWAL;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;
        $obj['index_id'] = $index_id;

        return $this->insert($obj);
    }

    /**
     * record delete item owner event (ETID_DELETE_ITEM_OWNER: 57).
     *
     * @param int $item_id  delete transferred item id
     * @param int $index_id delete transferred index id
     * @param int $to_uid   delete transferred to user id
     *
     * @return bool false if failure
     */
    public function recordDeleteItemUserEvent($item_id, $uid)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_DELETE_ITEM_OWNER;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['item_id'] = $item_id;
        $obj['uid'] = $uid;

        return $this->insert($obj, true);
    }

    /**
     * record move index event (ETID_MOVE_INDEX: 58).
     *
     * @param int $index_id move index id
     *
     * @return bool false if failure
     */
    public function recordMoveIndexEvent($index_id)
    {
        $obj = $this->create();
        $obj['event_type_id'] = ETID_MOVE_INDEX;
        $obj['exec_uid'] = $this->getExecUid();
        $obj['index_id'] = $index_id;

        return $this->insert($obj);
    }
}
