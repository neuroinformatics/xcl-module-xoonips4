<?php

use Xoonips\Core\CacheUtils;
use Xoonips\Core\FileUtils;
use Xoonips\Core\Functions;
use Xoonips\Core\StringUtils;
use Xoonips\Core\XoopsUtils;
use Xoonips\Core\ZipFile;

require_once dirname(__DIR__).'/class/core/Item.class.php';

/**
 * download action.
 */
class Xoonips_DownloadAction extends Xoonips_AbstractAction
{
    /**
     * item object.
     *
     * @var object
     */
    protected $mItemObj = 0;

    /**
     * item file object.
     *
     * @var object
     */
    protected $mItemFileObj = 0;

    /**
     * download file path.
     *
     * @var string
     */
    protected $mFilePath = '';

    /**
     * download file mime type.
     *
     * @var string
     */
    protected $mFileMimeType = '';

    /**
     * download file name.
     *
     * @var string
     */
    protected $mFileName = '';

    /**
     * error code.
     *
     * @var int
     */
    protected $mErrorCode = 0;

    /**
     * fetch request from url patterns:
     * - XOOPS_URL/modules/xoonips/download.php?file_id=${FILE_ID}
     * - XOOPS_URL/modules/xoonips/download.php?${IDNAME}=${ID}
     * - XOOPS_URL/modules/xoonips/download.php/${FILE_NAME}?file_id=${FILE_ID}
     * - XOOPS_URL/modules/xoonips/download.php/${ITEM_ID}/${FILE_NAME}
     * - XOOPS_URL/modules/xoonips/download.php/${ITEM_ID}/${FILE_ID}/${FILE_NAME}
     * - XOOPS_URL/modules/xoonips/download.php/${IDNAME}:${ID}/${FILE_NAME}
     * - XOOPS_URL/modules/xoonips/download.php/${IDNAME}:${ID}/${FILE_ID}/${FILE_NAME}.
     *
     * @return array/bool false if failure
     */
    protected function _fetchRequest()
    {
        $ret = array(
            'item_id' => 0,
            'item_doi' => false,
            'file_id' => 0,
            'file_name' => false,
        );
        if (array_key_exists('PATH_INFO', $_SERVER)) {
            $pathinfo = explode('/', $_SERVER['PATH_INFO']);
            if (!is_array($pathinfo) || count($pathinfo) < 2) {
                return false;
            }
            array_shift($pathinfo);
            $ret['file_name'] = array_pop($pathinfo);
            if (!empty($pathinfo)) {
                $item_str = array_shift($pathinfo);
                $item_arr = explode(':', $item_str);
                if (!is_array($item_arr)) {
                    return false;
                }
                switch (count($item_arr)) {
                case 1:
                    $ret['item_id'] = intval($item_arr[0]);
                    break;
                case 2:
                    if ($item_arr[0] != XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME) {
                        return false;
                    }
                    $ret['item_doi'] = $item_arr[1];
                    break;
                default:
                    return false;
                }
                if (!empty($pathinfo)) {
                    $ret['file_id'] = intval(array_shift($pathinfo));
                }
            }
        }
        $request = $this->mRoot->mContext->mRequest;
        $file_id = $request->getRequest('file_id');
        if (!empty($file_id)) {
            $ret['file_id'] = $file_id;
        }
        $item_doi = $request->getRequest(XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME);
        if (!empty($item_doi)) {
            $ret['item_doi'] = $item_doi;
        }

        return $ret;
    }

    /**
     * get objects by parameters.
     *
     * @param array $params
     *
     * @return bool false if failure
     */
    protected function _getObjectsByParams($params)
    {
        $itemObj = null;
        $itemFileObj = null;
        $dirname = $this->mAsset->mDirname;
        $itemHandler = Functions::getXoonipsHandler('Item', $dirname);
        $itemFileHandler = Functions::getXoonipsHandler('ItemFile', $dirname);
        if (!empty($params['item_id'])) {
            $itemObj = &$itemHandler->get($params['item_id']);
            if (!is_object($itemObj)) {
                return false;
            }
        } elseif (!empty($params['item_doi'])) {
            $itemObj = &$itemHandler->getByDoi($params['item_doi']);
            if (!is_object($itemObj)) {
                return false;
            }
        }
        if (!empty($params['file_id'])) {
            $itemFileObj = &$itemFileHandler->get($params['file_id']);
            if (!is_object($itemFileObj)) {
                return false;
            }
        } else {
            if (!is_object($itemObj)) {
                return false;
            }
            $objs = &$itemFileHandler->getObjectsForDownload($itemObj->get('item_id'), $params['file_name']);
            if (count($objs) != 1) {
                return false;
            }
            $itemFileObj = &$objs[0];
        }
        if (!is_object($itemObj)) {
            $itemObj = &$itemHandler->get($itemFileObj->get('item_id'));
            if (!is_object($itemObj)) {
                return false;
            }
        }
        if ($itemObj->get('item_id') != $itemFileObj->get('item_id')) {
            return false;
        }
        $this->mItemObj = $itemObj;
        $this->mItemFileObj = $itemFileObj;
        $this->mFilePath = $itemFileObj->getFilePath();
        $this->mFileMimeType = $itemFileObj->get('mime_type');
        $this->mFileName = empty($params['file_name']) ? $itemFileObj->get('original_file_name') : $params['file_name'];
        if (!file_exists($this->mFilePath)) {
            return false;
        }

        return true;
    }

    /**
     * create zip file.
     *
     * @return bool false if failure
     */
    protected function _createZipFile()
    {
        $dirname = $this->mAsset->mDirname;
        $trustDirname = $this->mAsset->mTrustDirname;
        $item_id = $this->mItemObj->get('item_id');
        $item_type_id = $this->mItemObj->get('item_type_id');
        $item = new Xoonips_Item($item_type_id, $dirname, $trustDirname);
        $metadata = $item->getMetaInfo($item_id);
        $metadata .= $this->mItemObj->getUrl()."\r\n";
        $metadata = StringUtils::convertEncodingToClient($metadata, _CHARSET, 'h');
        $zfpath = tempnam('/tmp', 'XooNIpsDownloadZipFile');
        if ($zfpath === false) {
            return false;
        }
        FileUtils::deleteFileOnExit($zfpath);
        $zip = new ZipFile();
        if ($zip->open($zfpath) === false) {
            return false;
        }
        $zip->add($this->mFilePath, StringUtils::convertEncodingToClientFileSystem($this->mFileName, _CHARSET));
        $zip->addFileFormString('metainfo.txt', $metadata);
        $zip->close();
        $this->mFilePath = $zfpath;
        $this->mFileMimeType = 'application/x-zip';
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $dirname, $trustDirname);
        $itemtypeName = $itemtypeBean->getItemTypeName($item_type_id);
        $this->mFileName = $itemtypeName.'_'.$item_id.'.zip';

        return true;
    }

    /**
     * delete zip file.
     *
     * @param string $zfpath
     */
    public function deleteZipFile($zfpath)
    {
        @unlink($zfpath);
    }

    /**
     * get default view.
     *
     * @return Enum
     */
    public function getDefaultView()
    {
        $dirname = $this->mAsset->mDirname;
        $trustDirname = $this->mAsset->mTrustDirname;
        $params = $this->_fetchRequest();
        if ($params === false) {
            $this->mErrorCode = 404;

            return $this->_getFrameViewStatus('ERROR');
        }
        if ($this->_getObjectsByParams($params) === false) {
            $this->mErrorCode = 404;

            return $this->_getFrameViewStatus('ERROR');
        }
        // check access permission
        $uid = XoopsUtils::getUid();
        if (!$this->mItemObj->isReadable($uid)) {
            $this->mErrorCode = 403;

            return $this->_getFrameViewStatus('ERROR');
        }
        // check download limit only registered user
        if ($uid == XOONIPS_UID_GUEST && $this->mItemObj->isDownloadLimit()) {
            $url = XOOPS_URL.'/user.php?xoops_redirect='.urlencode($this->mItemObj->getUrl());
            $this->mRoot->mController->executeForward($url);
        }
        // delegate
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $dirname, $trustDirname);
        $itemtypeName = $itemtypeBean->getItemTypeName($this->mItemObj->get('item_type_id'));
        XCube_DelegateUtils::call('Module.Xoonips.FileDownload.Prepare', $this->mItemObj->get('item_id'), $itemtypeName, $this->mItemFileObj->gets(), new XCube_Ref($this->mFilePath));
        // check compress
        $download_file_compression = Functions::getXoonipsConfig($dirname, 'download_file_compression');
        if ($download_file_compression == 'on') {
            if ($this->_createZipFile() === false) {
                $this->mErrorCode = 500;

                return $this->_getFrameViewStatus('ERROR');
            }
        }
        // notifiy to owner
        if ($this->mItemObj->isDownloadNotify()) {
            $db = &$this->mRoot->mController->getDB();
            $notification = new Xoonips_Notification($db, $dirname, $trustDirname);
            $notification->userFileDownloaded($this->mItemFileObj->get('file_id'), $uid);
        }

        return $this->_getFrameViewStatus('INDEX');
    }

    /**
     * execute.
     *
     * @return Enum
     */
    public function execute()
    {
        return $this->getDefaultView();
    }

    /**
     * execute view index.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewIndex(&$render)
    {
        $dirname = $this->mAsset->mDirname;
        $trustDirname = $this->mAsset->mTrustDirname;
        $itemHandler = Functions::getXoonipsHandler('Item', $dirname);
        $itemFileHandler = Functions::getXoonipsHandler('ItemFile', $dirname);
        // record download file event log
        $eventLogBean = Xoonips_BeanFactory::getBean('EventLogBean', $dirname, $trustDirname);
        $eventLogBean->recordDownloadFileEvent($this->mItemObj->get('item_id'), $this->mItemFileObj->get('file_id'));
        // increment dowonload count
        $download_count = $this->mItemFileObj->get('download_count') + 1;
        $this->mItemFileObj->set('download_count', $download_count);
        $itemFileHandler->insert($this->mItemFileObj, true);
        // download
        $mtime = filemtime($this->mFilePath);
        $etag = md5($this->mFilePath.filesize($this->mFilePath).$mtime);
        CacheUtils::downloadFile($mtime, $etag, $this->mFileMimeType, $this->mFilePath, $this->mFileName, _CHARSET);
    }

    /**
     * execute view error.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewError(&$render)
    {
        $constpref = '_MD_'.strtoupper($this->mAsset->mDirname);
        switch ($this->mErrorCode) {
        case 403:
            $this->mRoot->mController->executeRedirect(XOOPS_URL.'/', 3, constant($constpref.'_ITEM_CANNOT_ACCESS_ITEM'));
            break;
        case 404:
            CacheUtils::errorExit(404);
            break;
        case 500:
            CacheUtils::errorExit(500);
            break;
        }
        exit();
    }
}
