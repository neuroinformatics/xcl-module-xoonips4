<?php

/**
 * config object.
 */
class Xoonips_ConfigObject extends XoopsSimpleObject
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->initVar('id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('name', XOBJ_DTYPE_STRING, null, true, 255);
        $this->initVar('value', XOBJ_DTYPE_STRING, null, true);
    }
}

/**
 * config object handler.
 */
class Xoonips_ConfigHandler extends XoopsObjectGenericHandler
{
    private static $mConfigs = array();

    /**
     * constructor.
     *
     * @param XoopsDatabase &$db
     * @param string        $dirname
     */
    public function __construct(&$db, $dirname)
    {
        $this->mTable = $dirname.'_config';
        $this->mPrimary = 'id';
        $this->mClass = preg_replace('/Handler$/', 'Object', get_class());
        parent::__construct($db);
    }

    /**
     * get config.
     *
     * @param string $name
     *
     * @return string
     */
    public function getConfig($name)
    {
        $this->_loadConfig();

        return isset(self::$mConfigs[$name]) ? self::$mConfigs[$name]->get('value') : null;
    }

    /**
     * set config.
     *
     * @param string $name
     * @param string $value
     *
     * @return bool false if failure
     */
    public function setConfig($name, $value, $force = false)
    {
        $this->_loadConfig();
        if (!isset(self::$mConfigs[$name])) {
            return false;
        }
        $obj = self::$mConfigs[$name];
        $obj->set('value', $value);
        if (!$this->insert($obj, $force)) {
            return false;
        }
        self::$mConfigs[$name] = $obj;

        return true;
    }

    /**
     * insert configs.
     *
     * @param array
     *
     * @return bool false if failure
     */
    public function insertConfigs($configs)
    {
        $ret = true;
        foreach ($configs as $config) {
            $obj = &$this->create();
            $obj->set('name', $config['name']);
            $obj->set('value', $config['value']);
            if (!$this->insert($obj)) {
                $ret = false;
            }
            unset($obj);
        }
        self::$mConfigs = array();

        return $ret;
    }

    /**
     * load configs.
     */
    private function _loadConfig()
    {
        if (empty(self::$mConfigs)) {
            $objs = $this->getObjects();
            foreach ($objs as $obj) {
                self::$mConfigs[$obj->get('name')] = $obj;
            }
        }
    }
}
