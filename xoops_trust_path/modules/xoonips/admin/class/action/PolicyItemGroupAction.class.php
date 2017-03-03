<?php

require_once XOOPS_ROOT_PATH . '/core/XCube_PageNavigator.class.php';
require_once XOONIPS_TRUST_PATH . '/class/core/ActionBase.class.php';
require_once XOONIPS_TRUST_PATH . '/class/bean/ItemFieldGroupBean.class.php';
require_once XOONIPS_TRUST_PATH . '/class/core/Transaction.class.php';
require_once XOONIPS_TRUST_PATH . '/class/core/BeanFactory.class.php';

class Xoonips_PolicyItemGroupAction extends Xoonips_ActionBase {

	protected function doInit(&$request, &$response) {

		//title
		$title = _AM_XOONIPS_POLICY_ITEMFIELDGROUP_TITLE;
		$description = _AM_XOONIPS_POLICY_ITEMFIELDGROUP_DESC;
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
			    'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItem'
		  	),
			array(
			    'name' => $title,
			),
		);
		// get requsts
		$start = intval($request->getParameter('start'));

		// page navigation
		$limit = 50;
		$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
		$count = $groupBean->countItemgroups();

		$pageNavi = new XCube_PageNavigator("policy_itemgroup.php", XCUBE_PAGENAVI_START);
		$pageNavi->setTotalItems($count);
		$pageNavi->setPerpage($limit);
		$pageNavi->fetch();

		$navi_title = sprintf( _AM_XOONIPS_POLICY_ITEMGROUP_PAGENAVI_FORMAT,
		$start + 1, ($start + $limit) > $count ? $count : $start + $limit, $count);

		$itemgroups_objs = $groupBean->getItemgrouplist($limit, $start);
		$itemgroups = array();
		$itemBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);

		$itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
		foreach ($itemgroups_objs as $itemgroup) {
			$itemgroupid = $itemgroup['group_id'];
			$name = $itemgroup['name'];
			$xml = $itemgroup['xml'];
			$editing = '';
			if ($itemgroup['released'] == 0) {
				$editing = _AM_XOONIPS_LABEL_ITEMTYPE_EDITING;
			} elseif ($itemgroup['upid'] != '') {
				if ($this->isDiff($itemgroupid)) {
					$editing = _AM_XOONIPS_LABEL_ITEMTYPE_EDITING;
				} else {
					$groupInfo = $this->getGroupInfoForEdit($itemgroupid, true);
					$this->deleteGroupAll($groupInfo['b_group_id']);
				}
			}

			/* check number of item
			$disdel = false;
			$checkItemgroup = $itemBean->checkItemgroup($itemgroupid);
			if ($checkItemgroup == 0) {
				$disdel = true;
			}
			if ($itemgroup['released'] == 0) {
				$disdel = true;
			}
			*/
			// check number of type
			$disdel = true;
			$itemtypes = $itemtypeBean->getTypeByGroupId($itemgroupid);
			if ( count($itemtypes) > 0 ) {
				$disdel = false;
			}

			// check preselect
			if ($itemgroup['preselect'] == 1) {
				$disdel = false;
			}

			$itemgroups[] = array(
			    'itemgroupid' => $itemgroupid,
			    'name' => $name,
			    'xml' => $xml,
				'editing' => $editing,
				'disdel' => $disdel
			);
		}

		// token ticket
		$token_ticket = $this->createToken( $this->modulePrefix('admin_policy_itemgroup') );

		// get common viewdata
		$viewData = array();

		$viewData['token_ticket'] = $token_ticket;
		$viewData['navi_title'] = $navi_title;
		$viewData['itemgroups'] = $itemgroups;
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

	protected function doRegister(&$request, &$response) {

		// get requests
		$name = $request->getParameter('name');
		$xml = $request->getParameter('xml');
		$occurrence = $request->getParameter('occurrence');

		//title
		$title = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_TITLE;
		$description = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_DESC;

		// breadcrumbs
		$breadcrumbs = $this->setBreadcrumbs($title);

		// get details for select
		$itemfields = $this->getDetailsForSelect();

		// token ticket
		$token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_group_register'));

		// get common viewdata
		$viewData['breadcrumbs'] = $breadcrumbs;
		$viewData['name'] = $name;
		$viewData['xml'] = $xml;
		$viewData['occurrence'] = $occurrence;
		$viewData['itemfields'] = $itemfields;
		$viewData['token_ticket'] = $token_ticket;
		$viewData['title'] = $title;
		$viewData['description'] = $description;
		$viewData['dirname'] = $this->dirname;

		$response->setViewData($viewData);
		$response->setForward('register_success');

		return true;
	}

	protected function doRegistersave(&$request, &$response) {

		// get requests
		$name = $request->getParameter('name');
		$xml = $request->getParameter('xml');
		$occurrence = $request->getParameter('occurrence');
		$mode = $request->getParameter('mode');

		// get fields info
		$itemfields = array();
		$itemfieldBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
		$count = $itemfieldBean->countItemfields();
		$itemfields_objs = $itemfieldBean->getItemfieldlist($count, 0);

		foreach ($itemfields_objs as $itemfield) {
			$itemfieldid = $itemfield['item_field_detail_id'];
			if ($request->getParameter('checkbox_'.$itemfieldid)) {
				$itemfields[] = $itemfieldid;
			}
		}
		
		//title
		$title = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_TITLE;
		$description = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_DESC;

		// breadcrumbs
		$breadcrumbs = $this->setBreadcrumbs($title);

		// do check
		$errors = new Xoonips_Errors();
		if (!$this->doGroupregistersaveInputCheck($name, $xml, $errors)) {
			// get details for select
			$itemfields = $this->getDetailsForSelect();

			// token ticket
			$token_ticket = $this->createToken( $this->modulePrefix('admin_policy_itemtype_group_register') );
			$viewData['breadcrumbs'] = $breadcrumbs;
			$viewData['name'] = $name;
			$viewData['xml'] = $xml;
			$viewData['occurrence'] = $occurrence;
			$viewData['itemfields'] = $itemfields;
			$viewData['token_ticket'] = $token_ticket;
			$viewData['title'] = $title;
			$viewData['description'] = $description;
			$viewData['errors'] = $errors->getView($this->dirname);
			$viewData['dirname'] = $this->dirname;

			$response->setViewData($viewData);
			$response->setForward('register_success');
			return true;
		}

		// check token ticket
		if ( !$this->validateToken( $this->modulePrefix('admin_policy_itemtype_group_register') ) ) {
			return false;
		}

		// transaction
		$transaction = Xoonips_Transaction::getInstance();
		$transaction->start();

		// insert itemtype group
		$new_group_id = 0;
		if ( !$this->insertXoonipsItemtypeGroup( $name, $xml, $occurrence, $new_group_id ) ) {
			$transaction->rollback();

			$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php?op=register';
			$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_MSG_FAILURE;
			$response->setViewData($viewData);
			$response->setForward('registersave_success');
			return true;
		}

		// update link of group and detail
		if ( !$this->updateGroupDetailLink($new_group_id, $itemfields) ) {
			$transaction->rollback();

			$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php?op=register';
			$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_MSG_FAILURE;
			$response->setViewData($viewData);
			$response->setForward('registersave_success');
			return true;
		}

		// release mode
		if ($mode == 1) {
			// get group detail list
			$details = $this->getFieldInfos($new_group_id);
			if (count($details) == 0) {
				$transaction->rollback();

				$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname."/admin/policy_itemgroup.php?op=register&name=$name&xml=$xml&occurrence=$occurrence";
				$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_MSG_FAILURE2;
				$response->setViewData($viewData);
				$response->setForward('editsave_success');
				return true;
			}

			$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
			if (!$groupBean->release($new_group_id, $new_group_id)) {
				$transaction->rollback();

				$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php';
				$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_RELEASE_MSG_FAILURE;
				$response->setViewData($viewData);
				$response->setForward('registersave_success');
				return true;
			}
		}

		// success
		$transaction->commit();

		$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php';
		if ($mode == 1) $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_RELEASE_MSG_SUCCESS;
		else $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_REGIST_MSG_SUCCESS;
		$response->setViewData($viewData);
		$response->setForward('registersave_success');

		return true;
	}

	protected function doEdit(&$request, &$response) {

		// get requests
		$base_groupid = $request->getParameter('groupid');
		$groupid = $request->getParameter('groupid');
		$perpage = $request->getParameter('perpage');
		$startpage = $request->getParameter('start');

		//title
		$title = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_EDIT_TITLE;
		$description = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_DESC;

		// breadcrumbs
		$breadcrumbs = $this->setBreadcrumbs($title);

		// get base itemgroup info
		$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
		$baseInfo = $groupBean->getGroupEditInfo($base_groupid);

		// do copy
		if ($baseInfo['a_released'] == 1 && $baseInfo['b_update_id'] == null) {
			// transaction
			$transaction = Xoonips_Transaction::getInstance();
			$transaction->start();

			if ( !$this->doCopyItemgroup($groupid, false, $insertId) ) {
				$transaction->rollback();
				die( "copy item group failure!" );
			}

			$groupid = $insertId;

			// success
			$transaction->commit();
		} elseif ($baseInfo['a_released'] == 1) {
			$groupid = $baseInfo['b_group_id'];
		}

		// check disabled edit
		$disedi = false;
		$group_info = $groupBean->getItemGroup($base_groupid);
		foreach ($group_info as $grp) {
			if ($grp['released'] == 1 && $grp['preselect'] == 1) $disedi = true;
		}

		// get edit group info for edit
		$groupInfo = $this->getGroupInfoForEdit($groupid);

		// get group detail list
		$details = $this->getFieldInfos($base_groupid);
		
		// check editing detail link
		$detail_editing = 0;
		if (self::isDetailLinkDiff($base_groupid)) {
			$detail_editing = 1;
		}

		// check preselect
		$chk_groups = $groupBean->getItemgroup($base_groupid);
		$select_btn = 1;
		foreach($chk_groups as $chk_group){
			if ($chk_group['preselect'] == 1) {
				$select_btn = 0;
			}
		}

		// token ticket
		$token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_groupedit'));

		$viewData['breadcrumbs'] = $breadcrumbs;
		$viewData['token_ticket'] = $token_ticket;
		$viewData['base_groupid'] = $base_groupid;
		$viewData['groupid'] = $groupid;
		$viewData['groupInfo'] = $groupInfo;
		$viewData['detail_editing'] = $detail_editing;
		$viewData['details'] = $details;
		$viewData['select_btn'] = $select_btn;
		$viewData['title'] = $title;
		$viewData['description'] = $description;
		$viewData['dirname'] = $this->dirname;
		$viewData['perpage'] = $perpage;
		$viewData['startpage'] = $startpage;
		$viewData['disedi'] = $disedi;

		$response->setViewData($viewData);
		$response->setForward('edit_success');

		return true;
	}

	protected function doEditsave(&$request, &$response) {

		// get requests
		$base_groupid = $request->getParameter('base_groupid');
		$groupid = $request->getParameter('groupid');
		$mode = $request->getParameter('mode');

		//title
		$title = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_EDIT_TITLE;
		$description = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_DESC;

		// breadcrumbs
		$breadcrumbs = $this->setBreadcrumbs($title);

		// get edit group info
		$groupInfo = $this->getGroupInfoForEdit($groupid, true);

		// get group detail list
		$details = $this->getFieldInfos($groupid);

		// do update
		$errors = new Xoonips_Errors();
		$name = $request->getParameter('name');
		$xml = $request->getParameter('xml');
		$occurrence = $request->getParameter('occurrence');
		$detail_ids = $request->getParameter('detail_ids');
		$weights = $request->getParameter('weights');

		// do check
		if (!$this->doGroupeditsaveInputCheck($groupid, $name, $xml, $errors, $base_groupid)){
			// token ticket
			$token_ticket = $this->createToken( $this->modulePrefix('admin_policy_itemtype_groupedit') );
			$groupInfo['a_name'] = $name;
			$groupInfo['a_xml'] = $xml;
			$groupInfo['a_occurrence'] = $occurrence;
			$viewData['breadcrumbs'] = $breadcrumbs;
			$viewData['token_ticket'] = $token_ticket;
			$viewData['groupid'] = $groupid;
			$viewData['groupInfo'] = $groupInfo;
			$viewData['details'] = $details;
			$viewData['title'] = $title;
			$viewData['description'] = $description;
			$viewData['errors'] = $errors->getView($this->dirname);
			$viewData['dirname'] = $this->dirname;

			$response->setViewData($viewData);
			$response->setForward('edit_success');
			return true;
		}

		// check token ticket
		if ( !$this->validateToken( $this->modulePrefix('admin_policy_itemtype_groupedit') ) ) {
			return false;
		}

		// transaction
		$transaction = Xoonips_Transaction::getInstance();
		$transaction->start();

		// update group
		if ( !$this->updateXoonipsItemtypeGroup( $groupid, $name, $xml, $occurrence ) ) {
			$transaction->rollback();

			$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php';
			$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_MSG_FAILURE;
			$response->setViewData($viewData);
			$response->setForward('editsave_success');
			return true;
		}

		// update detail link weight
		if ( !$this->updateXoonipsItemtypeDetailOrder( $base_groupid, $detail_ids, $weights ) ) {
			$transaction->rollback();

			$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php';
			$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_MSG_FAILURE;
			$response->setViewData($viewData);
			$response->setForward('editsave_success');
			return true;
		}

		// release mode
		if ($mode == 1) {
			// get group detail list
			$details = $this->getFieldInfos($base_groupid);
			if (count($details) == 0) {
				$transaction->rollback();

				$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php?op=edit&groupid='.$base_groupid;
				$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_MSG_FAILURE2;
				$response->setViewData($viewData);
				$response->setForward('editsave_success');
				return true;
			}

			$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
			if (!$groupBean->release($groupid, $base_groupid)) {
				$transaction->rollback();

				$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php';
				$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_RELEASE_MSG_FAILURE;
				$response->setViewData($viewData);
				$response->setForward('editsave_success');
				return true;
			}
		}

		// success
		$transaction->commit();

		$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php';
		if ($mode == 1) $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_RELEASE_MSG_SUCCESS;
		else $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_MSG_SUCCESS;
		$response->setViewData($viewData);
		$response->setForward('editsave_success');
		return true;
	}

	protected function doSorteditsave(&$request, &$response) {

		// get requests
		$base_groupid = $request->getParameter('base_groupid');
		$groupid = $request->getParameter('groupid');

		//title
		$title = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_EDIT_TITLE;
		$description = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_DESC;

		// breadcrumbs
		$breadcrumbs = $this->setBreadcrumbs($title);

		// get edit group info
		$groupInfo = $this->getGroupInfoForEdit($groupid, true);

		// get group detail list
		$details = $this->getFieldInfos($groupid);

		// do update
		$errors = new Xoonips_Errors();
		$detail_ids = $request->getParameter('detail_ids');
		$weights = $request->getParameter('weights');

		// check token ticket
		if ( !$this->validateToken( $this->modulePrefix('admin_policy_itemtype_groupedit') ) ) {
			return false;
		}

		// transaction
		$transaction = Xoonips_Transaction::getInstance();
		$transaction->start();

		// update detail link weight
		if ( !$this->updateXoonipsItemtypeDetailOrder( $base_groupid, $detail_ids, $weights ) ) {
			$transaction->rollback();

			$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php';
			$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_MSG_FAILURE;
			$response->setViewData($viewData);
			$response->setForward('editsave_success');
			return true;
		}

		// success
		$transaction->commit();

		$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php?op=edit&groupid='.$base_groupid;
		$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_MSG_SUCCESS;
		$response->setViewData($viewData);
		$response->setForward('editsave_success');

		return true;
	}

	protected function doRelease(&$request, &$response) {

		//title
		$title = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_EDIT_TITLE;
		$description = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_MODIFY_DESC;

		// breadcrumbs
		$breadcrumbs = $this->setBreadcrumbs($title);

		// get requests
		$base_groupid = $request->getParameter('base_groupid');
		$groupid = $request->getParameter('groupid');

		// do release
		$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
		if (!$groupBean->release($groupid, $base_groupid)) {
			$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php';
			$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_MODIFY_MSG_FAILURE;
			$response->setViewData($viewData);
			$response->setForward('release_success');

			return true;
		}

		$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php';
		$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_MODIFY_MSG_SUCCESS;
		$response->setViewData($viewData);
		$response->setForward('release_success');

		return true;
	}

	protected function doDelete(&$request, &$response) {

		// get requests
		$groupid = $request->getParameter('groupid');

		// check token ticket
		if ( !$this->validateToken( $this->modulePrefix('admin_policy_itemgroup') ) ) {
			return false;
		}

		// do check
		$itemtypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
		$itemtypes = $itemtypeBean->getTypeByGroupId($groupid);
		if ( count($itemtypes) > 0 ) {
			$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php';
			$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_DELETE_MSG_FAILURE2;
			$response->setViewData($viewData);
			$response->setForward('delete_success');
			return true;
		}

		// transaction
		$transaction = Xoonips_Transaction::getInstance();
		$transaction->start();

		// delete all
		if (!$this->deleteGroupAll($groupid)) {
			$transaction->rollback();

			$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php';
			$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_DELETE_MSG_FAILURE;
			$response->setViewData($viewData);
			$response->setForward('delete_success');
			return true;
		}

		// success
		$transaction->commit();

		$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php';
		$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_DELETE_MSG_SUCCESS;
		$response->setViewData($viewData);
		$response->setForward('delete_success');

		return true;
	}

	protected function doDetailregister(&$request, &$response) {

		// get requests
		$groupid = $request->getParameter('base_groupid');
		$changeop = $request->getParameter('changeop');

		//title
		$title = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_SELECT_TITLE;
		$description = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_SELECT_DESC;

		// breadcrumbs
		$breadcrumbs = $this->setDetailBreadcrumbs($title, $groupid);

		// get edit group info
		$groupInfo = $this->getGroupInfoForEdit($groupid, true);

		// get group member
		$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean',$this->dirname,$this->trustDirname);
		$members = $groupBean->getGroupDetails($groupid);

		// get details for select
		$itemfields = $this->getDetailsForSelect($members);

		// token ticket
		$token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemtype_detailadd'));

		$viewData['breadcrumbs'] = $breadcrumbs;
		$viewData['token_ticket'] = $token_ticket;
		$viewData['groupid'] = $groupid;
		$viewData['group_name'] = $groupInfo['a_name'];
		$viewData['itemfields'] = $itemfields;
		$viewData['title'] = $title;
		$viewData['description'] = $description;
		$viewData['dirname'] = $this->dirname;

		$response->setViewData($viewData);
		$response->setForward('detailregister_success');
		return true;
	}

	protected function doDetailregistersave(&$request, &$response) {

		// get requests
		$groupid = $request->getParameter('groupid');
		$changeop = $request->getParameter('changeop');

		//title
		$title = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_SELECT_TITLE;
		$description = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_SELECT_DESC;

		// breadcrumbs
		$breadcrumbs = $this->setDetailBreadcrumbs($title, $groupid);

		// get fields info
		$itemfields = array();
		$itemfieldBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
		$count = $itemfieldBean->countItemfields();
		$itemfields_objs = $itemfieldBean->getItemfieldlist($count, 0);

		foreach ($itemfields_objs as $itemfield) {
			$itemfieldid = $itemfield['item_field_detail_id'];
			if ($request->getParameter('checkbox_'.$itemfieldid)) {
				$itemfields[] = $itemfieldid;
			}
		}

		// check token ticket
		if ( !$this->validateToken( $this->modulePrefix('admin_policy_itemtype_detailadd') ) ) {
			return false;
		}

		// update link of group and detail
		if ( !$this->updateGroupDetailLink($groupid, $itemfields) ) {
			$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php';
			$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_SELECT_MSG_FAILURE;
			$response->setViewData($viewData);
			$response->setForward('detailregistersave_success');
			return true;
		}

		$viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname."/admin/policy_itemgroup.php?op=edit&groupid=$groupid";
		$viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_SELECT_MSG_SUCCESS;
		$response->setViewData($viewData);
		$response->setForward('detailregistersave_success');
		return true;
	}

	private function setBreadcrumbs($title) {
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
			    'name' => _AM_XOONIPS_POLICY_ITEMFIELDGROUP_TITLE,
			    'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php',
			),
			array(
			    'name' => $title,
			),
		);
		return $breadcrumbs;
	}

	private function setDetailBreadcrumbs($title, $groupid) {
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
			    'name' => _AM_XOONIPS_POLICY_ITEMFIELDGROUP_TITLE,
			    'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php',
			),
			array(
			    'name' => _AM_XOONIPS_POLICY_ITEMTYPE_GROUP_EDIT_TITLE,
			    'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemgroup.php?'.'op=edit&groupid='.$groupid),
			array(
			    'name' => $title,
			),
		);
		return $breadcrumbs;
	}

	// get edit field group info
	private function getGroupInfoForEdit($groupid, $ng=false) {
		$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
		if ($ng) {
			$info = $groupBean->getGroupEditInfo($groupid);
		} else {
			$info = $groupBean->getGroupEditInfo($groupid, true);
		}
		$groupInfo = array (
		    'a_name' => $info['a_released']==1 ? $info['b_name'] : $info['a_name'],
			'a_xml' => $info['a_released']==1 ? $info['b_xml'] : $info['a_xml'],
		    'a_occurrence' => $info['a_released']==1 ? $info['b_occurrence'] : $info['a_occurrence'],
			'a_weight' => $info['a_released']==1 ? $info['b_weight'] : $info['a_weight'],
			'b_name' => $info['a_released']==1 ? $info['a_name'] : $info['b_name'],
		    'b_xml' => $info['a_released']==1 ? $info['a_xml'] : $info['b_xml'],
			'b_occurrence' => $info['a_released']==1 ? $info['a_occurrence'] : $info['b_occurrence'],
			'b_weight' => $info['a_released']==1 ? $info['a_weight'] : $info['b_weight'],
			'b_group_id' => $info['b_group_id']
		);
		return $groupInfo;
	}

	private function isDiff($groupid) {
		$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean', $this->dirname, $this->trustDirname);
		$info = $groupBean->getGroupEditInfo($groupid);
		if ($info['b_name'] != $info['a_name'] || $info['b_xml'] != $info['a_xml']
				|| $info['b_occurrence'] != $info['a_occurrence']
				|| $info['b_weight'] != $info['a_weight']) {
			return true;
		}

		// Diff detail link
		if (self::isDetailLinkDiff($groupid)) return true;

		return false;
	}

	private function isDetailLinkDiff($groupid) {
		$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean',$this->dirname,$this->trustDirname);
		$detailInfos = $groupBean->getGroupDetails($groupid);
		foreach ($detailInfos as $detail) {
			if ($detail['edit'] != $detail['link_release']
			|| $detail['edit_weight'] != $detail['weight']) {
				return true;
			}
		}
		return false;
	}

	private function doGroupregistersaveInputCheck($name, $xml, &$errors){
		// group name
		$parameters = array();
		$parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_GROUP_NAME;
		if ($name=='') {
			$errors->addError("_AM_XOONIPS_ERROR_REQUIRED", "", $parameters);
		} else {
			/* allow duplicate group name
			$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean',$this->dirname,$this->trustDirname);
			if ($groupBean->existGroupName(0, $name)) {
				$errors->addError("_AM_XOONIPS_ERROR_DUPLICATE_MSG", "", $parameters);
			}
			*/
		}

		// group xml
		$parameters = array();
		$parameters[] = _AM_XOONIPS_POLICY_ITEMFIELDGROUP_ID;
		if ($xml=='') {
			$errors->addError("_AM_XOONIPS_ERROR_REQUIRED", "", $parameters);
		} else {
			$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean',$this->dirname,$this->trustDirname);
			if ($groupBean->existGroupXml(0, $xml)) {
				$errors->addError("_AM_XOONIPS_ERROR_DUPLICATE_MSG", "", $parameters);
			}
		}

		if (count($errors->getErrors()) > 0) {
			return false;
		}
		return true;
	}

	private function deleteGroupAll($groupid) {

		$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean',$this->dirname,$this->trustDirname);

		// delete detail link
		if ( !$groupBean->deleteLink( $groupid ) ) return false;

		// delete group
		if ( !$groupBean->delete( $groupid ) ) return false;

		return true;
	}

	private function doCopyItemgroup( $groupid, $isCopy = false, &$insertId) {
		$map = array();
		$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean',$this->dirname,$this->trustDirname);
		if ($isCopy) {
			if (!$groupBean->copyById($groupid, $map)) return false;
		}
		else
		{
			if (!$groupBean->copyById($groupid, $map, true)) return false;

			$insertId = $map['group'][$groupid];
		}
		return true;
	}

	// insert itemtype group
	private function insertXoonipsItemtypeGroup( $name, $xml, $occurrence, &$new_group_id ) {
		$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean',$this->dirname,$this->trustDirname);

		$group_info = array();
		$group_info['preselect'] = 0;
		$group_info['released'] = 0;
		$group_info['item_type_id'] = 0;
		$group_info['name'] = $name;
		$group_info['xml'] = $xml;
		$group_info['weight'] = 1;
		$group_info['occurrence'] = $occurrence=='' ? 0 : $occurrence;
		$group_info['update_id'] = NULL;
		$new_group_id = 0;
		return $groupBean->insert($group_info, $new_group_id);
	}

	private function getFieldInfos($groupid) {
		$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean',$this->dirname,$this->trustDirname);
		$detailInfos = $groupBean->getGroupDetails($groupid);
		$details = array();
		foreach ($detailInfos as $detail) {
			if ($detail['edit'] == 1) {
				$details[] = array(
					'detail_id' => $detail['item_field_detail_id'],
				    'name' => $detail['name'],
				    'xml' => $detail['xml'],
					'weight' => $detail['edit_weight']
				);
			}
		}
		return $details;
	}

	private function doGroupeditsaveInputCheck($group_id, $name, $xml, &$errors, $base_groupid){
		// group name
		$parameters = array();
		$parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_GROUP_NAME;
		if ($name=='') {
			$errors->addError("_AM_XOONIPS_ERROR_REQUIRED", "", $parameters);
		}

		// group xml
		$parameters = array();
		$parameters[] = _AM_XOONIPS_POLICY_ITEMFIELDGROUP_ID;
		if ($xml=='') {
			$errors->addError("_AM_XOONIPS_ERROR_REQUIRED", "", $parameters);
		} else {
			$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean',$this->dirname,$this->trustDirname);
			if ($groupBean->existGroupXml($group_id, $xml, $base_groupid)) {
				$errors->addError("_AM_XOONIPS_ERROR_DUPLICATE_MSG", "", $parameters);
			}
		}

		if (count($errors->getErrors()) > 0) {
			return false;
		}
		return true;
	}

	// update group
	private function updateXoonipsItemtypeGroup( $groupid, $name, $xml, $occurrence ) {
		$group_info = array();
		$group_info['name'] = $name;
		$group_info['xml'] = $xml;
		$group_info['occurrence'] = $occurrence=='' ? 0 : $occurrence;

		$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean',$this->dirname,$this->trustDirname);
		return $groupBean->update($group_info, $groupid);
	}

	// update detail link weight
	private function updateXoonipsItemtypeDetailOrder( $groupid, $dids, $orders ) {

		if ($dids == '') return true;
		$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean',$this->dirname,$this->trustDirname);

		//update display order
		foreach ( $dids as $key =>$id) {
			if($orders[$key]!= $key+1){
				if (!$groupBean->updateWeightForLink($groupid, $id, $key+1)) return false;
			}
		}
		return true;
	}

	// update link of group and detail
	private function updateGroupDetailLink($groupid, $dids) {

		$groupBean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean',$this->dirname,$this->trustDirname);
		$detailInfos = $groupBean->getGroupDetails($groupid);

		// update set 0 at all cloumn of edit
		foreach ($detailInfos as $detail) {
			$groupBean->updateLinkEdit($groupid, $detail['item_field_detail_id'], 0);
		}

		foreach ($dids as $id) {
			$link_info = $groupBean->getGroupDetailById($groupid, $id);
			$insert_chk = (count($link_info) > 0) ? false : true;

			if ($insert_chk) {
				$info = array('group_id'=>$groupid,
				'item_field_detail_id'=>$id,
				'weight'=>255,
				'edit'=>1,
				'edit_weight'=>255,
				'released'=>0);
				$groupBean->insertLink($info, $insertId);
			} else {
				$groupBean->updateLinkEdit($groupid, $id, 1);
			}
		}

		return true;
	}

	// get details for select
	private function getDetailsForSelect($members=array()) {
		$itemfields = array();

		$detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
		$count = $detailBean->countItemfields();
		$itemfields_objs = $detailBean->getItemfieldlist($count, 0);

		foreach ($itemfields_objs as $itemfield) {
			$table = $itemfield['table_name'];
			if ($itemfield['released'] == 0
			|| (!strPos($table, 'item_extend') && !strPos($table, 'item_file'))) {
				continue;
			}
			$itemfieldid = $itemfield['item_field_detail_id'];
			$name = $itemfield['name'];
			$xml = $itemfield['xml'];
			$preselect = $itemfield['preselect'];
			$select = 0;
			foreach ($members as $member) {
				if ($member['item_field_detail_id'] == $itemfieldid
				&& $member['edit'] == 1){
					$select = 1;
				}
			}

			$itemfields[] = array(
			    'itemfieldid' => $itemfieldid,
			    'name' => $name,
			    'xml' => $xml,
				'select' => $select,
				'preselect' => $preselect
			);
		}

		return $itemfields;
	}

}
