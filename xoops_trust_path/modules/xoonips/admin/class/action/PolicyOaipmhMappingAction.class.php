<?php

require_once dirname(dirname(dirname(__DIR__))).'/class/core/ActionBase.class.php';
require_once dirname(dirname(dirname(__DIR__))).'/class/core/ItemFieldManagerFactory.class.php';

class Xoonips_PolicyOaipmhMappingAction extends Xoonips_ActionBase
{
    protected function doInit(&$request, &$response)
    {
        $this->setCommonData($request, $viewData);
        $oaipmhSchemaBean = Xoonips_BeanFactory::getBean('OaipmhSchemaBean', $this->dirname, $this->trustDirname);
        $prefixList = $oaipmhSchemaBean->getPrefixList();
        $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $itemtypeList = $itemTypeBean->getItemTypeList();
        $viewData['prefixList'] = $prefixList;
        $viewData['itemtypeList'] = $itemtypeList;

        $response->setViewData($viewData);
        $response->setForward('init_success');

        return true;
    }

    protected function doChange(&$request, &$response)
    {
        $this->setCommonData($request, $viewData);
        $this->setData($request, $viewData, $schemaList);
        $this->setDataFromDB($schemaList, $request, $viewData['selecteditemtype'], $viewData['selectedprefix']);
        $viewData['schemaList'] = $schemaList;

        $response->setViewData($viewData);
        $response->setForward('change_success');

        return true;
    }

    protected function doJoin(&$request, &$response)
    {
        $this->setCommonData($request, $viewData);
        $this->setData($request, $viewData, $schemaList);
        $targetid = $request->getParameter('targetid');
        $ids = explode('_', $targetid);
        $this->setDataFromForm($schemaList, $request);
        foreach ($schemaList as $key => $obj) {
            if ($obj['schema_id'] == $ids[0]) {
                $schemaList[$key]['data'][$ids[1]]['selects'][] = '';
                break;
            }
        }
        $viewData['schemaList'] = $schemaList;

        $this->setUrlData($viewData);
        $response->setViewData($viewData);
        $response->setForward('join_success');

        return true;
    }

    protected function doAdd(&$request, &$response)
    {
        $this->setCommonData($request, $viewData);
        $this->setData($request, $viewData, $schemaList);
        $targetid = $request->getParameter('targetid');
        $this->setDataFromForm($schemaList, $request);
        foreach ($schemaList as $key => $obj) {
            if ($obj['schema_id'] == $targetid) {
                if (false == $obj['hasValueSet']) {
                    $schemaList[$key]['data'][] = ['selects' => [''], 'checkbox' => 0, 'text' => ''];
                } else {
                    $schemaList[$key]['data'][] = ['selects' => ['']];
                }
                break;
            }
        }
        $viewData['schemaList'] = $schemaList;

        $this->setUrlData($viewData);
        $response->setViewData($viewData);
        $response->setForward('add_success');

        return true;
    }

    protected function doDelete(&$request, &$response)
    {
        $this->setCommonData($request, $viewData);
        $this->setData($request, $viewData, $schemaList);
        $targetid = $request->getParameter('targetid');
        $ids = explode('_', $targetid);
        $this->setDataFromForm($schemaList, $request);
        foreach ($schemaList as $key => $obj) {
            if ($obj['schema_id'] == $ids[0]) {
                if (1 == count($schemaList[$key]['data'][$ids[1]]['selects'])) {
                    unset($schemaList[$key]['data'][$ids[1]]);
                } else {
                    unset($schemaList[$key]['data'][$ids[1]]['selects'][$ids[2]]);
                }
                break;
            }
        }
        $viewData['schemaList'] = $schemaList;

        $this->setUrlData($viewData);
        $response->setViewData($viewData);
        $response->setForward('delete_success');

        return true;
    }

    protected function doAutocreate(&$request, &$response)
    {
        global $xoonips_admin;
        $this->setCommonData($request, $viewData);

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('admin_system_oaipmh_mapping'))) {
            return false;
        }
        $this->setData($request, $viewData, $schemaList);
        $this->setDataFromForm($schemaList, $request);
        $linkBean = Xoonips_BeanFactory::getBean('OaipmhSchemaItemtypeLinkBean', $this->dirname, $this->trustDirname);
        $transaction = Xoonips_Transaction::getInstance();
        $transaction->start();
        if (!$linkBean->delete($viewData['selectedprefix'], $viewData['selecteditemtype'])) {
            $transaction->rollback();

            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItem';
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_OAIPMHQUOTA_AUTOCREATE_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('autocreate_success');

            return true;
        }
        if (!$linkBean->autoCreate($viewData['selecteditemtype'])) {
            $transaction->rollback();

            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItem';
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_OAIPMHQUOTA_AUTOCREATE_MSG_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('autocreate_success');

            return true;
        }
        $transaction->commit();

        $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_oaipmh_mapping.php?'.'op=change&amp;selectedprefix=oai_dc&selecteditemtype='.$viewData['selecteditemtype'];
        $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_OAIPMHQUOTA_AUTOCREATE_MSG_SUCCESS;
        $response->setViewData($viewData);
        $response->setForward('autocreate_success');

        return true;
    }

    protected function doUpdate(&$request, &$response)
    {
        global $xoonips_admin;
        $this->setCommonData($request, $viewData);

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('admin_system_oaipmh_mapping'))) {
            return false;
        }

        $this->setData($request, $viewData, $schemaList);
        $this->setDataFromForm($schemaList, $request);
        $errors = $this->inputCheck($schemaList);
        if (!$errors->hasError()) {
            $oaipmhSchemaBean = Xoonips_BeanFactory::getBean('OaipmhSchemaBean', $this->dirname, $this->trustDirname);
            $linkBean = Xoonips_BeanFactory::getBean('OaipmhSchemaItemtypeLinkBean', $this->dirname, $this->trustDirname);
            $transaction = Xoonips_Transaction::getInstance();
            $transaction->start();
            if (!$linkBean->delete($viewData['selectedprefix'], $viewData['selecteditemtype'])) {
                $transaction->rollback();

                $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItem';
                $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_OAIPMHQUOTA_UPDATE_MSG_FAILURE;
                $response->setViewData($viewData);
                $response->setForward('update_success');

                return true;
            }
            foreach ($schemaList as $schema) {
                foreach ($schema['data'] as $data) {
                    $link = [];
                    $selects = $data['selects'];
                    $this->removeEmpty($selects);
                    if (count($selects) > 0) {
                        $groupids = [];
                        $detailids = [];
                        foreach ($selects as $key_detail) {
                            if (1 == preg_match('/^\d+_\d+$/', $key_detail)) {
                                list($key, $detail_id) = explode('_', $key_detail);
                                $groupids[] = $key;
                                $detailids[] = $detail_id;
                            } else {
                                $groupids[] = null;
                                $detailids[] = $key_detail;
                            }
                        }
                        $detailId = implode(',', $detailids);
                        if (count($groupids) > 1) {
                            $link['group_id'] = implode(',', $groupids);
                        } else {
                            $link['group_id'] = $groupids[0];
                        }
                        $link['schema_id'] = $schema['schema_id'];
                        $link['item_type_id'] = $viewData['selecteditemtype'];
                        $link['item_field_detail_id'] = $detailId;
                        if (isset($data['checkbox']) && 1 == $data['checkbox']) {
                            $link['value'] = $data['text'];
                        } else {
                            $link['value'] = null;
                        }
                        if (!$linkBean->insert($link)) {
                            $transaction->rollback();

                            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItem';
                            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_OAIPMHQUOTA_UPDATE_MSG_FAILURE;
                            $response->setViewData($viewData);
                            $response->setForward('update_success');

                            return true;
                        }
                    }
                }
            }
            $transaction->commit();
            if ('oai_dc' == $viewData['selectedprefix']) {
                $this->setUrlData($viewData, true);
            } else {
                $this->setUrlData($viewData, false);
            }
            $viewData['redirect_msg'] = _AM_XOONIPS_POLICY_OAIPMHQUOTA_UPDATE_MSG_SUCCESS;
            $response->setViewData($viewData);
            $response->setForward('update_success');

            return true;
        } else {
            $viewData['errors'] = $errors->getView($this->dirname);
            $viewData['schemaList'] = $schemaList;
        }

        $this->setUrlData($viewData);
        $response->setViewData($viewData);
        $response->setForward('init_success');

        return true;
    }

    private function setData(&$request, &$viewData, &$schemaList)
    {
        $token_ticket = $this->createToken($this->modulePrefix('admin_system_oaipmh_mapping'));

        $prefix = $viewData['selectedprefix'];
        $itemtype = $viewData['selecteditemtype'];
        $oaipmhSchemaBean = Xoonips_BeanFactory::getBean('OaipmhSchemaBean', $this->dirname, $this->trustDirname);
        $schemaList = $oaipmhSchemaBean->getSchemaList($prefix);
        $valueSetList = $oaipmhSchemaBean->getSchemaValueSetList($prefix);
        $itemList = $this->getItemList($itemtype);
        foreach ($schemaList as $key => $value) {
            $valuesets = false;
            foreach ($valueSetList as $obj) {
                if ($obj['schema_id'] == $value['schema_id']) {
                    $valuesets[] = ['key' => $obj['seq_id'], 'value' => $obj['value']];
                }
            }
            if (false == $valuesets) {
                $schemaList[$key]['hasValueSet'] = false;
                if ('publisher' == $value['name']) {
                    $schemaList[$key]['list'] = array_merge(
                        [
                            ['key' => 'meta_author',
                                'value' => 'meta_author',
                            ],
                            ['key' => 'owner',
                                'value' => 'owner',
                            ],
                        ],
                        $itemList
                    );
                } elseif ('type' == $value['name']) {
                    $schemaList[$key]['list'] = array_merge(
                        [
                            ['key' => 'itemtype',
                                'value' => 'itemtype',
                            ],
                        ],
                        $itemList
                    );
                } elseif ('identifier' == $value['name']) {
                    if ('oai_dc' == $prefix) {
                        $schemaList[$key]['list'] = array_merge(
                            [
                                ['key' => 'http://',
                                    'value' => 'http://',
                                ],
                                ['key' => 'ID',
                                    'value' => 'ID',
                                ],
                                ['key' => 'full_text',
                                    'value' => 'full_text',
                                ],
                            ],
                            $itemList
                        );
                    } else {
                        $schemaList[$key]['list'] = array_merge(
                            [
                                ['key' => 'ID',
                                    'value' => 'ID',
                                ],
                            ],
                            $itemList
                        );
                    }
                } elseif ('URI' == $value['name']) {
                    $schemaList[$key]['list'] = array_merge(
                        [
                            ['key' => 'http://',
                                'value' => 'http://',
                            ],
                        ],
                        $itemList
                    );
                } elseif ('fullTextURL' == $value['name']) {
                    $schemaList[$key]['list'] = array_merge(
                        [
                            ['key' => 'full_text',
                                'value' => 'full_text',
                            ],
                        ],
                        $itemList
                    );
                } else {
                    $schemaList[$key]['list'] = &$itemList;
                }
            } else {
                $schemaList[$key]['hasValueSet'] = false; //FIXME
                $schemaList[$key]['list'] = array_merge(
                        $valuesets,
                        $itemList
                        );
            }
        }
        $viewData['token_ticket'] = $token_ticket;
    }

    private function inputCheck(&$schemaList)
    {
        $ret = new Xoonips_Errors();
        foreach ($schemaList as $schema) {
            if (1 == $schema['min_occurences']) {
                foreach ($schema['data'] as $data) {
                    $selects = $data['selects'];
                    $this->removeEmpty($selects);
                    if (0 == count($selects)) {
                        $parameters = [$schema['name']];
                        $ret->addError('_AM_XOONIPS_ERROR_REQUIRED', '', $parameters);
                    }
                }
            }
            /* FIXME  oai_dc mapping
            if (!$this->duplicateCheck($schema)) {
                $parameters = array($schema['name']);
                $ret->addError('_AM_XOONIPS_ERROR_DUPLICATE_MSG', '', $parameters);
            }
            */
        }

        return $ret;
    }

    private function duplicateCheck($schema)
    {
        $temp = [];
        foreach ($schema['data'] as $data) {
            $selects = $data['selects'];
            $this->removeEmpty($selects);
            foreach ($selects as $select) {
                if (isset($temp[$select])) {
                    return false;
                } else {
                    $temp[$select] = 0;
                }
            }
        }

        return true;
    }

    private function removeEmpty(&$selects)
    {
        foreach ($selects as $key => $select) {
            if ('' == $select) {
                unset($selects[$key]);
            }
        }
    }

    private function setDataFromForm(&$schemaList, &$request)
    {
        foreach ($schemaList as $idx => $schema) {
            $count = 0;
            $data = [];
            if (isset($_POST['select_'.$schema['schema_id'].'_'.$count])) {
                $selects = $_POST['select_'.$schema['schema_id'].'_'.$count];
            } else {
                $selects = null;
            }
            while (null != $selects) {
                //FIXME
                if (false == $schema['hasValueSet']) {
                    $checkbox = $request->getParameter('checkbox_'.$schema['schema_id'].'_'.$count);
                    $text = $request->getParameter('text_'.$schema['schema_id'].'_'.$count);
                    $data[] = ['selects' => $selects, 'checkbox' => $checkbox, 'text' => $text];
                } else {
                    $data[] = ['selects' => $selects];
                }
                ++$count;
                if (isset($_POST['select_'.$schema['schema_id'].'_'.$count])) {
                    $selects = $_POST['select_'.$schema['schema_id'].'_'.$count];
                } else {
                    $selects = null;
                }
            }
            $schema['data'] = $data;
            $schemaList[$idx] = $schema;
        }
    }

    private function setDataFromDB(&$schemaList, &$request, $itemtype, $prefix)
    {
        $linkBean = Xoonips_BeanFactory::getBean('OaipmhSchemaItemtypeLinkBean', $this->dirname, $this->trustDirname);
        $links = $linkBean->get($prefix, $itemtype);
        foreach ($schemaList as $idx => $schema) {
            $data = false;
            foreach ($links as $link) {
                if ($link['schema_id'] == $schema['schema_id']) {
                    //$data = array();
                    $selects = [];
                    if (is_null($link['group_id'])) {
                        $selid = $link['item_field_detail_id'];
                        $selects = explode(',', $selid);
                    } else {
                        $field_ids = explode(',', $link['item_field_detail_id']);
                        foreach (explode(',', $link['group_id']) as $gid) {
                            $field_id = array_shift($field_ids);
                            if ('' == $gid) {
                                $selects[] = $field_id;
                            } else {
                                $selects[] = $gid.'_'.$field_id;
                            }
                        }
                    }
                    //FIXME
                    if (false == $schema['hasValueSet']) {
                        $text = $link['value'];
                        if ('' == $text) {
                            $checkbox = 0;
                        } else {
                            $checkbox = 1;
                        }
                        $data[] = ['selects' => $selects, 'checkbox' => $checkbox, 'text' => $text];
                    } else {
                        $data[] = ['selects' => $selects];
                    }
                } elseif (false != $data) {
                    break;
                }
            }
            $schema['data'] = $data;
            $schemaList[$idx] = $schema;
        }
    }

    private function getItemList($item_type)
    {
        $ret = [];
        $itemFieldManager = Xoonips_ItemFieldManagerFactory::getInstance()->getItemFieldManager($item_type);
        $itemGroups = $itemFieldManager->getFieldGroups();
        foreach ($itemGroups as $itemGroup) {
            $items = $itemGroup->getFields();
            foreach ($items as $item) {
                $name = $itemGroup->getName().'.'.$item->getName();
                $key = $itemGroup->getId().'_'.$item->getId();
                $ret[] = ['key' => $key, 'value' => $name];
            }
        }

        return $ret;
    }

    private function setCommonData(&$request, &$viewData)
    {
        $request = new Xoonips_Request();
        $selectedprefix = $request->getParameter('selectedprefix');
        $selecteditemtype = $request->getParameter('selecteditemtype');

        //get the item type value
        $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $itemtypeList = $itemTypeBean->getItemTypeList();

        $selecteditemtypename = '';
        foreach ($itemtypeList as $itemtype) {
            if ($itemtype['item_type_id'] == $selecteditemtype) {
                $selecteditemtypename = $itemtype['name'];
            }
        }

        $title = _AM_XOONIPS_POLICY_OAIPMH_QUOTA_TITLE;
        $description = _AM_XOONIPS_POLICY_OAIPMH_QUOTA_DESC;

        // breadcrumbs
        $breadcrumbs = [
            [
                'name' => _AD_XOONIPS_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php',
            ],
            [
                'name' => _AD_XOONIPS_POLICY_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=Policy',
            ],
            [
                'name' => _AD_XOONIPS_POLICY_ITEM_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/admin/index.php?action=PolicyItem',
            ],
            [
                'name' => $title,
            ],
        ];

        //get common viewdata
        $viewData = [];

        $viewData['breadcrumbs'] = $breadcrumbs;
        $viewData['title'] = $title;
        $viewData['description'] = $description;
        $viewData['selectedprefix'] = $selectedprefix;
        $viewData['selecteditemtype'] = $selecteditemtype;
        $viewData['selecteditemtypename'] = $selecteditemtypename;
        $viewData['dirname'] = $this->dirname;
        $viewData['mytrustdirname'] = $this->trustDirname;
    }

    private function setUrlData(&$viewData, $isAutoCreate = false)
    {
        $url = XOOPS_URL.'/modules/'.$this->dirname.'/admin/policy_oaipmh_mapping.php';
        if (('oai_dc' == $viewData['selectedprefix'] && !$isAutoCreate) ||
            ('oai_dc' != $viewData['selectedprefix'] && $isAutoCreate)) {
            $url .= '?op=change&amp;selectedprefix=oai_dc&amp;selecteditemtype='.$viewData['selecteditemtype'];
        }
        $viewData['url'] = $url;
    }
}
