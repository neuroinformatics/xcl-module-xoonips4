<?php

namespace Xoonips\Object;

/**
 * abstract object class.
 */
abstract class AbstractObject
{
    /**
     * new flag.
     *
     * @var bool
     */
    protected $mIsNew = false;

    /**
     * table information.
     *
     * @var array
     */
    protected $mTableInfo = array();

    /**
     * values.
     *
     * @var array
     */
    protected $mValues = array();

    /**
     * extra values.
     *
     * @var array
     */
    protected $mExtraValues = array();

    /**
     * dirname.
     *
     * @var string
     */
    protected $mDirname = '';

    /**
     * constructor.
     *
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        $this->mDirname = $dirname;
    }

    /**
     * set new flag.
     */
    public function setNew()
    {
        $this->mIsNew = true;
    }

    /**
     * unset new flag.
     */
    public function unsetNew()
    {
        $this->mIsNew = false;
    }

    /**
     * get new flag.
     *
     * @return bool
     */
    public function isNew()
    {
        return $this->mIsNew;
    }

    /**
     * get table information.
     *
     * @return array
     */
    public function getTableInfo()
    {
        return $this->mTableInfo;
    }

    /**
     * get value.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (!array_key_exists($key, $this->mValues)) {
            return null;
        }

        return $this->mValues[$key];
    }

    /**
     * get values.
     *
     * @return array
     */
    public function getArray()
    {
        return $this->mValues;
    }

    /**
     * get value for show.
     *
     * @param $key
     *
     * @return string
     */
    public function getShow($key)
    {
        $ret = '';
        if (!array_key_exists($key, $this->mValues)) {
            return $ret;
        }
        method_exists('\MyTextSanitizer', 'sGetInstance') ? $myts = &\MyTextSanitizer::sGetInstance() : $myts = &\MyTextSanitizer::getInstance();
        switch ($this->mTableInfo[$key]['dataType']) {
        case XOBJ_DTYPE_BOOL:
        case XOBJ_DTYPE_INT:
        case XOBJ_DTYPE_FLOAT:
            $ret = strval($this->mValues[$key]);
            break;
        case XOBJ_DTYPE_STRING:
            $ret = $myts->htmlSpecialChars($this->mValues[$key]);
            break;
        case XOBJ_DTYPE_TEXT:
            $html = (array_key_exists('dohtml', $this->mTableInfo[$key]) && $this->mTableInfo[$key]['dohtml']) ? 1 : 0;
            $xcode = (array_key_exists('doxcode', $this->mTableInfo[$key]) && $this->mTableInfo[$key]['doxcode']) ? 1 : 0;
            $smiley = (array_key_exists('dosmiley', $this->mTableInfo[$key]) && $this->mTableInfo[$key]['dosmiley']) ? 1 : 0;
            $image = (array_key_exists('doimage', $this->mTableInfo[$key]) && $this->mTableInfo[$key]['doimage']) ? 1 : 0;
            $br = (array_key_exists('dobr', $this->mTableInfo[$key]) && $this->mTableInfo[$key]['dobr']) ? 1 : 0;
            $ret = $myts->displayTarea($this->mValues[$key], $html, $smiley, $xcode, $image, $br);
            break;
        }

        return $ret;
    }

    /**
     * get value for edit.
     *
     * @param $key
     *
     * @return string
     */
    public function getEdit($key)
    {
        $ret = '';
        if (!array_key_exists($key, $this->mValues)) {
            return $ret;
        }
        method_exists('\MyTextSanitizer', 'sGetInstance') ? $myts = &\MyTextSanitizer::sGetInstance() : $myts = &\MyTextSanitizer::getInstance();
        switch ($this->mTableInfo[$key]['dataType']) {
        case XOBJ_DTYPE_BOOL:
        case XOBJ_DTYPE_INT:
        case XOBJ_DTYPE_FLOAT:
            $ret = strval($this->mValues[$key]);
            break;
        case XOBJ_DTYPE_STRING:
            $ret = $myts->htmlSpecialChars($this->mValues[$key]);
            break;
        case XOBJ_DTYPE_TEXT:
            $ret = htmlspecialchars($ret, ENT_QUOTES);
            break;
        }

        return $ret;
    }

    /**
     * get value for preview.
     *
     * @param $key
     *
     * @return string
     */
    public function getPreview($key)
    {
        $ret = '';
        if (!array_key_exists($key, $this->mValues)) {
            return $ret;
        }
        method_exists('\MyTextSanitizer', 'sGetInstance') ? $myts = &\MyTextSanitizer::sGetInstance() : $myts = &\MyTextSanitizer::getInstance();
        switch ($this->mTableInfo[$key]['dataType']) {
        case XOBJ_DTYPE_BOOL:
        case XOBJ_DTYPE_INT:
        case XOBJ_DTYPE_FLOAT:
            $ret = strval($this->mValues[$key]);
            break;
        case XOBJ_DTYPE_STRING:
            $ret = $myts->htmlSpecialChars($this->mValues[$key]);
            break;
        case XOBJ_DTYPE_TEXT:
            $html = (array_key_exists('dohtml', $this->mTableInfo[$key]) && $this->mTableInfo[$key]['dohtml']) ? 1 : 0;
            $xcode = (array_key_exists('doxcode', $this->mTableInfo[$key]) && $this->mTableInfo[$key]['doxcode']) ? 1 : 0;
            $smiley = (array_key_exists('dosmiley', $this->mTableInfo[$key]) && $this->mTableInfo[$key]['dosmiley']) ? 1 : 0;
            $image = (array_key_exists('doimage', $this->mTableInfo[$key]) && $this->mTableInfo[$key]['doimage']) ? 1 : 0;
            $br = (array_key_exists('dobr', $this->mTableInfo[$key]) && $this->mTableInfo[$key]['dobr']) ? 1 : 0;
            $ret = $myts->previewTarea($this->mValues[$key], $html, $smiley, $xcode, $image, $br);
            break;
        }

        return $ret;
    }

    /**
     * set value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function set($key, $value)
    {
        if (!array_key_exists($key, $this->mTableInfo)) {
            return false;
        }
        method_exists('\MyTextSanitizer', 'sGetInstance') ? $myts = &\MyTextSanitizer::sGetInstance() : $myts = &\MyTextSanitizer::getInstance();
        switch ($this->mTableInfo[$key]['dataType']) {
        case XOBJ_DTYPE_BOOL:
            $this->mValues[$key] = $value ? 1 : 0;
            break;
        case XOBJ_DTYPE_INT:
            $this->mValues[$key] = null !== $value ? intval($value) : null;
            break;
        case XOBJ_DTYPE_FLOAT:
            $this->mValues[$key] = null !== $value ? floatval($value) : null;
            break;
        case XOBJ_DTYPE_STRING:
            if ($this->mTableInfo[$key]['maxlength'] !== null && mb_strlen($value) > $this->mTableInfo[$key]['maxlength']) {
                return false;
            }
            // no break
        case XOBJ_DTYPE_TEXT:
            $this->mValues[$key] = $myts->censorString($value);
            break;
        default:
            return false;
        }

        return true;
    }

    /**
     * set values.
     *
     * @param array $values
     *
     * @return bool
     */
    public function setArray($values)
    {
        $ret = true;
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $this->mTableInfo)) {
                if (!$this->set($key, $value)) {
                    $ret = false;
                }
            } else {
                $this->setExtra($key, $value);
            }
        }

        return $ret;
    }

    /**
     * get extra value.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getExtra($key)
    {
        if (!array_key_exists($key, $this->mExtraVars)) {
            return null;
        }

        return $this->mExtraVars[$key];
    }

    /**
     * set extra value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function setExtra($key, $value)
    {
        $this->mExtraVars[$key] = $value;

        return true;
    }

    /**
     * init variable.
     *
     * @param string $key
     * @param int    $dataType
     * @param mixed  $value
     * @param bool   $required
     * @param int    $size
     */
    protected function initVar($key, $dataType, $value = null, $required = false, $size = null)
    {
        $this->mTableInfo[$key] = array(
            'dataType' => $dataType,
            'required' => $required ? true : false,
            'maxlength' => $size ? (int) $size : null,
        );
        $this->set($key, $value);
    }

    /**
     * get dirname.
     *
     * @return string
     **/
    public function getDirname()
    {
        return $this->mDirname;
    }
}
