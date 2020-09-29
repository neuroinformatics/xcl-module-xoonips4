<?php

use Xoonips\Core\CacheUtils;
use Xoonips\Core\Functions;
use Xoonips\Core\JoinCriteria;
use Xoonips\Core\XoopsUtils;

require_once dirname(__DIR__).'/core/Item.class.php';
require_once dirname(__DIR__).'/core/ActionBase.class.php';
require_once dirname(__DIR__).'/XmlItemExport.class.php';

class Xoonips_DetailAction extends Xoonips_ActionBase
{
    protected function doInit(&$request, &$response)
    {
        $itemId = intval($request->getParameter('item_id'));
        $uid = XoopsUtils::getUid();

        $bean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        if (0 == $itemId) {
            $itemId = intval($bean->getItemIdBydoi($request->getParameter(XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME)));
        }

        // login in user can view item check
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        if (!$itemBean->canView($itemId, $uid)) {
            redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_ITEM_CANNOT_ACCESS_ITEM);
            exit();
        }

        // update view_count
        $bean->updateViewCount($itemId);

        // get common viewdata
        $viewData = [];
        $this->getCommonViewData($itemId, $uid, $viewData);

        $token_ticket = $this->createToken($this->modulePrefix('detail'));
        $viewData['token_ticket'] = $token_ticket;

        $buttonVisible = [];
        if (XoopsUtils::UID_GUEST != $uid) {
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
        $itemId = intval($request->getParameter('item_id'));

        $uid = XoopsUtils::getUid();

        // login in user can view item check
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        if (!$itemBean->canView($itemId, $uid)) {
            redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_ITEM_CANNOT_ACCESS_ITEM);
            exit();
        }

        // get common viewdata
        $viewData = [];
        $this->getCommonViewData($itemId, $uid, $viewData);

        $buttonVisible = [];
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

    protected function doExport(&$request, &$response)
    {
        // get requests
        $items = [];
        $items[] = intval($request->getParameter('item_id'));

        // access check
        $uid = XoopsUtils::getUid();
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        if (!$itemBean->canItemExport($itemId, $uid)) {
            CacheUtils::errorExit(403);
        }

        // do export
        $xmlexport = new XmlItemExport();
        $xmlexport->export_zip($items);
    }

    private function getCommonViewData($itemId, $uid, &$viewData)
    {
        // get item list link index id
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexId = $indexBean->getItemlistLinkIndex($uid);

        $breadcrumbs = [
            [
                'name' => _MD_XOONIPS_ITEM_LISTING_ITEM,
                'url' => Functions::getItemListUrl($this->dirname).'?index_id='.$indexId,
            ],
            [
                'name' => _MD_XOONIPS_ITEM_DETAIL_ITEM_TITLE,
            ],
        ];

        $bean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        $result = $bean->getItemBasicInfo($itemId);
        $itemtypeId = $result['item_type_id'];
        $itemtype = new Xoonips_Item($itemtypeId, $this->dirname, $this->trustDirname);
        $viewData['detailView'] = $itemtype->getDetailView($itemId);

        $itembean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $limit = $itembean->getDownloadLimit($itemId, $itemtypeId);
        if (true === $limit && XOONIPS_UID_GUEST == $uid) {
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
                if (XOONIPS_CERTIFY_REQUIRED == $link['certify_state'] || XOONIPS_WITHDRAW_REQUIRED == $link['certify_state']) {
                    $message = sprintf(_MD_XOONIPS_WARNING_CANNOT_EDIT_LOCKED_ITEM, _MD_XOONIPS_LOCK_TYPE_STRING_CERTIFY_OR_WITHDRAW_REQUEST);
                }
            }
        }

        return $message;
    }

    // set button visible
    private function setButtonVisible($itemId, $uid)
    {
        $buttonVisible = [];
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $buttonVisible['item_edit'] = $itemBean->canItemEdit($itemId, $uid);
        $buttonVisible['users_edit'] = $itemBean->canItemUsersEdit($itemId, $uid);
        $buttonVisible['index_edit'] = $itemBean->canItemIndexEdit($itemId, $uid);
        $buttonVisible['item_delete'] = $itemBean->canItemDelete($itemId, $uid);
        $buttonVisible['accept_certify'] = $this->isCertifyRequiredItem($itemId, $uid);
        $buttonVisible['item_export'] = $itemBean->canItemExport($itemId, $uid);

        return $buttonVisible;
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
        if (false === $item_rights) {
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

        if ('0' == $attachment_dl_notify && !$use_license) {
            return '';
        }

        $files = $this->getFileInfo($item_id);
        if (empty($files)) {
            return '';
        }

        global $xoopsTpl;
        $xoopsTpl->assign('dirname', $this->dirname);
        $xoopsTpl->assign('files', $files);
        $xoopsTpl->assign('use_license', $use_license);
        $xoopsTpl->assign('attachment_dl_notify', $attachment_dl_notify);
        $xoopsTpl->assign('use_cc', $use_cc);
        $xoopsTpl->assign('cc_license', $cc_license);
        $xoopsTpl->assign('rights', $rights);
        $url = XOOPS_URL.'/modules/'.$this->dirname.'/download.php';
        $xoopsTpl->assign('download_url', $url);

        return $xoopsTpl->fetch('db:'.$this->dirname.'_detail_download_confirm.html');
    }

    /**
     * get file information by item id.
     *
     * @param int $itemId
     * @param int $uid
     *
     * @return bool
     */
    private function isCertifyRequiredItem($itemId, $uid)
    {
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $isModerator = $userBean->isModerator($uid);

        $groupsBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname, $this->trustDirname);
        $adminGids = $groupsBean->getAdminGroupIds($uid);

        if (!$isModerator && empty($adminGids)) {
            return false;
        }

        $indexHandler = Functions::getXoonipsHandler('IndexObject', $this->dirname);
        $indexItemLinkHandler = Functions::getXoonipsHandler('IndexItemLinkObject', $this->dirname);
        $indexTable = $indexHandler->getTable();
        $indexItemLinkTable = $indexItemLinkHandler->getTable();
        $join = new JoinCriteria('INNER', $indexItemLinkTable, 'index_id', $indexTable, 'index_id');
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('item_id', $itemId, '=', $indexItemLinkTable));
        $criteria->add(new Criteria('certify_state', [$indexItemLinkHandler::CERTIFY_STATE_CERTIFY_REQUIRED, $indexItemLinkHandler::CERTIFY_STATE_WITHDRAW_REQUIRED], 'IN', $indexItemLinkTable));
        $criteriaIndexType = new CriteriaCompo();
        if ($isModerator) {
            $criteriaModerator = new CriteriaCompo();
            $criteriaModerator->add(new Criteria('open_level', $indexHandler::OPEN_LEVEL_PUBLIC, '=', $indexTable));
            $criteriaIndexType->add($criteriaModerator);
        }
        if (!empty($adminGids)) {
            $criteriaGroup = new CriteriaCompo();
            $criteriaGroup->add(new Criteria('open_level', $indexHandler::OPEN_LEVEL_GROUP, '=', $indexTable));
            $criteriaGroup->add(new Criteria('groupid', $adminGids, 'IN', $indexTable));
            $criteriaIndexType->add($criteriaGroup, 'OR');
        }
        $criteria->add($criteriaIndexType);

        return $indexHandler->getCount($criteria, $join) > 0;
    }

    /**
     * get file information by item id.
     *
     * @param int $itemId
     *
     * @return array
     */
    private function getFileInfo($itemId)
    {
        $files = [];

        $itemFileHandler = Functions::getXoonipsHandler('ItemFileObject', $this->dirname);
        $criteria = new Criteria('item_id', $itemId);
        if ($res = $itemFileHandler->open($criteria)) {
            while ($obj = $itemFileHandler->getNext($res)) {
                $fileSize = $obj->get('file_size');
                if ($fileSize >= 1024 * 1024) {
                    $fileSizeStr = sprintf('%01.1f MB', $fileSize / (1024 * 1024));
                } elseif ($fileSize >= 1024) {
                    $fileSizeStr = sprintf('%01.1f KB', $fileSize / 1024);
                } else {
                    $fileSizeStr = sprintf('%d bytes', $fileSize);
                }
                $files[] = [
                    'fileID' => $obj->get('file_id'),
                    'fileName' => $obj->get('original_file_name'),
                    'fileSizeStr' => $fileSizeStr,
                    'mimeType' => $obj->get('mime_type'),
                    'lastUpdated' => $obj->get('timestamp'),
                ];
            }
            $itemFileHandler->close($res);
        }

        return $files;
    }
}
