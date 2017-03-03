<?php

use Xoonips\Core\XoopsUtils;

class Xoonips_Workflow
{
    private static $_mKnownDirnames = array('leprogress', 'xworkflow');

    /**
     * check whether workflow module available.
     *
     * @return bool false if not available
     */
    public static function hasWorkflow()
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $cache = false;
        if (defined('LEGACY_WORKFLOW_DIRNAME')) {
            $dirname = LEGACY_WORKFLOW_DIRNAME;
            $trustDirname = Xoonips_Utils::getTrustDirnameByDirname($dirname);
            if ($trustDirname !== null) {
                $dirname = $trustDirname;
            }
            $cache = in_array($dirname, self::$_mKnownDirnames);
        }

        return $cache;
    }

    /**
     * get dirname of workflow module.
     *
     * @return string dirname
     */
    public static function getDirname()
    {
        if (!self::hasWorkflow()) {
            return '';
        }

        return LEGACY_WORKFLOW_DIRNAME;
    }

    /**
     * add workflow item.
     *
     * @param string $title
     * @param string $dirname
     * @param string $dataname
     * @param int    $target_id
     * @param string $url
     *
     * @return bool false if workflow task is not in progress
     */
    public static function addItem($title, $dirname, $dataname, $target_id, $url)
    {
        if (!self::hasWorkflow()) {
            return false;
        }
        XCube_DelegateUtils::call('Legacy_Workflow.AddItem', $title, $dirname, $dataname, $target_id, $url);
        if (!self::isInProgressItem($dirname, $dataname, $target_id)) {
            self::deleteItem($dirname, $dataname, $target_id);

            return false;
        }

        return true;
    }

    /**
     * delete workflow item.
     *
     * @param string $dirname
     * @param string $dataname
     * @param int    $target_id
     *
     * @return bool false if failure
     */
    public static function deleteItem($dirname, $dataname, $target_id)
    {
        if (!self::hasWorkflow()) {
            return false;
        }
        XCube_DelegateUtils::call('Legacy_Workflow.DeleteItem', $dirname, $dataname, $target_id);

        return true;
    }

    /**
     * check whether user is has workflow approver.
     *
     * @param int $uid
     *
     * @return bool
     */
    public static function isApprover($uid)
    {
        if (!self::hasWorkflow()) {
            return false;
        }
        $service = &self::_getWorkflowService();
        if (is_object($service)) {
            return $service->isApprover($uid);
        }
        // for leprogress
        $aHandler = &XoopsUtils::getModuleHandler('approval', LEGACY_WORKFLOW_DIRNAME);
        $cnt = $aHandler->getCount(new Criteria('uid', $uid));

        return $cnt > 0;
    }

    /**
     * get approval user ids.
     *
     * @param string $dirname
     * @param string $dataname
     * @param int    $target_id
     *
     * @return array
     */
    public static function getAllApproverUserIds($dirname, $dataname, $target_id)
    {
        $uids = array();
        if (!self::hasWorkflow()) {
            return $uids;
        }
        $service = &self::_getWorkflowService();
        if (is_object($service)) {
            return $service->getAllApproverUserIds($dirname, $dataname, $target_id);
        }
        // for leprogress
        $aHandler = &XoopsUtils::getModuleHandler('approval', LEGACY_WORKFLOW_DIRNAME);
        $criteria = self::_makeCriteria(array(
            'dirname' => $dirname,
            'dataname' => $dataname,
        ));
        $objs = $aHandler->getObjects($criteria);
        foreach ($objs as $obj) {
            $uids[] = $obj->get('uid');
        }
        $uids = array_unique($uids);
        sort($uids);

        return $uids;
    }

    /**
     * get current item approver user ids.
     * this funciton will used to request ceritify item to next approver.
     *
     * @param string $dirname
     * @param string $dataname
     * @param int    $target_id
     *
     * @return array
     */
    public static function getCurrentApproverUserIds($dirname, $dataname, $target_id)
    {
        $uids = array();
        if (!self::hasWorkflow()) {
            return $uids;
        }
        $service = &self::_getWorkflowService();
        if (is_object($service)) {
            return $service->getCurrentApproverUserIds($dirname, $dataname, $target_id);
        }
        // for leprogress
        $aHandler = &XoopsUtils::getModuleHandler('approval', LEGACY_WORKFLOW_DIRNAME);
        $iHandler = &XoopsUtils::getModuleHandler('item', LEGACY_WORKFLOW_DIRNAME);
        $criteria = self::_makeCriteria(array(
            'dirname' => $dirname,
            'dataname' => $dataname,
            'target_id' => $target_id,
            'status' => Lenum_WorkflowStatus::PROGRESS,
        ));
        $iObjs = $iHandler->getObjects($criteria);
        if (empty($iObjs)) {
            return $uids;
        }
        $iObj = array_shift($iObjs);
        $criteria = self::_makeCriteria(array(
            'dirname' => $dirname,
            'dataname' => $dataname,
            'step' => $iObj->get('step'),
        ));
        $aObjs = $aHandler->getObjects($criteria);
        if (empty($aObjs)) {
            return $uids;
        }
        $aObj = array_shift($aObjs);
        $uids[] = $aObj->get('uid');

        return $uids;
    }

    /**
     * count user's in progress items.
     *
     * @param int $uid
     *
     * @return int
     */
    public static function countInProgressItems($uid)
    {
        if (!self::hasWorkflow()) {
            return 0;
        }
        $service = &self::_getWorkflowService();
        if (is_object($service)) {
            return $service->countInProgressItems($uid);
        }
        // for leprogress
        $aHandler = &XoopsUtils::getModuleHandler('approval', LEGACY_WORKFLOW_DIRNAME);
        $iHandler = &XoopsUtils::getModuleHandler('item', LEGACY_WORKFLOW_DIRNAME);
        $objs = $aHandler->getObjects(new Criteria('uid', $uid));
        if (empty($objs)) {
            return 0;
        }
        $criteria = new CriteriaCompo();
        foreach ($objs as $obj) {
            $criteria2 = self::_makeCriteria(array(
                'dirname' => $obj->get('dirname'),
                'dataname' => $obj->get('dataname'),
                'step' => $obj->get('step'),
                'status' => Lenum_WorkflowStatus::PROGRESS, ));
            $criteria->add($criteria2, 'OR');
            unset($criteria2);
        }

        return $iHandler->getCount($criteria);
    }

    /**
     * check whether item is in progress.
     * this funciton will used to check result of addItem().
     *
     * @param string $dirname
     * @param string $dataname
     * @param int    $target_id
     *
     * @return int
     */
    public static function isInProgressItem($dirname, $dataname, $target_id)
    {
        if (!self::hasWorkflow()) {
            return false;
        }
        $service = &self::_getWorkflowService();
        if (is_object($service)) {
            return $service->isInProgressItem($dirname, $dataname, $target_id);
        }
        // for leprogress
        $iHandler = &XoopsUtils::getModuleHandler('item', LEGACY_WORKFLOW_DIRNAME);
        $criteria = self::_makeCriteria(array(
            'dirname' => $dirname,
            'dataname' => $dataname,
            'target_id' => $target_id,
            'status' => Lenum_WorkflowStatus::PROGRESS,
        ));

        return $iHandler->getCount($criteria) > 0;
    }

    /**
     * get workflow service handler.
     *
     * @return objct&
     */
    private static function &_getWorkflowService()
    {
        $ret = null;
        $ns = 'Xworkflow\\Handler';
        $name = 'WorkflowService';
        if (!class_exists($ns.'\\'.$name.'Handler')) {
            return $ret;
        }
        $ret = &XoopsUtils::getModuleHandler($name, LEGACY_WORKFLOW_DIRNAME);

        return $ret;
    }

    /**
     * make criteria object from array.
     *
     * @param array $params
     *
     * @return CriteriaElement
     */
    private static function _makeCriteria($params)
    {
        if (empty($params)) {
            return new CriteriaElement();
        }
        $criteria = new CriteriaCompo();
        foreach ($params as $key => $value) {
            $criteria->add(new Criteria($key, $value));
        }

        return $criteria;
    }
}
