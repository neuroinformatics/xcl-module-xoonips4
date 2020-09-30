<?php

use Xoonips\Core\Functions;
use Xoonips\Core\XoopsUtils;

require_once dirname(__DIR__).'/core/ActionBase.class.php';

class Xoonips_IndexDescriptionAction extends Xoonips_ActionBase
{
    const PRIVATE_INDEX = 'Private';

    protected function doInit(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexId = intval($request->getParameter('index_id'));
        $index = $indexBean->getIndex($indexId);
        if (empty($index) || !$indexBean->checkWriteRight($indexId, $uid)) {
            // User doesn't have the right to write.
            $response->setSystemError(_NOPERM);

            return false;
        }

        $breadcrumbs = [
            [
                'name' => _MD_XOONIPS_INDEX_DETAILED_DESCRIPTION,
            ],
        ];

        // get index full path
        $fullpathInfo = $indexBean->getFullPathIndexes($indexId);
        $fullPathIndexes = [];
        foreach ($fullpathInfo as $index2) {
            if (1 == $index2['parent_index_id'] && XOONIPS_OL_PRIVATE == $index2['open_level']) {
                $index['html_title'] = 'Private';
            }
            $fullPathIndexes[] = $index2;
        }

        // get icon
        $thumbnail = sprintf('%s/modules/%s/image.php/index/%u/%s', XOOPS_URL, $this->dirname, $indexId, $index['icon']);
        $index_upload_dir = Functions::getXoonipsConfig($this->dirname, 'index_upload_dir');

        $file_path = $index_upload_dir.'/index/'.$indexId;
        $showThumbnail = 0;
        if (file_exists($file_path)) {
            $showThumbnail = 1;
        }

        // root dir check
        $root_dir = false;
        if (XOONIPS_IID_ROOT == $index['parent_index_id']) {
            $root_dir = true;
            if (!empty($index['uid'])) {
                $index['title'] = self::PRIVATE_INDEX;
            }
        }

        // back
        $back = intval($request->getParameter('back'));
        if (0 != $back) {
            $back = Functions::getItemListUrl($this->dirname).'?index_id='.$indexId;
        } else {
            $back = 'editindex.php?index_id='.$back;
        }

        $viewData = [];
        $viewData['index_id'] = $indexId;
        $viewData['root_dir'] = $root_dir;
        $viewData['title'] = $index['title'];
        $viewData['detailed_title'] = $index['detailed_title'];
        $viewData['detailed_description'] = $index['detailed_description'];
        $viewData['icon'] = $index['icon'];
        $viewData['thumbnail'] = $thumbnail;
        $viewData['show_thumbnail'] = $showThumbnail;
        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['back'] = $back;
        $viewData['error_message'] = false;
        $token_ticket = $this->createToken($this->modulePrefix('index_description'));
        $viewData['token_ticket'] = $token_ticket;
        $viewData['dirname'] = $this->dirname;
        $viewData['itemListUrl'] = Functions::getItemListUrl($this->dirname);
        $response->setViewData($viewData);
        $response->setViewDataByKey('index_path', $fullPathIndexes);
        $response->setForward('init_success');

        return true;
    }

    protected function doUpdate(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexId = intval($request->getParameter('index_id'));
        $index = $indexBean->getIndex($indexId);
        if (empty($index) || !$indexBean->checkWriteRight($indexId, $uid)) {
            // User doesn't have the right to write.
            $response->setSystemError(_NOPERM);

            return false;
        }

        if (!$this->validateToken(($this->modulePrefix('index_description')))) {
            $response->setSystemError('Ticket error');

            return false;
        }

        $title = $request->getParameter('title');
        $show_thumbnail = $request->getParameter('show_thumbnail');
        $detailed_title = $request->getParameter('detailed_title');
        $detailed_description = $request->getParameter('detailed_description');

        $oldTitle = $index['title'];

        $index_upload_dir = Functions::getXoonipsConfig($this->dirname, 'index_upload_dir');
        $upload_path = $index_upload_dir.'/index';

        //get upload icon information
        $file = $request->getFile('filepath');
        if (!empty($file)) {
            $index['icon'] = $file['name'];
            $index['mime_type'] = $file['type'];
        } elseif (2 == $show_thumbnail) {
            $index['icon'] = null;
            $index['mime_type'] = null;
        }

        // start transaction
        $this->startTransaction();

        $viewData = [];
        if (XOONIPS_IID_ROOT != $index['parent_index_id']) {
            if (empty($title)) {
                $viewData['redirect_msg'] = _MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_TITLE_ERROR;
                $viewData['url'] = 'indexdescription.php?index_id='.$indexId;
                $response->setForward('update_error');
                $response->setViewData($viewData);
                $this->rollbackTransaction();

                return true;
            } else {
                $index['title'] = $title;
            }
        }

        if (!empty($detailed_title)) {
            $index['detailed_title'] = $detailed_title;
        } else {
            $index['detailed_title'] = null;
        }

        if (!empty($detailed_description)) {
            $index['detailed_description'] = $detailed_description;
        } else {
            $index['detailed_description'] = null;
        }

        if (!$indexBean->updateIndexDetailed($index)) {
            $viewData['redirect_msg'] = _MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_UPDATE_ERROR;
            $viewData['url'] = 'indexdescription.php?index_id='.$indexId;
            $response->setForward('update_error');
            $response->setViewData($viewData);
            $this->rollbackTransaction();

            return true;
        }

        if (2 == $show_thumbnail) {
            $uploadfile = $upload_path.'/'.$indexId;
            @unlink($uploadfile);
        }

        //upload index icon
        if (!empty($file)) {
            if (!file_exists($upload_path)) {
                @mkdir($upload_path, 0777, true);
            }
            $uploadfile = $upload_path.'/'.$indexId;
            if (!move_uploaded_file($file['tmp_name'], $uploadfile)) {
                $response->setSystemError(_MD_XOONIPS_ERROR_INDEX_ICON_UPLOAD);

                return false;
            }
        }

        if ($oldTitle != $title) {
            // write log
            $this->log->recordUpdateIndexEvent($indexId);
            // event
            $this->notification->afterUserIndexRenamed($this->notification->beforeUserIndexRenamed($indexId));
        }

        if (empty($viewData['redirect_msg'])) {
            $viewData['redirect_msg'] = _MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_UPDATE_SUCCESS;
        }
        $viewData['url'] = Functions::getItemListUrl($this->dirname).'?index_id='.$indexId;
        $response->setViewData($viewData);
        $response->setForward('update_success');

        return true;
    }

    protected function doDelete(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $indexId = intval($request->getParameter('index_id'));
        $index = $indexBean->getIndex($indexId);
        if (empty($index) || !$indexBean->checkWriteRight($indexId, $uid)) {
            // User doesn't have the right to write.
            $response->setSystemError(_NOPERM);

            return false;
        }

        $index['detailed_title'] = null;
        $index['icon'] = null;
        $index['mime_type'] = null;
        $index['detailed_description'] = null;

        // start transaction
        $this->startTransaction();

        $viewData = [];
        if (!$indexBean->updateIndexDetailed($index)) {
            $viewData['redirect_msg'] = _MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_DELETE_ERROR;
            $viewData['url'] = 'indexdescription.php?index_id='.$indexId;
            $response->setForward('delete_error');
            $response->setViewData($viewData);
            $this->rollbackTransaction();

            return true;
        }

        $index_upload_dir = Functions::getXoonipsConfig($this->dirname, 'index_upload_dir');
        $uploadfile = $index_upload_dir.'/index/'.$indexId;
        if (file_exists($uploadfile)) {
            @unlink($uploadfile);
        }

        $viewData['redirect_msg'] = _MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_DELETE_SUCCESS;
        $viewData['url'] = Functions::getItemListUrl($this->dirname).'?index_id='.$indexId;
        $response->setViewData($viewData);
        $response->setForward('delete_success');

        return true;
    }
}
