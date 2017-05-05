<?php

use Xoonips\Core\CacheUtils;
use Xoonips\Core\FileUtils;
use Xoonips\Core\StringUtils;
use Xoonips\Core\UnzipFile;
use Xoonips\Core\ZipFile;

require_once XOONIPS_TRUST_PATH.'/class/core/ActionBase.class.php';
require_once XOOPS_ROOT_PATH.'/core/XCube_PageNavigator.class.php';
require_once XOONIPS_TRUST_PATH.'/class/bean/ItemFieldDetailBean.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/Transaction.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/BeanFactory.class.php';
require_once XOONIPS_TRUST_PATH.'/class/core/ImportItemtype.class.php';

class Xoonips_PolicyItemTypeAction extends Xoonips_ActionBase
{
    protected function doInit(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_DESC;

        // breadcrumbs
        $breadcrumbs = array(
            array(
                'name' => _AM_XOONIPS_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php',
            ),
            array(
                'name' => _AM_XOONIPS_POLICY_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=Policy',
            ),
            array(
                'name' => _AD_XOONIPS_POLICY_ITEM_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItem',
            ),
            array(
                'name' => $title,
            ),
        );
        // get requsts
        $start = intval($request->getParameter('start'));

        // page navigation
        $limit = 50;
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $count = $itemtypeBean->countItemtypes();

        $pageNavi = new XCube_PageNavigator('policy_itemtype.php', XCUBE_PAGENAVI_START);
        $pageNavi->setTotalItems($count);
        $pageNavi->setPerpage($limit);
        $pageNavi->fetch();

        $navi_title = sprintf(_AM_XOONIPS_POLICY_ITEMTYPE_PAGENAVI_FORMAT,
        $start + 1, ($start + $limit) > $count ? $count : $start + $limit, $count);

        $itemtypes_objs = $itemtypeBean->itemtypeGetItemtypelist($limit, $start);
        $itemtypes = array();
        $itemBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        foreach ($itemtypes_objs as $itemtype) {
            $itemtypeid = $itemtype['item_type_id'];
            $name = $itemtype['name'];
            $icon = false;
            if (!empty($itemtype['icon'])) {
                $icon = XOOPS_URL.'/modules/'.$this->dirname.'/images/'.$itemtype['icon'];
            }
            $desc = $itemtype['description'];
            $subtypes = $this->getSubtypes($itemtypeid);
            $editing = '';
            if ($itemtype['released'] == 0) {
                $editing = _AM_XOONIPS_LABEL_ITEMTYPE_EDITING;
            } elseif ($itemtype['upid'] != '') {
                if ($this->isDiff($itemtypeid)) {
                    $editing = _AM_XOONIPS_LABEL_ITEMTYPE_EDITING;
                } else {
                    $upd_itemtypeid = '';
                    $this->getItemtypeInfoForEdit($itemtypeBean, $itemtypeid, $upd_itemtypeid);
                    $this->deleteAll($upd_itemtypeid);
                }
            }

            // check number of item
            $disdel = false;
            $checkItemtype = $itemBean->checkItemtype($itemtypeid);
            if ($checkItemtype == 0) {
                $disdel = true;
            }
            if ($itemtype['released'] == 0) {
                $disdel = true;
            }

            $itemtypes[] = array(
                'itemtypeid' => $itemtypeid,
                'name' => $name,
                'editing' => $editing,
                'icon' => $icon,
                'desc' => $desc,
                'subtypes' => $subtypes,
                'disdel' => $disdel,
            );
        }

        // token ticket
        $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_default'));

        // get common viewdata
        $viewData = array();

        $viewData['token_ticket'] = $token_ticket;
        $viewData['navi_title'] = $navi_title;
        $viewData['itemtypes'] = $itemtypes;
        $viewData['pageNavi'] = $pageNavi;
        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['title'] = $title;
        $viewData['description'] = $description;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
        $viewData['perpage'] = $limit;
        $viewData['startpage'] = $start;

        $response->setViewData($viewData);
        $response->setForward('init_success');

        return true;
    }

    protected function doRegister(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_REGIST_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_REGIST_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // get groups for select
        $groups = $this->getGroupsForSelect(0);

        // token ticket
        $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_register'));

        // get common viewdata
        $viewData = array();

        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['title'] = $title;
        $viewData['description'] = $description;

        $viewData['groups'] = $groups;

        $viewData['token_ticket'] = $token_ticket;
        $viewData['dirname'] = $this->dirname;

        $response->setViewData($viewData);
        $response->setForward('register_success');

        return true;
    }

    protected function doRegistersave(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_REGIST_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_REGIST_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // get requests
        $name = $request->getParameter('name');
        $descrip = $request->getParameter('descrip');
        $weight = $request->getParameter('weight');
        $template = $request->getParameter('template');
        $icon_file = $request->getFile('icon_file');
        $mode = $request->getParameter('mode');

        if ($weight == '') {
            $weight = '0';
        }

        // get groups info
        $groups = array();
        $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
        $count = $groupBean->countItemgroups();
        $groups_objs = $groupBean->getItemgrouplist($count, 0);

        foreach ($groups_objs as $itemgroup) {
            $itemgroupid = $itemgroup['group_id'];
            if ($request->getParameter('checkbox_'.$itemgroupid) || $itemgroup['preselect'] == 1) {
                if ($itemgroup['preselect'] == 1) {
                    $itemgroup['edit_weight'] = $itemgroup['weight'];
                }
                $groups[] = $itemgroupid;
            }
        }

        // do check
        $errors = new Xoonips_Errors();
        $parameters = array();
        $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_NAME;
        if ($name == '') {
            // get groups for select
            $groups = $this->getGroupsForSelect(0);

            // token ticket
            $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_register'));
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
            $viewData['breadcrumbs'] = $breadcrumbs;
            $viewData['name'] = $name;
            $viewData['title'] = $title;
            $viewData['descrip'] = $descrip;
            $viewData['weight'] = $weight;
            $viewData['template'] = $template;
            $viewData['token_ticket'] = $token_ticket;
            $viewData['errors'] = $errors->getView($this->dirname);
            $viewData['dirname'] = $this->dirname;
            $viewData['groups'] = $groups;

            $response->setViewData($viewData);
            $response->setForward('register_success');

            return true;
        } else {
            $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
            if ($itemtypeBean->existItemTypeName(0, $name)) {
                // get groups for select
                $groups = $this->getGroupsForSelect(0);

                // token ticket
                $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_register'));
                $errors->addError('_AM_XOONIPS_ERROR_DUPLICATE_MSG', '', $parameters);
                $viewData['breadcrumbs'] = $breadcrumbs;
                $viewData['name'] = $name;
                $viewData['title'] = $title;
                $viewData['descrip'] = $descrip;
                $viewData['weight'] = $weight;
                $viewData['template'] = $template;
                $viewData['token_ticket'] = $token_ticket;
                $viewData['errors'] = $errors->getView($this->dirname);
                $viewData['dirname'] = $this->dirname;

                $viewData['groups'] = $groups;

                $response->setViewData($viewData);
                $response->setForward('register_success');

                return true;
            }
        }

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('admin_policy_itemtype_register'))) {
            return false;
        }

        // transaction
        $transaction = Xoonips_Transaction::getInstance();
        $transaction->start();

        // insert itemtype
        $itemtype_id = 0;
        if (!$this->insertXoonipsItemtype($itemtype_id, $name, $weight, $descrip, $template, $icon_file)) {
            $transaction->rollback();

            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_REGIST_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('registersave_success');

            return true;
        }

        // insert itemtype group and detail
        if (!$this->insertXoonipsItemtypeGroupAndDetail($itemtype_id)) {
            $transaction->rollback();

            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_REGIST_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('registersave_success');

            return true;
        }

        // update link of type and group
        if (!$this->updateTypeGroupLink($itemtype_id, $groups)) {
            $transaction->rollback();

            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_REGIST_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('registersave_success');

            return true;
        }

        // release mode
        if ($mode == 1) {
            if (!$this->releaseXoonipsItemtype($itemtype_id, $itemtype_id)) {
                $transaction->rollback();

                $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
                $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_RELEASED_MSG_FAILURE;
                $response->setViewData($viewData);
                $response->setForward('registersave_success');

                return true;
            }
        }

        // success
        $transaction->commit();

        $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
        if ($mode == 1) {
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_RELEASED_MSG_SUCCESS;
        } else {
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_REGIST_MSG_SUCCESS;
        }
        $response->setViewData($viewData);
        $response->setForward('registersave_success');

        return true;
    }

    protected function doEdit(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_EDIT_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_MODIFY_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // get requests
        $base_itemtypeid = $request->getParameter('itemtypeid');
        $perpage = $request->getParameter('perpage');
        $startpage = $request->getParameter('start');

        // get base itemtype info
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $baseInfo = $itemtypeBean->getItemtypeEditInfo($base_itemtypeid);
        if (!$baseInfo || count($baseInfo) == 0) {
            die("can't get item type");
        }

        // do copy
        if ($baseInfo['a_released'] == 1 && $baseInfo['b_update_id'] != $base_itemtypeid) {
            // transaction
            $transaction = Xoonips_Transaction::getInstance();
            $transaction->start();

            if (!$this->doCopyItemtype($base_itemtypeid)) {
                $transaction->rollback();
                die('copy item type failure!');
            }

            // success
            $transaction->commit();
        }

        // get edit itemtype info
        $upd_itemtypeid = '';
        $itemtypeInfo = $this->getItemtypeInfoForEdit($itemtypeBean, $base_itemtypeid, $upd_itemtypeid);

        // get itemtype group list
        $groups = $this->getFieldGroupsInfo($base_itemtypeid);

        $group_diff = 0;
        if (self::isGroupLinkDiff($base_itemtypeid)) {
            $group_diff = 1;
        }

        // token ticket
        $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_edit'));

        // get common viewdata
        $viewData = array();

        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['itemtypeInfo'] = $itemtypeInfo;
        $viewData['base_itemtypeid'] = $base_itemtypeid;
        $viewData['upd_itemtypeid'] = $upd_itemtypeid;
        $viewData['group_diff'] = $group_diff;
        $viewData['complement_diff'] = $this->isComplementDiff($upd_itemtypeid, $base_itemtypeid);
        $viewData['groups'] = $groups;
        $viewData['title'] = $title;
        $viewData['description'] = $description;
        $viewData['dirname'] = $this->dirname;
        $viewData['perpage'] = $perpage;
        $viewData['startpage'] = $startpage;

        $response->setViewData($viewData);
        $response->setForward('edit_success');

        return true;
    }

    protected function doEditsave(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_EDIT_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_MODIFY_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // get requests
        $base_itemtypeid = $request->getParameter('itemtypeid');
        $mode = $request->getParameter('mode');

        // get base itemtype info
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $baseInfo = $itemtypeBean->getItemtypeEditInfo($base_itemtypeid);
        if (!$baseInfo || count($baseInfo) == 0) {
            die("can't get item type");
        }

        // do copy
        if ($baseInfo['a_released'] == 1 && $baseInfo['b_update_id'] != $base_itemtypeid) {
            // transaction
            $transaction = Xoonips_Transaction::getInstance();
            $transaction->start();

            if (!$this->doCopyItemtype($base_itemtypeid)) {
                $transaction->rollback();
                die('copy item type failure!');
            }

            // success
            $transaction->commit();
        }

        // get edit itemtype info
        $upd_itemtypeid = '';
        $itemtypeInfo = $this->getItemtypeInfoForEdit($itemtypeBean, $base_itemtypeid, $upd_itemtypeid);

        // get itemtype group list
        $groups = $itemtypeBean->getTypeGroups($base_itemtypeid);

        // do update
        $errors = new Xoonips_Errors();
        $name = $request->getParameter('name');
        $description = $request->getParameter('description');
        $weight = $request->getParameter('weight');
        $template = $request->getParameter('template');
        $icon_file = $request->getFile('icon_file');
        $group_ids = $request->getParameter('group_ids');
        $weights = $request->getParameter('weights');

        // do check
        $parameters = array();
        $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_NAME;
        if ($name == '') {
            // token ticket
            $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_edit'));
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
            $itemtypeInfo['a_name'] = $name;
            $itemtypeInfo['a_description'] = $description;
            $itemtypeInfo['a_weight'] = $weight;
            $itemtypeInfo['a_template'] = $template;
            $viewData['breadcrumbs'] = $breadcrumbs;
            $viewData['token_ticket'] = $token_ticket;
            $viewData['itemtypeInfo'] = $itemtypeInfo;
            $viewData['base_itemtypeid'] = $base_itemtypeid;
            $viewData['upd_itemtypeid'] = $upd_itemtypeid;
            $viewData['groups'] = $groups;
            $viewData['title'] = $title;
            $viewData['description'] = $description;
            $viewData['errors'] = $errors->getView($this->dirname);
            $viewData['dirname'] = $this->dirname;

            $response->setViewData($viewData);
            $response->setForward('edit_success');

            return true;
        } else {
            $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
            if ($itemtypeBean->existItemTypeName($upd_itemtypeid, $name)) {
                // token ticket
                $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_edit'));
                $errors->addError('_AM_XOONIPS_ERROR_DUPLICATE_MSG', '', $parameters);
                $itemtypeInfo['a_name'] = $name;
                $itemtypeInfo['a_description'] = $description;
                $itemtypeInfo['a_weight'] = $weight;
                $itemtypeInfo['a_template'] = $template;
                $viewData['breadcrumbs'] = $breadcrumbs;
                $viewData['token_ticket'] = $token_ticket;
                $viewData['itemtypeInfo'] = $itemtypeInfo;
                $viewData['base_itemtypeid'] = $base_itemtypeid;
                $viewData['upd_itemtypeid'] = $upd_itemtypeid;
                $viewData['groups'] = $groups;
                $viewData['title'] = $title;
                $viewData['description'] = $description;
                $viewData['errors'] = $errors->getView($this->dirname);
                $viewData['dirname'] = $this->dirname;

                $response->setViewData($viewData);
                $response->setForward('edit_success');

                return true;
            }
        }

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('admin_policy_itemtype_edit'))) {
            return false;
        }

        // transaction
        $transaction = Xoonips_Transaction::getInstance();
        $transaction->start();

        // update itemtype
        if (!$this->updateXoonipsItemtype($upd_itemtypeid, $name, $weight, $description, $template, $icon_file)) {
            $transaction->rollback();

            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_MODIFY_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('editsave_success');

            return true;
        }

        // update itemtype group weight
        if (!$this->updateXoonipsItemtypeGroupOrder($base_itemtypeid, $group_ids, $weights)) {
            $transaction->rollback();

            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_MODIFY_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('editsave_success');

            return true;
        }

        // success
        $transaction->commit();

        $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
        if ($mode == 1) {
            $viewData['url'] .= '?op=edit&amp;itemtypeid='.$base_itemtypeid;
        }
        $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_MODIFY_MSG_SUCCESS;
        $response->setViewData($viewData);
        $response->setForward('editsave_success');

        return true;
    }

    protected function doSorteditsave(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_EDIT_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_MODIFY_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // get requests
        $base_itemtypeid = $request->getParameter('itemtypeid');
        $mode = $request->getParameter('mode');
        $group_ids = $request->getParameter('group_ids');
        $weights = $request->getParameter('weights');

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('admin_policy_itemtype_edit'))) {
            return false;
        }

        // transaction
        $transaction = Xoonips_Transaction::getInstance();
        $transaction->start();

        // update itemtype group weight
        if (!$this->updateXoonipsItemtypeGroupOrder($base_itemtypeid, $group_ids, $weights)) {
            $transaction->rollback();

            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_MODIFY_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('editsave_success');

            return true;
        }

        // success
        $transaction->commit();

        $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
        if ($mode == 1) {
            $viewData['url'] .= '?op=edit&amp;itemtypeid='.$base_itemtypeid;
        }
        $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_MODIFY_MSG_SUCCESS;
        $response->setViewData($viewData);
        $response->setForward('editsave_success');

        return true;
    }

    private function setBreadcrumbs($title)
    {
        $breadcrumbs = array(
            array(
                'name' => _AM_XOONIPS_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php',
            ),
            array(
                'name' => _AM_XOONIPS_POLICY_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=Policy',
            ),
            array(
                'name' => _AD_XOONIPS_POLICY_ITEM_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItem',
            ),
            array(
                'name' => _AM_XOONIPS_POLICY_ITEMTYPE_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php',
            ),
            array(
                'name' => $title,
            ),
        );

        return $breadcrumbs;
    }

    // get edit itemtype info
    private function getItemtypeInfoForEdit($itemtypeBean, $itemtypeid, &$upd_itemtypeid)
    {
        $info = $itemtypeBean->getItemtypeEditInfo($itemtypeid);
        $itemtypeInfo = array(
            'a_name' => $info['a_released'] == 1 ? $info['b_name'] : $info['a_name'],
            'a_description' => $info['a_released'] == 1 ? $info['b_description'] : $info['a_description'],
            'a_weight' => $info['a_released'] == 1 ? $info['b_weight'] : $info['a_weight'],
            'a_icon' => $info['a_released'] == 1 ? $info['b_icon'] : $info['a_icon'],
            'a_template' => $info['a_released'] == 1 ? $info['b_template'] : $info['a_template'],
            'b_name' => $info['a_released'] == 1 ? $info['a_name'] : $info['b_name'],
            'b_description' => $info['a_released'] == 1 ? $info['a_description'] : $info['b_description'],
            'b_weight' => $info['a_released'] == 1 ? $info['a_weight'] : $info['b_weight'],
            'b_icon' => $info['a_released'] == 1 ? $info['a_icon'] : $info['b_icon'],
            'b_template' => $info['a_released'] == 1 ? $info['a_template'] : $info['b_template'],
        );
        $upd_itemtypeid = empty($info['b_item_type_id']) ? $info['a_item_type_id'] : $info['b_item_type_id'];

        return $itemtypeInfo;
    }

    private function getFieldGroupsInfo($itemtypeid)
    {
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $groupInfos = $itemtypeBean->getTypeGroups($itemtypeid);

        $groups = array();
        foreach ($groupInfos as $group) {
            if ($group['edit'] == 1) {
                $group['weight'] = $group['edit_weight'];
                $groups[] = $group;
            }
        }

        return $groups;
    }

    private function isGroupDiff($groupid)
    {
        $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
        $info = $groupBean->getGroupEditInfo($groupid, true);
        if ($info['b_name'] != $info['a_name'] || $info['b_xml'] != $info['a_xml']
                || $info['b_occurrence'] != $info['a_occurrence']
                || $info['b_weight'] != $info['a_weight']) {
            return true;
        }
        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        $detailInfos = $detailBean->getGroupDetails($groupid);
        foreach ($detailInfos as $detail) {
            if ($this->isDetailDiff($detailBean, $detail['item_field_detail_id'])) {
                return true;
            }
        }

        return false;
    }

    private function isDiff($base_itemtypeid)
    {
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $upd_itemtypeid = '';
        $info = $this->getItemtypeInfoForEdit($itemtypeBean, $base_itemtypeid, $upd_itemtypeid);
        if ($info['b_name'] != $info['a_name'] || $info['b_description'] != $info['a_description']
                || $info['b_weight'] != $info['a_weight'] || $info['b_icon'] != $info['a_icon']
                || $info['b_template'] != $info['a_template']) {
            return true;
        }

        // get itemtype group list
        if (self::isGroupLinkDiff($base_itemtypeid)) {
            return true;
        }

        if ($this->isComplementDiff($upd_itemtypeid, $base_itemtypeid)) {
            return true;
        }

        return false;
    }

    protected function doCopy(&$request, &$response)
    {
        // get requests
        $itemtypeid = $request->getParameter('itemtypeid');

        // itemtype name double check
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $itemtypeObj = $itemtypeBean->getItemType($itemtypeid);
        if ($itemtypeBean->existItemTypeName(0, $itemtypeObj['name'].'_'._AM_XOONIPS_LABEL_COPY)) {
            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_NAME_DUPLICATE_MSG;
            $response->setViewData($viewData);
            $response->setForward('copy_success');

            return true;
        }

        // transaction
        $transaction = Xoonips_Transaction::getInstance();
        $transaction->start();

        // copy itemtype
        if (!$this->doCopyItemtype($itemtypeid, true)) {
            $transaction->rollback();

            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_COPY_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('copy_success');

            return true;
        }

        // success
        $transaction->commit();

        $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
        $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_COPY_MSG_SUCCESS;
        $response->setViewData($viewData);
        $response->setForward('copy_success');

        return true;
    }

    protected function doImport(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // token ticket
        $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_import'));

        // get common viewdata
        $viewData = array();
        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['title'] = $title;
        $viewData['description'] = $description;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['dirname'] = $this->dirname;

        $response->setViewData($viewData);
        $response->setForward('import_success');

        return true;
    }

    protected function doImportsave(&$request, &$response)
    {
        $import_file = $request->getFile('import_file');
        $mode = $request->getParameter('mode');

        //error
        $errors = new Xoonips_Errors();
        $parameters = array();

        if (empty($import_file['name']) || $import_file['size'] == 0) {
            $errors->addError('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_FILE_NONE', '', '');

            //title
            $title = _AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_TITLE;
            $description = _AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_DESC;

            // breadcrumbs
            $breadcrumbs = $this->setBreadcrumbs($title);

            // token ticket
            $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_import'));

            // get common viewdata
            $viewData = array();
            $viewData['errors'] = $errors->getView($this->dirname);
            $viewData['breadcrumbs'] = $breadcrumbs;
            $viewData['title'] = $title;
            $viewData['description'] = $description;
            $viewData['token_ticket'] = $token_ticket;
            $viewData['dirname'] = $this->dirname;

            $response->setViewData($viewData);
            $response->setForward('import_success');

            return true;
        }

        // check file exists
        if (!file_exists($import_file['tmp_name'])) {
            die("don't exist temporary file '".$import_file['tmp_name']."'.");
        }

        // create temporary directry
        $tmp = Xoonips_Utils::getXooNIpsConfig($this->dirname, 'upload_dir');
        $time = date('YmdHis');
        $uploaddir = "${tmp}/itemtypes-import-${time}";
        if (!mkdir($uploaddir, 0755)) {
            die("can't create directry '${uploaddir}'.");
        }

        //unzip
        $unzip = new UnzipFile();
        if ($unzip->open($import_file['tmp_name'])) {
            $files = $unzip->getFileList();
            foreach ($files as $file) {
                $unzip->extractFile($file, $uploaddir);
            }
            $unzip->close();
        } else {
            $errors->addError('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_UPLOAD_FAILURE', '', $import_file['name']);

            //title
            $title = _AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_TITLE;
            $description = _AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_DESC;

            // breadcrumbs
            $breadcrumbs = $this->setBreadcrumbs($title);

            // token ticket
            $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_import'));

            // get common viewdata
            $viewData = array();
            $viewData['errors'] = $errors->getView($this->dirname);
            $viewData['breadcrumbs'] = $breadcrumbs;
            $viewData['title'] = $title;
            $viewData['description'] = $description;
            $viewData['token_ticket'] = $token_ticket;
            $viewData['dirname'] = $this->dirname;

            $response->setViewData($viewData);
            $response->setForward('import_success');

            return true;
        }
        //extract itemtype zip
        $itemtypes = array();
        $res_dir = opendir($uploaddir);
        while (false !== ($itemtypezip = readdir($res_dir))) {
            $info = pathinfo($itemtypezip);
            $itemtype = $info['filename'];
            if ($itemtypezip == '.' || $itemtypezip == '..') {
                continue;
            }
            if ($unzip->open($uploaddir.'/'.$itemtypezip)) {
                $files = $unzip->getFileList();
                foreach ($files as $file) {
                    $unzip->extractFile($file, $uploaddir.'/'.$itemtype);
                }
                $unzip->close();
                $itemtypes[] = $itemtype;
                //rm item type zip
                unlink($uploaddir.'/'.$itemtypezip);
            } else {
                $errors->addError('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_UPLOAD_FAILURE', '', $import_file['name']);

                //title
                $title = _AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_TITLE;
                $description = _AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_DESC;

                // breadcrumbs
                $breadcrumbs = $this->setBreadcrumbs($title);

                // token ticket
                $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_import'));

                // get common viewdata
                $viewData = array();
                $viewData['errors'] = $errors->getView($this->dirname);
                $viewData['breadcrumbs'] = $breadcrumbs;
                $viewData['title'] = $title;
                $viewData['description'] = $description;
                $viewData['token_ticket'] = $token_ticket;
                $viewData['dirname'] = $this->dirname;

                $viewData['fname'] = $import_file['tmp_name'];

                $response->setViewData($viewData);
                $response->setForward('import_success');

                return true;
            }
        }
        closedir($res_dir);

        //Item type
        $importItemtype = new Xoonips_ImportItemType($this->dirname, $this->trustDirname);

        // transaction
        $transaction = Xoonips_Transaction::getInstance();
        $transaction->start();
        $successFlag = false;
        $successMsg = array();
        $importFlag = false;
        if ($mode == 1) {
            $importFlag = true;
        }
        foreach ($itemtypes as $itemtype) {
            if (!$importItemtype->importItemType($uploaddir, $itemtype, $importFlag, $errors)) {
                $transaction->rollback();
                if ($importFlag) {
                    $errors->addError('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_FAILURE', '', urldecode($itemtype));
                } else {
                    $errors->addError('_AM_XOONIPS_POLICY_ITEMTYPE_CHECK_FAILURE', '', urldecode($itemtype));
                }
            } else {
                $successFlag = true;
                if ($importFlag) {
                    $successMsg[] = sprintf(_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_SUCCESS, urldecode($itemtype));
                } else {
                    $successMsg[] = sprintf(_AM_XOONIPS_POLICY_ITEMTYPE_CHECK_SUCCESS, urldecode($itemtype));
                }
            }
        }

        //remove export zip file
        FileUtils::deleteDirectory($uploaddir);

        // success
        $transaction->commit();

        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // token ticket
        $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_import'));

        // get common viewdata
        $viewData = array();
        if ($errors->hasError()) {
            $errors->addError('_AM_XOONIPS_POLICY_ITEMTYPE_IMPORT_FILE_FAILURE', '', $import_file['name']);
            $viewData['errors'] = $errors->getView($this->dirname);
        }

        if ($successFlag) {
            if (!$errors->hasError()) {
                if ($importFlag) {
                    $successMsg[] = sprintf(_AM_XOONIPS_POLICY_ITEMTYPE_IMPORTED_SUCCESS, $import_file['name']);
                } else {
                    $successMsg[] = sprintf(_AM_XOONIPS_POLICY_ITEMTYPE_CHECKED_SUCCESS, $import_file['name']);
                }
            }

            $viewData['import_success'] = $successFlag;
            $viewData['import_success_msg'] = $successMsg;
        }

        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['title'] = $title;
        $viewData['description'] = $description;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['dirname'] = $this->dirname;

        $response->setViewData($viewData);
        $response->setForward('import_success');

        return true;
    }

    protected function doDelete(&$request, &$response)
    {
        // get requests
        $itemtypeid = $request->getParameter('itemtypeid');

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('admin_policy_itemtype_default'))) {
            return false;
        }

        // delete message
        $delete_msg = _AM_XOONIPS_POLICY_ITEMTYPE_DELETE_MSG_SUCCESS;

        // delete all
        if (!$this->deleteAll($itemtypeid)) {
            $delete_msg = _AM_XOONIPS_POLICY_ITEMTYPE_DELETE_MSG_FAILURE;
        }

        $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
        $viewData['redirect_msg'] = $delete_msg;
        $response->setViewData($viewData);
        $response->setForward('delete_success');

        return true;
    }

    protected function doRelease(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_EDIT_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_MODIFY_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // get requests
        $base_itemtypeid = $request->getParameter('itemtypeid');

        // get base itemtype info
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $baseInfo = $itemtypeBean->getItemtypeEditInfo($base_itemtypeid);
        if (!$baseInfo || count($baseInfo) == 0) {
            die("can't get item type");
        }

        // do copy
        if ($baseInfo['a_released'] == 1 && $baseInfo['b_update_id'] != $base_itemtypeid) {
            // transaction
            $transaction = Xoonips_Transaction::getInstance();
            $transaction->start();

            if (!$this->doCopyItemtype($base_itemtypeid)) {
                $transaction->rollback();
                die('copy item type failure!');
            }

            // success
            $transaction->commit();
        }

        // get edit itemtype info
        $upd_itemtypeid = '';
        $itemtypeInfo = $this->getItemtypeInfoForEdit($itemtypeBean, $base_itemtypeid, $upd_itemtypeid);

        // get itemtype group list
        $groups = $itemtypeBean->getTypeGroups($base_itemtypeid);

        // do update
        $errors = new Xoonips_Errors();
        $name = $request->getParameter('name');
        $description = $request->getParameter('description');
        $weight = $request->getParameter('weight');
        $template = $request->getParameter('template');
        $icon_file = $request->getFile('icon_file');
        $group_ids = $request->getParameter('group_ids');
        $weights = $request->getParameter('weights');

        // token ticket
        $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_edit'));

        // do check
        $parameters = array();
        $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_NAME;
        if ($name == '') {
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
            $viewData['breadcrumbs'] = $breadcrumbs;
            $viewData['token_ticket'] = $token_ticket;
            $viewData['itemtypeInfo'] = $itemtypeInfo;
            $viewData['base_itemtypeid'] = $base_itemtypeid;
            $viewData['upd_itemtypeid'] = $upd_itemtypeid;
            $viewData['groups'] = $groups;
            $viewData['title'] = $title;
            $viewData['description'] = $description;
            $viewData['errors'] = $errors->getView($this->dirname);
            $viewData['dirname'] = $this->dirname;

            $response->setViewData($viewData);
            $response->setForward('edit_success');

            return true;
        } else {
            $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
            if ($itemtypeBean->existItemTypeName($upd_itemtypeid, $name)) {
                $errors->addError('_AM_XOONIPS_ERROR_DUPLICATE_MSG', '', $parameters);
                $viewData['breadcrumbs'] = $breadcrumbs;
                $viewData['token_ticket'] = $token_ticket;
                $viewData['itemtypeInfo'] = $itemtypeInfo;
                $viewData['base_itemtypeid'] = $base_itemtypeid;
                $viewData['upd_itemtypeid'] = $upd_itemtypeid;
                $viewData['groups'] = $groups;
                $viewData['title'] = $title;
                $viewData['description'] = $description;
                $viewData['errors'] = $errors->getView($this->dirname);
                $viewData['dirname'] = $this->dirname;

                $response->setViewData($viewData);
                $response->setForward('edit_success');

                return true;
            }
        }

        $itemtypeInfo['a_name'] = $name;
        $itemtypeInfo['a_description'] = $description;
        $itemtypeInfo['a_weight'] = $weight;
        $itemtypeInfo['a_template'] = $template;

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('admin_policy_itemtype_edit'))) {
            return false;
        }

        // transaction
        $transaction = Xoonips_Transaction::getInstance();
        $transaction->start();

        // update itemtype
        if (!$this->updateXoonipsItemtype($upd_itemtypeid, $name, $weight, $description, $template, $icon_file, true)) {
            $transaction->rollback();

            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_RELEASED_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('release_success');

            return true;
        }

        // update copy data to base data
        if (!$this->releaseXoonipsItemtype($base_itemtypeid, $upd_itemtypeid)) {
            $transaction->rollback();

            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_RELEASED_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('release_success');

            return true;
        }

        // success
        $transaction->commit();

        // delete templates_c file
        $this->deleteTemplatesFile($base_itemtypeid);

        $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
        $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_RELEASED_MSG_SUCCESS;
        $response->setViewData($viewData);
        $response->setForward('release_success');

        return true;
    }

    protected function doBreak(&$request, &$response)
    {
        // get requests
        $itemtypeid = $request->getParameter('upd_itemtypeid');

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('admin_policy_itemtype_edit'))) {
            return false;
        }

        // delete message
        $delete_msg = _AM_XOONIPS_POLICY_ITEMTYPE_BREAK_MSG_SUCCESS;

        // delete all
        if (!$this->deleteAll($itemtypeid)) {
            $delete_msg = _AM_XOONIPS_POLICY_ITEMTYPE_BREAK_MSG_FAILURE;
        }

        $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php';
        $viewData['redirect_msg'] = $delete_msg;
        $response->setViewData($viewData);
        $response->setForward('break_success');

        return true;
    }

    protected function doExport(&$request, &$response)
    {
        // get requests
        $itemtypeid = $request->getParameter('itemtypeid');

        //get Export aItemType XML Elment Data
        //item type
        $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $itemTypeObj = $itemTypeBean->getItemtype($itemtypeid);
        if ($itemTypeObj === false) {
            return false;
        }
        //item group
        $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
        $groupObj = $groupBean->getExportItemTypeGroup($itemtypeid);
        if ($groupObj === false) {
            return false;
        }
        //value set (item select list)
        $valueSetBean = Xoonips_BeanFactory::getBean('ItemFieldValueSetBean', $this->dirname, $this->trustDirname);
        $valueSetObj = $valueSetBean->getExportItemTypeValueSet();
        if ($valueSetObj === false) {
            return false;
        }
        //item complement
        $complementBean = Xoonips_BeanFactory::getBean('ComplementBean', $this->dirname, $this->trustDirname);
        $complementObj = $complementBean->getExportItemTypeComplement($itemtypeid);
        if ($complementObj === false) {
            return false;
        }
        //item sort
        $sortHandler = Xoonips_Utils::getModuleHandler('ItemSort', $this->dirname);
        $sortObj = $sortHandler->getExportDataForItemType($itemtypeid);
        if ($sortObj === false) {
            return false;
        }
        //OAI-PMH
        $oaipmhBean = Xoonips_BeanFactory::getBean('OaipmhSchemaItemtypeLinkBean', $this->dirname, $this->trustDirname);
        $oaipmhObj = $oaipmhBean->getExportItemTypeOaipmh($itemtypeid);
        if ($oaipmhObj === false) {
            return false;
        }

        //TODO move class/XmlItemTypeExport.php
        // get itemtype element
        $xml = $this->makeExportItemTypeXML($itemTypeObj, $groupObj, $valueSetObj, $complementObj, $sortObj, $oaipmhObj);
        if (!$xml) {
            return false;
        }

        // create temporary directry
        $tmp = '/tmp';
        $time = date('YmdHis');
        $tmpdir = "${tmp}/export_itemtype_tmp_${time}";
        if (!mkdir($tmpdir, 755)) {
            return false;
        }

        // create item type directry
        //file name URL encode
        $fname = urlencode($itemTypeObj['name']);
        $itemtypedir = "${tmpdir}/${fname}";
        if (!mkdir($itemtypedir, 755)) {
            return false;
        }

        //make xml file
        $xmlfile = "${itemtypedir}/${fname}.xml";
        $fhdl = fopen($xmlfile, 'w');
        if (!$fhdl) {
            die("can't open file '${xmlfile}' for write.");
        }
        if (!fwrite($fhdl, $xml)) {
            return false;
        }
        fclose($fhdl);

        //make template file
        $templateSet = false;
        if ($itemTypeObj['template'] != null) {
            $templateSet = true;
            $templatefile = "${itemtypedir}/${fname}.tpl";
            $fhdl = fopen($templatefile, 'w');
            $template = $itemTypeObj['template'];
            if (!$fhdl) {
                die("can't open file '${templatefile}' for write.");
            }
            if (!fwrite($fhdl, $template)) {
                return false;
            }
        }

        //copy image file
        $iconSet = false;
        if ($itemTypeObj['icon'] != null) {
            $imgsrc = XOOPS_ROOT_PATH.'/modules/'.$this->dirname.'/images/'.$itemTypeObj['icon'];
            if (file_exists($imgsrc)) {
                $iconSet = true;
                $imgdst = $itemtypedir.'/'.$itemTypeObj['icon'];
                if (!copy($imgsrc, $imgdst)) {
                    return false;
                }
            }
        }

        //create zip
        $itemtype = $fname.'.zip';
        $itemtypezip = $tmpdir.'/'.$itemtype;
        $zipClass = new ZipFile();
        if ($zipClass->open($itemtypezip)) {
            $zipClass->add($xmlfile, $fname.'.xml');
            if ($templateSet) {
                $zipClass->add($templatefile, $fname.'.tpl');
            }
            if ($iconSet) {
                $zipClass->add($imgdst, $itemTypeObj['icon']);
            }
            $zipClass->close();
        } else {
            return false;
        }
        //delete
        unlink($xmlfile);
        unlink($templatefile);
        unlink($imgdst);
        rmdir($itemtypedir);

        $time = date('YmdHis');
        $export = "export_itemtype_${fname}_${time}.zip";
        $exportzip = "${tmp}/${export}";
// 		$res = $zip->open($exportzip, ZipArchive::CREATE);
// 		if ($res === true) {
// 			 $zip->addfile($itemtypezip, $itemtype);
// 		}
// 		$zip->close();
        if ($zipClass->open($exportzip)) {
            $zipClass->add($itemtypezip, $itemtype);
            $zipClass->close();
        } else {
            return false;
        }
        unlink($itemtypezip);
        rmdir($tmpdir);

        FileUtils::deleteFileOnExit($exportzip);
        CacheUtils::downloadFile(false, false, 'application/x-zip', $exportzip, $export, _CHARSET);
    }

    protected function doExports(&$request, &$response)
    {
        //get Export aItemType XML Elment Data
                //itemtype
                $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);

        $itemtypeids = array();
        $itemtypeids = $itemTypeBean->getAllItemTypeId();
                //create zip
                $zipClass = new ZipFile();

        // create temporary directry
        $tmp = '/tmp';
        $time = date('YmdHis');
        $tmpdir = "${tmp}/export_itemtype_all_tmp_${time}";
        if (!mkdir($tmpdir, 755)) {
            return false;
        }
        //loop start
        $itemtypes = array();
        foreach ($itemtypeids as $itemtypeid) {
            //item type
                    $itemTypeObj = $itemTypeBean->getItemtype($itemtypeid);
            if ($itemTypeObj === false) {
                return false;
            }
                       //item group
                    $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
            $groupObj = $groupBean->getExportItemTypeGroup($itemtypeid);
            if ($groupObj === false) {
                return false;
            }
                    //value set (item select list)
                    $valueSetBean = Xoonips_BeanFactory::getBean('ItemFieldValueSetBean', $this->dirname, $this->trustDirname);
            $valueSetObj = $valueSetBean->getExportItemTypeValueSet();
            if ($valueSetObj === false) {
                return false;
            }
                    //item complement
                    $complementBean = Xoonips_BeanFactory::getBean('ComplementBean', $this->dirname, $this->trustDirname);
            $complementObj = $complementBean->getExportItemTypeComplement($itemtypeid);
            if ($complementObj === false) {
                return false;
            }
                    //item sort
            $sortHandler = Xoonips_Utils::getModuleHandler('ItemSort', $this->dirname);
            $sortObj = $sortHandler->getExportDataForItemType($itemtypeid);
            if ($sortObj === false) {
                return false;
            }
                    //OAI-PMH
                    $oaipmhBean = Xoonips_BeanFactory::getBean('OaipmhSchemaItemtypeLinkBean', $this->dirname, $this->trustDirname);
            $oaipmhObj = $oaipmhBean->getExportItemTypeOaipmh($itemtypeid);
            if ($oaipmhObj === false) {
                return false;
            }

            // get itemtype element
            $xml = $this->makeExportItemTypeXML($itemTypeObj, $groupObj, $valueSetObj, $complementObj, $sortObj, $oaipmhObj);
            if (!$xml) {
                return false;
            }

                    // create item type directry
                    //file name URL encode
                    $fname = urlencode($itemTypeObj['name']);
            $itemtypedir = "${tmpdir}/${fname}";
            if (!mkdir($itemtypedir, 755)) {
                return false;
            }

                    //make xml file
                    $xmlfile = "${itemtypedir}/${fname}.xml";
            $fhdl = fopen($xmlfile, 'w');
            if (!$fhdl) {
                die("can't open file '${xmlfile}' for write.");
            }
            if (!fwrite($fhdl, $xml)) {
                return false;
            }
            fclose($fhdl);

                    //make template file
            $templateSet = false;
            if ($itemTypeObj['template'] != null) {
                $templateSet = true;
                $templatefile = "${itemtypedir}/${fname}.tpl";
                $fhdl = fopen($templatefile, 'w');
                $template = $itemTypeObj['template'];
                if (!$fhdl) {
                    die("can't open file '${templatefile}' for write.");
                }
                if (!fwrite($fhdl, $template)) {
                    return false;
                }
            }

            //copy image file
            $iconSet = false;
            if ($itemTypeObj['icon'] != null) {
                $imgsrc = XOOPS_ROOT_PATH.'/modules/'.$this->dirname.'/images/'.$itemTypeObj['icon'];
                if (file_exists($imgsrc)) {
                    $iconSet = true;
                    $imgdst = $itemtypedir.'/'.$itemTypeObj['icon'];
                    if (!copy($imgsrc, $imgdst)) {
                        return false;
                    }
                }
            }

                // itemtype dir
            $itemtype = $fname.'.zip';
            $itemtypezip = $tmpdir.'/'.$itemtype;
// 			$res = $zip->open ( $itemtypezip, ZipArchive::CREATE );
// 			if ($res === true) {
// 				$zip->addfile ( $xmlfile, $fname . ".xml" );
// 				if ($templateSet) {
// 					$zip->addfile ( $templatefile, $fname . ".tpl" );
// 				}
// 				if ($iconSet) {
// 					$zip->addfile ( $imgdst, $itemTypeObj ['icon'] );
// 				}
// 			}
// 			$zip->close ();

            if ($zipClass->open($itemtypezip)) {
                $zipClass->add($xmlfile, $fname.'.xml');
                if ($templateSet) {
                    $zipClass->add($templatefile, $fname.'.tpl');
                }
                if ($iconSet) {
                    $zipClass->add($imgdst, $itemTypeObj['icon']);
                }
                $zipClass->close();
            } else {
                return false;
            }
            // delete
            unlink($xmlfile);
            unlink($templatefile);
            unlink($imgdst);
            rmdir($itemtypedir);
            $itemtypes[] = $itemtype;
        }
        $time = date('YmdHis');
        $export = "export_itemtype_all_${time}.zip";
        $exportzip = "${tmp}/${export}";
// 		$res = $zip->open ( $exportzip, ZipArchive::CREATE );
// 		if ($res === true) {
// 			foreach ( $itemtypes as $itemtype ) {
// 				$zip->addfile ( $tmpdir . "/" . $itemtype, $itemtype );
// 			}
// 		}
// 		$zip->close ();
        if ($zipClass->open($exportzip)) {
            foreach ($itemtypes as $itemtype) {
                $zipClass->add($tmpdir.'/'.$itemtype, $itemtype);
            }
            $zipClass->close();
        } else {
            return false;
        }
        foreach ($itemtypes as $itemtype) {
            unlink($tmpdir.'/'.$itemtype);
        }
        rmdir($tmpdir);

        FileUtils::deleteFileOnExit($exportzip);
        CacheUtils::downloadFile(false, false, 'application/x-zip', $exportzip, $export, _CHARSET);
    }

    /**
     * makeExportItemTypeXML.
     *
     * @param object itemTypeObj
     * @param object groupObj
     * @param object valueSetObj
     * @param object complementObj
     * @param object sortObj
     * @param object oaipmhObj
     *
     * @return stirng xml
     **/
    private function makeExportItemTypeXML($itemTypeObj, $groupObj, $valueSetObj, $complementObj, $sortObj, $oaipmhObj)
    {
        //get detail
        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        $xml = '';
        $itemTypeName = urlencode($itemTypeObj['name']);
        //header
        $xml .= "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
        $xml .= "<item_type>\n";
        //itemtype
        $xml .= ' <weight>'.StringUtils::xmlSpecialChars($itemTypeObj['weight'], _CHARSET)."</weight>\n";
        $xml .= ' <name>'.StringUtils::xmlSpecialChars($itemTypeName, _CHARSET)."</name>\n";
        $xml .= ' <description>'.StringUtils::xmlSpecialChars($itemTypeObj['description'], _CHARSET)."</description>\n";
        $xml .= ' <icon>'.StringUtils::xmlSpecialChars($itemTypeObj['icon'], _CHARSET)."</icon>\n";
        $xml .= ' <mime_type>'.StringUtils::xmlSpecialChars($itemTypeObj['mime_type'], _CHARSET)."</mime_type>\n";
        //template file_path
        $xml .= ' <template>'.StringUtils::xmlSpecialChars($itemTypeName.'/'.$itemTypeName, _CHARSET).".tpl</template>\n";
        //Group
        $itemTypeLists = array();
        foreach ($groupObj as $group) {
            $xml .= " <group>\n";
            $xml .= '  <name>'.StringUtils::xmlSpecialChars($group['name'], _CHARSET)."</name>\n";
            $xml .= '  <xml>'.StringUtils::xmlSpecialChars($group['xml'], _CHARSET)."</xml>\n";
            $xml .= '  <occurrence>'.StringUtils::xmlSpecialChars($group['occurrence'], _CHARSET)."</occurrence>\n";
            //item detail
            $detailObj = $detailBean->getExportItemTypeDetail($group['group_id']);
            if (!$detailObj) {
                return false;
            }
            foreach ($detailObj as $detail) {
                $xml .= "  <item>\n";
                $xml .= '   <table_name>'.StringUtils::xmlSpecialChars($detail['table_name'], _CHARSET)."</table_name>\n";
                $xml .= '   <column_name>'.StringUtils::xmlSpecialChars($detail['column_name'], _CHARSET)."</column_name>\n";
                $xml .= '   <group_id>'.StringUtils::xmlSpecialChars($detail['group_id'], _CHARSET)."</group_id>\n";
                $xml .= '   <name>'.StringUtils::xmlSpecialChars($detail['name'], _CHARSET)."</name>\n";
                $xml .= '   <xml>'.StringUtils::xmlSpecialChars($detail['xml'], _CHARSET)."</xml>\n";
                $xml .= '   <view_type_id>'.StringUtils::xmlSpecialChars($detail['view_type_id'], _CHARSET)."</view_type_id>\n";
                $xml .= '   <data_type_id>'.StringUtils::xmlSpecialChars($detail['data_type_id'], _CHARSET)."</data_type_id>\n";
                $xml .= '   <data_length>'.StringUtils::xmlSpecialChars($detail['data_length'], _CHARSET)."</data_length>\n";
                $xml .= '   <data_decimal_places>'.StringUtils::xmlSpecialChars($detail['data_decimal_places'], _CHARSET)."</data_decimal_places>\n";
                if (isset($detail['default_value'])) {
                    $xml .= '   <default_value>'.StringUtils::xmlSpecialChars($detail['default_value'], _CHARSET)."</default_value>\n";
                }
                if (isset($detail['list'])) {
                    $xml .= '   <list>'.StringUtils::xmlSpecialChars($detail['list'], _CHARSET)."</list>\n";
                    $itemTypeLists[] = $detail['list'];
                }
                $xml .= '   <essential>'.StringUtils::xmlSpecialChars($detail['essential'], _CHARSET)."</essential>\n";
                $xml .= '   <detail_target>'.StringUtils::xmlSpecialChars($detail['detail_target'], _CHARSET)."</detail_target>\n";
                $xml .= '   <scope_search>'.StringUtils::xmlSpecialChars($detail['scope_search'], _CHARSET)."</scope_search>\n";
                $xml .= "  </item>\n";
            }
            $xml .= " </group>\n";
        }
        //Value Set
        foreach ($valueSetObj as $valueSet) {
            if (in_array($valueSet['select_name'], $itemTypeLists)) {
                $xml .= " <item_list>\n";
                $xml .= '  <select_name>'.StringUtils::xmlSpecialChars($valueSet['select_name'], _CHARSET)."</select_name>\n";
                $xml .= '  <title_id>'.StringUtils::xmlSpecialChars($valueSet['title_id'], _CHARSET)."</title_id>\n";
                $xml .= '  <title>'.StringUtils::xmlSpecialChars($valueSet['title'], _CHARSET)."</title>\n";
                $xml .= " </item_list>\n";
            }
        }

        //Complement
        if (count($complementObj) > 0) {
            foreach ($complementObj as $complement) {
                $xml .= " <complement>\n";
                $xml .= '  <complement_id>'.StringUtils::xmlSpecialChars($complement['complement_id'], _CHARSET)."</complement_id>\n";
                $xml .= '  <base_item_field_detail_id>'.StringUtils::xmlSpecialChars($complement['base_item_field_detail_id'], _CHARSET)."</base_item_field_detail_id>\n";
                $xml .= '  <complement_detail_id>'.StringUtils::xmlSpecialChars($complement['complement_detail_id'], _CHARSET)."</complement_detail_id>\n";
                $xml .= '  <item_field_detail_id>'.StringUtils::xmlSpecialChars($complement['item_field_detail_id'], _CHARSET)."</item_field_detail_id>\n";
                $xml .= " </complement>\n";
            }
        }

        //Sort
        if (count($sortObj) > 0) {
            foreach ($sortObj as $sort) {
                $xml .= " <sort>\n";
                $xml .= '  <sort_id>'.StringUtils::xmlSpecialChars($sort['sort_id'], _CHARSET)."</sort_id>\n";
                $xml .= '  <item_field_detail_id>'.StringUtils::xmlSpecialChars($sort['item_field_detail_id'], _CHARSET)."</item_field_detail_id>\n";
                $xml .= " </sort>\n";
            }
        }

        //OAI-PMH
        if (count($oaipmhObj) > 0) {
            foreach ($oaipmhObj as $oaipmh) {
                $xml .= " <oaipmh>\n";
                $xml .= '  <schema_id>'.StringUtils::xmlSpecialChars($oaipmh['schema_id'], _CHARSET)."</schema_id>\n";
                $xml .= '  <item_field_detail_id>'.StringUtils::xmlSpecialChars($oaipmh['item_field_detail_id'], _CHARSET)."</item_field_detail_id>\n";
                $xml .= '  <value>'.StringUtils::xmlSpecialChars($oaipmh['value'], _CHARSET)."</value>\n";
                $xml .= " </oaipmh>\n";
            }
        }
        //end
        $xml .= "</item_type>\n";

        return $xml;
    }

    private function setGroupBreadcrumbs($title, $base_itemtypeid)
    {
        $breadcrumbs = array(
            array(
                'name' => _AM_XOONIPS_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php',
            ),
            array(
                'name' => _AM_XOONIPS_POLICY_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=Policy',
            ),
            array(
                'name' => _AD_XOONIPS_POLICY_ITEM_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItem',
            ),
            array(
                'name' => _AM_XOONIPS_POLICY_ITEMTYPE_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php',
            ),
            array(
                'name' => _AM_XOONIPS_POLICY_ITEMTYPE_EDIT_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php?'.'op=edit&itemtypeid='.$base_itemtypeid,
            ),
            array(
                'name' => $title,
            ),
        );

        return $breadcrumbs;
    }

    // get edit field group info
    private function getGroupInfoForEdit($groupid)
    {
        $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
        $info = $groupBean->getGroupEditInfo($groupid, true);
        $groupInfo = array(
            'a_name' => $info['a_released'] == 1 ? $info['b_name'] : $info['a_name'],
            'a_xml' => $info['a_released'] == 1 ? $info['b_xml'] : $info['a_xml'],
            'a_occurrence' => $info['a_released'] == 1 ? $info['b_occurrence'] : $info['a_occurrence'],
            'a_weight' => $info['a_released'] == 1 ? $info['b_weight'] : $info['a_weight'],
            'b_name' => $info['a_released'] == 1 ? $info['a_name'] : $info['b_name'],
            'b_xml' => $info['a_released'] == 1 ? $info['a_xml'] : $info['b_xml'],
            'b_occurrence' => $info['a_released'] == 1 ? $info['a_occurrence'] : $info['b_occurrence'],
            'b_weight' => $info['a_released'] == 1 ? $info['a_weight'] : $info['b_weight'],
        );

        return $groupInfo;
    }

    private function isDetailDiff($detailBean, $detailid)
    {
        $dinfo = $detailBean->getDetailEditInfo($detailid, true);

        return $dinfo['b_name'] != $dinfo['a_name'] || $dinfo['b_xml'] != $dinfo['a_xml']
                || $dinfo['b_view_type_id'] != $dinfo['a_view_type_id']
                || $dinfo['b_data_type_id'] != $dinfo['a_data_type_id']
                || $dinfo['b_data_length'] != $dinfo['a_data_length']
                || $dinfo['b_data_decimal_places'] != $dinfo['a_data_decimal_places']
                || $dinfo['b_default_value'] != $dinfo['a_default_value']
                || $dinfo['b_list'] != $dinfo['a_list']
                || $dinfo['b_essential'] != $dinfo['a_essential']
                || $dinfo['b_detail_display'] != $dinfo['a_detail_display']
                || $dinfo['b_detail_target'] != $dinfo['a_detail_target']
                || $dinfo['b_scope_search'] != $dinfo['a_scope_search']
                || $dinfo['b_nondisplay'] != $dinfo['a_nondisplay']
                || $dinfo['b_weight'] != $dinfo['a_weight'];
    }

    protected function doComplement(&$request, &$response)
    {
        // get requests
        $itemtypeid = $request->getParameter('itemtypeid');
        $base_itemtypeid = $request->getParameter('base_itemtypeid');
        $select = $request->getParameter('select');

        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_COMPLEMENT_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_COMPLEMENT_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setGroupBreadcrumbs($title, $base_itemtypeid);

        $baseDetailId = 0;
        $groupId = 0;
        $relation['detail'] = array();
        $itemtype['detail'] = array();
        $basecomplementlist = array();
        $complementBean = Xoonips_BeanFactory::getBean('ComplementBean', $this->dirname, $this->trustDirname);
        $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        $linkBean = Xoonips_BeanFactory::getBean('ItemFieldDetailComplementLinkBean', $this->dirname, $this->trustDirname);
        if (isset($select) && $select != '') {
            $temp = explode(',', $select);
            $relaId = $temp[0];
            $baseDetailId = $temp[1];
            $groupId = $temp[2];
            $detailrelation = $linkBean->getInfoByItemtypeIdAndComplementId($itemtypeid, $relaId, $baseDetailId, $groupId);

            $relationdetail = $complementBean->getComplementDetailInfo($relaId);
            foreach ($relationdetail as $vars) {
                $relation['detail'][] = array(
                    'complement_detail_id' => $vars['complement_detail_id'],
                    'item_field_detail_id' => '',
                    'group_id' => '',
                    'title' => $vars['title'],
                );
            }
            if (count($detailrelation) > 0) {
                // complement detail
                $relationdetail = $linkBean->getComplementDetailAndDetailLink($relaId, $baseDetailId, $groupId);

                foreach ($relationdetail as $vars) {
                    foreach ($relation['detail'] as &$detail) {
                        if ($detail['complement_detail_id'] == $vars['complement_detail_id']) {
                            $detail['item_field_detail_id'] = $vars['item_field_detail_id'];
                            $detail['group_id'] = $vars['group_id'];
                        }
                    }
                }
            }

            // detail list
            $detaillist = $groupBean->getDetailList($base_itemtypeid, $baseDetailId);

            $itemtype['detail'][] = array(
                'detail_id' => '',
                'group_id' => '',
                'title' => '--------------',
            );

            foreach ($detaillist as $vars) {
                $itemtype['detail'][] = array(
                      'detail_id' => $vars['item_field_detail_id'],
                      'group_id' => $vars['group_id'],
                    'title' => $vars['group_name'].' : '.$vars['detail_name'],
                );
            }

            $detailInfo = $detailBean->getDetailEditInfo($baseDetailId, true);
            $detailrelation = $linkBean->getInfoByItemtypeIdAndComplementId($base_itemtypeid, $relaId, $detailInfo['b_item_field_detail_id']);
            if (count($detailrelation) == 0) {
                // relation detail
                $relationdetail = $complementBean->getComplementDetailInfo($relaId);
                foreach ($relationdetail as $vars) {
                    $basecomplementlist[$vars['complement_detail_id']]['detail_id'] = '';
                }
            } else {
                // complement detail
                $relationdetail = $linkBean->getComplementDetailAndDetailLink($relaId, $detailInfo['b_item_field_detail_id']);
                foreach ($relationdetail as $vars) {
                    $basecomplementlist[$vars['complement_detail_id']]['detail_id'] = $vars['item_field_detail_id'];
                }
            }
            $detaillist = $groupBean->getDetailList($base_itemtypeid, $detailInfo['b_item_field_detail_id']);
            foreach ($detaillist as $vars) {
                foreach ($basecomplementlist as &$basecomplement) {
                    if ($basecomplement['detail_id'] == $vars['item_field_detail_id']) {
                        $basecomplement['title'] = $vars['group_name'].' : '.$vars['detail_name'];
                    }
                }
            }
        }

        // itemtype name
        $itmtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $itemtypeName = $itmtypeBean->getItemTypeName($itemtypeid);

        // relation list
        $relationlist = $complementBean->getComplementList($base_itemtypeid);

        $itemtype['relation'] = array();
        $itemtype['relation'][] = array(
            'complement_id' => '',
            'title' => '--------------',
            'selected' => '',
        );
        foreach ($relationlist as $vars) {
            $itemtype['relation'][] = array(
                'complement_id' => $vars['complement_id'].','.$vars['item_field_detail_id'].','.$vars['group_id'],
                'title' => $vars['group_name'].' : '.$vars['detail_name'],
                'selected' => ($vars['item_field_detail_id'] == $baseDetailId && $vars['group_id'] == $groupId) ? 'selected="selected"' : '',
            );
        }

        // token ticket
        $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_relation'));

        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['itemtypeid'] = $itemtypeid;
        $viewData['base_itemtypeid'] = $base_itemtypeid;
        $viewData['itemtypename'] = $itemtypeName;
        $viewData['relationlist'] = $itemtype['relation'];
        $viewData['detaillist'] = $itemtype['detail'];
        $viewData['relationdetaillist'] = $relation['detail'];
        $viewData['basecomplementlist'] = $basecomplementlist;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['title'] = $title;
        $viewData['description'] = $description;
        $viewData['dirname'] = $this->dirname;

        $response->setViewData($viewData);
        $response->setForward('complement_success');

        return true;
    }

    protected function doComplementsave(&$request, &$response)
    {
        // check token ticket
        if (!$this->validateToken($this->modulePrefix('admin_policy_itemtype_relation'))) {
            return false;
        }

        // get requests
        $request = new Xoonips_Request();
        $itemtypeid = $request->getParameter('itemtypeid');
        $base_itemtypeid = $request->getParameter('base_itemtypeid');
        $selectid = $request->getParameter('selectid');
        $selectdetailids = $request->getParameter('selectdetailid');

        // drelation message
        $drelation_msg = _AM_XOONIPS_POLICY_ITEMTYPE_RELATION_MSG_SUCCESS;

        // do relation
        if (!$this->insItemtypeDetailRelation($itemtypeid, $selectid, $selectdetailids)) {
            $drelation_msg = _AM_XOONIPS_POLICY_ITEMTYPE_RELATION_MSG_FAILURE;
        }

        $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php?'.'op=edit&itemtypeid='.$base_itemtypeid;
        $viewData['redirect_msg'] = $drelation_msg;
        $response->setViewData($viewData);
        $response->setForward('complementsave_success');

        return true;
    }

    private function isComplementDiff($itemtypeid, $base_itemtypeid)
    {
        $fieldDetails = array();
        $baseFieldDetails = array();

        // complement list
        $complinkBean = Xoonips_BeanFactory::getBean('ItemFieldDetailComplementLinkBean', $this->dirname, $this->trustDirname);
        $complementlist = $complinkBean->getFieldDetailComplementByItemtypeId($itemtypeid);
        foreach ($complementlist as $vars) {
            $fieldDetails[] = array(
                'complement_id' => $vars['complement_id'],
                'item_field_detail_id' => $vars['item_field_detail_id'],
                'update_id' => $vars['update_id'],
            );
        }
        // complement list
        $complementlist = $complinkBean->getFieldDetailComplementByItemtypeId($base_itemtypeid);
        foreach ($complementlist as $vars) {
            $baseFieldDetails[] = array(
                'complement_id' => $vars['complement_id'],
                'item_field_detail_id' => $vars['item_field_detail_id'],
                'seq_id' => $vars['seq_id'],
            );
        }

        if (count($fieldDetails) != count($baseFieldDetails)) {
            return true;
        }

        foreach ($fieldDetails as $fieldDetail) {
            $diffflag = true;
            foreach ($baseFieldDetails as $baseFieldDetail) {
                if ($fieldDetail['complement_id'] == $baseFieldDetail['complement_id']) {
                    if ($fieldDetail['update_id'] == $baseFieldDetail['seq_id']
                    && $fieldDetail['item_field_detail_id'] != $baseFieldDetail['item_field_detail_id']) {
                        return true;
                    }

                    $diffflag = false;
                }
            }
            if ($diffflag) {
                return true;
            }
        }

        return false;
    }

    private function isComplementDetailDiff($itemtypeid, $base_itemtypeid, $relaId, $baseDetailId)
    {
        $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        $linkBean = Xoonips_BeanFactory::getBean('ItemFieldDetailComplementLinkBean', $this->dirname, $this->trustDirname);

        $complementlist = array();
        $basecomplementlist = array();
        $detailrelation = $linkBean->getInfoByItemtypeIdAndComplementId($itemtypeid, $relaId, $baseDetailId);
        if (count($detailrelation) > 0) {
            // complement detail
            $relationdetail = $linkBean->getComplementDetailAndDetailLink($relaId, $baseDetailId);
            foreach ($relationdetail as $vars) {
                $complementlist[$vars['complement_detail_id']]['detail_id'] = $vars['item_field_detail_id'];
            }

            // detail list
            $detaillist = $groupBean->getDetailList($itemtypeid, $baseDetailId);
            foreach ($detaillist as $vars) {
                foreach ($complementlist as &$complement) {
                    if ($complement['detail_id'] == $vars['item_field_detail_id']) {
                        $complement['title'] = $vars['group_name'].' : '.$vars['detail_name'];
                    }
                }
            }
        }

        $detailInfo = $detailBean->getDetailEditInfo($baseDetailId, true);
        $detailrelation = $linkBean->getInfoByItemtypeIdAndComplementId($base_itemtypeid, $relaId, $detailInfo['b_item_field_detail_id']);
        if (count($detailrelation) > 0) {
            // complement detail
            $relationdetail = $linkBean->getComplementDetailAndDetailLink($relaId, $detailInfo['b_item_field_detail_id']);
            foreach ($relationdetail as $vars) {
                $basecomplementlist[$vars['complement_detail_id']]['detail_id'] = $vars['item_field_detail_id'];
            }

            $detaillist = $groupBean->getDetailList($base_itemtypeid, $detailInfo['b_item_field_detail_id']);
            foreach ($detaillist as $vars) {
                foreach ($basecomplementlist as &$basecomplement) {
                    if ($basecomplement['detail_id'] == $vars['item_field_detail_id']) {
                        $basecomplement['title'] = $vars['group_name'].' : '.$vars['detail_name'];
                    }
                }
            }
        }

        if (count($complementlist) != count($basecomplementlist)) {
            return true;
        }

        foreach ($complementlist as $key => $complement) {
            $diffflag = true;
            foreach ($basecomplementlist as $basekey => $basecomplement) {
                if ($key == $basekey) {
                    if ($complement['title'] != $basecomplement['title']) {
                        return true;
                    } else {
                        $diffflag = false;
                    }
                }
            }
            if ($diffflag) {
                return true;
            }
        }

        return false;
    }

    // get subtypes
    private function getSubtypes($itemtypeId)
    {
        $detailbean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        $filetypelist = $detailbean->getFileTypeList($itemtypeId);
        $ret = '';
        $int = 1;
        foreach ($filetypelist as $filetype) {
            if ($int != 1) {
                $ret .= ' / ';
            }
            $ret .= $filetype['title'];
            ++$int;
        }

        return $ret;
    }

    // insert itemtype
    public function insertXoonipsItemtype(&$itemtype_id, $name, $order, $descrip, $template, $icon)
    {
        $itemtype_info = array();
        $itemtype_info['preselect'] = 0;
        $itemtype_info['released'] = 0;
        $itemtype_info['weight'] = $order;
        $itemtype_info['name'] = $name;
        $itemtype_info['description'] = $descrip;
        $itemtype_info['icon'] = null;
        $itemtype_info['mime_type'] = null;
        $itemtype_info['template'] = $template;
        $itemtype_info['update_id'] = null;
        $fileRandName = time();
        if (!empty($icon)) {
            $itemtype_info['icon'] = 'icon_'.$fileRandName.'.'.end(explode('.', $icon['name']));
            $itemtype_info['mime_type'] = $icon['type'];
        }
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        if (!$itemtypeBean->insert($itemtype_info, $itemtype_id)) {
            return false;
        }
        if (empty($icon)) {
            return true;
        }

        $uploadDir = XOOPS_ROOT_PATH.'/modules/xoonips/images';
        $uploadfile = $uploadDir.'/'.'icon_'.$fileRandName.'.'.end(explode('.', $icon['name']));
        if (!move_uploaded_file($icon['tmp_name'], $uploadfile)) {
            return false;
        }

        return true;
    }

    // insert default itemtype group and detail
    private function insertXoonipsItemtypeGroupAndDetail($itemtype_id)
    {
        $typeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);

        $defaultGroups = $groupBean->getDefaultItemTypeGroup();
        foreach ($defaultGroups as $group) {
            // insert default itemtype group
            $lgroup['item_type_id'] = $itemtype_id;
            $lgroup['group_id'] = $group['group_id'];
            $lgroup['weight'] = $group['weight'];
            $lgroup['edit_weight'] = $group['weight'];
            $lgroup['edit'] = 1;
            $lgroup['released'] = 0;
            if (!$typeBean->insertLink($lgroup, $insertId)) {
                return false;
            }
        }

        return true;
    }

    private function doCopyItemtype($itemtypeid, $isCopy = false)
    {
        $map = array();
        if ($isCopy) {
            $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
            if (!$itemtypeBean->copyById($itemtypeid, $map, false, false, true)) {
                return false;
            }

            if (!$itemtypeBean->copyLinkById($itemtypeid, $map)) {
                return false;
            }

            $relationBean = Xoonips_BeanFactory::getBean('ItemFieldDetailComplementLinkBean', $this->dirname, $this->trustDirname);
            if (!$relationBean->copyById($itemtypeid, $map, false, false, true)) {
                return false;
            }
        } else {
            $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
            if (!$itemtypeBean->copyById($itemtypeid, $map, true)) {
                return false;
            }

            $relationBean = Xoonips_BeanFactory::getBean('ItemFieldDetailComplementLinkBean', $this->dirname, $this->trustDirname);
            if (!$relationBean->copyById($itemtypeid, $map, true, false, true)) {
                return false;
            }
        }

        return true;
    }

    // update itemtype
    private function updateXoonipsItemtype($itemtype_id, $name, $order, $descrip, $template, $icon, $isRelease = false)
    {
        $itemtype_info = array();
        $itemtype_info['weight'] = $order;
        $itemtype_info['name'] = $name;
        $itemtype_info['description'] = $descrip;
        $itemtype_info['template'] = $template;
        if ($isRelease) {
            $itemtype_info['released'] = 1;
        } else {
            $itemtype_info['released'] = 0;
        }

        $hasIcon = false;
        $fileRandName = time();
        if (!empty($icon)) {
            $itemtype_info['icon'] = 'icon_'.$fileRandName.'.'.end(explode('.', $icon['name']));
            $itemtype_info['mime_type'] = $icon['type'];
            $hasIcon = true;
        }
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        if (!$itemtypeBean->update($itemtype_info, $itemtype_id, $hasIcon)) {
            return false;
        }
        if (empty($icon)) {
            return true;
        }

        $uploadDir = XOOPS_ROOT_PATH.'/modules/xoonips/images';
        $uploadfile = $uploadDir.'/'.'icon_'.$fileRandName.'.'.end(explode('.', $icon['name']));
        if (!move_uploaded_file($icon['tmp_name'], $uploadfile)) {
            return false;
        }

        return true;
    }

    // update group weight
    private function updateXoonipsItemtypeGroupOrder($typeid, $gids, $orders)
    {
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        //update display order
        foreach ($gids as $key => $id) {
            if ($orders[$key] != $key + 1) {
                if (!$itemtypeBean->updateWeightForLink($typeid, $id, $key + 1)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function doImportItemtype($itemtypeObj, $groupObj, $detailObj, $relationObj)
    {
        $map = array();
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        if (!$itemtypeBean->copyByObj($itemtypeObj, $map, false, true, false)) {
            return false;
        }

        $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
        if (!$groupBean->copyByObj($groupObj, $map, false, true)) {
            return false;
        }

        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        if (!$detailBean->copyByObj($detailObj, $map, false, true)) {
            return false;
        }

        $relationBean = Xoonips_BeanFactory::getBean('ItemFieldDetailComplementLinkBean', $this->dirname, $this->trustDirname);
        if (!$relationBean->copyByObj($relationObj, $map, false, true)) {
            return false;
        }

        // create extend table
        if (!$this->createItemExtendTable($map['itemtype'][$itemtypeObj['item_type_id']])) {
            return false;
        }

        return true;
    }

    // create extend table
    private function createItemExtendTable($itemtype_id, $isRelease = false)
    {
        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        if ($isRelease) {
            $newDetails = $detailBean->getNewItemTypeDetail($itemtype_id);
            if (!$detailBean->createExtendTable($newDetails)) {
                return false;
            }
        } else {
            $detailObj = $detailBean->getReleasedDetail($itemtype_id);
            if (!$detailBean->createExtendTable($detailObj)) {
                return false;
            }
        }

        return true;
    }

    private function deleteAll($itemtypeid)
    {
        // delete item_type
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        if (!$itemtypeBean->delete($itemtypeid)) {
            echo 'failure in delete item_type';

            return false;
        }

        // delete item_field_detail_complement_link
        $linkBean = Xoonips_BeanFactory::getBean('ItemFieldDetailComplementLinkBean', $this->dirname, $this->trustDirname);
        if (!$linkBean->deleteByItemtypeId($itemtypeid)) {
            echo 'failure in delete item_field_detail_complement_link';

            return false;
        }

        // delete item_type_sort
        $sortHandler = Xoonips_Utils::getModuleHandler('ItemSort', $this->dirname);
        if (!$sortHandler->deleteSortFieldsByItemTypeId($itemtypeid)) {
            echo 'failure in delete item_type_sort_detail';

            return false;
        }

        // delete oaipmh_schema_itemtype_link
        $oaipmhBean = Xoonips_BeanFactory::getBean('OaipmhSchemaItemtypeLinkBean', $this->dirname, $this->trustDirname);
        if (!$oaipmhBean->delete(null, $itemtypeid)) {
            echo 'failure in delete oaipmh_schema_itemtype_link';

            return false;
        }

        return true;
    }

    // release itemtype
    private function releaseXoonipsItemtype($base_itemtypeid, $itemtype_id)
    {
        $itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        if ($base_itemtypeid == $itemtype_id) {
            $info = $itemtypeBean->getItemType($itemtype_id);
            $info['released'] = 1;
            if (!$itemtypeBean->update($info, $itemtype_id, false)) {
                return false;
            }
        } else {
            // itemtype data
            if (!$itemtypeBean->updateCopyToBase($itemtype_id)) {
                return false;
            }
        }

        // itemtype group data
        if (!$itemtypeBean->updateLinkSync($base_itemtypeid, true)) {
            return false;
        }

        // itemtype detail relation data
        $relationBean = Xoonips_BeanFactory::getBean('ItemFieldDetailComplementLinkBean', $this->dirname, $this->trustDirname);
        if (!$relationBean->updateNewDetailRelation($base_itemtypeid, $itemtype_id)) {
            return false;
        }
        if (!$relationBean->updateCopyToBaseDetailRelation($itemtype_id)) {
            return false;
        }

        // delete copy data
        if (!$itemtypeBean->deleteCopyItemtype($itemtype_id)) {
            return false;
        }

        if (!$relationBean->deleteCopyItemtypeDetailRelation($itemtype_id)) {
            return false;
        }

        return true;
    }

    // delete templates_c file
    private function deleteTemplatesFile($itemtypeId)
    {
        $reg = '/itemtype%3A'.$itemtypeId.'/';

        $fpath = XOOPS_COMPILE_PATH;
        $fileNames = scandir($fpath);
        if (!$fileNames) {
            return false;
        }
        foreach ($fileNames as $fileName) {
            if (preg_match($reg, $fileName, $matches) == 1) {
                unlink(XOOPS_COMPILE_PATH.'/'.$fileName);
                break;
            }
        }

        return true;
    }

    private function doGroupregistersaveInputCheck($itemtypeid, $name, $xml, &$errors)
    {
        // group name
        $parameters = array();
        $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_GROUP_NAME;
        if ($name == '') {
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
        } else {
            $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
            if ($groupBean->existGroupName(0, $name)) {
                $errors->addError('_AM_XOONIPS_ERROR_DUPLICATE_MSG', '', $parameters);
            }
        }

        // group xml
        $parameters = array();
        $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_XML_TAG;
        if ($xml == '') {
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
        } else {
            $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
            if ($groupBean->existGroupXml(0, $xml)) {
                $errors->addError('_AM_XOONIPS_ERROR_DUPLICATE_MSG', '', $parameters);
            }
        }

        if (count($errors->getErrors()) > 0) {
            return false;
        }

        return true;
    }

    // insert itemtype group
    private function insertXoonipsItemtypeGroup($itemtypeid, $name, $xml, $occurrence)
    {
        $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
        $maxOrder = $groupBean->getMaxGroupWeight($itemtypeid);

        $group_info = array();
        $group_info['preselect'] = 0;
        $group_info['released'] = 0;
        $group_info['item_type_id'] = $itemtypeid;
        $group_info['name'] = $name;
        $group_info['xml'] = $xml;
        $group_info['weight'] = $maxOrder + 1;
        $group_info['occurrence'] = $occurrence == '' ? 0 : $occurrence;
        $group_info['update_id'] = null;
        $new_group_id = 0;

        return $groupBean->insert($group_info, $new_group_id);
    }

    // update group
    private function updateXoonipsItemtypeGroup($groupid, $name, $xml, $occurrence)
    {
        $group_info = array();
        $group_info['name'] = $name;
        $group_info['xml'] = $xml;
        $group_info['occurrence'] = $occurrence == '' ? 0 : $occurrence;

        $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);

        return $groupBean->update($group_info, $groupid);
    }

    private function insItemtypeDetailRelation($itemtypeid, $selectid, $selectdetailids)
    {
        if (isset($selectid) && $selectid != '') {
            $temp = explode(',', $selectid);
            $complementid = $temp[0];
            $baseid = $temp[1];
            $groupid = $temp[2];
        }

        $linkBean = Xoonips_BeanFactory::getBean('ItemFieldDetailComplementLinkBean', $this->dirname, $this->trustDirname);
        foreach ($selectdetailids as $ids) {
            $temp = explode(',', $ids);
            $comDetailId = $temp[0];
            $itemFieldDetailId = $temp[1];

            if ($itemFieldDetailId == '') {
                continue;
            }

            $link_info = array(
            'released' => 0,
            'complement_id' => $complementid,
            'item_type_id' => $itemtypeid,
            'base_item_field_detail_id' => $baseid,
            'complement_detail_id' => $comDetailId,
            'item_field_detail_id' => $itemFieldDetailId,
            'update_id' => 0,
            'group_id' => $groupid,
            );
            if (!$linkBean->update($link_info)) {
                echo 'failure in update item_field_detail_complement_link';

                return false;
            }
        }

        return true;
    }

    protected function doGroupregister(&$request, &$response)
    {
        // get requests
        $itemtypeid = $request->getParameter('itemtypeid');
        $base_itemtypeid = $request->getParameter('base_itemtypeid');

        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_SELECT_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_SELECT_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setGroupBreadcrumbs($title, $base_itemtypeid);

        // itemtype name
        $itmtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $itemtype_name = $itmtypeBean->getItemTypeName($itemtypeid);

        // get itemtype group list
        $members = $itmtypeBean->getTypeGroups($base_itemtypeid);

        // get groups for select
        $groups = $this->getGroupsForSelect(1, $members);

        // token ticket
        $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_group_register'));

        // get common viewdata
        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['itemtypeid'] = $itemtypeid;
        $viewData['itemtype_name'] = $itemtype_name;
        $viewData['base_itemtypeid'] = $base_itemtypeid;
        $viewData['groups'] = $groups;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['title'] = $title;
        $viewData['description'] = $description;
        $viewData['dirname'] = $this->dirname;

        $response->setViewData($viewData);
        $response->setForward('groupregister_success');

        return true;
    }

    protected function doGroupregistersave(&$request, &$response)
    {
        // get requests
        $itemtypeid = $request->getParameter('itemtypeid');
        $base_itemtypeid = $request->getParameter('base_itemtypeid');

        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_SELECT_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_SELECT_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setGroupBreadcrumbs($title, $base_itemtypeid);

        // itemtype name
        $itmtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $itemtype_name = $itmtypeBean->getItemTypeName($itemtypeid);

        // get groups info
        $groups = array();
        $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
        $count = $groupBean->countItemgroups();
        $groups_objs = $groupBean->getItemgrouplist($count, 0);

        foreach ($groups_objs as $itemgroup) {
            $itemgroupid = $itemgroup['group_id'];
            if ($request->getParameter('checkbox_'.$itemgroupid) || $itemgroup['preselect'] == 1) {
                $groups[] = $itemgroupid;
            }
        }

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('admin_policy_itemtype_group_register'))) {
            return false;
        }

        // update link of type and group
        if (!$this->updateTypeGroupLink($base_itemtypeid, $groups)) {
            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php?'.'op=edit&itemtypeid='.$base_itemtypeid;
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_SELECT_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('groupregistersave_success');

            return true;
        }

        $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemtype.php?'.'op=edit&itemtypeid='.$base_itemtypeid;
        $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_SELECT_MSG_SUCCESS;
        $response->setViewData($viewData);
        $response->setForward('groupregistersave_success');

        return true;
    }

    // update link of type and group
    private function updateTypeGroupLink($typeid, $gids)
    {
        $typeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $groupInfos = $typeBean->getTypeGroups($typeid);

        // update set 0 at all cloumn of edit
        foreach ($groupInfos as $group) {
            $typeBean->updateLinkEdit($typeid, $group['group_id'], 0);
        }

        foreach ($gids as $id) {
            $insert_chk = true;
            foreach ($groupInfos as $group) {
                if ($group['group_id'] == $id) {
                    $insert_chk = false;
                    break;
                }
            }
            if ($insert_chk) {
                $info = array('item_type_id' => $typeid,
                'group_id' => $id,
                'weight' => 255,
                'edit' => 1,
                'edit_weight' => 255,
                'released' => 0, );
                $typeBean->insertLink($info, $insertId);
            } else {
                $typeBean->updateLinkEdit($typeid, $id, 1);
            }
        }

        return true;
    }

    private function isGroupLinkDiff($typeid)
    {
        $typeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $groupInfos = $typeBean->getTypeGroups($typeid);
        foreach ($groupInfos as $group) {
            if ($group['edit'] != $group['link_release']
            || $group['edit_weight'] != $group['weight']) {
                return true;
            }
        }

        return false;
    }

    // get groups for select
    private function getGroupsForSelect($mode = 1, $members = array())
    {
        $groups = array();

        $groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
        $count = $groupBean->countItemgroups();
        $groups_objs = $groupBean->getItemgrouplist($count, 0);

        foreach ($groups_objs as $itemgroup) {
            if ($itemgroup['released'] == 0) {
                continue;
            }
            $group_id = $itemgroup['group_id'];
            $name = $itemgroup['name'];
            $xml = $itemgroup['xml'];
            $preselect = $itemgroup['preselect'];
            $select = 0;

            if ($mode == 0) {
                if ($preselect == 1) {
                    $select = 1;
                }
            } else {
                foreach ($members as $member) {
                    if ($member['group_id'] == $group_id
                    && $member['edit'] == 1) {
                        $select = 1;
                    }
                }
            }

            $groups[] = array(
                'groupid' => $group_id,
                'name' => $name,
                'xml' => $xml,
                'select' => $select,
                'preselect' => $preselect,
            );
        }

        return $groups;
    }
}
