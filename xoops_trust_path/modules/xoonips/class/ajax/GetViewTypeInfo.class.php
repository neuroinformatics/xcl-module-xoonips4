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
        if (($uid = XoopsUtils::getUid()) == XOONIPS_UID_GUEST) {
            return false;
        }
        if (!XoopsUtils::isAdmin($uid, $this->mDirname)) {
            return false;
        } // permission error
        $vtId = intval($this->mRequest->getRequest('viewTypeId'));
        $vtHandler = &Functions::getXoonipsHandler('ViewType', $this->mDirname);
        $vtObj = $vtHandler->get($vtId);
        if (!is_object($vtObj)) {
            return false;
        } // view type object not found
        $mode = trim($this->mRequest->getRequest('mode'));
        if (!in_array($mode, array('', 'default'))) {
            return false;
        }
        if ($mode == '') {
            $ret = array(
                'hasSelectionList' => $vtObj->hasSelectionList(),
                'dataTypesInfo' => $vtObj->getDataTypesInfo(),
            );
        } else {
            $list = trim($this->mRequest->getRequest('list'));
            $default = trim($this->mRequest->getRequest('default'));
            $disabled = (intval($this->mRequest->getRequest('disabled')) == 0 ? false : true);
            $ret = $vtObj->getDefaultValueAdminHtml($list, $default, $disabled);
        }
        $this->mResult = json_encode($ret);

        return true;
    }
}
