<?php

use Xoonips\Core\Functions;
use Xoonips\Core\XoopsUtils;

require_once dirname(__DIR__).'/core/ViewTypeFactory.class.php';

class Xoonips_GetViewTypeInfoAjaxMethod extends Xoonips_AbstractAjaxMethod
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
        $vtId = intval($this->mRequest->getRequest('viewTypeId'));
        $vtHandler = Functions::getXoonipsHandler('ViewType', $this->mDirname);
        $vtObj = $vtHandler->get($vtId);
        if (!is_object($vtObj)) {
            return false;
        } // view type object not found
        $mode = trim($this->mRequest->getRequest('mode'));
        if (!in_array($mode, ['', 'default'])) {
            return false;
        }
        if ('' == $mode) {
            $ret = [
                'hasSelectionList' => $vtObj->hasSelectionList(),
                'dataTypesInfo' => $vtObj->getDataTypesInfo(),
            ];
        } else {
            $list = trim($this->mRequest->getRequest('list'));
            $default = trim($this->mRequest->getRequest('default'));
            $disabled = (0 == intval($this->mRequest->getRequest('disabled')) ? false : true);
            $ret = $vtObj->getDefaultValueAdminHtml($list, $default, $disabled);
        }
        $this->mResult = json_encode($ret);

        return true;
    }
}
