<?php

require_once dirname(__FILE__).'/Search.class.php';

/**
 * view type factory class.
 */
class Xoonips_ViewTypeFactory
{
    /**
     * instances cache.
     *
     * @var {Trustdirname}_ViewType[]
     */
    private $viewTypeInstances = array();

    /**
     * constructor.
     *
     * @param string $dirname
     * @param string $trustDirname
     */
    private function __construct($dirname, $trustDirname)
    {
        global $xoopsDB;
        global $xoopsTpl;

        // get view type data
        $sql = sprintf('SELECT * FROM `%s`', $xoopsDB->prefix($dirname.'_view_type'));
        $result = $xoopsDB->query($sql);
        $search = &Xoonips_Search::getInstance();
        while ($row = $xoopsDB->fetchArray($result)) {
            $typeModule = $row['module'];
            $className = ucfirst($trustDirname).'_'.$typeModule;
            if (!file_exists($fpath = sprintf('%s/modules/%s/class/viewtype/%s.class.php', XOOPS_TRUST_PATH, $trustDirname, $typeModule))) {
                return false;
            } // module class is not found
            $mydirname = $dirname;
            $mytrustdirname = $trustDirname;
            require_once $fpath;
            $viewType = new $className();
            $viewTypeId = $row['view_type_id'];
            $viewType->setId($viewTypeId);
            $viewType->setName($row['name']);
            $viewType->setPreslect($row['preselect']);
            $viewType->setModule($typeModule);
            $viewType->setMulti($row['multi']);
            $viewType->setDirname($dirname);
            $viewType->setTrustDirname($trustDirname);
            $viewType->setXoopsTpl($xoopsTpl);
            $viewType->setTemplate();
            $viewType->setSearch($search);
            $this->viewTypeInstances[$viewTypeId] = $viewType;
        }
    }

    /**
     * get view typefactory instance.
     *
     * @param string $dirname
     * @param string $trustDirname
     *
     * @return {Trustdirname}_ViewTypeFactory
     */
    public static function getInstance($dirname, $trustDirname)
    {
        static $instance = array();
        if (!isset($instance[$dirname])) {
            $instance[$dirname] = new self($dirname, $trustDirname);
        }

        return $instance[$dirname];
    }

    /**
     * get view type instance.
     *
     * @param string $id
     *
     * @return {Trustdirname}_ViewType
     */
    public function getViewType($id)
    {
        return $this->viewTypeInstances[$id];
    }
}
