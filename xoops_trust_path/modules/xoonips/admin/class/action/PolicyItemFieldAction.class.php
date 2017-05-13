<?php

require_once XOOPS_ROOT_PATH.'/core/XCube_PageNavigator.class.php';
require_once dirname(dirname(dirname(__DIR__))).'/class/core/ActionBase.class.php';
require_once dirname(dirname(dirname(__DIR__))).'/class/bean/ItemFieldDetailBean.class.php';
require_once dirname(dirname(dirname(__DIR__))).'/class/core/Transaction.class.php';

class Xoonips_PolicyItemFieldAction extends Xoonips_ActionBase
{
    protected function doRegister(&$request, &$response)
    {
        // get requests
        $changeop = $request->getParameter('changeop');
        $name = $request->getParameter('name');
        $xml = $request->getParameter('xml');
        $view_type = $request->getParameter('view_type');
        $data_type = $request->getParameter('data_type');
        $list = $request->getParameter('list');
        $data_length = $request->getParameter('data_length');
        $data_decimal_places = $request->getParameter('data_decimal_places');
        $default_value = $request->getParameter('default_value');
        $essential = $request->getParameter('essential');
        $detail_display = ($request->getParameter('detail_display') === 0) ? 0 : 1;
        $detail_target = $request->getParameter('detail_target');
        $scope_search = $request->getParameter('scope_search');
        $scope_search_arr = $request->getParameter('scope_search_arr');

        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_REGIST_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_REGIST_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // get viewtype info
        $viewtypelist = $this->getViewTypeList($view_type);

        // do view_type change
        if ($changeop == 'vtchange') {
            $data_type = '';
            $data_length = '';
            $data_decimal_places = '';
            $list = '';
            $default_value = '';
        }

        // do list change
        if ($changeop == 'listchange') {
            $default_value = '';
        }

        // get list block
        $list_block = $this->getDetailregisterListBlock($view_type, $list);

        // get default block
        $default_block = $this->getDetailregisterDefaultValutBlock($view_type, $list, $default_value);

        // get datatype info
        $selected_datatype_name = '';
        $datatypelist = $this->getDataTypeList($view_type, $data_type, $selected_datatype_name, $scope_search, $scope_search_arr);

        // token ticket
        $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemfield_add'));

        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['viewtypelist'] = $viewtypelist;
        $viewData['datatypelist'] = $datatypelist;
        $viewData['selected_datatype_name'] = $selected_datatype_name;
        $viewData['name'] = $name;
        $viewData['xml'] = $xml;
        $viewData['data_length'] = $data_length;
        $viewData['data_decimal_places'] = $data_decimal_places;
        $viewData['default_value'] = $default_value;
        $viewData['essential'] = $essential;
        $viewData['detail_display'] = $detail_display;
        $viewData['detail_target'] = $detail_target;
        $viewData['scope_search'] = $scope_search;
        $viewData['scope_search_arr'] = $scope_search_arr;
        $viewData['list_block'] = $list_block;
        $viewData['default_block'] = $default_block;
        $viewData['title'] = $title;
        $viewData['description'] = $description;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->mytrustdirname;

        $response->setViewData($viewData);
        $response->setForward('register_success');

        return true;
    }

    protected function doRegistersave(&$request, &$response)
    {
        // get requests
        $changeop = $request->getParameter('changeop');
        $name = $request->getParameter('name');
        $xml = $request->getParameter('xml');
        $view_type = $request->getParameter('view_type');
        $data_type = $request->getParameter('data_type');
        $list = $request->getParameter('list');
        $data_length = $request->getParameter('data_length');
        $data_decimal_places = $request->getParameter('data_decimal_places');
        $default_value = $request->getParameter('default_value');
        $essential = $request->getParameter('essential');
        $detail_display = $request->getParameter('detail_display');
        $detail_target = $request->getParameter('detail_target');
        $scope_search = $request->getParameter('scope_search');
        $scope_search_arr = $request->getParameter('scope_search_arr');
        $mode = $request->getParameter('mode');

        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_REGIST_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_REGIST_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // get viewtype info
        $viewtypelist = $this->getViewTypeList($view_type);

        // do view_type change
        if ($changeop == 'vtchange') {
            $data_type = '';
            $data_length = '';
            $data_decimal_places = '';
            $list = '';
            $default_value = '';
        }

        // do list change
        if ($changeop == 'listchange') {
            $default_value = '';
        }

        // get list block
        $list_block = $this->getDetailregisterListBlock($view_type, $list);

        // get default block
        $default_block = $this->getDetailregisterDefaultValutBlock($view_type, $list, $default_value);

        // get datatype info
        $selected_datatype_name = '';
        $datatypelist = $this->getDataTypeList($view_type, $data_type, $selected_datatype_name, $scope_search, $scope_search_arr);

        // do check
        $errors = new Xoonips_Errors();
        $inputData = array();
        $inputData['name'] = $name;
        $inputData['xml'] = $xml;
        $inputData['view_type'] = $view_type;
        $inputData['data_type'] = $data_type;
        $inputData['list'] = $list;
        $inputData['length'] = $data_length;
        $inputData['length2'] = $data_decimal_places;
        $inputData['default'] = $default_value;

        if (!$this->doDetailregistersaveInputCheck($inputData, $errors)) {
            // token ticket
            $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemfield_add'));
            $viewData['breadcrumbs'] = $breadcrumbs;
            $viewData['token_ticket'] = $token_ticket;
            $viewData['viewtypelist'] = $viewtypelist;
            $viewData['datatypelist'] = $datatypelist;
            $viewData['errors'] = $errors->getView($this->dirname);
            $viewData['name'] = $name;
            $viewData['xml'] = $xml;
            $viewData['data_length'] = $data_length;
            $viewData['data_decimal_places'] = $data_decimal_places;
            $viewData['default_value'] = $default_value;
            $viewData['essential'] = $essential;
            $viewData['detail_display'] = $detail_display;
            $viewData['detail_target'] = $detail_target;
            $viewData['scope_search'] = $scope_search;
            $viewData['scope_search_arr'] = $scope_search_arr;
            $viewData['list_block'] = $list_block;
            $viewData['default_block'] = $default_block;
            $viewData['title'] = $title;
            $viewData['description'] = $description;
            $viewData['dirname'] = $this->dirname;

            $response->setViewData($viewData);
            $response->setForward('register_success');

            return true;
        }

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('admin_policy_itemfield_add'))) {
            return false;
        }

        // insert itemtype detail
        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);

        $detail_info = array();
        $detail_info['released'] = 0;
        $detail_info['preselect'] = 0;
        $detail_info['table_name'] = 'xoonips_item_extend';
        $detail_info['column_name'] = 'value';
        $detail_info['item_type_id'] = 0;
        $detail_info['group_id'] = 0;
        $detail_info['weight'] = 1;
        $detail_info['name'] = $name;
        $detail_info['xml'] = $xml;
        $detail_info['view_type_id'] = $view_type;
        $detail_info['data_type_id'] = $data_type;
        $detail_info['data_length'] = ($data_length == '') ? -1 : $data_length;
        $detail_info['data_decimal_places'] = ($data_decimal_places == '') ? -1 : $data_decimal_places;
        $detail_info['default_value'] = ($default_value == '') ? null : $default_value;
        $detail_info['list'] = ($list == '') ? null : $list;
        $detail_info['essential'] = empty($essential) ? 0 : $essential;
        $detail_info['detail_display'] = empty($detail_display) ? 0 : $detail_display;
        $detail_info['detail_target'] = empty($detail_target) ? 0 : $detail_target;
        $detail_info['scope_search'] = empty($scope_search) ? 0 : $scope_search;
        $detail_info['nondisplay'] = 0;
        $detail_info['update_id'] = null;

        // transaction
        $transaction = Xoonips_Transaction::getInstance();
        $transaction->start();

        $new_detail_id = 0;
        if (!$detailBean->insert($detail_info, $new_detail_id)) {
            $transaction->rollback();

            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemfield.php?op=register';
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_REGIST_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('registersave_success');

            return true;
        }

        if (!$detailBean->updateTableName($new_detail_id)) {
            $transaction->rollback();

            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemfield.php?op=register';
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_REGIST_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('registersave_success');

            return true;
        }

        // release mode
        if ($mode == 1) {
            if (!$detailBean->release($new_detail_id, $new_detail_id)) {
                $transaction->rollback();

                $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItemField';
                $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_RELEASE_MSG_FAILURE;
                $response->setViewData($viewData);
                $response->setForward('registersave_success');

                return true;
            }

            // Quick Search Setting
            if (!$this->installItemSearch($new_detail_id)) {
                $transaction->rollback();

                $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItemField';
                $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_RELEASE_MSG_FAILURE;
                $response->setViewData($viewData);
                $response->setForward('registersave_success');

                return true;
            }
        }

        // success
        $transaction->commit();

        $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItemField';
        if ($mode == 1) {
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_RELEASE_MSG_SUCCESS;
        } else {
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_REGIST_MSG_SUCCESS;
        }
        $response->setViewData($viewData);
        $response->setForward('registersave_success');

        return true;
    }

    protected function doEdit(&$request, &$response)
    {
        // get requests
        $base_detailid = $request->getParameter('detailid');
        $detailid = $request->getParameter('detailid');
        $changeop = $request->getParameter('changeop');
        $name = $request->getParameter('name');
        $xml = $request->getParameter('xml');
        $view_type = $request->getParameter('view_type');
        $data_type = $request->getParameter('data_type');
        $list = $request->getParameter('list');
        $data_length = $request->getParameter('data_length');
        $data_decimal_places = $request->getParameter('data_decimal_places');
        $default_value = $request->getParameter('default_value');
        $essential = $request->getParameter('essential');
        $detail_display = $request->getParameter('detail_display');
        $detail_target = $request->getParameter('detail_target');
        $scope_search = $request->getParameter('scope_search');
        $nondisplay = $request->getParameter('nondisplay');
        $scope_search_arr = $request->getParameter('scope_search_arr');
        $perpage = $request->getParameter('perpage');
        $startpage = $request->getParameter('start');

        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_EDIT_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_MODIFY_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // get base itemfield info
        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        $baseInfo = $detailBean->getDetailEditInfo($base_detailid);

        // do copy
        if ($baseInfo['a_released'] == 1 && $baseInfo['b_update_id'] == null) {
            // transaction
            $transaction = Xoonips_Transaction::getInstance();
            $transaction->start();

            if (!$this->doCopyItemfield($detailid, false, $insertId)) {
                $transaction->rollback();
                die('copy item field failure!');
            }

            $detailid = $insertId;

            // success
            $transaction->commit();
        } elseif ($baseInfo['a_released'] == 1) {
            $detailid = $baseInfo['b_item_field_detail_id'];
        }

        // check disabled edit
        $disedi = false;
        $detail_info = $detailBean->getItemTypeDetailById($base_detailid);
        if ($detail_info['preselect'] == 1) {
            $disedi = true;
        }

        // get detail info for edit
        $disabled_arr = '';
        $detailInfo = $this->getDetailInfoForEdit($detailid, $disabled_arr);

        // get default_value title
        if ($detailInfo['b_list'] != '' && $detailInfo['b_default_value'] != '') {
            $valueSetBean = Xoonips_BeanFactory::getBean('ItemFieldValueSetBean', $this->dirname, $this->trustDirname);
            $detailInfo['b_default_value'] = $valueSetBean->getItemTypeValueTitle($detailInfo['b_list'], $detailInfo['b_default_value']);
        }

        // initialization
        if ($changeop == '') {
            $name = $detailInfo['a_name'];
            $xml = $detailInfo['a_xml'];
            $view_type = $detailInfo['a_view_type'];
            $data_type = $detailInfo['a_data_type'];
            $list = $detailInfo['a_list'];
            $data_length = $detailInfo['a_data_length'];
            $data_decimal_places = $detailInfo['a_data_decimal_places'];
            $default_value = $detailInfo['a_default_value'];
            $essential = $detailInfo['a_essential'];
            $detail_display = $detailInfo['a_detail_display'];
            $detail_target = $detailInfo['a_detail_target'];
            $scope_search = $detailInfo['a_scope_search'];
            $nondisplay = $detailInfo['a_nondisplay'];
        }

        // get viewtype info
        $viewtypelist = $this->getViewTypeList($view_type);

        // do view_type change
        if ($changeop == 'vtchange') {
            $data_type = '';
            $data_length = '';
            $data_decimal_places = '';
            $list = '';
            $default_value = '';
        }

        // do list change
        if ($changeop == 'listchange') {
            $default_value = '';
        }

        // get list block
        $list_block = $this->getDetaileditListBlock($view_type, $list, $disabled_arr);

        // get default block
        $default_block = $this->getDetaileditDefaultValutBlock($view_type, $list, $default_value, $disabled_arr);

        // get datatype info
        $selected_datatype_name = '';
        $datatypelist = $this->getDataTypeList($view_type, $data_type, $selected_datatype_name, $scope_search, $scope_search_arr);

        // token ticket
        $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemfield_edit'));

        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['base_detailid'] = $base_detailid;
        $viewData['detailid'] = $detailid;
        $viewData['detailInfo'] = $detailInfo;
        $viewData['viewtypelist'] = $viewtypelist;
        $viewData['datatypelist'] = $datatypelist;
        $viewData['selected_datatype_name'] = $selected_datatype_name;
        $viewData['name'] = $name;
        $viewData['xml'] = $xml;
        $viewData['view_type'] = $view_type;
        $viewData['data_type'] = $data_type;
        $viewData['data_length'] = $data_length;
        $viewData['data_decimal_places'] = $data_decimal_places;
        $viewData['default_value'] = $default_value;
        $viewData['essential'] = $essential;
        $viewData['detail_display'] = $detail_display;
        $viewData['detail_target'] = $detail_target;
        $viewData['scope_search'] = $scope_search;
        $viewData['nondisplay'] = $nondisplay;
        $viewData['scope_search_arr'] = $scope_search_arr;
        $viewData['disabled_arr'] = $disabled_arr;
        $viewData['list_block'] = $list_block;
        $viewData['default_block'] = $default_block;
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

    protected function doEditsave(&$request, &$response)
    {
        // get requests
        $base_detailid = $request->getParameter('base_detailid');
        $detailid = $request->getParameter('detailid');
        $changeop = $request->getParameter('changeop');
        $name = $request->getParameter('name');
        $xml = $request->getParameter('xml');
        $view_type = $request->getParameter('view_type');
        $data_type = $request->getParameter('data_type');
        $list = $request->getParameter('list');
        $data_length = $request->getParameter('data_length');
        $data_decimal_places = $request->getParameter('data_decimal_places');
        $default_value = $request->getParameter('default_value');
        $essential = $request->getParameter('essential');
        $detail_display = $request->getParameter('detail_display');
        $detail_target = $request->getParameter('detail_target');
        $scope_search = $request->getParameter('scope_search');
        $nondisplay = $request->getParameter('nondisplay');
        $scope_search_arr = $request->getParameter('scope_search_arr');
        $mode = $request->getParameter('mode');

        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_EDIT_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_MODIFY_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // get detail info
        $disabled_arr = '';
        $detailInfo = $this->getDetailInfoForEdit($detailid, $disabled_arr, true);

        // get default_value title
        if ($detailInfo['b_list'] != '' && $detailInfo['b_default_value'] != '') {
            $valueSetBean = Xoonips_BeanFactory::getBean('ItemFieldValueSetBean', $this->dirname, $this->trustDirname);
            $detailInfo['b_default_value'] = $valueSetBean->getItemTypeValueTitle($detailInfo['b_list'], $detailInfo['b_default_value']);
        }

        // get viewtype info
        $viewtypelist = $this->getViewTypeList($view_type);

        // do view_type change
        if ($changeop == 'vtchange') {
            $data_type = '';
            $data_length = '';
            $data_decimal_places = '';
            $list = '';
            $default_value = '';
        }

        // do list change
        if ($changeop == 'listchange') {
            $default_value = '';
        }

        // get list block
        $list_block = $this->getDetaileditListBlock($view_type, $list, $disabled_arr);

        // get default block
        $default_block = $this->getDetaileditDefaultValutBlock($view_type, $list, $default_value, $disabled_arr);

        // get datatype info
        $selected_datatype_name = '';
        $datatypelist = $this->getDataTypeList($view_type, $data_type, $selected_datatype_name, $scope_search, $scope_search_arr);

        // do check
        $errors = new Xoonips_Errors();
        $inputData = array();
        $inputData['name'] = $name;
        $inputData['xml'] = $xml;
        $inputData['view_type'] = $view_type;
        $inputData['data_type'] = $data_type;
        $inputData['list'] = $list;
        $inputData['length'] = $data_length;
        $inputData['length2'] = $data_decimal_places;
        $inputData['default'] = $default_value;

        if (!$this->doDetaileditsaveInputCheck($detailid, $inputData, $errors, $base_detailid)) {
            // token ticket
            $token_ticket = $this->createToken($this->modulePrefix('admin_policy_itemfield_edit'));
            $viewData['breadcrumbs'] = $breadcrumbs;
            $viewData['token_ticket'] = $token_ticket;
            $viewData['detailid'] = $detailid;
            $viewData['detailInfo'] = $detailInfo;
            $viewData['viewtypelist'] = $viewtypelist;
            $viewData['datatypelist'] = $datatypelist;
            $viewData['errors'] = $errors->getView($this->dirname);
            $viewData['name'] = $name;
            $viewData['xml'] = $xml;
            $viewData['view_type'] = $view_type;
            $viewData['data_type'] = $data_type;
            $viewData['data_length'] = $data_length;
            $viewData['data_decimal_places'] = $data_decimal_places;
            $viewData['default_value'] = $default_value;
            $viewData['essential'] = $essential;
            $viewData['detail_display'] = $detail_display;
            $viewData['detail_target'] = $detail_target;
            $viewData['scope_search'] = $scope_search;
            $viewData['nondisplay'] = $nondisplay;
            $viewData['scope_search_arr'] = $scope_search_arr;
            $viewData['disabled_arr'] = $disabled_arr;
            $viewData['list_block'] = $list_block;
            $viewData['default_block'] = $default_block;
            $viewData['title'] = $title;
            $viewData['description'] = $description;
            $viewData['dirname'] = $this->dirname;

            $response->setViewData($viewData);
            $response->setForward('edit_success');

            return true;
        }

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('admin_policy_itemfield_edit'))) {
            return false;
        }

        // update itemtype detail
        $detail_info = array();
        $detail_info['name'] = $name;
        $detail_info['xml'] = $xml;
        $detail_info['view_type_id'] = $view_type;
        $detail_info['data_type_id'] = $data_type;
        $detail_info['data_length'] = ($data_length == '') ? -1 : $data_length;
        $detail_info['data_decimal_places'] = ($data_decimal_places == '') ? -1 : $data_decimal_places;
        $detail_info['default_value'] = ($default_value == '') ? null : $default_value;
        $detail_info['list'] = ($list == '') ? null : $list;
        $detail_info['essential'] = empty($essential) ? 0 : $essential;
        $detail_info['detail_display'] = empty($detail_display) ? 0 : $detail_display;
        $detail_info['detail_target'] = empty($detail_target) ? 0 : $detail_target;
        $detail_info['scope_search'] = empty($scope_search) ? 0 : $scope_search;
        $detail_info['nondisplay'] = empty($nondisplay) ? 0 : $nondisplay;

        // transaction
        $transaction = Xoonips_Transaction::getInstance();
        $transaction->start();

        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        if (!$detailBean->update($detail_info, $detailid)) {
            $transaction->rollback();

            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_itemfield.php?op=edit&detailid='.$base_detailid;
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_MODIFY_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('editsave_success');

            return true;
        }

        // release mode
        if ($mode == 1) {
            if (!$detailBean->release($detailid, $base_detailid)) {
                $transaction->rollback();

                $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItemField';
                $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_RELEASE_MSG_FAILURE;
                $response->setViewData($viewData);
                $response->setForward('editsave_success');

                return true;
            }

            // Quick Search Setting
            if (!$this->installItemSearch($base_detailid)) {
                $transaction->rollback();

                $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItemField';
                $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_RELEASE_MSG_FAILURE;
                $response->setViewData($viewData);
                $response->setForward('editsave_success');

                return true;
            }
        }

        // success
        $transaction->commit();

        $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItemField';
        if ($mode == 1) {
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_RELEASE_MSG_SUCCESS;
        } else {
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_MODIFY_MSG_SUCCESS;
        }
        $response->setViewData($viewData);
        $response->setForward('editsave_success');

        return true;
    }

    protected function doRelease(&$request, &$response)
    {
        //title
        $title = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_EDIT_TITLE;
        $description = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_MODIFY_DESC;

        // breadcrumbs
        $breadcrumbs = $this->setBreadcrumbs($title);

        // get requests
        $base_detailid = $request->getParameter('base_detailid');
        $detailid = $request->getParameter('detailid');

        // do release
        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        if (!$detailBean->release($detailid, $base_detailid)) {
            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItemField';
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_MODIFY_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('release_success');

            return true;
        }

        $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItemField';
        $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_ITEMTYPE_DETAIL_MODIFY_MSG_SUCCESS;
        $response->setViewData($viewData);
        $response->setForward('release_success');

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
                'name' => _AM_XOONIPS_POLICY_ITEMFIELD_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItemField',
            ),
            array(
                'name' => $title,
            ),
        );

        return $breadcrumbs;
    }

    // get edit field info
    private function getDetailInfoForEdit($detailid, &$disabled_arr, $ng = false)
    {
        $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
        if ($ng) {
            $dinfo = $detailBean->getDetailEditInfo($detailid);
        } else {
            $dinfo = $detailBean->getDetailEditInfo($detailid, true);
        }
        $disabled_arr = $dinfo['b_released'] == 1 ? 'disabled="disabled"' : '';
        $b_view_type_id = $dinfo['a_released'] == 1 ? $dinfo['a_view_type_id'] : $dinfo['b_view_type_id'];
        $b_data_type_id = $dinfo['a_released'] == 1 ? $dinfo['a_data_type_id'] : $dinfo['b_data_type_id'];
        $detailInfo = array(
            'a_name' => $dinfo['a_released'] == 1 ? $dinfo['b_name'] : $dinfo['a_name'],
            'a_xml' => $dinfo['a_released'] == 1 ? $dinfo['b_xml'] : $dinfo['a_xml'],
            'a_view_type' => $dinfo['a_released'] == 1 ? $dinfo['b_view_type_id'] : $dinfo['a_view_type_id'],
            'a_data_type' => $dinfo['a_released'] == 1 ? $dinfo['b_data_type_id'] : $dinfo['a_data_type_id'],
            'a_data_length' => $dinfo['a_released'] == 1 ? $dinfo['b_data_length'] : $dinfo['a_data_length'],
            'a_data_decimal_places' => $dinfo['a_released'] == 1 ? $dinfo['b_data_decimal_places'] : $dinfo['a_data_decimal_places'],
            'a_default_value' => $dinfo['a_released'] == 1 ? $dinfo['b_default_value'] : $dinfo['a_default_value'],
            'a_list' => $dinfo['a_released'] == 1 ? $dinfo['b_list'] : $dinfo['a_list'],
            'a_essential' => $dinfo['a_released'] == 1 ? $dinfo['b_essential'] : $dinfo['a_essential'],
            'a_detail_display' => $dinfo['a_released'] == 1 ? $dinfo['b_detail_display'] : $dinfo['a_detail_display'],
            'a_detail_target' => $dinfo['a_released'] == 1 ? $dinfo['b_detail_target'] : $dinfo['a_detail_target'],
            'a_scope_search' => $dinfo['a_released'] == 1 ? $dinfo['b_scope_search'] : $dinfo['a_scope_search'],
            'a_nondisplay' => $dinfo['a_released'] == 1 ? $dinfo['b_nondisplay'] : $dinfo['a_nondisplay'],
            'a_weight' => $dinfo['a_released'] == 1 ? $dinfo['b_weight'] : $dinfo['a_weight'],
            'b_name' => $dinfo['a_released'] == 1 ? $dinfo['a_name'] : $dinfo['b_name'],
            'b_xml' => $dinfo['a_released'] == 1 ? $dinfo['a_xml'] : $dinfo['b_xml'],
            'b_view_type' => $b_view_type_id,
            'b_data_type' => $b_data_type_id,
            'b_view_name' => empty($b_view_type_id) ? '' : Xoonips_ViewTypeFactory::getInstance($this->dirname, $this->trustDirname)->getViewType($b_view_type_id)->getName(),
            'b_data_name' => empty($b_data_type_id) ? '' : Xoonips_DataTypeFactory::getInstance($this->dirname, $this->trustDirname)->getDataType($b_data_type_id)->getName(),
            'b_data_length' => $dinfo['a_released'] == 1 ? $dinfo['a_data_length'] : $dinfo['b_data_length'],
            'b_data_decimal_places' => $dinfo['a_released'] == 1 ? $dinfo['a_data_decimal_places'] : $dinfo['b_data_decimal_places'],
            'b_default_value' => $dinfo['a_released'] == 1 ? $dinfo['a_default_value'] : $dinfo['b_default_value'],
            'b_list' => $dinfo['a_released'] == 1 ? $dinfo['a_list'] : $dinfo['b_list'],
            'b_essential' => $dinfo['a_released'] == 1 ? $dinfo['a_essential'] : $dinfo['b_essential'],
            'b_detail_display' => $dinfo['a_released'] == 1 ? $dinfo['a_detail_display'] : $dinfo['b_detail_display'],
            'b_detail_target' => $dinfo['a_released'] == 1 ? $dinfo['a_detail_target'] : $dinfo['b_detail_target'],
            'b_scope_search' => $dinfo['a_released'] == 1 ? $dinfo['a_scope_search'] : $dinfo['b_scope_search'],
            'b_nondisplay' => $dinfo['a_released'] == 1 ? $dinfo['a_nondisplay'] : $dinfo['b_nondisplay'],
            'b_weight' => $dinfo['a_released'] == 1 ? $dinfo['a_weight'] : $dinfo['b_weight'],
            'b_item_field_detail_id' => $dinfo['b_item_field_detail_id'],
        );

        return $detailInfo;
    }

    private function isDiff($detailBean, $detailid)
    {
        $dinfo = $detailBean->getDetailEditInfo($detailid);

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

    private function getDataTypeList($view_type, $data_type, &$selected_datatype_name, &$scope_search, &$scope_search_arr)
    {
        $datatypeBean = Xoonips_BeanFactory::getBean('DataTypeBean', $this->dirname, $this->trustDirname);
        $datatypes = $datatypeBean->selectDatatypesByViewtype($view_type);
        $datatypelist = array(array('datatype_id' => '0', 'name' => '-------------'));
        foreach ($datatypes as $dt) {
            $datatype['datatype_id'] = $dt['data_type_id'];
            $datatype['name'] = $dt['name'];
            $datatype['data_length'] = $dt['data_length'];
            $datatype['data_decimal_places'] = $dt['data_decimal_places'];
            $datatype['selected'] = ($dt['data_type_id'] == $data_type) ? 'selected="selected"' : '';
            if ($datatype['selected'] != '') {
                $selected_datatype_name = $dt['name'];
            }
            $datatypelist[] = $datatype;
        }

        // scope_search contral
        if (intval($data_type) == $datatypeBean->selectByName('char')
                || intval($data_type) == $datatypeBean->selectByName('varchar')
                || intval($data_type) == $datatypeBean->selectByName('text')
                || intval($data_type) == $datatypeBean->selectByName('blob')) {
            $scope_search = '';
            $scope_search_arr = "disabled='disabled'";
        } else {
            $scope_search_arr = '';
        }

        return $datatypelist;
    }

    private function doDetailregistersaveInputCheck($inputData, &$errors)
    {
        // detail name
        $parameters = array();
        $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_DETAIL_NAME;
        $viewTypeBean = Xoonips_BeanFactory::getBean('ViewTypeBean', $this->dirname, $this->trustDirname);
        $dataTypeBean = Xoonips_BeanFactory::getBean('DataTypeBean', $this->dirname, $this->trustDirname);

        if ($inputData['name'] == '') {
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
        } else {
            // name double check except preview & file upload
            if ($inputData['view_type'] != $viewTypeBean->selectByName('preview') && $inputData['view_type'] != $viewTypeBean->selectByName('file upload')) {
                /*  allow duplicate item detail name
                $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
                if ($detailBean->existDetailName(0, $inputData['name'], 0)) {
                    $errors->addError("_AM_XOONIPS_ERROR_DUPLICATE_MSG", "", $parameters);
                }
                */
            }
        }

        // detail xml
        $parameters = array();
        $parameters[] = _AM_XOONIPS_POLICY_ITEMFIELD_ID;
        if ($inputData['xml'] == '') {
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
        } else {
            // xml double check except preview & file upload
            if ($inputData['view_type'] != $viewTypeBean->selectByName('preview') && $inputData['view_type'] != $viewTypeBean->selectByName('file upload')) {
                $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
                if ($detailBean->existDetailXml(0, $inputData['xml'], 0)) {
                    $errors->addError('_AM_XOONIPS_ERROR_DUPLICATE_MSG', '', $parameters);
                }
            }
        }

        // view_type
        if ($inputData['view_type'] == '0') {
            $parameters = array();
            $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_VIEW_TYPE;
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
        } else {
            $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
            $viewtypeObj = Xoonips_ViewTypeFactory::getInstance($this->dirname, $this->trustDirname)->getViewType($inputData['view_type']);
            if ($viewtypeObj->isMulti() == false && $detailBean->existViewtype(0, $inputData['view_type'])) {
                $parameters = array();
                $parameters[] = '';
                $errors->addError('_AM_XOONIPS_POLICY_ITEMTYPE_VIEWTYPE_DUPLICATE_MSG', '', $parameters);
            }
        }

        // data_type
        if ($inputData['data_type'] == '0') {
            $parameters = array();
            $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_DATA_TYPE;
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
        }

        // list required when view_type is radio & select
        if (($inputData['view_type'] == $viewTypeBean->selectByName('radio') || $inputData['view_type'] == $viewTypeBean->selectByName('checkbox')) && $inputData['list'] == '') {
            $parameters = array();
            $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_SUBTYPES;
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
        }

        // length
        if ($inputData['length'] == '') {
            if ($inputData['data_type'] == $dataTypeBean->selectByName('int')) {
                $inputData['length'] = 11;
            } elseif ($inputData['data_type'] == $dataTypeBean->selectByName('float')) {
                $inputData['length'] = 24;
            } elseif ($inputData['data_type'] == $dataTypeBean->selectByName('double')) {
                $inputData['length'] = 53;
            } elseif ($inputData['data_type'] == $dataTypeBean->selectByName('varchar')) {
                $inputData['length'] = 255;
            } elseif ($inputData['data_type'] != $dataTypeBean->selectByName('char')) {
                $inputData['length'] = -1;
            }
        }
        if ($inputData['length'] != '' && !is_numeric($inputData['length'])) {
            $parameters = array();
            $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_DATA_LENGTH;
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
        }

        // length2
        if ($inputData['length2'] == '') {
            if ($inputData['data_type'] == $dataTypeBean->selectByName('float') || $inputData['data_type'] == $dataTypeBean->selectByName('double')) {
                $inputData['length2'] = 0;
            } else {
                $inputData['length2'] = -1;
            }
        }
        if ($inputData['length2'] != '' && !is_numeric($inputData['length2'])) {
            $parameters = array();
            $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_DATA_LENGTH2;
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
        }

        if (count($errors->getErrors()) > 0) {
            return false;
        } else {
            // 'value' colmun attribute check
            $item = new Xoonips_ItemField();
            $item->setLen($inputData['length']);
            $item->setDecimalPlaces($inputData['length2']);
            $item->setDefault($inputData['default']);
            $datatypeObj = Xoonips_DataTypeFactory::getInstance($this->dirname, $this->trustDirname)->getDataType($inputData['data_type']);
            $datatypeObj->valueAttrCheck($item, $errors);
            if (count($errors->getErrors()) > 0) {
                return false;
            }
        }

        return true;
    }

    // get list block view
    private function getDetailregisterListBlock($view_type, $list)
    {
        if (empty($view_type)) {
            return $this->getListBlockHtml($view_type);
        }
        $viewTypeManage = Xoonips_ViewTypeFactory::getInstance($this->dirname, $this->trustDirname)->getViewType($view_type);

        return $viewTypeManage->getListBlockView($list);
    }

    // get default value block view
    private function getDetailregisterDefaultValutBlock($view_type, $list, $default_value)
    {
        if (empty($view_type)) {
            return $this->getDefaultListBlockHtml($default_value);
        }
        $viewTypeManage = Xoonips_ViewTypeFactory::getInstance($this->dirname, $this->trustDirname)->getViewType($view_type);

        return $viewTypeManage->getDefaultValueBlockView($list, $default_value);
    }

    private function doDetaileditsaveInputCheck($detailid, $inputData, &$errors, $base_detailid)
    {
        // detail name
        $viewTypeBean = Xoonips_BeanFactory::getBean('ViewTypeBean', $this->dirname, $this->trustDirname);

        $parameters = array();
        $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_DETAIL_NAME;
        if ($inputData['name'] == '') {
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
        }

        // detail xml
        $parameters = array();
        $parameters[] = _AM_XOONIPS_POLICY_ITEMFIELD_ID;
        if ($inputData['xml'] == '') {
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
        } else {
            // xml double check except preview & file upload
            if ($inputData['view_type'] != $viewTypeBean->selectByName('preview') && $inputData['view_type'] != $viewTypeBean->selectByName('file upload')) {
                $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
                if ($detailBean->existDetailXml($detailid, $inputData['xml'], $base_detailid)) {
                    $errors->addError('_AM_XOONIPS_ERROR_DUPLICATE_MSG', '', $parameters);
                }
            }
        }

        // view_type
        if ($inputData['view_type'] == '0') {
            $parameters = array();
            $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_VIEW_TYPE;
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
        } else {
            $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
            $viewtypeObj = Xoonips_ViewTypeFactory::getInstance($this->dirname, $this->trustDirname)->getViewType($inputData['view_type']);
            if ($viewtypeObj->isMulti() == false && $detailBean->existViewtype($detailid, $inputData['view_type'], $base_detailid)) {
                $parameters = array();
                $parameters[] = '';
                $errors->addError('_AM_XOONIPS_POLICY_ITEMTYPE_VIEWTYPE_DUPLICATE_MSG', '', $parameters);
            }
        }

        // data_type
        if ($inputData['data_type'] == '0') {
            $parameters = array();
            $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_DATA_TYPE;
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
        }

        // list required when view_type is radio & select
        if (($inputData['view_type'] == $viewTypeBean->selectByName('radio') || $inputData['view_type'] == $viewTypeBean->selectByName('select')) && $inputData['list'] == '') {
            $parameters = array();
            $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_SUBTYPES;
            $errors->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
        }

        // length
        if ($inputData['length'] != '' && !is_numeric($inputData['length'])) {
            $parameters = array();
            $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_DATA_LENGTH;
            $errors->addError('_AM_XOONIPS_CHECK_INPUT_ERROR_MSG', '', $parameters);
        }

        // length2
        if ($inputData['length2'] != '' && !is_numeric($inputData['length2'])) {
            $parameters = array();
            $parameters[] = _AM_XOONIPS_LABEL_ITEMTYPE_DATA_LENGTH2;
            $errors->addError('_AM_XOONIPS_CHECK_INPUT_ERROR_MSG', '', $parameters);
        }

        if (count($errors->getErrors()) > 0) {
            return false;
        } else {
            // 'value' colmun attribute check
            $item = new Xoonips_ItemField();
            $item->setLen($inputData['length']);
            $item->setDecimalPlaces($inputData['length2']);
            $item->setDefault($inputData['default']);
            $datatypeObj = Xoonips_DataTypeFactory::getInstance($this->dirname, $this->trustDirname)->getDataType($inputData['data_type']);
            $datatypeObj->valueAttrCheck($item, $errors);
            if (count($errors->getErrors()) > 0) {
                return false;
            }
        }

        return true;
    }

    // get list block view
    private function getDetaileditListBlock($view_type, $list, $disabled_arr)
    {
        if (empty($view_type)) {
            return $this->getListBlockHtml($view_type);
        }
        $viewTypeManage = Xoonips_ViewTypeFactory::getInstance($this->dirname, $this->trustDirname)->getViewType($view_type);

        return $viewTypeManage->getListBlockView($list, $disabled_arr);
    }

    // get default value block view
    private function getDetaileditDefaultValutBlock($view_type, $list, $default_value, $disabled_arr)
    {
        if (empty($view_type)) {
            return $this->getDefaultListBlockHtml($default_value);
        }
        $viewTypeManage = Xoonips_ViewTypeFactory::getInstance($this->dirname, $this->trustDirname)->getViewType($view_type);

        return $viewTypeManage->getDefaultValueBlockView($list, $default_value, $disabled_arr);
    }

    private function getListBlockHtml($view_type)
    {
        $root = &XCube_Root::getSingleton();
        $root->mContext->mModule->setAdminMode(true);
        $controller = $root->getController();
        $render = $controller->mRoot->mContext->mModule->getRenderTarget();
        $render->setTemplateName('policy_itemtype.inc.html');
        $render->setAttribute('flg', true);
        $render->setAttribute('value', $view_type);
        $renderSystem = $controller->mRoot->getRenderSystem($controller->mRoot->mContext->mModule->getRenderSystemName());
        $renderSystem->renderMain($render);
        $ret = $render->getResult();

        return $ret;
    }

    private function getDefaultListBlockHtml($default_value)
    {
        $root = &XCube_Root::getSingleton();
        $root->mContext->mModule->setAdminMode(true);
        $controller = $root->getController();
        $render = $controller->mRoot->mContext->mModule->getRenderTarget();
        $render->setTemplateName('policy_itemtype.inc.html');
        $render->setAttribute('flg', false);
        $render->setAttribute('value', $default_value);
        $renderSystem = $controller->mRoot->getRenderSystem($controller->mRoot->mContext->mModule->getRenderSystemName());
        $renderSystem->renderMain($render);
        $ret = $render->getResult();

        return $ret;
    }

    private function getViewTypeList($view_type)
    {
        $viewtypeBean = Xoonips_BeanFactory::getBean('ViewTypeBean', $this->dirname, $this->trustDirname);
        $viewtypes = $viewtypeBean->getViewtypeList();
        $viewtypelist = array(array('viewtype_id' => '0', 'name' => '-------------'));
        foreach ($viewtypes as $vt) {
            $viewtype['viewtype_id'] = $vt['view_type_id'];
            $viewtype['name'] = $vt['name'];
            $viewtype['selected'] = ($vt['view_type_id'] == $view_type) ? 'selected="selected"' : '';
            $viewtypelist[] = $viewtype;
        }

        return $viewtypelist;
    }

    private function doCopyItemfield($itemfieldid, $isCopy, &$insertId)
    {
        $map = array();
        if ($isCopy) {
            $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
            if (!$detailBean->copyById($itemfieldid, $map)) {
                return false;
            }
        } else {
            $detailBean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
            if (!$detailBean->copyById($itemfieldid, $map, true)) {
                return false;
            }

            $insertId = $map['detail'][$itemfieldid];
        }

        return true;
    }

    /**
     * installItemSearch.
     *
     * @param   $detailid
     *
     * @return bool
     **/
    private function installItemSearch($detailid)
    {
        // always true
        // now, condition id 1 will contains all searchable field ids,
        // each field id does not need to insert into condition detail table.
        return true;
    }
}
