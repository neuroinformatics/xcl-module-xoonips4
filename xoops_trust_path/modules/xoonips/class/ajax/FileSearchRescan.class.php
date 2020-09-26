<?php

use Xoonips\Core\XoopsUtils;

require_once dirname(__DIR__).'/core/File.class.php';

class Xoonips_FileSearchRescanAjaxMethod extends Xoonips_AbstractAjaxMethod
{
    /**
     * execute.
     *
     * return bool
     */
    public function execute()
    {
        if (XOONIPS_UID_GUEST == ($uid = XoopsUtils::getUid())) {
            return false;
        }
        if (!XoopsUtils::isAdmin($uid, $this->mDirname)) {
            return false;
        } // permission error
        $mode = trim($this->mRequest->getRequest('mode'));
        $num = intval($this->mRequest->getRequest('num'));
        if (!in_array($mode, ['info', 'index'])) {
            return false;
        } // invalid mode parameter
        $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->mDirname, $this->mTrustDirname);
        $total = $fileBean->countFile();
        if ($num < 0 || $num > $total) {
            return false;
        } // invalid num parameter
        $files = $fileBean->getFiles();
        $file_id = $files[$num - 1]['file_id'];
        if (false === $file_id) {
            return false;
        } // file id not found
        $xoonipsFile = new Xoonips_File($this->mDirname, $this->mTrustDirname, true);
        if ('info' == $mode) {
            $xoonipsFile->updateFileInfo($file_id);
        } else {
            $xoonipsFile->updateFileSearchText($file_id, true);
        }
        $ret = [
            'mode' => $mode,
            'num' => $num,
        ];
        $this->mResult = json_encode($ret);

        return true;
    }
}
