<?php

use Xoonips\Core\CacheUtils;

/**
 * abstract template action.
 */
class Xoonips_AbstractTemplateAction extends Xoonips_AbstractAction
{
    /**
     * file information.
     *
     * @var array
     */
    protected $mFileInfo = [];

    /**
     * get id.
     *
     * @return string
     */
    protected function _getId()
    {
        // override
        $req = $this->mRoot->mContext->mRequest;
        $dataId = $req->getRequest(_REQUESTED_DATA_ID);
        if (isset($_SERVER['PATH_INFO']) && preg_match('/^\/([a-z0-9]+)(?:\/([a-z0-9][a-zA-Z0-9\._\-]*))?(?:\/([a-z0-9]+))?$/', $_SERVER['PATH_INFO'], $matches)) {
            if (isset($matches[2])) {
                $dataId = $matches[2];
            }
        }
        if (!isset($dataId)) {
            $dataId = $req->getRequest('file');
        }

        return isset($dataId) ? trim($dataId) : '';
    }

    /**
     * get template file type.
     *
     * @return array
     */
    protected function _getTemplateFileType()
    {
        // override
        return [
            'mime' => 'text/html',
            'extension' => '.html',
        ];
    }

    /**
     * get default view.
     *
     * @return Enum
     */
    public function getDefaultView()
    {
        $fname = $this->_getId();
        $type = $this->_getTemplateFileType();
        $pattern = sprintf('/%s$/', preg_quote($type['extension'], '/'));
        if (!preg_match($pattern, $fname)) {
            return $this->_getFrameViewStatus('ERROR');
        }
        if (!$this->_setTemplateFileInfo($fname)) {
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
        $type = $this->_getTemplateFileType();
        $render->setTemplateName($this->mFileInfo['name']);
        $renderSystem = &$this->mModule->getRenderSystem();
        $renderSystem->render($render);
        $data = $render->getResult();
        CacheUtils::outputData(false, false, $type['mime'], $data);
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

    /**
     * set template file information.
     *
     * @param string $fname
     *
     * @return bool
     */
    protected function _setTemplateFileInfo($fname)
    {
        if ($this->mModule->mAdminFlag) {
            $fpath = XOOPS_TRUST_PATH.'/modules/'.$this->mAsset->mTrustDirname.'/admin/templates/'.$fname;
            if (empty($fname) || !file_exists($fpath)) {
                return false;
            }
            $name = $fname;
            $mtime = filemtime($fpath);
        } else {
            $fpath = XOOPS_TRUST_PATH.'/modules/'.$this->mAsset->mTrustDirname.'/templates/'.$fname;
            if (empty($fname) || !file_exists($fpath)) {
                return false;
            }
            $root = &XCube_Root::getSingleton();
            $db = &$root->mController->getDB();
            $tpl_module = $this->mAsset->mDirname;
            $tpl_tplset = $root->mContext->getXoopsConfig('template_set');
            $tpl_file = sprintf('%s_%s', $tpl_module, $fname);
            $sql = sprintf('SELECT `tpl_lastmodified` FROM `%s` WHERE `tpl_module`=%s AND `tpl_tplset`=%s AND `tpl_file`=%s', $db->prefix('tplfile'), $db->quoteString($tpl_module), $db->quoteString($tpl_tplset), $db->quoteString($tpl_file));
            if (!($res = $db->query($sql))) {
                return false;
            }
            $row = $db->fetchArray($res);
            if (false === $row && 'default' != $tpl_tplset) {
                // retry to get from default tplset
                $sql = sprintf('SELECT `tpl_lastmodified` FROM `%s` WHERE `tpl_module`=%s AND `tpl_tplset`=\'default\' AND `tpl_file`=%s', $db->prefix('tplfile'), $db->quoteString($tpl_module), $db->quoteString($tpl_file));
                if (!($res = $db->query($sql))) {
                    return false;
                }
                $row = $db->fetchArray($res);
            }
            if (false === $row) {
                return false;
            }
            $name = $tpl_file;
            $mtime = $row['tpl_lastmodified'];
            $db->freeRecordSet($res);
        }
        $this->mFileInfo = [
            'name' => $name,
            'path' => $fpath,
            'mtime' => $mtime,
            'etag' => md5($fpath.filesize($fpath).$mtime),
        ];

        return true;
    }
}
