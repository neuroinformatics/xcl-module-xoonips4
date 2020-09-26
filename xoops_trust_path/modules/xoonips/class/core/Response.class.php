<?php

require_once XOOPS_ROOT_PATH.'/class/template.php';
require_once __DIR__.'/LayeredWindow.class.php';

class Xoonips_Response
{
    private $forward;
    private $errors;
    private $viewData;
    private $systemError = false;
    private $dirname = 'xoonips';

    public function setDirname($v)
    {
        $this->dirname = $v;
    }

    public function setForward($v)
    {
        $this->forward = $v;
    }

    public function getForward()
    {
        return $this->forward;
    }

    public function setErrors($v)
    {
        $this->errors = $v;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setViewData($v)
    {
        $this->viewData = $v;
    }

    public function getViewData()
    {
        return $this->viewData;
    }

    public function setSystemError($v)
    {
        $this->systemError = $v;
    }

    public function getSystemError()
    {
        return $this->systemError;
    }

    public function forward($actionMap, $isAdmin = false)
    {
        global $xoopsOption;
        global $xoopsTpl;

        // if have system error
        if (false != $this->systemError) {
            redirect_header(XOOPS_URL.'/index.php', 3, $this->systemError);
            exit;
        }

        $target = $actionMap[$this->forward];

        // if redirect
        if ('redirect_header' == $target) {
            if (isset($this->viewData['redirect_msg'])) {
                redirect_header($this->viewData['url'], 3, $this->viewData['redirect_msg']);
            } else {
                redirect_header($this->viewData['url'], 3, 'Succeed');
            }
            exit;
        }
        if ('redirect' == $target) {
            header('Location:'.$this->viewData['url']);
            exit;
        }
        if ('.php' == substr($target, -4, 4)) {
            require_once $target;
            exit;
        }

        // if normal page
        if ($isAdmin) {
            $root = &XCube_Root::getSingleton();
            $root->mContext->mModule->setAdminMode(true);
            $controller = $root->getController();
            $render = $controller->mRoot->mContext->mModule->getRenderTarget();
            $render->setTemplateName($target);
            if ($this->viewData) {
                foreach ($this->viewData as $key => $value) {
                    $render->setAttribute($key, $value);
                }
            }
        } else {
            $xoopsOption['template_main'] = $target;
            if ($this->viewData) {
                foreach ($this->viewData as $key => $value) {
                    $xoopsTpl->assign($key, $value);
                }
            }
        }
    }

    public function forwardLayeredWindow($actionMap, $isAdmin = false)
    {
        global $xoopsTpl;
        $lw = new Xoonips_LayeredWindow();

        // if have system error
        if (false != $this->systemError) {
            $errors[] = $this->systemError;
            if ($isAdmin) {
                $root = &XCube_Root::getSingleton();
                $root->mContext->mModule->setAdminMode(true);
                $controller = $root->getController();
                $render = $controller->mRoot->mContext->mModule->getRenderTarget();
                $render->setTemplateName('admin_common_msg_sub.html');
                $render->setAttribute('errors', $errors);
                $renderSystem = $controller->mRoot->getRenderSystem($controller->mRoot->mContext->mModule->getRenderSystemName());
                $renderSystem->renderMain($render);
                $ret = $render->getResult();
                if (_CHARSET != 'UTF-8') {
                    $ret = mb_convert_encoding($ret, 'utf-8', _CHARSET);
                }
                echo $ret;
            } else {
                $lw->setTpl($this->dirname.'_common_msg_sub.html');
                $xoopsTpl->assign('errors', $errors);
                echo $lw->getHtml();
            }
            // if normal page
        } else {
            $target = $actionMap[$this->forward];

            // if redirect
            if ('redirect_header' == $target) {
                if (isset($this->viewData['redirect_msg'])) {
                    redirect_header($this->viewData['url'], 3, $this->viewData['redirect_msg']);
                } else {
                    redirect_header($this->viewData['url'], 3, 'Succeed');
                }
                exit;
            }

            if ('.php' == substr($target, -4, 4)) {
                require_once $target;
                exit;
            }
            if ($isAdmin) {
                $root = &XCube_Root::getSingleton();
                $root->mContext->mModule->setAdminMode(true);
                $controller = $root->getController();
                $render = $controller->mRoot->mContext->mModule->getRenderTarget();
                $render->setTemplateName($target);
                if ($this->viewData) {
                    foreach ($this->viewData as $key => $value) {
                        $render->setAttribute($key, $value);
                    }
                }
                $renderSystem = $controller->mRoot->getRenderSystem($controller->mRoot->mContext->mModule->getRenderSystemName());
                $renderSystem->renderMain($render);
                $ret = $render->getResult();
                if (_CHARSET != 'UTF-8') {
                    $ret = mb_convert_encoding($ret, 'utf-8', _CHARSET);
                }
                echo $ret;
            } else {
                $lw->setTpl($target);
                if ($this->viewData) {
                    foreach ($this->viewData as $key => $value) {
                        $xoopsTpl->assign($key, $value);
                    }
                }
                echo $lw->getHtml();
            }
        }
    }

    public function setViewDataByKey($key, $value)
    {
        $this->viewData[$key] = $value;
    }
}
