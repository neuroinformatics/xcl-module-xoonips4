<?php

require_once __DIR__.'/Transaction.class.php';
require_once __DIR__.'/Errors.class.php';
require_once XOOPS_ROOT_PATH.'/class/token.php';

abstract class Xoonips_AbstractActionBase
{
    protected $dirname = null;
    protected $trustDirname = null;
    protected $notification = false;
    protected $transaction = false;

    public function __construct($dirname = null)
    {
        global $xoopsModule;
        if (is_object($xoopsModule)) {
            $this->dirname = strtolower($xoopsModule->getVar('dirname'));
            $this->trustDirname = $xoopsModule->getVar('trust_dirname');
        } elseif (null != $dirname) {
            $this->dirname = strtolower($dirname);
            $module_handler = &xoops_gethandler('module');
            $module = &$module_handler->getByDirname($dirname);
            $this->trustDirname = $module->getVar('trust_dirname');
        }
    }

    public function doAction(&$request, &$response, $paramFlg = false)
    {
        global $xoopsDB;
        /**
         * modify for one file hack
         * support if $paramFlg = true
         * xxxx.php?op=yyy&action=zzz
         * => doZzz('yyy').
         **/
        // operation
        $method = '';
        $op = $request->getParameter('op');
        $action = $request->getParameter('action');

        // if not exist then set 'init'
        if ($paramFlg) {
            if (null == $action) {
                $action = 'init';
            }
            $method = 'do'.ucfirst($action);
        } else {
            if (null == $op) {
                $op = 'init';
            }
            $method = 'do'.ucfirst($op);
        }
        if (!method_exists($this, $method)) {
            die('fatal error');
        }
        $result = $this->$method($request, $response);
        if (false != $this->transaction) {
            if ($result) {
                $this->transaction->commit();
            } else {
                $this->transaction->rollback();
            }
            $this->transaction = false;
        }
        if (!$result) {
            // return false and have not set system error
            if (false == $response->getSystemError()) {
                $response->setSystemError('System error!');
            }
        }
    }

    public function startTransaction()
    {
        $this->transaction = Xoonips_Transaction::getInstance();
        $this->transaction->start();
    }

    public function rollbackTransaction()
    {
        $this->transaction->rollback();
        $this->transaction = false;
    }

    public function createToken($name)
    {
        $token = &XoopsMultiTokenHandler::quickCreate($name);

        return $token->getHtml();
    }

    public function validateToken($name)
    {
        return XoopsMultiTokenHandler::quickValidate($name);
    }

    /**
     * attach the module dirname.'_' to a given tablename.
     *
     * @param string $name
     *
     * @return string
     */
    protected function modulePrefix($name)
    {
        return $this->dirname.'_'.$name;
    }
}
