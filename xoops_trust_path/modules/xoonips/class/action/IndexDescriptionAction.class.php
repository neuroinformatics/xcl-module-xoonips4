<?php

use Xoonips\Core\Functions;

require_once dirname(dirname(__FILE__)).'/core/ActionBase.class.php';

class Xoonips_IndexDescriptionAction extends Xoonips_ActionBase
{
    const PRIVATE_INDEX = 'Private';

    protected function doInit(&$request, &$response)
    {
        global $xoopsUser;
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $viewData = array();
        $indexId = $request->getParameter('index_id');
        $uid = $xoopsUser->getVar('uid');
        if ($indexId == null) {
            $index = $indexBean->getPrivateIndex($uid);
            $indexId = $index['index_id'];
        } else {
            $index = $indexBean->getIndex($indexId);
            if (!$indexBean->checkWriteRight($indexId, $uid)) {
                // User doesn't have the right to write.
                $response->setSystemError(_NOPERM);

                return false;
            }
        }

        $breadcrumbs = array(
            array(
                'name' => _MD_XOONIPS_INDEX_DETAILED_DESCRIPTION,
            ),
        );

        // get index full path
        $fullpathInfo = $indexBean->getFullPathIndexes($indexId);
        $fullPathIndexes = array();
        foreach ($fullpathInfo as $index) {
            if ($index['parent_index_id'] == 1 && $index['open_level'] == XOONIPS_OL_PRIVATE) {
                $index['html_title'] = 'Private';
            }
            $fullPathIndexes[] = $index;
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
        if ($index['parent_index_id'] == XOONIPS_IID_ROOT) {
            $root_dir = true;
            if (!empty($index['uid'])) {
                $index['title'] = self::PRIVATE_INDEX;
            }
        }

        // back
        $back = $request->getParameter('back');
        if (empty($back)) {
            $back = 'list.php?index_id='.$indexId;
        } else {
            $back = 'editindex.php?index_id='.$back;
        }

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
        $response->setViewData($viewData);
        $response->setViewDataByKey('index_path', $fullPathIndexes);
        $response->setForward('init_success');

        return true;
    }

    protected function doUpdate(&$request, &$response)
    {
        $viewData = array();

        if (!$this->validateToken(($this->modulePrefix('index_description')))) {
            $response->setSystemError('Ticket error');

            return false;
        }

        $index_id = $request->getParameter('index_id');
        $title = $request->getParameter('title');
        $show_thumbnail = $request->getParameter('show_thumbnail');
        $detailed_title = $request->getParameter('detailed_title');
        $detailed_description = $request->getParameter('detailed_description');

        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $index = $indexBean->getIndex($index_id);

        $index_upload_dir = Functions::getXoonipsConfig($this->dirname, 'index_upload_dir');
        $upload_path = $index_upload_dir.'/index';

        //get upload icon information
        $file = $request->getFile('filepath');
        if (!empty($file)) {
            $index['icon'] = $file['name'];
            $index['mime_type'] = $file['type'];
        } elseif ($show_thumbnail == 2) {
            $index['icon'] = null;
            $index['mime_type'] = null;
        }

        // start transaction
        $this->startTransaction();

        if ($index['parent_index_id'] != XOONIPS_IID_ROOT) {
            if (empty($title)) {
                $viewData = array();
                if (empty($viewData['redirect_msg'])) {
                    $viewData['redirect_msg'] = _MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_TITLE_ERROR;
                }
                $viewData['url'] = 'indexdescription.php?index_id='.$index_id;
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
            $viewData = array();
            if (empty($viewData['redirect_msg'])) {
                $viewData['redirect_msg'] = _MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_UPDATE_ERROR;
            }
            $viewData['url'] = 'indexdescription.php?index_id='.$index_id;
            $response->setForward('update_error');
            $response->setViewData($viewData);
            $this->rollbackTransaction();

            return true;
        }

        if ($show_thumbnail == 2) {
            $uploadfile = $upload_path.'/'.$index_id;
            unlink($uploadfile);
        }

        //upload index icon
        if (!empty($file)) {
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            $uploadfile = $upload_path.'/'.$index_id;
            if (!move_uploaded_file($file['tmp_name'], $uploadfile)) {
                $response->setSystemError(_MD_XOONIPS_ERROR_INDEX_ICON_UPLOAD);

                return false;
            }
        }

        if (empty($viewData['redirect_msg'])) {
            $viewData['redirect_msg'] = _MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_UPDATE_SUCCESS;
        }
        $viewData['url'] = 'list.php?index_id='.$index_id;
        $response->setViewData($viewData);
        $response->setForward('update_success');

        return true;
    }

    protected function doDelete(&$request, &$response)
    {
        $viewData = array();

        $index_id = $request->getParameter('index_id');
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $index = $indexBean->getIndex($index_id);

        $index['detailed_title'] = null;
        $index['icon'] = null;
        $index['mime_type'] = null;
        $index['detailed_description'] = null;

        // start transaction
        $this->startTransaction();

        if (!$indexBean->updateIndexDetailed($index)) {
            $viewData = array();
            if (empty($viewData['redirect_msg'])) {
                $viewData['redirect_msg'] = _MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_DELETE_ERROR;
            }
            $viewData['url'] = 'indexdescription.php?index_id='.$index_id;
            $response->setForward('delete_error');
            $response->setViewData($viewData);
            $this->rollbackTransaction();

            return true;
        }

        $index_upload_dir = Functions::getXoonipsConfig($this->dirname, 'index_upload_dir');
        $uploadfile = $index_upload_dir.'/index/'.$index_id;
        if (file_exists($uploadfile)) {
            unlink($uploadfile);
        }

        if (empty($viewData['redirect_msg'])) {
            $viewData['redirect_msg'] = _MD_XOONIPS_MESSAGE_INDEX_DESCRIPTION_DELETE_SUCCESS;
        }
        $viewData['url'] = 'list.php?index_id='.$index_id;
        $response->setViewData($viewData);
        $response->setForward('delete_success');

        return true;
    }
}
