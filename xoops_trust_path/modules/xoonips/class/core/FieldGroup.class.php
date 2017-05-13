<?php

require_once __DIR__.'/Field.class.php';

class Xoonips_FieldGroup
{
    protected $id;
    protected $preSelect;
    protected $weight;
    protected $name;
    protected $xmlTag;
    protected $occurrence = false;
    protected $updateId;
    protected $fields = array();
    private $dirname;
    private $trustDirname;
    private $xoopsTpl;
    protected $template;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
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

    public function setOccurrence($v)
    {
        $this->occurrence = $v;
    }

    public function getOccurrence()
    {
        return $this->occurrence;
    }

    public function setFields($v)
    {
        $this->fields = $v;
    }

    public function getFields()
    {
        return $this->fields;
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
     * generate html name attribute value for item.
     *
     * @param object $field
     *                      int $groupLoopId
     *
     * @return string
     */
    protected function getFieldName($field, $groupLoopId)
    {
        if ($groupLoopId == 0) {
            return '0'.Xoonips_Enum::ITEM_ID_SEPARATOR.$groupLoopId.Xoonips_Enum::ITEM_ID_SEPARATOR.$field->getId();
        } else {
            return $this->getId().Xoonips_Enum::ITEM_ID_SEPARATOR.$groupLoopId.Xoonips_Enum::ITEM_ID_SEPARATOR.$field->getId();
        }
    }

    /**
     * set template name.
     *
     * @param
     */
    public function setTemplate()
    {
        $this->template = $this->dirname.'_field_group.html';
    }

    /**
     * get registry view.
     *
     * @param int $cnt
     *                 int $userType
     *
     * @return array
     */
    public function getRegistryView($cnt, $userType = Xoonips_Enum::USER_TYPE_USER, $op = Xoonips_Enum::OP_TYPE_REGISTRY)
    {
        $fieldGroup = array();
        foreach ($this->fields as $field) {
            if ($field->isDisplay($op, $userType)) {
                $fieldGroup[] = $field->getRegistryView($cnt);
            }
        }
        $id = $this->id.Xoonips_Enum::ITEM_ID_SEPARATOR.'2';
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('_LABEL_ADD', constant('_MD_'.strtoupper($this->trustDirname).'_LABEL_ADD'));
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);
        $this->xoopsTpl->assign('occurrence', $this->occurrence);
        $this->xoopsTpl->assign('id', $id);
        $this->xoopsTpl->assign('dirname', $this->dirname);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /**
     * get registry view with data.
     *
     * @param array $data : data
     *                    int $cnt
     *                    int $userType
     *
     * @return array
     */
    public function getRegistryViewWithData(&$data, $cnt, $userType = Xoonips_Enum::USER_TYPE_USER, $op = Xoonips_Enum::OP_TYPE_REGISTRY)
    {
        $loopArray = array();
        //get groupLoopId array
        foreach ($data as $key => $v) {
            $idArray = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $key);
            if ($idArray[0] == $this->getId()) {
                $loopArray[] = $idArray[1];
            }
        }
        $loopArray = array_unique($loopArray);
        $fieldGroups = array();
        $num = 1;
        foreach ($loopArray as $groupLoopId) {
            if ($this->hasFieldGroupData($data, $groupLoopId)) {
                $fieldGroup = array();
                foreach ($this->fields as $field) {
                    if ($field->isDisplay($op, $userType)) {
                        $value = $data[$this->getFieldName($field, $groupLoopId)];
                        $fieldGroup[] = $field->getRegistryViewWithData($value, $num, $cnt);
                    }
                }
                $fieldGroups[] = array('id' => $this->id.Xoonips_Enum::ITEM_ID_SEPARATOR.$num, 'fieldGroup' => $fieldGroup);
                ++$num;
            }
        }

        return $this->commonEditAssign($fieldGroups, $num);
    }

    /**
     * upload file.
     *
     * @param array $data : data
     *                    int $cnt
     *                    int $userType
     *
     * @return
     */
    public function fileUpload(&$data, $cnt, $userType = Xoonips_Enum::USER_TYPE_USER, $op = Xoonips_Enum::OP_TYPE_REGISTRY)
    {
        $ret = '';
        $loopArray = array();
        //get groupLoopId array
        foreach ($data as $key => $v) {
            $idArray = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $key);
            if ($idArray[0] == $this->getId()) {
                $loopArray[] = $idArray[1];
            }
        }
        $loopArray = array_unique($loopArray);
        $num = 1;
        foreach ($loopArray as $groupLoopId) {
            if ($this->hasFieldGroupData($data, $groupLoopId)) {
                foreach ($this->fields as $field) {
                    if ($field->isDisplay($op, $userType)) {
                        $value = $data[$this->getFieldName($field, $groupLoopId)];
                        $ret = $ret.$field->fileUpload($value, $num, $cnt);
                    }
                }
                ++$num;
            }
        }

        return $ret;
    }

    /**
     * get search view with data.
     *
     * @param array $data : data array
     *                    int $cnt
     *
     * @return array
     */
    public function getSearchViewWithData(&$data, $cnt, $groupLoopId = 1, $userType = Xoonips_Enum::USER_TYPE_USER)
    {
        $fieldGroup = array();
        $hasGroup = $this->hasFieldGroupData($data, $groupLoopId);
        if ($hasGroup) {
            foreach ($this->fields as $field) {
                if ($field->isDisplay(Xoonips_Enum::OP_TYPE_SEARCH, $userType)) {
                    $value = $data[$this->getFieldName($field, $groupLoopId)];
                    $fieldGroup[] = $field->getSearchViewWithData($value, $groupLoopId, $cnt);
                }
            }
        }
        $this->xoopsTpl->assign('viewType', 'search');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);
        $this->xoopsTpl->assign('hasGroup', $hasGroup);
        self::setTemplate();

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /**
     * get edit view.
     *
     * @param array $data : data
     *                    int $cnt
     *                    int $userType
     *
     * @return array
     */
    public function getEditView(&$data, $cnt, $userType = Xoonips_Enum::USER_TYPE_USER)
    {
        $fieldGroups = array();
        $groupLoopId = 1;
        do {
            $fieldGroup = array();
            foreach ($this->fields as $field) {
                if ($field->isDisplay(Xoonips_Enum::OP_TYPE_EDIT, $userType)) {
                    if (!isset($data[$this->getFieldName($field, $groupLoopId)])) {
                        $value = '';
                    } else {
                        $value = $data[$this->getFieldName($field, $groupLoopId)];
                    }
                    $fieldGroup[] = $field->getEditView($value, $groupLoopId, $cnt);
                }
            }
            $fieldGroups[] = array('id' => $this->id.Xoonips_Enum::ITEM_ID_SEPARATOR.$groupLoopId, 'fieldGroup' => $fieldGroup);
            ++$groupLoopId;
        } while ($this->hasFieldGroupData($data, $groupLoopId));

        return $this->commonEditAssign($fieldGroups, $groupLoopId);
    }

    /**
     * get edit view with data.
     *
     * @param array $data : data
     *                    int $cnt
     *                    int $userType
     *
     * @return array
     */
    public function getEditViewWithData(&$data, $cnt, $userType = Xoonips_Enum::USER_TYPE_USER)
    {
        $loopArray = array();
        //get groupLoopId array
        foreach ($data as $key => $v) {
            $idArray = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $key);
            if ($idArray[0] == $this->getId()) {
                $loopArray[] = $idArray[1];
            }
        }
        $loopArray = array_unique($loopArray);
        $fieldGroups = array();
        $num = 1;
        foreach ($loopArray as $groupLoopId) {
            if ($this->hasFieldGroupData($data, $groupLoopId)) {
                $fieldGroup = array();
                foreach ($this->fields as $field) {
                    if ($field->isDisplay(Xoonips_Enum::OP_TYPE_EDIT, $userType)) {
                        $value = $data[$this->getFieldName($field, $groupLoopId)];
                        $fieldGroup[] = $field->getEditViewWithData($value, $num, $cnt);
                    }
                }
                $fieldGroups[] = array('id' => $this->id.Xoonips_Enum::ITEM_ID_SEPARATOR.$num, 'fieldGroup' => $fieldGroup);
                ++$num;
            }
        }

        return $this->commonEditAssign($fieldGroups, $num);
    }

    /**
     * get edit view with data for moderator.
     *
     * @param array $data : data
     *                    int $cnt
     *
     * @return array
     */
    public function getEditViewWithDataForModerator(&$data, $cnt)
    {
        $loopArray = array();
        //get groupLoopId array
        foreach ($data as $key => $v) {
            $idArray = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $key);
            if ($idArray[0] == $this->getId()) {
                $loopArray[] = $idArray[1];
            }
        }
        $loopArray = array_unique($loopArray);
        $fieldGroups = array();
        $num = 1;
        foreach ($loopArray as $groupLoopId) {
            if ($this->hasFieldGroupData($data, $groupLoopId)) {
                $fieldGroup = array();
                foreach ($this->fields as $field) {
                    if ($field->isDisplay(Xoonips_Enum::OP_TYPE_MANAGER_EDIT, Xoonips_Enum::USER_TYPE_MODERATOR)) {
                        $value = $data[$this->getFieldName($field, $groupLoopId)];
                        $fieldGroup[] = $field->getEditViewWithDataForModerator($value, $num, $cnt);
                    }
                }
                $fieldGroups[] = array('id' => $this->id.Xoonips_Enum::ITEM_ID_SEPARATOR.$num, 'fieldGroup' => $fieldGroup);
                ++$num;
            }
        }
        $id = $this->id.Xoonips_Enum::ITEM_ID_SEPARATOR.$num;

        return $this->commonEditAssign($fieldGroups, $num);
    }

    /**
     * get edit view for moderator.
     *
     * @param array $data : data
     *                    int $cnt
     *
     * @return array
     */
    public function getEditViewForModerator(&$data, $cnt)
    {
        $fieldGroups = array();
        $groupLoopId = 1;
        do {
            $fieldGroup = array();
            foreach ($this->fields as $field) {
                if ($field->isDisplay(Xoonips_Enum::OP_TYPE_MANAGER_EDIT, Xoonips_Enum::USER_TYPE_MODERATOR)) {
                    if (isset($data[$this->getFieldName($field, $groupLoopId)])) {
                        $value = $data[$this->getFieldName($field, $groupLoopId)];
                    } else {
                        $value = '';
                    }

                    $fieldGroup[] = $field->getEditViewForModerator($value, $groupLoopId, $cnt);
                }
            }
            $fieldGroups[] = array('id' => $this->id.Xoonips_Enum::ITEM_ID_SEPARATOR.$groupLoopId, 'fieldGroup' => $fieldGroup);
            ++$groupLoopId;
        } while ($this->hasFieldGroupData($data, $groupLoopId));

        return $this->commonEditAssign($fieldGroups, $groupLoopId);
    }

    private function commonEditAssign($fieldGroups, $groupLoopId)
    {
        $id = $this->id.Xoonips_Enum::ITEM_ID_SEPARATOR.$groupLoopId;
        $this->xoopsTpl->assign('viewType', 'edit');
        $this->xoopsTpl->assign('_LABEL_ADD', constant('_MD_'.strtoupper($this->trustDirname).'_LABEL_ADD'));
        $this->xoopsTpl->assign('_LABEL_DELETE', constant('_MD_'.strtoupper($this->trustDirname).'_LABEL_DELETE'));
        $this->xoopsTpl->assign('fieldGroups', $fieldGroups);
        $this->xoopsTpl->assign('occurrence', $this->occurrence);
        $this->xoopsTpl->assign('id', $id);
        $this->xoopsTpl->assign('dirname', $this->dirname);
        self::setTemplate();

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /**
     * check item group data.
     *
     * @param array $data : data
     *                    int $groupLoopId
     *
     * @return bool
     */
    public function hasFieldGroupData(&$data, $groupLoopId)
    {
        $ret = false;
        foreach ($this->fields as $field) {
            $key = $this->getFieldName($field, $groupLoopId);
            if (isset($data[$key])) {
                $ret = true;
                break;
            }
        }

        return $ret;
    }

    /**
     * get display field count.
     *
     * @param int $op
     *                int $userType
     *
     * @return int
     */
    public function countDisplayField($op, $userType)
    {
        $ret = 0;
        foreach ($this->fields as $field) {
            if ($field->isDisplay($op, $userType) == true) {
                ++$ret;
            }
        }

        return $ret;
    }

    /**
     * get detail display item count.
     *
     * @param int $displayFlg
     *
     * @return int
     */
    public function countDetailDisplayItem($displayFlg)
    {
        $ret = 0;
        foreach ($this->fields as $field) {
            if ($field->getNonDisplay() == 0 && ($field->getDetailDisplay() > $displayFlg || $field->getDetailDisplay() == $displayFlg)) {
                ++$ret;
            }
        }

        return $ret;
    }

    /**
     * must item check.
     *
     * @param int $op
     *                int $userType
     *
     * @return bool
     */
    public function isMust($op, $userType)
    {
        foreach ($this->fields as $field) {
            if ($field->isDisplay($op, $userType) == true) {
                if ($field->getEssential() == 1) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * registry input check.
     *
     * @param array $data:data array
     *                         object $error:error
     *
     * @return
     */
    public function inputCheck(&$data, &$errors)
    {
        $groupLoopId = 1;
        while ($this->hasFieldGroupData($data, $groupLoopId)) {
            foreach ($this->fields as $field) {
                //if this item is existed
                if (isset($data[$this->getFieldName($field, $groupLoopId)])) {
                    $id = $this->getFieldName($field, $groupLoopId);
                    $value = $data[$this->getFieldName($field, $groupLoopId)];
                    $field->inputCheck($value, $errors, $groupLoopId);
                }
            }
            ++$groupLoopId;
        }
    }

    /**
     * edit input check.
     *
     * @param array $data:data array
     *                         object $error:error
     *                         int $uid:user id
     *
     * @return
     */
    public function editCheck(&$data, &$errors, $uid)
    {
        $groupLoopId = 1;
        while ($this->hasFieldGroupData($data, $groupLoopId)) {
            foreach ($this->fields as $field) {
                if (isset($data[$this->getFieldName($field, $groupLoopId)])) {
                    $value = $data[$this->getFieldName($field, $groupLoopId)];
                    $field->editCheck($value, $errors, $groupLoopId, $uid);
                }
            }
            ++$groupLoopId;
        }
    }

    /**
     * search input check.
     *
     * @param array $data:data array
     *                         object $error:error
     *
     * @return
     */
    public function searchCheck(&$data, &$errors)
    {
        $groupLoopId = 1;
        while ($this->hasFieldGroupData($data, $groupLoopId)) {
            foreach ($this->fields as $field) {
                if (isset($data[$this->getFieldName($field, $groupLoopId)])) {
                    $value = $data[$this->getFieldName($field, $groupLoopId)];
                    $field->searchCheck($value, $errors, $groupLoopId);
                }
            }
            ++$groupLoopId;
        }
    }

    /**
     * do registry.
     *
     * @param array $data:data array
     *                         array $sqlStrings:sql array
     *                         int $userType
     *
     * @return
     */
    public function doRegistry(&$data, &$sqlStrings, $userType = Xoonips_Enum::USER_TYPE_USER, $op = Xoonips_Enum::OP_TYPE_REGISTRY)
    {
        $groupLoopId = 1;
        while ($this->hasFieldGroupData($data, $groupLoopId)) {
            foreach ($this->fields as $field) {
                if ($field->isDisplay($op, $userType)) {
                    $field->doRegistry($data, $sqlStrings, $groupLoopId);
                }
            }
            ++$groupLoopId;
        }
    }

    /**
     * do edit.
     *
     * @param array $data:data array
     *                         array $sqlStrings:sql array
     *                         int $userType
     *
     * @return
     */
    public function doEdit(&$data, &$sqlStrings, $userType = Xoonips_Enum::USER_TYPE_USER)
    {
        $groupLoopId = 1;
        while ($this->hasFieldGroupData($data, $groupLoopId)) {
            foreach ($this->fields as $field) {
                if ($field->isDisplay(Xoonips_Enum::OP_TYPE_EDIT, $userType)) {
                    $field->doEdit($data, $sqlStrings, $groupLoopId);
                }
            }
            ++$groupLoopId;
        }
    }

    /**
     * do edit for moderator.
     *
     * @param array $data:data array
     *                         array $sqlStrings:sql array
     *                         int $userType
     *
     * @return
     */
    public function doEditForModerator(&$data, &$sqlStrings, $userType = Xoonips_Enum::USER_TYPE_MODERATOR)
    {
        $groupLoopId = 1;
        while ($this->hasFieldGroupData($data, $groupLoopId)) {
            foreach ($this->fields as $field) {
                if ($field->isDisplay(Xoonips_Enum::OP_TYPE_MANAGER_EDIT, $userType)) {
                    $field->doEdit($data, $sqlStrings, $groupLoopId);
                }
            }
            ++$groupLoopId;
        }
    }

    /**
     * get search view.
     *
     * @param int $cnt
     *                 int $userType
     *
     * @return array
     */
    public function getSearchView($cnt, $userType = Xoonips_Enum::USER_TYPE_USER, $groupLoopId = 1)
    {
        $fieldGroup = array();
        foreach ($this->fields as $field) {
            if ($field->isDisplay(Xoonips_Enum::OP_TYPE_SEARCH, $userType)) {
                $fieldGroup[] = $field->getSearchView($cnt, $groupLoopId);
            }
        }
        $this->xoopsTpl->assign('viewType', 'search');
        $this->xoopsTpl->assign('fieldGroup', $fieldGroup);
        $this->xoopsTpl->assign('hasGroup', true);
        self::setTemplate();

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /**
     * do search.
     *
     * @param array $data:data array
     *                         array $sqlStrings:sql array
     *
     * @return
     */
    public function doSearch(&$data, &$sqlStrings, $userType = Xoonips_Enum::USER_TYPE_USER, $op = Xoonips_Enum::OP_TYPE_SEARCH, $groupLoopId = 1, $scopeSearchFlg = true, $isExact = false)
    {
        if ($this->hasFieldGroupData($data, $groupLoopId)) {
            foreach ($this->fields as $field) {
                if ($field->isDisplay($op, $userType)) {
                    $field->doSearch($data, $sqlStrings, $groupLoopId, $scopeSearchFlg, $isExact);
                }
            }
        }
    }

    /**
     * get confirm view.
     *
     * @param array $data:data array
     *                         int $cnt
     *                         int $op
     *                         int $userType
     *
     * @return array
     */
    public function getConfirmView(&$data, $cnt, $op, $userType = Xoonips_Enum::USER_TYPE_USER)
    {
        $loopArray = array();
        //get groupLoopId array
        foreach ($data as $key => $v) {
            $idArray = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $key);
            if ($idArray[0] == $this->getId()) {
                $loopArray[] = $idArray[1];
            }
        }
        $loopArray = array_unique($loopArray);
        $fieldGroups = array();
        foreach ($loopArray as $groupLoopId) {
            if ($this->hasFieldGroupData($data, $groupLoopId)) {
                $fieldGroup = array();
                foreach ($this->fields as $field) {
                    if ($field->isDisplay($op, $userType)) {
                        $value = $data[$this->getFieldName($field, $groupLoopId)];
                        $fieldGroup[] = $field->getConfirmView($value, $groupLoopId, $cnt);
                    }
                }
                $fieldGroups[] = $fieldGroup;
            }
        }
        $this->xoopsTpl->assign('viewType', 'confirm');
        $this->xoopsTpl->assign('fieldGroups', $fieldGroups);
        self::setTemplate();

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /**
     * get detail view.
     *
     * @param array $data:data array
     *                         int $cnt
     *                         int $userType
     *
     * @return array
     */
    public function getDetailView(&$data, $cnt, $userType = Xoonips_Enum::USER_TYPE_USER, $display = true)
    {
        $fieldGroups = array();
        $groupLoopId = 1;
        do {
            $fieldGroup = array();
            foreach ($this->fields as $field) {
                if ($field->isDisplay(Xoonips_Enum::OP_TYPE_DETAIL, $userType)) {
                    if (!isset($data[$this->getFieldName($field, $groupLoopId)])) {
                        $value = '';
                    } else {
                        $value = $data[$this->getFieldName($field, $groupLoopId)];
                    }
                    $fieldGroup[] = $field->getDetailView($value, $groupLoopId, $cnt, $display);
                }
            }
            $fieldGroups[] = $fieldGroup;
            ++$groupLoopId;
        } while ($this->hasFieldGroupData($data, $groupLoopId));
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('fieldGroups', $fieldGroups);
        $this->xoopsTpl->assign('dirname', $this->dirname);
        self::setTemplate();

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /**
     * get detail view for moderator.
     *
     * @param array $data:data array
     *                         int $cnt
     *                         int $userType
     *
     * @return array
     */
    public function getDetailViewForModerator(&$data, $cnt, $userType = Xoonips_Enum::USER_TYPE_MODERATOR)
    {
        $fieldGroups = array();
        $groupLoopId = 1;
        do {
            $fieldGroup = array();
            foreach ($this->fields as $field) {
                if ($field->isDisplay(Xoonips_Enum::OP_TYPE_DETAIL, $userType)) {
                    if (!isset($data[$this->getFieldName($field, $groupLoopId)])) {
                        $value = '';
                    } else {
                        $value = $data[$this->getFieldName($field, $groupLoopId)];
                    }
                    $fieldGroup[] = $field->getDetailViewForModerator($value, $groupLoopId, $cnt);
                }
            }
            $fieldGroups[] = $fieldGroup;
            ++$groupLoopId;
        } while ($this->hasFieldGroupData($data, $groupLoopId));
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('fieldGroups', $fieldGroups);
        $this->xoopsTpl->assign('dirname', $this->dirname);
        self::setTemplate();

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /**
     * get detail view for certify.
     *
     * @param array $data:data array
     *                         int $cnt
     *                         int $userType
     *
     * @return array
     */
    public function getDetailViewForCertify(&$data, $cnt, $userType = Xoonips_Enum::USER_TYPE_USER)
    {
        $fieldGroups = array();
        $groupLoopId = 1;
        do {
            $fieldGroup = array();
            foreach ($this->fields as $field) {
                if ($field->isDisplay(Xoonips_Enum::OP_TYPE_DETAIL, $userType)) {
                    if (!isset($data[$this->getFieldName($field, $groupLoopId)])) {
                        $value = '';
                    } else {
                        $value = $data[$this->getFieldName($field, $groupLoopId)];
                    }
                    $fieldGroup[] = $field->getDetailViewForCertify($value, $groupLoopId, $cnt);
                }
            }
            $fieldGroups[] = $fieldGroup;
            ++$groupLoopId;
        } while ($this->hasFieldGroupData($data, $groupLoopId));
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('fieldGroups', $fieldGroups);
        $this->xoopsTpl->assign('dirname', $this->dirname);
        self::setTemplate();

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }
}
