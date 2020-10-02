<?php

require_once dirname(__DIR__).'/core/File.class.php';
require_once dirname(__DIR__).'/core/Request.class.php';
require_once __DIR__.'/ViewType.class.php';

class Xoonips_ViewTypePreview extends Xoonips_ViewType
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_preview.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $fileName = $fieldName.'_file';
        if ($this->isLayered()) {
            $fileId = 'none';
        } else {
            $file = new Xoonips_File($this->dirname, $this->trustDirname);
            $fileId = $file->uploadFile($fileName, 'preview', 0, $field->getId());
        }
        if (!empty($value)) {
            if ('none' == $fileId) {
                $fileId = $value;
            } else {
                $fileBean->delete($value);
            }
        }
        $preview = !empty($fileId) ? $fileBean->getFile($fileId) : false;
        $this->xoopsTpl->assign('preview', $preview);
        $this->xoopsTpl->assign('viewType', 'input');
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('fileName', $fileName);
        $this->xoopsTpl->assign('value', $value);
        $this->xoopsTpl->assign('fileId', $fileId);
        $this->xoopsTpl->assign('dirname', $this->dirname);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function fileUpload($field, $value, $groupLoopId)
    {
        $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $fileName = $fieldName.'_file';
        if (empty($value)) {
            $file = new Xoonips_File($this->dirname, $this->trustDirname);
            $fileId = $file->uploadFile($fileName, 'preview', 0, $field->getId(), $field->getFieldGroupId());
        } else {
            $fileId = $value;
        }
        $preview = !empty($fileId) ? $fileBean->getFile($fileId) : false;
        $this->xoopsTpl->assign('preview', $preview);
        $this->xoopsTpl->assign('viewType', 'fileUpload');
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $value);
        $this->xoopsTpl->assign('fileId', $fileId);
        $this->xoopsTpl->assign('dirname', $this->dirname);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $fileName = $fieldName.'_file';
        if ($this->isLayered()) {
            $fileId = 'none';
        } else {
            $file = new Xoonips_File($this->dirname, $this->trustDirname);
            $fileId = $file->uploadFile($fileName, 'preview', 0, $field->getId());
        }
        if (!empty($value)) {
            if ('none' == $fileId) {
                $fileId = $value;
            } else {
                $fileBean->delete($value);
            }
        }
        $preview = !empty($fileId) ? $fileBean->getFile($fileId) : false;
        $this->xoopsTpl->assign('preview', $preview);
        $this->xoopsTpl->assign('viewType', 'confirm');
        $this->xoopsTpl->assign('fieldName', $fieldName);
        $this->xoopsTpl->assign('value', $value);
        $this->xoopsTpl->assign('fileId', $fileId);
        $this->xoopsTpl->assign('dirname', $this->dirname);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getEditView($field, $value, $groupLoopId)
    {
        return $this->getInputView($field, $value, $groupLoopId);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
        $fileId = $value;
        $preview = !empty($fileId) ? $fileBean->getFile($fileId) : false;
        $this->xoopsTpl->assign('preview', $preview);
        $this->xoopsTpl->assign('viewType', 'detail');
        $this->xoopsTpl->assign('fileId', $fileId);
        $this->xoopsTpl->assign('dirname', $this->dirname);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function getSearchView($field, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->xoopsTpl->assign('viewType', 'search');
        $this->xoopsTpl->assign('fieldName', $fieldName);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    public function mustCheck(&$errors, $field, $value, $fieldName)
    {
        if (1 == $field->getEssential() && '' == $value) {
            $fileName = $fieldName.'_file';
            $request = new Xoonips_Request();
            $file = $request->getFile($fileName);
            if (empty($file)) {
                $parameters = [];
                $parameters[] = $field->getName();
                $errors->addError('_MD_XOONIPS_ERROR_REQUIRED', $fieldName, $parameters);
            }
        }
    }

    public function inputCheck(&$errors, $field, $value, $fieldName)
    {
        $request = new Xoonips_Request();
        $req_file = $request->getParameter($fieldName);
        if (!$req_file) {
            return true;
        }

        $file_ids = [];
        if (is_array($req_file)) {
            $file_ids = $req_file;
        } else {
            $file_ids[] = $req_file;
        }

        // storage limit check
        global $xoopsUser;
        $uid = $xoopsUser->getVar('uid');
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $filesizes = $itemBean->getFilesizePrivate($uid);
        foreach ($file_ids as $file_id) {
            $filesizes = $filesizes + $itemBean->getFilesizePrivateByFileId($file_id);
        }
        $privateItemLimit = $itemBean->getPrivateItemLimit($uid);
        if ($filesizes > $privateItemLimit['itemStorage'] && $privateItemLimit['itemStorage'] > 0) {
            $parameters = [];
            $parameters[] = '';
            $errors->addError('_MD_XOONIPS_ITEM_WARNING_ITEM_STORAGE_LIMIT', '', $parameters);
        }
    }

    public function editCheck(&$errors, $field, $value, $fieldName, $uid)
    {
        $this->inputCheck($errors, $field, $value, $fieldName);
    }

    public function doRegistry($field, &$data, &$sqlStrings, $groupLoopId)
    {
        $tableName = $field->getTableName();
        $columnName = $this->getData($field, $data, $groupLoopId);
        $bean = Xoonips_BeanFactory::getBean('ItemFieldDetailComplementLinkBean', $this->dirname, $this->trustDirname);
        $result = $bean->getItemTypeDetail($field->getItemTypeId(), $field->getId());
        $value = '';
        if ($result) {
            $value = $data[$this->getFieldName($field, $groupLoopId, $result[0]['item_field_detail_id'])];
        }

        if (isset($sqlStrings[$tableName])) {
            $tableData = &$sqlStrings[$tableName];
        } else {
            $tableData = [];
            $sqlStrings[$tableName] = &$tableData;
        }

        if (isset($tableData[$columnName])) {
            $columnData = &$tableData[$columnName];
        } else {
            $columnData = [];
            $tableData[$columnName] = &$columnData;
        }
        $columnData[0] = $field->getDataType()->convertSQLStr($value);
        $columnData[1] = $field->getId();
    }

    public function doSearch($field, &$data, &$sqlStrings, $groupLoopId, $scopeSearchFlg, $isExact)
    {
        $tableName = $field->getTableName();
        $columnName = $field->getColumnName();
        $value = $data[$this->getFieldName($field, $groupLoopId)];

        if (isset($sqlStrings[$tableName])) {
            $tableData = &$sqlStrings[$tableName];
        } else {
            $tableData = [];
            $sqlStrings[$tableName] = &$tableData;
        }
        if ('' != $value) {
            $tableData[] = $value;
        }
    }

    /**
     * get default value block view.
     *
     * @param $list, $value, $disabled
     *
     * @return string
     */
    public function getDefaultValueBlockView($list, $value, $disabled = '')
    {
        $this->xoopsTpl->assign('viewType', 'default');
        $this->xoopsTpl->assign('value', $value);

        return $this->xoopsTpl->fetch('db:'.$this->template);
    }

    /**
     * must Create item_extend table.
     *
     * @param
     *
     * @return bool
     */
    public function mustCreateItemExtendTable()
    {
        return false;
    }

    public function getMetadata($field, &$data)
    {
        $table = $field->getTableName();
        $column = $field->getColumnName();
        $detail_id = $field->getId();
        foreach ($data[$table] as $value) {
            if ($value['item_field_detail_id'] == $detail_id) {
                return $value[$column];
            }
        }

        return '';
    }
}
