<?php

require_once dirname(dirname(__FILE__)).'/Enum.class.php';

abstract class Xoonips_Field
{
    protected $id;
    protected $preSelect;
    protected $tableName;
    protected $columnName;
    protected $fieldGroupId;
    protected $weight;
    protected $name;
    protected $xmlTag;
    protected $viewType;
    protected $viewTypeId;
    protected $dataType;
    protected $default;
    protected $listId;
    protected $len;
    protected $decimalPlaces;
    protected $essential;
    protected $listDisplay;
    protected $listSortKey;
    protected $listWidth;
    protected $detailDisplay;
    protected $registry;
    protected $edit;
    protected $search;
    protected $scopeSearch;
    protected $nonDisplay;
    protected $updateId;
    protected $dirname;
    protected $trustDirname;
    private $xoopsTpl;
    protected $template;

    abstract protected function getList();

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setPreSelect($v)
    {
        $this->preSelect = $v;
    }

    public function getPreSelect()
    {
        return $this->preSelect;
    }

    public function setTableName($v)
    {
        $this->tableName = $v;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setColumnName($v)
    {
        $this->columnName = $v;
    }

    public function getColumnName()
    {
        return $this->columnName;
    }

    public function setFieldGroupId($v)
    {
        $this->fieldGroupId = $v;
    }

    public function getFieldGroupId()
    {
        return $this->fieldGroupId;
    }

    public function setName($v)
    {
        $this->name = $v;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setXmlTag($v)
    {
        $this->xmlTag = $v;
    }

    public function getXmlTag()
    {
        return $this->xmlTag;
    }

    public function setViewType($v)
    {
        $this->viewType = $v;
    }

    public function getViewType()
    {
        return $this->viewType;
    }

    public function setViewTypeId($v)
    {
        $this->viewTypeId = $v;
    }

    public function getViewTypeId()
    {
        return $this->viewTypeId;
    }

    public function setDataType($v)
    {
        $this->dataType = $v;
    }

    public function getDataType()
    {
        return $this->dataType;
    }

    public function setLen($v)
    {
        $this->len = $v;
    }

    public function getLen()
    {
        return $this->len;
    }

    public function setDecimalPlaces($v)
    {
        $this->decimalPlaces = $v;
    }

    public function getDecimalPlaces()
    {
        return $this->decimalPlaces;
    }

    public function setDefault($v)
    {
        $this->default = $v;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setEssential($v)
    {
        $this->essential = $v;
    }

    public function getEssential()
    {
        return $this->essential;
    }

    public function setListDisplay($v)
    {
        $this->listDisplay = $v;
    }

    public function getListDisplay()
    {
        return $this->listDisplay;
    }

    public function setListSortKey($v)
    {
        $this->listSortKey = $v;
    }

    public function getListSortKey()
    {
        return $this->listSortKey;
    }

    public function setListWidth($v)
    {
        $this->listWidth = $v;
    }

    public function getListWidth()
    {
        return $this->listWidth;
    }

    public function setDetailDisplay($v)
    {
        $this->detailDisplay = $v;
    }

    public function getDetailDisplay()
    {
        return $this->detailDisplay;
    }

    public function setRegistry($v)
    {
        $this->registry = $v;
    }

    public function getRegistry()
    {
        return $this->registry;
    }

    public function setEdit($v)
    {
        $this->edit = $v;
    }

    public function getEdit()
    {
        return $this->edit;
    }

    public function setSearch($v)
    {
        $this->search = $v;
    }

    public function getSearch()
    {
        return $this->search;
    }

    public function setScopeSearch($v)
    {
        $this->scopeSearch = $v;
    }

    public function getScopeSearch()
    {
        return $this->scopeSearch;
    }

    public function setNonDisplay($v)
    {
        $this->nonDisplay = $v;
    }

    public function getNonDisplay()
    {
        return $this->nonDisplay;
    }

    public function setUpdateId($v)
    {
        $this->updateId = $v;
    }

    public function getUpdateId()
    {
        return $this->updateId;
    }

    public function setListId($v)
    {
        $this->listId = $v;
    }

    public function getListId()
    {
        return $this->listId;
    }

    public function setDirname($v)
    {
        $this->dirname = $v;
    }

    public function setTrustDirname($v)
    {
        $this->trustDirname = $v;
    }

    public function setXoopsTpl($obj)
    {
        $this->xoopsTpl = $obj;
    }

    protected function getXoopsTpl()
    {
        return $this->xoopsTpl;
    }

    /**
     * generate html name attribute value for groupLoop.
     *
     * @param int $groupLoopId
     *
     * @return string
     */
    protected function getFieldName($groupLoopId)
    {
        return $this->getFieldGroupId().Xoonips_Enum::ITEM_ID_SEPARATOR.$groupLoopId.Xoonips_Enum::ITEM_ID_SEPARATOR.$this->getId();
    }

    /**
     * set template name.
     *
     * @param
     */
    public function setTemplate()
    {
        $this->template = $this->dirname.'_field.html';
    }

    /**
     * get registry view.
     *
     * @param int $cnt
     *
     * @return array
     */
    public function getRegistryView($cnt)
    {
        return $this->getInputView($this->viewType->getRegistryView($this), $cnt);
    }

    /**
     * get registry view with data.
     *
     * @param string $value:data value
     *                           int $groupLoopId
     *                           int $cnt
     *
     * @return array
     */
    public function getRegistryViewWithData($value, $groupLoopId, $cnt)
    {
        return $this->getInputView($this->viewType->getRegistryViewWithData($this, $value, $groupLoopId), $cnt);
    }

    /**
     * upload file.
     *
     * @param string $value:data value
     *                           int $groupLoopId
     *                           int $cnt
     *
     * @return
     */
    public function fileUpload($value, $groupLoopId, $cnt)
    {
        $ret = '';
        if ($this->viewType->getModule() == 'ViewTypeFileUpload' || $this->viewType->getModule() == 'ViewTypePreview') {
            $ret = $this->viewType->fileUpload($this, $value, $groupLoopId);
        }

        return $ret;
    }

    /**
     * get search view with data.
     *
     * @param string $value:data value
     *                           int $groupLoopId
     *                           int $cnt
     *
     * @return array
     */
    public function getSearchViewWithData($value, $groupLoopId, $cnt)
    {
        return $this->getDetailViewSub($this->viewType->getSearchViewWithData($this, $value, $groupLoopId), $cnt);
    }

    /**
     * get edit view.
     *
     * @param string $value:data value
     *                           int $groupLoopId
     *                           int $cnt
     *
     * @return array
     */
    public function getEditView($value, $groupLoopId, $cnt)
    {
        return $this->getInputView($this->viewType->getEditView($this, $value, $groupLoopId), $cnt);
    }

    /**
     * get edit view with data.
     *
     * @param string $value:data value
     *                           int $groupLoopId
     *                           int $cnt
     *
     * @return array
     */
    public function getEditViewWithData($value, $groupLoopId, $cnt)
    {
        return $this->getInputView($this->viewType->getEditViewWithData($this, $value, $groupLoopId), $cnt);
    }

    /**
     * get edit view with data for moderator.
     *
     * @param string $value:data value
     *                           int $groupLoopId
     *                           int $cnt
     *
     * @return array
     */
    public function getEditViewWithDataForModerator($value, $groupLoopId, $cnt)
    {
        return $this->getInputView($this->viewType->getEditViewWithDataForModerator($this, $value, $groupLoopId), $cnt);
    }

    /**
     * get edit view for moderator.
     *
     * @param string $value:data value
     *                           int $groupLoopId
     *                           int $cnt
     *
     * @return array
     */
    public function getEditViewForModerator($value, $groupLoopId, $cnt)
    {
        return $this->getInputView($this->viewType->getEditViewForModerator($this, $value, $groupLoopId), $cnt);
    }

    private function getInputView($view, $cnt)
    {
        $fieldName = '';
        $essential = false;
        if ($cnt > 1 && $this->getViewType()->isDisplayFieldName()) {
            $fieldName = $this->getName();
            $essential = ($this->getEssential() == 1);
        }
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('essential', $essential);
        $this->xoopsTpl->assign('viewTypeInput', $view);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    /**
     * item display check.
     *
     * @param int $op
     *                int $userType
     *
     * @return bool
     */
    abstract protected function isDisplay($op, $userType);

    /**
     * registry input check.
     *
     * @param string $value:item value
     *                           object $errors:error
     *                           int $groupLoopId
     *
     * @return
     */
    public function inputCheck($value, &$errors, $groupLoopId)
    {
        $fieldName = $this->getFieldName($groupLoopId);
        //mustCheck
        $this->viewType->mustCheck($errors, $this, $value, $fieldName);

        //viewTypeCheck
        $this->viewType->inputCheck($errors, $this, $value, $fieldName);
    }

    /**
     * edit input check.
     *
     * @param string $value:item value
     *                           object $errors:error
     *                           int $groupLoopId
     *                           int $uid:user id
     *
     * @return
     */
    public function editCheck($value, &$errors, $groupLoopId, $uid)
    {
        $fieldName = $this->getFieldName($groupLoopId);
        //mustCheck
        $this->viewType->mustCheck($errors, $this, $value, $fieldName);

        //viewTypeCheck
        $this->viewType->editCheck($errors, $this, $value, $fieldName, $uid);
    }

    /**
     * search input check.
     *
     * @param string $value:item value
     *                           object $errors:error
     *                           int $groupLoopId
     *
     * @return
     */
    public function searchCheck($value, &$errors, $groupLoopId)
    {
        $fieldName = $this->getFieldName($groupLoopId);
        //viewTypeCheck
        $this->viewType->searchCheck($errors, $this, $value, $fieldName);
    }

    public function doRegistry(&$data, &$sqlStrings, $groupLoopId)
    {
        $this->viewType->doRegistry($this, $data, $sqlStrings, $groupLoopId);
    }

    public function doSearch(&$data, &$sqlStrings, $groupLoopId, $scopeSearchFlg, $isExact)
    {
        $this->viewType->doSearch($this, $data, $sqlStrings, $groupLoopId, $scopeSearchFlg, $isExact);
    }

    public function doEdit(&$data, &$sqlStrings, $groupLoopId)
    {
        $this->viewType->doEdit($this, $data, $sqlStrings, $groupLoopId);
    }

    /**
     * get search view.
     *
     * @param int $cnt
     *
     * @return array
     */
    public function getSearchView($cnt, $groupLoopId)
    {
        return $this->getDetailViewSub($this->viewType->getSearchView($this, $groupLoopId), $cnt);
    }

    /**
     * get confirm view.
     *
     * @param string $value:data value
     *                           int $groupLoopId
     *                           int $cnt
     *
     * @return array
     */
    public function getConfirmView($value, $groupLoopId, $cnt)
    {
        return $this->getDetailViewSub($this->viewType->getConfirmView($this, $value, $groupLoopId), $cnt);
    }

    /**
     * get detail view.
     *
     * @param string $value:data value
     *                           int $groupLoopId
     *                           int $cnt
     *
     * @return array
     */
    public function getDetailView($value, $groupLoopId, $cnt, $display = true)
    {
        return $this->getDetailViewSub($this->viewType->getDetailView($this, $value, $groupLoopId, $display), $cnt);
    }

    /**
     * get detail view for moderator.
     *
     * @param string $value:data value
     *                           int $groupLoopId
     *                           int $cnt
     *
     * @return array
     */
    public function getDetailViewForModerator($value, $groupLoopId, $cnt)
    {
        return $this->getDetailViewSub($this->viewType->getDetailViewForModerator($this, $value, $groupLoopId), $cnt);
    }

    /**
     * get detail view for certify.
     *
     * @param string $value:data value
     *                           int $groupLoopId
     *                           int $cnt
     *
     * @return array
     */
    public function getDetailViewForCertify($value, $groupLoopId, $cnt)
    {
        return $this->getDetailViewSub($this->viewType->getDetailViewForCertify($this, $value, $groupLoopId), $cnt);
    }

    private function getDetailViewSub($view, $cnt)
    {
        $fieldName = '';
        if ($cnt > 1 && $this->getViewType()->isDisplayFieldName()) {
            $fieldName = $this->getName();
        }
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('viewTypeDetail', $view);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }
}
