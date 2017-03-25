<?php

/**
 * abstract ajax method.
 */
abstract class Xoonips_AbstractAjaxMethod
{
    /**
     * request.
     *
     * @var XCube_HttpRequest
     */
    protected $mRequest;

    /**
     * result.
     *
     * @var mixed
     */
    protected $mResult;

    /**
     * dirname.
     *
     * @var string
     */
    protected $mDirname;

    /**
     * trust dirname.
     *
     * @var string
     */
    protected $mTrustDirname;

    /**
     * prepare.
     *
     * @param XCube_HttpRequest $request
     * @param string            $dirname
     * @param string            $trustDirname
     */
    public function prepare($request, $dirname, $trustDirname)
    {
        $this->mRequest = $request;
        $this->mDirname = $dirname;
        $this->mTrustDirname = $trustDirname;
    }

    /**
     * get type.
     *
     * return string
     */
    public function getType()
    {
        return 'json';
    }

    /**
     * execute.
     *
     * return bool
     */
    public function execute()
    {
        return true;
    }

    /**
     * get result.
     *
     * @param XCube_RenderTarget &$render
     *                                    return mixed
     */
    public function getResult(&$render)
    {
        return $this->mResult;
    }
}
