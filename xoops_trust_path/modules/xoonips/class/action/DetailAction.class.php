<?php

require_once dirname(dirname(__FILE__)).'/core/Item.class.php';
require_once dirname(dirname(__FILE__)).'/core/ActionBase.class.php';
require_once dirname(dirname(__FILE__)).'/XmlItemExport.class.php';

class Xoonips_DetailAction extends Xoonips_ActionBase
{
    protected function doInit(&$request, &$response)
    {
        $bean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        $itemId = $request->getParameter('item_id');
        if (empty($itemId)) {
            $itemId = $bean->getItemIdBydoi($request->getParameter(XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME));
        }

        global $xoopsUser;
        $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;

        // login in user can view item check
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        if (!$itemBean->canView($itemId, $uid)) {
            redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_ITEM_CANNOT_ACCESS_ITEM);
            exit();
        }

        // update view_count
        $bean->updateViewCount($itemId);

        // get common viewdata
        $viewData = array();
        $this->getCommonViewData($itemId, $uid, $viewData);

        $token_ticket = $this->createToken($this->modulePrefix('detail'));
        $viewData['token_ticket'] = $token_ticket;

        $buttonVisible = array();
        if (is_object($xoopsUser)) {
            $buttonVisible = $this->setButtonVisible($itemId, $uid);
        } else {
            $buttonVisible['item_edit'] = false;
            $buttonVisible['users_edit'] = false;
            $buttonVisible['item_delete'] = false;
            $buttonVisible['accept_certify'] = false;
            $buttonVisible['index_edit'] = false;
            $buttonVisible['item_export'] = false;
        }
        $viewData['buttonVisible'] = $buttonVisible;
        $viewData['isPrintPage'] = false;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $response->setViewData($viewData);
        $response->setForward('init_success');
        // insert event log
        $this->log->recordViewItemEvent($itemId);

        return true;
    }

    protected function doPrint(&$request, &$response)
    {
        $itemId = $request->getParameter('item_id');

        global $xoopsUser;
        $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;

        // login in user can view item check
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        if (!$itemBean->canView($itemId, $uid)) {
            redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_ITEM_CANNOT_ACCESS_ITEM);
            exit();
        }

        // get common viewdata
        $viewData = array();
        $this->getCommonViewData($itemId, $uid, $viewData);

        $buttonVisible = array();
        $buttonVisible['item_edit'] = false;
        $buttonVisible['users_edit'] = false;
        $buttonVisible['item_delete'] = false;
        $buttonVisible['accept_certify'] = false;
        $buttonVisible['index_edit'] = false;
        $viewData['buttonVisible'] = $buttonVisible;
        $viewData['isPrintPage'] = true;
        $response->setViewData($viewData);
        $response->setForward('init_success');

        return true;
    }

    private function getCommonViewData($itemId, $uid, &$viewData)
    {
        // get item list link index id
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexId = $indexBean->getItemlistLinkIndex($uid);

        $breadcrumbs = array(
        array(
                'name' => _MD_XOONIPS_ITEM_LISTING_ITEM,
                'url' => 'list.php?index_id='.$indexId,
        ),
        array(
                'name' => _MD_XOONIPS_ITEM_DETAIL_ITEM_TITLE,
        ),
        );

        $bean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        $result = $bean->getItemBasicInfo($itemId);
        $itemtypeId = $result['item_type_id'];
        $itemtype = new Xoonips_Item($itemtypeId, $this->dirname, $this->trustDirname);
        $viewData['detailView'] = $itemtype->getDetailView($itemId);

        $itembean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $limit = $itembean->getDownloadLimit($itemId, $itemtypeId);
        if ($limit === true && $uid == XOONIPS_UID_GUEST) {
            $download_confirmation = $this->getDownloadConfirmation($itemId, $itemtypeId, true);
        } else {
            $download_confirmation = $this->getDownloadConfirmation($itemId, $itemtypeId, false);
        }

        $viewData['item_id'] = $itemId;
        $viewData['viewed_count'] = $result['view_count'];
        $viewData['itemtype_name'] = $this->getItemtypeName($itemtypeId);
        $viewData['locked_message'] = $this->getLockedMessage($itemId);
        $viewData['download_confirmation'] = $download_confirmation;
        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
    }

    // get itemtype_name by itemtype_id
    private function getItemtypeName($itemtypeId)
    {
        $bean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $result = $bean->getItemTypeName($itemtypeId);
        if (!$result) {
            return '';
        }

        return $result;
    }

    // get item locked message
    private function getLockedMessage($itemId)
    {
        $linkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $result = $linkBean->getIndexItemLinkInfo($itemId);
        $message = '';
        if ($result) {
            foreach ($result as $link) {
                if ($link['certify_state'] == XOONIPS_CERTIFY_REQUIRED || $link['certify_state'] == XOONIPS_WITHDRAW_REQUIRED) {
                    $message = sprintf(_MD_XOONIPS_WARNING_CANNOT_EDIT_LOCKED_ITEM,
                    _MD_XOONIPS_LOCK_TYPE_STRING_CERTIFY_OR_WITHDRAW_REQUEST);
                }
            }
        }

        return $message;
    }

    // set button visible
    private function setButtonVisible($itemId, $uid)
    {
        $buttonVisible = array();
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $buttonVisible['item_edit'] = $itemBean->canItemEdit($itemId, $uid);
        $buttonVisible['users_edit'] = $itemBean->canItemUsersEdit($itemId, $uid);
        $buttonVisible['index_edit'] = $itemBean->canItemIndexEdit($itemId, $uid);
        $buttonVisible['item_delete'] = $itemBean->canItemDelete($itemId, $uid);
        $buttonVisible['accept_certify'] = $this->getAcceptCertifyVisible($itemId, $uid);
        $buttonVisible['item_export'] = $itemBean->canItemExport($itemId, $uid);

        return $buttonVisible;
    }

    // get accept certify visible
    private function getAcceptCertifyVisible($itemId, $uid)
    {
        global $xoopsDB;
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $isModerator = $userBean->isModerator($uid);
        $bean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $result = $bean->getPublicIndexItemLinkInfo($itemId);
        if ($result) {
            foreach ($result as $link) {
                if (($link['certify_state'] == XOONIPS_CERTIFY_REQUIRED || $link['certify_state'] == XOONIPS_WITHDRAW_REQUIRED) && $isModerator) {
                    return true;
                }
            }
        }
        $indexTable = $xoopsDB->prefix($this->modulePrefix('index'));
        $linkTable = $xoopsDB->prefix($this->modulePrefix('index_item_link'));
        $sql = "SELECT it.groupid FROM $linkTable lt, $indexTable it WHERE lt.index_id=it.index_id ";
        $sql .= "AND lt.item_id=$itemId AND it.open_level=2 AND (lt.certify_state=1 OR lt.certify_state=3)";
        $ret = $xoopsDB->queryF($sql);
        if ($ret) {
            while ($row = $xoopsDB->fetchArray($ret)) {
                if ($userBean->isGroupManager($row['groupid'], $uid)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getDownloadConfirmation($item_id, $itemtypeId, $guestUser)
    {
        $bean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $attachment_dl_notify = '0';
        if (!$guestUser) {
            $attachment_dl_notify = $bean->getDownloadNotify($item_id, $itemtypeId);
        }
        $use_license = false;
        $use_cc = '0';
        $rights = '';
        $item_rights = $bean->getRights($item_id, $itemtypeId);
        if ($item_rights === false) {
        } else {
            $use_license = true;
            if (strlen($item_rights) >= 4) {
                $rightsValue = explode(',', $item_rights);
                $use_cc = substr($rightsValue[0], 0, 1);
                $cc_commercial_use = substr($rightsValue[0], 1, 1);
                $cc_modification = substr($rightsValue[0], 2, 1);
                $rights = (strlen($item_rights) > strlen($rightsValue[0]) + 1) ? substr($item_rights, strlen($rightsValue[0]) + 1) : '';
                $cc_license = Xoonips_Utils::getCcLicense($cc_commercial_use, $cc_modification);
            }
        }

        if ($attachment_dl_notify == '0' && !$use_license) {
            return '';
        }

        $files = $this->getFileInfo($item_id);
        if ($files == false || count($files) == 0) {
            return '';
        }

        $ar = array();
        foreach ($files as $file) {
            list($fileID, $fileName, $fileSize, $mimeType, $timestamp) = $file;
            if ($fileSize >= 1024 * 1024) {
                $fileSizeStr = sprintf('%01.1f MB', $fileSize / (1024 * 1024));
            } elseif ($fileSize >= 1024) {
                $fileSizeStr = sprintf('%01.1f KB', $fileSize / 1024);
            } else {
                $fileSizeStr = sprintf('%d bytes', $fileSize);
            }
            $ar[] = array(
                'fileID' => $fileID,
                'fileName' => $fileName,
                'fileSizeStr' => $fileSizeStr,
                'mimeType' => $mimeType,
                'lastUpdated' => $timestamp,
            );
        }

        global $xoopsTpl;
        $xoopsTpl->assign('dirname', $this->dirname);
        $xoopsTpl->assign('files', $ar);
        $xoopsTpl->assign('use_license', $use_license);
        $xoopsTpl->assign('attachment_dl_notify', $attachment_dl_notify);
        $xoopsTpl->assign('use_cc', $use_cc);
        $xoopsTpl->assign('cc_license', $cc_license);
        $xoopsTpl->assign('rights', $rights);
        $url = XOOPS_URL.'/modules/'.$this->dirname.'/download.php';
        $xoopsTpl->assign('download_url', $url);

        return $xoopsTpl->fetch('db:'.$this->dirname.'_detail_download_confirm.html');
    }

    private function getFileInfo($item_id)
    {
        global $xoopsDB;
        $sql = 'select file_id, original_file_name, file_size, mime_type, timestamp from '
        .$xoopsDB->prefix($this->modulePrefix('item_file'))." where item_id = $item_id ";
        $result = $xoopsDB->query($sql);
        if ($result == false) {
            return false;
        }
        $files = array();
        while (false != ($row = $xoopsDB->fetchRow($result))) {
            $files[] = $row;
        }

        return $files;
    }

    protected function doExport(&$request, &$response)
    {

        // get requests
        $items = array();
        $items[] = $request->getParameter('item_id');

        // do export
        $xmlexport = new XmlItemExport();
        $xmlexport->export_zip($items);
    }
}
