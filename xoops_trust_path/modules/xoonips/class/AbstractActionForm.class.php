<?php

require_once XOOPS_ROOT_PATH.'/core/XCube_ActionForm.class.php';
require_once XOOPS_MODULE_PATH.'/legacy/class/Legacy_Validator.class.php';

/**
 * abstract action form.
 */
class Xoonips_AbstractActionForm extends XCube_ActionForm
{
    const TYPE_INT = 1;
    const TYPE_FLOAT = 2;
    const TYPE_STRING = 3;
    const TYPE_TEXT = 4;
    const TYPE_FILE = 5;
    const TYPE_IMAGE = 6;
    const TYPE_INTARRAY = 7;
    const TYPE_FLOATARRAY = 8;
    const TYPE_STRINGARRAY = 9;
    const TYPE_TEXTARRAY = 10;
    const TYPE_FILEARRAY = 11;
    const TYPE_IMAGEARRAY = 12;

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
     * is admin mode.
     *
     * @return bool
     */
    protected function _isAdminMode()
    {
        // override
        return false;
    }

    /**
     * is multiple mode.
     *
     * @return bool
     */
    protected function _isMultipleMode()
    {
        // override
        return false;
    }

    /**
     * get form params.
     *
     * @return array
     */
    protected function _getFormParams()
    {
        // override
        // - normal mode
        // return array(
        //   {name1} => array(
        //     'type' => self::{TYPE_XXXXX},
        //     'label' => {label1},
        //     'depends' => array(
        //       'required' => true,
        //       'min' => 0,...
        //      ),
        //    ),...
        //  );
        // - multiple mode
        // return array(
        //   {mode1} => array(
        //     {name1} => array(
        //       'type' => self::{TYPE_XXXXX},
        //       'label' => {label1},
        //       'depends' => array(
        //	 'required' => true,
        //	 'min' => 0,...
        //	),
        //      ),...
        //    ),...
        //  );
        return [];
    }

    /**
     * get object value.
     *
     * @param mixed  &$obj
     * @param string $key
     *
     * @return mixed
     */
    protected function _getObjectValue(&$obj, $key)
    {
        // override
        // return $obj->get($key);
        return $obj[$key];
    }

    /**
     * set object value.
     *
     * @param mixed  &$obj
     * @param string $key
     * @param mixed  $value
     */
    protected function _setObjectValue(&$obj, $key, $value)
    {
        // override
        // $obj->set($key, $value);
        $obj[$key] = $value;
    }

    /**
     * set dirname.
     *
     * @param string $dirname
     * @param string $trustDirname
     */
    public function setDirname($dirname, $trustDirname)
    {
        $this->mDirname = $dirname;
        $this->mTrustDirname = $trustDirname;
    }

    /**
     * get token name.
     *
     * @return string
     */
    public function getTokenName()
    {
        $name = preg_replace('/^'.ucfirst($this->mTrustDirname).'_/', '', get_class($this));

        return 'module.'.$this->mDirname.'.'.$name.'.TOKEN';
    }

    /**
     * prepare.
     */
    public function prepare()
    {
        $params = $this->_getFormParams();
        if ($this->_isMultipleMode()) {
            foreach ($params as $mode => $param) {
                $name = '_form_mode_'.$mode;
                $this->mFormProperties[$name] = new XCube_StringProperty($name);
                $value = $this->mContext->mRequest->getRequest($name);
                $setField = !empty($value);
                $this->_setProperties($param, $setField);
            }
        } else {
            $this->_setProperties($params, true);
        }
    }

    /**
     * load.
     *
     * @param mixed &$obj
     */
    public function load(&$obj)
    {
        $isObj = is_object($obj);
        $params = $this->_getFormParams();
        if (!$this->_isMultipleMode()) {
            $params = [$params];
        }
        foreach ($params as $type => $param) {
            foreach (array_keys($param) as $key) {
                $this->set($key, $this->_getObjectValue($obj, $key));
            }
        }
    }

    /**
     * update.
     *
     * @param mixed &$obj
     */
    public function update(&$obj)
    {
        $isObj = is_object($obj);
        $params = $this->_getFormParams();
        if ($this->_isMultipleMode()) {
            foreach ($params as $mode => $param) {
                $name = '_form_mode_'.$mode;
                $value = $this->get($name);
                if (!empty($value)) {
                    $this->_setObjectValue($obj, 'mode', $mode);
                    foreach ($param as $key => $info) {
                        $this->_setObjectValue($obj, $key, $this->get($key));
                    }
                }
            }
        } else {
            foreach ($params as $key => $info) {
                $this->_setObjectValue($obj, $key, $this->get($key));
            }
        }
    }

    /**
     * set form and field properties.
     *
     * @param array $params
     * @param bool  $setField
     */
    protected function _setProperties($params, $setField)
    {
        $constpref = ($this->_isAdminMode() ? '_AD_' : '_MD_').strtoupper($this->mDirname);
        // set form properties
        foreach ($params as $key => $info) {
            switch ($info['type']) {
            case self::TYPE_INT:
                $this->mFormProperties[$key] = new XCube_IntProperty($key);
                break;
            case self::TYPE_FLOAT:
                $this->mFormProperties[$key] = new XCube_FloatProperty($key);
                break;
            case self::TYPE_STRING:
                $this->mFormProperties[$key] = new XCube_StringProperty($key);
                break;
            case self::TYPE_TEXT:
                $this->mFormProperties[$key] = new XCube_TextProperty($key);
                break;
            case self::TYPE_FILE:
                $this->mFormProperties[$key] = new XCube_FileProperty($key);
                break;
            case self::TYPE_IMAGE:
                $this->mFormProperties[$key] = new XCube_ImageFileProperty($key);
                break;
            case self::TYPE_INTARRAY:
                $this->mFormProperties[$key] = new XCube_IntArrayProperty($key);
                break;
            case self::TYPE_FLOATARRAY:
                $this->mFormProperties[$key] = new XCube_FloatArrayProperty($key);
                break;
            case self::TYPE_STRINGARRAY:
                $this->mFormProperties[$key] = new XCube_StringArrayProperty($key);
                break;
            case self::TYPE_TEXTARRAY:
                $this->mFormProperties[$key] = new XCube_TextArrayProperty($key);
                break;
            case self::TYPE_FILEARRAY:
                $this->mFormProperties[$key] = new XCube_FileArrayProperty($key);
                break;
            case self::TYPE_IMAGEARRAY:
                $this->mFormProperties[$key] = new XCube_ImageFileArrayProperty($key);
                break;
            }
        }
        // set field properties
        if (!$setField) {
            return;
        }
        foreach ($params as $key => $info) {
            if (isset($info['depends']) && is_array($info['depends'])) {
                $depends = $info['depends'];
                $this->mFieldProperties[$key] = new XCube_FieldProperty($this);
                $dependKeys = array_keys($depends);
                if (in_array('intRange', $dependKeys)) {
                    $dependKeys = array_diff($dependKeys, ['min', 'max']);
                }
                $this->mFieldProperties[$key]->setDependsByArray($dependKeys);
                foreach ($depends as $depend => $value) {
                    if (!in_array($depend, ['required', 'intRange', 'email', 'objectExist'])) {
                        $this->mFieldProperties[$key]->addVar($depend, $value);
                    }
                    switch ($depend) {
                    case 'required':
                        $this->mFieldProperties[$key]->addMessage($depend, constant($constpref.'_ERROR_REQUIRED'), $info['label']);
                        break;
                    case 'minlength':
                        $this->mFieldProperties[$key]->addMessage($depend, constant($constpref.'_ERROR_MINLENGTH'), $info['label'], $depends['minlength']);
                        break;
                    case 'maxlength':
                        $this->mFieldProperties[$key]->addMessage($depend, constant($constpref.'_ERROR_MAXLENGTH'), $info['label'], $depends['maxlength']);
                        break;
                    case 'intRange':
                        $this->mFieldProperties[$key]->addMessage($depend, constant($constpref.'_ERROR_INTRANGE'), $info['label'], $depends['min'], $depends['max']);
                        break;
                    case 'min':
                        $this->mFieldProperties[$key]->addMessage($depend, constant($constpref.'_ERROR_MIN'), $info['label'], $depends['min']);
                        break;
                    case 'max':
                        $this->mFieldProperties[$key]->addMessage($depend, constant($constpref.'_ERROR_MAX'), $info['label'], $depends['max']);
                        break;
                    case 'email':
                        $this->mFieldProperties[$key]->addMessage($depend, constant($constpref.'_ERROR_EMAIL'), $info['label']);
                        break;
                    case 'mask':
                        $this->mFieldProperties[$key]->addMessage($depend, constant($constpref.'_ERROR_MASK'), $info['label'], $depends['mask']);
                        break;
                    case 'extension':
                        $this->mFieldProperties[$key]->addMessage($depend, constant($constpref.'_ERROR_EXTENSION'), $info['label'], $depends['extension']);
                        break;
                    case 'maxfilesize':
                        $this->mFieldProperties[$key]->addMessage($depend, constant($constpref.'_ERROR_MAXFILESIZE'), $info['label'], $depends['maxfilesize']);
                        break;
                    case 'objectExist':
                        $this->mFieldProperties[$key]->addMessage($depend, constant($constpref.'_ERROR_OBJECTEXIST'), $info['label']);
                        break;
                    }
                }
            }
        }
    }
}
