<?php

require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/class/Enum.class.php';
require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/class/core/BeanFactory.class.php';
require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/class/core/ItemComplementManager.class.php';

abstract class Xoonips_Complement
{
    /**
     * id.
     *
     * @var int
     */
    protected $mId;

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
     * constructor.
     *
     * @param int    $id
     * @param string $dirname
     * @param string $trustDirname
     */
    public function __construct($id, $dirname, $trustDirname)
    {
        $this->mId = $id;
        $this->mDirname = $dirname;
        $this->mTrustDirname = $trustDirname;
    }

    /**
     * do complete.
     *
     * @param {Trustdirname}_ItemField $field
     * @param string                   $id
     * @param array                    &$data
     *
     * @return bool
     */
    abstract protected function complete($field, $id, &$data);
}
