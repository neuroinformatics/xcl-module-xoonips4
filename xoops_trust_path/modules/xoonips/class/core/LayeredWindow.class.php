<?php

class Xoonips_LayeredWindow
{
    private $tpl;
    private $viewData;

    public function setTpl($v)
    {
        $this->tpl = $v;
    }

    public function getTpl()
    {
        return $this->tpl;
    }

    public function setViewData($v)
    {
        $this->viewData = $v;
    }

    public function getViewData()
    {
        return $this->viewData;
    }

    public function getHtml()
    {
        $ret = $this->getHeaderHtml().$this->getContentHtml().$this->getFooterHtml();
        if (_CHARSET != 'UTF-8') {
            $ret = mb_convert_encoding($ret, 'utf-8', _CHARSET);
        }

        return $ret;
    }

    private function getHeaderHtml()
    {
        return '';
    }

    private function getContentHtml()
    {
        $root = &XCube_Root::getSingleton();
        $renderSystem = &$root->getRenderSystem(XOOPSFORM_DEPENDENCE_RENDER_SYSTEM);

        $renderTarget = &$renderSystem->createRenderTarget('main');

        $renderTarget->setAttribute('legacy_module', 'legacy');
        $renderTarget->setTemplateName($this->tpl);

        $renderSystem->render($renderTarget);
        $ret = $renderTarget->getResult();

        return $ret;
    }

    private function getFooterHtml()
    {
        return '';
    }
}
