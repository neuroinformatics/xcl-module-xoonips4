<?php

use Xoonips\Core\CacheUtils;

require_once dirname(__DIR__).'/class/AbstractAjaxMethod.class.php';

/**
 * ajax action.
 */
class Xoonips_AjaxAction extends Xoonips_AbstractAction
{
    /**
     * type.
     *
     * @var string
     */
    protected $mType = null;

    /**
     * acceptable types.
     *
     * @var array
     */
    protected $mKnownTypes = array(
        'json' => 'application/json',
        'jsonp' => 'application/javascript',
        'script' => 'application/javascript',
        'xml' => 'text/xml',
        'html' => 'text/html',
    );

    /**
     * get method.
     *
     * @return &{Trustdirname}_AbstractAjaxMethod
     */
    protected function &_getMethod()
    {
        $req = $this->mRoot->mContext->mRequest;
        $dirname = $this->mAsset->mDirname;
        $trustDirname = $this->mAsset->mTrustDirname;
        $method = trim($req->getRequest('method'));
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $method)) {
            return false;
        }
        if (!file_exists($fpath = XOOPS_TRUST_PATH.'/modules/'.$trustDirname.'/class/ajax/'.$method.'.class.php')) {
            return false;
        }
        $mydirname = $dirname;
        $mytrustdirname = $trustDirname;
        require_once $fpath;
        $cname = ucfirst($trustDirname).'_'.$method.'AjaxMethod';
        if (!class_exists($cname)) {
            return false;
        }
        $instance = new $cname();
        $instance->prepare($req, $dirname, $trustDirname);

        return $instance;
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
        if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], XOOPS_URL) !== 0) {
            return $this->_getFrameViewStatus('ERROR');
        }
        $this->mMethod = $this->_getMethod();
        if ($this->mMethod === false) {
            return $this->_getFrameViewStatus('ERROR');
        }
        $this->mType = $this->mMethod->getType();
        if (!in_array($this->mType, array_keys($this->mKnownTypes))) {
            return $this->_getFrameViewStatus('ERROR');
        }
        if (!$this->mMethod->execute()) {
            return $this->_getFrameViewStatus('ERROR');
        }

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
        $data = $this->mMethod->getResult($render);
        $mime = $this->mKnownTypes[$this->mType];
        CacheUtils::outputData(false, false, $mime, $data);
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
