<?php

use Xoonips\Core\Functions;

require_once dirname(__DIR__).'/core/File.class.php';
require_once dirname(__DIR__).'/core/Request.class.php';
require_once __DIR__.'/ViewType.class.php';

class Xoonips_ViewTypeFileUpload extends Xoonips_ViewType
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_fileupload.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $fileName = $fieldName.'_file';
        if ($this->isLayered()) {
            $fileId = 'none';
        } else {
            $file = new Xoonips_File($this->dirname, $this->trustDirname, true);
            $fileId = $file->uploadFile($fileName, 'upload', 0, $field->getId());
        }
        $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
        if (!empty($value)) {
            if ('none' == $fileId) {
                $fileId = $value;
            } else {
                $fileBean->delete($value);
            }
        }
        $fileInfo = $fileBean->getFileInformation($fileId);
        $this->getXoopsTpl()->assign('viewType', 'input');
        $this->getXoopsTpl()->assign('fileName', $fileName);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);
        $this->getXoopsTpl()->assign('fileId', $fileId);
        $this->getXoopsTpl()->assign('fileInfo', $fileInfo);
        $this->getXoopsTpl()->assign('dirname', $this->dirname);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function fileUpload($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $fileName = $fieldName.'_file';
        if (empty($value)) {
            $file = new Xoonips_File($this->dirname, $this->trustDirname, true);
            $fileId = $file->uploadFile($fileName, 'upload', 0, $field->getId(), $field->getFieldGroupId());
            //upload_max_filesize check
            if ('none' == $fileId) {
                $fileId = -1;
            }
        } else {
            $fileId = $value;
        }
        $this->getXoopsTpl()->assign('viewType', 'fileUpload');
        $this->getXoopsTpl()->assign('fileName', $fileName);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);
        $this->getXoopsTpl()->assign('fileId', $fileId);
        $this->getXoopsTpl()->assign('dirname', $this->dirname);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $fileName = $fieldName.'_file';

        if ($this->isLayered()) {
            $fileId = 'none';
        } else {
            $file = new Xoonips_File($this->dirname, $this->trustDirname, true);
            $fileId = $file->uploadFile($fileName, 'upload', 0, $field->getId());
        }
        $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
        if (!empty($value)) {
            if ('none' == $fileId) {
                $fileId = $value;
            } else {
                $fileBean->delete($value);
            }
        }
        $fileInfo = $fileBean->getFileInformation($fileId);
        $this->getXoopsTpl()->assign('viewType', 'confirm');
        $this->getXoopsTpl()->assign('fileName', $fileName);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);
        $this->getXoopsTpl()->assign('fileId', $fileId);
        $this->getXoopsTpl()->assign('fileInfo', $fileInfo);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getEditView($field, $value, $groupLoopId)
    {
        return $this->getInputView($field, $value, $groupLoopId);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        if (!empty($value)) {
            $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
            $fileInfo = $fileBean->getFileInformation($value);
            $file = $fileBean->getFile($value);
            if (false !== $file) {
                $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
                $notify = $itemBean->getDownloadNotify($file['item_id'], $field->getItemTypeId());
                $rights = $itemBean->getRights($file['item_id'], $field->getItemTypeId());
                $limit = $itemBean->getDownloadLimit($file['item_id'], $field->getItemTypeId());
            }
            global $xoopsUser;
            $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;
            $newFileInfo = [];
            foreach ($fileInfo as $file) {
                if (false === $file || (false === $rights && (false === $notify || '0' == $notify))) {
                    $file['notify'] = false;
                } else {
                    $file['notify'] = true;
                }
                if (false === $file || (XOONIPS_UID_GUEST == $uid && false === $limit)) {
                    $file['limit'] = false;
                } else {
                    $file['limit'] = true;
                }
                $itemFileHandler = Functions::getXoonipsHandler('ItemFile', $this->dirname);
                $itemFileObj = &$itemFileHandler->get($value);
                $file['download_url'] = $itemFileObj->getDownloadUrl();
                $newFileInfo[] = $file;
            }
            $fileInfo = $newFileInfo;
            $this->getXoopsTpl()->assign('fileInfo', $fileInfo);
        } else {
            $this->getXoopsTpl()->assign('fileInfo', '');
        }
        $this->getXoopsTpl()->assign('viewType', 'detail');
        $this->getXoopsTpl()->assign('fileId', $value);
        $this->getXoopsTpl()->assign('display', $display);
        $this->getXoopsTpl()->assign('dirname', $this->dirname);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getSearchView($field, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->getXoopsTpl()->assign('viewType', 'search');
        $this->getXoopsTpl()->assign('fieldName', $fieldName);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function isDisplay($op)
    {
        if (Xoonips_Enum::OP_TYPE_METAINFO == $op) {
            return false;
        } //hidden when metainfo form

        return true;
    }

    public function mustCheck(&$errors, $field, $value, $fieldName)
    {
        if (1 == $field->getEssential() && '' == $value) {
            $fileName = $fieldName;
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

        $parameters = [];
        $parameters[] = '';
        //upload_max_filesize
        if (-1 == $value) {
            $errors->addError('_MD_XOONIPS_ITEM_WARNING_UPLOAD_MAX_FILESIZE', '', $parameters);
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
        $tableData;
        $columnData;
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
        $columnData[0] = $field->getDataType()->convertSQLStr('');
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
            $query1 = $this->search->getSearchSql('original_file_name', $value, _CHARSET, $field->getDataType(), $isExact);
            $query2 = $this->search->getFulltextSearchSql('search_text', $value, _CHARSET);
            if ('' != $query2) {
                $tableData[] = '('.$query1.' OR '.$query2.')';
            } else {
                $tableData[] = $query1;
            }
        }
    }

    public function getMetadata($field, &$data)
    {
        $table = $field->getTableName();
        $column = $field->getColumnName();
        $detail_id = $field->getId();
        $original_file_name = [];
        $mime_type = [];
        foreach ($data[$table] as $value) {
            if ($value['item_field_detail_id'] == $detail_id) {
                $original_file_name[] = $value['original_file_name'];
                $mime_type[] = $value['mime_type'];
            }
        }

        return [implode(',', $original_file_name), implode(',', $mime_type)];
    }

    /**
     * get entity data.
     *
     * @param object $field
     *                      array $data
     *
     * @return mix
     */
    public function getEntitydata($field, &$data)
    {
        $table = $field->getTableName();
        $column = $field->getColumnName();
        $detail_id = $field->getId();
        $ret = [];
        $i = 0;
        foreach ($data[$table] as $value) {
            if ($value['item_field_detail_id'] == $detail_id) {
                $ret[$i]['original_file_name'] = $value['original_file_name'];
                $ret[$i]['mime_type'] = $value['mime_type'];
                $ret[$i]['file_size'] = $value['file_size'];
                $ret[$i]['handle_name'] = $value['handle_name'];
                $ret[$i]['caption'] = $value['caption'];
                $ret[$i]['download_count'] = $value['download_count'];
                ++$i;
            }
        }

        return $ret;
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
        $this->getXoopsTpl()->assign('viewType', 'default');
        $this->getXoopsTpl()->assign('value', $value);

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
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
}
