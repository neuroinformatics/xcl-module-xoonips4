<?php

namespace Xoonips\Handler;

/**
 * config object handler.
 */
class ConfigObjectHandler extends AbstractObjectHandler
{
    /**
     * objects cache.
     *
     * @var array
     */
    protected $mObjects;

    /**
     * constructor.
     *
     * @param \XoopsDatabase &$db
     * @param string         $dirname
     */
    public function __construct(\XoopsDatabase &$db, $dirname)
    {
        parent::__construct($db, $dirname);
        $this->mTable = $db->prefix($dirname.'_config');
        $this->mPrimaryKey = 'id';
        $this->mObjects = array();
    }

    /**
     * get config value.
     *
     * @param string $name
     *
     * @return string
     */
    public function getConfig($name)
    {
        if (empty($this->mObjects) && !$this->_loadConfigs()) {
            return null;
        }
        if (!array_key_exists($name, $this->mObjects)) {
            return null;
        }

        return $this->mObjects[$name]->get('value');
    }

    /**
     * set config value.
     *
     * @param string $name
     * @param string $value
     * @param bool   $force
     *
     * @return bool
     */
    public function setConfig($name, $value, $force = false)
    {
        $oldValue = $this->getConfig($name);
        if (is_null($oldValue)) {
            return false;
        }
        $this->mObjects[$name]->set('value', $value);
        if (!$this->insert($this->mObjects[$name], $force)) {
            $this->mObjects[$name]->set('value', $oldValue);

            return false;
        }

        return true;
    }

    /**
     * insert configs.
     *
     * @param array $values
     * @param bool  $force
     *
     * @return bool
     */
    public function insertConfigs($values, $force = false)
    {
        foreach ($values as $name => $value) {
            $oldValue = $this->getConfig($name);
            if (is_null($oldValue)) {
                $obj = $this->create();
                $obj->set('name', $name);
                $obj->set('value', $value);
                if (!$this->insert($obj, $force)) {
                    return false;
                }
                $this->mObjects[$name] = $obj;
            } else {
                if (!$this->setConfig($name, $value, $force)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * load configs.
     *
     * @return bool
     */
    private function _loadConfigs()
    {
        if (!$res = $this->open()) {
            return false;
        }
        $this->mObjects = array();
        while ($obj = $this->getNext($res)) {
            $name = $obj->get('name');
            $this->mObjects[$name] = $obj;
        }
        $this->close($res);

        return true;
    }
}
