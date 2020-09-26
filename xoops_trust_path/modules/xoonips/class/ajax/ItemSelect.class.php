<?php

require_once dirname(__DIR__).'/core/Request.class.php';
require_once dirname(__DIR__).'/core/Response.class.php';
require_once dirname(__DIR__).'/action/ItemSelectSubAction.class.php';

class Xoonips_ItemSelectAjaxMethod extends Xoonips_AbstractAjaxMethod
{
    public function getType()
    {
        return 'html';
    }

    /**
     * execute.
     *
     * return bool
     */
    public function execute()
    {
        Xoonips_Utils::denyGuestAccess();

        $request = new Xoonips_Request();
        $response = new Xoonips_Response();
        $op = $request->getParameter('op');
        if (null == $op) {
            $op = 'init';
        }

        // check request
        if (!in_array($op, ['init', 'search'])) {
            return false;
        }

        // set action map
        $actionMap = [];
        $actionMap['init_success'] = $this->mDirname.'_ajax_itemselect.html';
        $actionMap['search_success'] = $this->mDirname.'_ajax_itemselect.html';

        //do action
        $action = new Xoonips_ItemSelectSubAction();
        $action->doAction($request, $response);

        // forward
        ob_start();
        $response->forwardLayeredWindow($actionMap);
        $this->mResult = ob_get_clean();

        return true;
    }
}
