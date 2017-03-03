<?php

use Xoonips\Core\CacheUtils;
use Xoonips\Core\XoopsUtils;

/**
 * item import log download action.
 */
class Xoonips_ImportDownloadAction extends Xoonips_AbstractAction
{
    /**
     * import log file name.
     *
     * @var string
     */
    protected $mFileName = '';

    /**
     * import log object.
     *
     * @var string
     */
    protected $mObject;

    /**
     * get id.
     * - index.php?action=ImportDownload&import_id=[ID]
     * - index.php/import/[ID]/download
     * - index.php/import/[ID]/download/[FILENAME].
     *
     * @return string
     */
    protected function _getId()
    {
        $req = $this->mRoot->mContext->mRequest;
        $dataId = $req->getRequest(_REQUESTED_DATA_ID);
        if (isset($_SERVER['PATH_INFO']) && preg_match('/^\/[^\/]+\/(\d+)\/[^\/]+(?:\/([a-zA-Z0-9][a-zA-Z0-9_.-]+))?$/', $_SERVER['PATH_INFO'], $matches)) {
            $dataId = intval($matches[1]);
            if (array_key_exists(2, $matches)) {
                $this->mFileName = trim($matches[2]);
            }
        } else {
            $dataId = $req->getRequest('import_id');
        }

        return $dataId;
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
        $uid = XoopsUtils::getUid();
        if ($uid == XoopsUtils::UID_GUEST) {
            return $this->_getFrameViewStatus('ERROR');
        }
        $importId = $this->_getId();
        $handler = Xoonips_Utils::getModuleHandler('itemImportLog', $dirname);
        $this->mObject = &$handler->get($importId);
        if (!is_object($this->mObject)) {
            return $this->_getFrameViewStatus('ERROR');
        }
        if ($this->mObject->get('uid') != $uid && !XoopsUtils::isSiteAdmin($uid) && !XoopsUtils::isModuleAdmin($uid, $dirname)) {
            return $this->_getFrameViewStatus('ERROR');
        }

        return $this->_getFrameViewStatus('SUCCESS');
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
     * execute view success.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewSuccess(&$render)
    {
        $logId = $this->mObject->get('item_import_log_id');
        $mtime = $this->mObject->get('timestamp');
        if (empty($this->mFileName)) {
            $this->mFileName = 'ItemImport-'.$logId.'-'.date('YmdHis', $mtime).'.log';
        }
        $etag = md5($this->mFileName.$logId.$mtime);
        CacheUtils::downloadData($mtime, $etag, 'text/plain', $this->mObject->get('log'), $this->mFileName, _CHARSET);
    }

    /**
     * execute view error.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewError(&$render)
    {
        CacheUtils::errorExit(404);
    }
}
