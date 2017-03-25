<?php

require_once XOONIPS_TRUST_PATH.'/class/core/BeanFactory.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/Request.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/Response.class.php';
require_once XOONIPS_TRUST_PATH.'/class/action/ItemTypeAction.class.php';

/**
 * index action.
 */
class Xoonips_IndexAction extends Xoonips_AbstractAction
{
    /**
     * get default view.
     *
     * @return Enum
     */
    public function getDefaultView()
    {
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
        $uid = Legacy_Utils::getUid();
        $dirname = $this->mAsset->mDirname;
        $request = new Xoonips_Request();
        $response = new Xoonips_Response();
        $action = new Xoonips_ItemTypeAction($dirname);
        $action->doAction($request, $response);
        $block = $response->getViewData();
        $render->setTemplateName($dirname.'_index.html');
        if (count($block['explain']) != 0) {
            $render->setAttribute('blocks', $block['explain']);
        }
        $render->setAttribute($dirname.'_editprofile_url', XOOPS_URL.'/modules/'.$dirname.'/edituser.php?uid='.$uid);
    }
}
