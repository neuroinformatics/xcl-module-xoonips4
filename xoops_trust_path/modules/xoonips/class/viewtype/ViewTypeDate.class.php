<?php

use Xoonips\Core\XoopsUtils;

require_once __DIR__.'/ViewType.class.php';

class Xoonips_ViewTypeDate extends Xoonips_ViewType
{
    public function setTemplate()
    {
        $this->template = $this->dirname.'_viewtype_date.html';
    }

    public function getInputView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $yearValue = '';
        $monthValue = '';
        $dayValue = '';
        if ('' != $value) {
            if (1 == count(explode('-', $value))) {
                $value = date('Y-m-d', $value);
            }
            $dateArray = explode('-', $value);
            $yearValue = $dateArray[0];
            $monthValue = $dateArray[1];
            $dayValue = $dateArray[2];
        }
        $monthList = $this->getMonths();
        $dayList = $this->getDays();
        $this->getXoopsTpl()->assign('viewType', 'input');
        $this->getXoopsTpl()->assign('yearValue', $yearValue);
        $this->getXoopsTpl()->assign('monthValue', $monthValue);
        $this->getXoopsTpl()->assign('dayValue', $dayValue);
        $this->getXoopsTpl()->assign('monthList', $monthList);
        $this->getXoopsTpl()->assign('dayList', $dayList);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('value', $value);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getSearchView($field, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $monthList = $this->getMonths();
        $dayList = $this->getDays();
        $this->getXoopsTpl()->assign('viewType', 'search');
        $this->getXoopsTpl()->assign('monthList', $monthList);
        $this->getXoopsTpl()->assign('dayList', $dayList);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('scope', $field->getScopeSearch());
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getSearchViewWithData($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $monthList = $this->getMonths();
        $dayList = $this->getDays();
        $yearFromValue = '';
        $monthFromValue = '';
        $dayFromValue = '';
        $yearToValue = '';
        $monthToValue = '';
        $dayToValue = '';
        $dateFromValue = '';
        $dateToValue = '';
        if (is_array($value)) {
            $_ENVyearFromValue = '';
            if ('' != $value[0]) {
                $dateArrayFrom = explode('-', $value[0]);
                $yearFromValue = $dateArrayFrom[0];
                $monthFromValue = $dateArrayFrom[1];
                $dayFromValue = $dateArrayFrom[2];
                $dateFromValue = $yearFromValue.'-'.$monthFromValue.'-'.$dayFromValue;
            }
            if ('' != $value[1]) {
                $dateArrayTo = explode('-', $value[1]);
                $yearToValue = $dateArrayTo[0];
                $monthToValue = $dateArrayTo[1];
                $dayToValue = $dateArrayTo[2];
                $dateToValue = $yearToValue.'-'.$monthToValue.'-'.$dayToValue;
            }
        }
        $this->getXoopsTpl()->assign('viewType', 'search');
        $this->getXoopsTpl()->assign('monthList', $monthList);
        $this->getXoopsTpl()->assign('dayList', $dayList);
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('scope', $field->getScopeSearch());
        $this->getXoopsTpl()->assign('dateFromValue', $dateFromValue);
        $this->getXoopsTpl()->assign('yearFromValue', $yearFromValue);
        $this->getXoopsTpl()->assign('monthFromValue', $monthFromValue);
        $this->getXoopsTpl()->assign('dayFromValue', $dayFromValue);
        $this->getXoopsTpl()->assign('dateToValue', $dateToValue);
        $this->getXoopsTpl()->assign('yearToValue', $yearToValue);
        $this->getXoopsTpl()->assign('monthToValue', $monthToValue);
        $this->getXoopsTpl()->assign('dayToValue', $dayToValue);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getDisplayView($field, $value, $groupLoopId)
    {
        $fieldName = $this->getFieldName($field, $groupLoopId);
        $this->getXoopsTpl()->assign('viewType', 'confirm');
        $this->getXoopsTpl()->assign('fieldName', $fieldName);
        $this->getXoopsTpl()->assign('date', $value);
        $this->getXoopsTpl()->assign('value', $value);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getEditView($field, $value, $groupLoopId)
    {
        return $this->getInputView($field, $value, $groupLoopId);
    }

    public function getDetailDisplayView($field, $value, $display)
    {
        $this->getXoopsTpl()->assign('viewType', 'detail');
        $this->getXoopsTpl()->assign('date', $value);
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getMetaInfo($field, $value)
    {
        return date('Y/m/d', $value);
    }

    private function formatDatetime($str)
    {
        $ret = '';
        if (10 == strlen($str)) {
            $ret = $this->getDate(substr($str, 0, 4), substr($str, 5, 2), substr($str, 8, 2));
        }

        return $ret;
    }

    private function getDate($year, $month, $day)
    {
        $int_year = intval($year);
        $int_month = intval($month);
        $int_day = intval($day);
        if (0 == $int_month) {
            $date = date(constant($this->trustDirname.'_YEAR_FORMAT'), mktime(0, 0, 0, 1, 1, $int_year));
        } else {
            if (0 == $int_day) {
                $date = date(constant($this->trustDirname.'_YEAR_MONTH_FORMAT'), mktime(0, 0, 0, $int_month, 1, $int_year));
            } else {
                $date = date(constant($this->trustDirname.'_DATE_FORMAT'), mktime(0, 0, 0, $int_month, $int_day, $int_year));
            }
        }
        if ($int_year < 0) {
            $date = str_replace('1970', strval(abs($int_year)), $date);
            $date .= 'B.C.';
        } elseif ($int_year < 1970) {
            $date = str_replace('1970', strval($int_year), $date);
        } elseif ($int_year >= 2070) {
            $date = str_replace('1970', strval($int_year), $date);
        }

        return $date;
    }

    protected function getMonths()
    {
        $ret = ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun',
            '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec', ];

        return $ret;
    }

    protected function getDays()
    {
        $ret = [];
        for ($i = 1; $i < 32; ++$i) {
            $value = $i;
            if ($i < 10) {
                $value = "0$i";
            }
            $ret[$value] = $value;
        }

        return $ret;
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
            if (1 == $field->getScopeSearch() && $scopeSearchFlg) {
                $value[0] = trim($value[0]);
                $value[1] = trim($value[1]);
                if ('' != $value[0]) {
                    $value[0] = $this->getTimes($value[0]);
                    $v = $field->getDataType()->convertSQLStr($value[0]);
                    $tableData[] = "$columnName>=$v";
                }
                if ('' != $value[1]) {
                    $value[1] = $this->getTimes($value[1]);
                    $v = $field->getDataType()->convertSQLStr($value[1]);
                    $tableData[] = "$columnName<=$v";
                }
            } else {
                $value = $this->getTimes(trim($value));
                $value = substr($value, 0, 5);
                $v = $field->getDataType()->convertSQLStrLike($value);
                $tableData[] = "$columnName like '%$v%'";
            }
        }
    }

    private function getTimes($value)
    {
        $char = '/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/';
        if (!preg_match($char, $value)) {
            return $value;
        }
        $valueArr = [];
        $valueArr = explode('-', $value);

        return mktime(0, 0, 0, $valueArr[1], $valueArr[2], $valueArr[0]);
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
        self::setTemplate();

        return $this->getXoopsTpl()->fetch('db:'.$this->template);
    }

    public function getRegistryView($field)
    {
        return $this->getInputView($field, date('Y-m-d'), 1);
    }

    public function inputCheck(&$errors, $field, $value, $fieldName)
    {
        $char = '([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})';
        if (is_array($value)) {
            $value[0] = trim($value[0]);
            $value[1] = trim($value[1]);
            if ('' !== $value[0]) {
                $dateArray = explode('-', $value[0]);
                $yearValue = $dateArray[0];
                $monthValue = $dateArray[1];
                $dayValue = $dateArray[2];
                if (!ctype_digit($yearValue) || !checkdate($monthValue, $dayValue, $yearValue)) {
                    $parameters = [];
                    $parameters[] = $field->getName().'[from]';
                    $errors->addError('_MD_XOONIPS_ERROR_DATE', $fieldName, $parameters);
                }
            }
            if ('' !== $value[1]) {
                $dateArray = explode('-', $value[0]);
                $yearValue = $dateArray[0];
                $monthValue = $dateArray[1];
                $dayValue = $dateArray[2];
                if (!ctype_digit($yearValue) || !checkdate($monthValue, $dayValue, $yearValue)) {
                    $parameters = [];
                    $parameters[] = $field->getName().'[to]';
                    $errors->addError('_MD_XOONIPS_ERROR_DATE', $fieldName, $parameters);
                }
            }
        } else {
            $value = trim($value);
            if ('' !== $value) {
                $dateArray = explode('-', $value);
                $yearValue = $dateArray[0];
                $monthValue = $dateArray[1];
                $dayValue = $dateArray[2];
                if (!(ctype_digit($yearValue) && 4 == strlen($yearValue)) || !checkdate($monthValue, $dayValue, $yearValue)) {
                    $parameters = [];
                    $parameters[] = $field->getName();
                    $errors->addError('_MD_XOONIPS_ERROR_DATE', $fieldName, $parameters);
                }
            }
            if ($field->getLen() > 0 && strlen($value) > $field->getLen()) {
                $parameters = [];
                $parameters[] = $field->getName();
                $parameters[] = $field->getLen();
                $errors->addError('_MD_XOONIPS_ERROR_MAXLENGTH', $fieldName, $parameters);
            }
        }
    }

    public function editCheck(&$errors, $field, $value, $fieldName, $uid)
    {
        $this->inputCheck($errors, $field, $value, $fieldName);
    }

    public function doRegistry($field, &$data, &$sqlStrings, $groupLoopId)
    {
        $tableName = $field->getTableName();
        $columnName = $field->getColumnName();
        //get data
        $value = $this->getData($field, $data, $groupLoopId);
        $tableData;
        $groupData;
        $columnData;

        if (isset($sqlStrings[$tableName])) {
            $tableData = &$sqlStrings[$tableName];
        } else {
            $tableData = [];
            $sqlStrings[$tableName] = &$tableData;
        }

        if (false !== strpos($tableName, '_extend')) {
            $groupid = $field->getFieldGroupId();
            if (isset($tableData[$groupid])) {
                $groupData = &$tableData[$groupid];
            } else {
                $groupData = [];
                $tableData[$groupid] = &$groupData;
            }

            if (isset($groupData[$columnName])) {
                $columnData = &$groupData[$columnName];
            } else {
                $columnData = [];
                $groupData[$columnName] = &$columnData;
            }
        } else {
            if (isset($tableData[$columnName])) {
                $columnData = &$tableData[$columnName];
            } else {
                $columnData = [];
                $tableData[$columnName] = &$columnData;
            }
        }

        //set value into array
        $columnData[] = $field->getDataType()->convertSQLStr($this->convertTime($value));
    }

    private function convertTime($str)
    {
        $ret = '';
        if (10 == strlen($str)) {
            $int_year = intval(substr($str, 0, 4));
            $int_month = intval(substr($str, 5, 2));
            $int_day = intval(substr($str, 8, 2));
            $ret = mktime(0, 0, 0, $int_month, $int_day, $int_year);
        }

        return $ret;
    }

    /**
     * get datetime of user timezone.
     *
     * @return int
     */
    protected function getTimeZoneOffset()
    {
        $uid = XoopsUtils::getUid();
        $server_TZ = XoopsUtils::getXoopsConfig('server_TZ');
        $default_TZ = XoopsUtils::getXoopsConfig('default_TZ');
        if (XoopsUtils::UID_GUEST != $uid) {
            $userbean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
            $userInfo = $userbean->getUserBasicInfo($uid);

            return ($userInfo['timezone_offset'] - $server_TZ) * 3600;
        } else {
            return ($default_TZ - $server_TZ) * 3600;
        }
    }

    public function isDate()
    {
        return true;
    }
}
