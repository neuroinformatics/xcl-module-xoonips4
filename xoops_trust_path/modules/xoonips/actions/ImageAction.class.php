<?php

use Xoonips\Core\CacheUtils;
use Xoonips\Core\Functions;
use Xoonips\Core\ImageUtils;

/**
 * image action.
 */
class Xoonips_ImageAction extends Xoonips_AbstractAction
{
    const THUMBNAIL_MAX_WIDTH = 120;
    const THUMBNAIL_MAX_HEIGHT = 120;

    /**
     * image file path.
     *
     * @var string
     */
    protected $mImageFilePath = '';

    /**
     * image file name.
     *
     * @var string
     */
    protected $mImageFileName = '';

    /**
     * flag for thumbnail.
     *
     * @var bool
     */
    protected $mIsThumbnail = false;

    /**
     * get id.
     *
     * @return string
     */
    protected function _getId()
    {
        $req = $this->mRoot->mContext->mRequest;
        $dataId = $req->getRequest(_REQUESTED_DATA_ID);
        if (isset($_SERVER['PATH_INFO']) && preg_match('/^\/image\/(.+)$/', $_SERVER['PATH_INFO'], $matches)) {
            $dataId = $matches[1];
            if (preg_match('/[[:cntrl:]]/', $dataId)) {
                return '';
            }
            if (preg_match('/\.\./', $dataId)) {
                return '';
            }
        }
        if (!isset($dataId)) {
            $dataId = $req->getRequest('file');
        }

        return isset($dataId) ? trim($dataId) : '';
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
        $fname = $this->_getId();
        if (empty($fname)) {
            return $this->_getFrameViewStatus('ERROR');
        }
        if (preg_match('/^group\/(\d+)\/.+$/', $fname, $matches)) {
            // group icon mode
            $fpath = sprintf('%s/uploads/%s/group/%u', XOOPS_ROOT_PATH, $dirname, $matches[1]);
            $this->mImageFilePath = $fpath;
            $this->mImageFileName = basename($fpath);

            return $this->_getFrameViewStatus('SUCCESS');
        }
        if (preg_match('/^index\/(\d+)\/.+$/', $fname, $matches)) {
            // index icon mode
            $index_upload_dir = Functions::getXoonipsConfig($dirname, 'index_upload_dir');
            $fpath = sprintf('%s/index/%u', $index_upload_dir, $matches[1]);
            $this->mImageFilePath = $fpath;
            $this->mImageFileName = basename($fpath);

            return $this->_getFrameViewStatus('SUCCESS');
        }
        if (preg_match('/^(thumbnail|file)\/(\d+)\/.+$/', $fname, $matches)) {
            // preview image
            $mode = $matches[1];
            $fileId = $matches[2];
            $upload_dir = Functions::getXoonipsConfig($dirname, 'upload_dir');
            $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $dirname, $trustDirname);
            $fileInfo = $fileBean->getFile($fileId);
            if (!$fileInfo) {
                return $this->_getFrameViewStatus('ERROR');
            }
            $itemId = isset($fileInfo['item_id']) ? intval($fileInfo['item_id']) : 0;
            if (0 == $itemId) {
                $fpath = sprintf('%s/%u', $upload_dir, $fileId);
            } else {
                $fpath = sprintf('%s/item/%u/%u', $upload_dir, $itemId, $fileId);
            }
            if ('thumbnail' == $mode) {
                $this->mIsThumbnail = true;
            }
            $this->mImageFilePath = $fpath;
            $this->mImageFileName = $fileInfo['original_file_name'];

            return $this->_getFrameViewStatus('SUCCESS');
        }
        // normal mode
        $module_path = XOOPS_MODULE_PATH.'/'.$this->mAsset->mDirname;
        $trust_path = XOOPS_TRUST_PATH.'/modules/'.$this->mAsset->mTrustDirname;
        if (!file_exists($fpath = $module_path.'/images/'.$fname)) {
            if (!file_exists($fpath = $trust_path.'/images/'.$fname)) {
                return $this->_getFrameViewStatus('ERROR');
            }
        }
        $size = @getimagesize($fpath);
        if (false === $size) {
            return $this->_getFrameViewStatus('ERROR');
        }
        $this->mImageFilePath = $fpath;
        $this->mImageFileName = basename($fpath);

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
        if ($this->mIsThumbnail) {
            ImageUtils::showThumbnail($this->mImageFilePath, $this->mImageFileName, self::THUMBNAIL_MAX_WIDTH, self::THUMBNAIL_MAX_HEIGHT);
        }
        ImageUtils::showImage($this->mImageFilePath, $this->mImageFileName);
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
