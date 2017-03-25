<?php

use Xoonips\Core\FileUtils;
use Xoonips\Core\StringUtils;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/File.class.php';

abstract class XmlItemImportUpdate_Base
{
    protected $dirname;
    protected $trustDirname;
    protected $uid;
    protected $err_msg;
    protected $err_code;
    protected $line_no;
    protected $transaction;
    protected $tmp_file_arr;
    protected $create_item_id;  // create_xoonips_item method get new item_id

  protected $item_title_bean;
    protected $item_keyword_bean;
    protected $item_extend_bean;
    protected $related_to_bean;
    protected $item_link_bean;
    protected $item_file_bean;
    protected $index_change_log_bean;
    protected $item_user_link_bean;
    protected $item_virtual_bean;
    protected $item_bean;
    protected $item_group_bean;
    protected $index_bean;
    protected $users_bean;

    const itemns = 'http://xoonips.sourceforge.jp/xoonips/item/';
    public function __construct()
    {
        $this->dirname = $this->trustDirname = 'xoonips';
        $this->err_code = 200;
        $this->line_no = 0;
        $this->transaction = Xoonips_Transaction::getInstance();
        $this->create_item_id = -1;
        $this->new_indexes = array();

        $this->item_title_bean = Xoonips_BeanFactory::getBean('ItemTitleBean',
                                                    $this->dirname,
                                                    $this->trustDirname);
        $this->item_keyword_bean = Xoonips_BeanFactory::getBean('ItemKeywordBean',
                                                      $this->dirname,
                                                      $this->trustDirname);
        $this->item_extend_bean = Xoonips_BeanFactory::getBean('ItemExtendBean',
                                                      $this->dirname,
                                                      $this->trustDirname);
        $this->related_to_bean = Xoonips_BeanFactory::getBean('ItemRelatedToBean',
                                                      $this->dirname,
                                                      $this->trustDirname);
        $this->item_link_bean = Xoonips_BeanFactory::getBean('IndexItemLinkBean',
                                                      $this->dirname,
                                                      $this->trustDirname);
        $this->item_file_bean = Xoonips_BeanFactory::getBean('ItemFileBean',
                                                      $this->dirname,
                                                      $this->trustDirname);
        $this->index_change_log_bean = Xoonips_BeanFactory::getBean('ItemChangeLogBean',
                                                      $this->dirname,
                                                      $this->trustDirname);
        $this->item_user_link_bean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean',
                                                      $this->dirname,
                                                      $this->trustDirname);
        $this->item_virtual_bean = Xoonips_BeanFactory::getBean('ItemVirtualBean',
                                                      $this->dirname,
                                                      $this->trustDirname);
        $this->item_bean = Xoonips_BeanFactory::getBean('ItemBean',
                                                $this->dirname,
                                                $this->trustDirname);
        $this->item_group_bean = Xoonips_BeanFactory::getBean('ItemFieldGroupBean',
                                                $this->dirname,
                                                $this->trustDirname);
        $this->index_bean = Xoonips_BeanFactory::getBean('IndexBean',
                                                $this->dirname,
                                                $this->trustDirname);
        $this->users_bean = Xoonips_BeanFactory::getBean('UsersBean',
                                                $this->dirname);
    }

    public function get_err_msg()
    {
        return $this->err_msg;
    }

    public function get_err_code()
    {
        return $this->err_code;
    }

    public function get_line_no()
    {
        return $this->line_no;
    }

    protected function set_err_msg($err_code, $msg, $line_no)
    {
        $this->err_msg = $msg;
        $this->err_code = $err_code;
        $this->line_no = $line_no;

        return $err_code;
    }

    public function get_create_item_id()
    {
        return $this->create_item_id;
    }

  /**
   * You have to set following array.
   *
   * $file_arr[original_filename in xoonips_item_file field] = temp fullpath;
   *
   * @param type $file_arr
   */
  public function set_tmp_file_array(&$file_arr)
  {
      $this->tmp_file_arr = $file_arr;
  }

  /**
   * Convert from UTF-8 to Other char code.
   *
   * @param string $str
   *
   * @return string
   */
  protected function convert_str($str)
  {
      return StringUtils::convertEncoding($str, 'UTF-8', _CHARSET, 'h');
  }
  ////////////////////////////////////////////////////////
  /**
   * @param int $xml_file_id
   * @param int $file_id
   * @param int  $item_id
   *
   * @return int 1:Success,
   */
  private function fileUpload($xml_file_id, $file_id, $item_id)
  {
      if (!empty($this->tmp_file_arr[$xml_file_id])) {
          $tmp_filename = $this->tmp_file_arr[$xml_file_id];
          $uploadDir = Xoonips_Utils::getXooNIpsConfig($this->dirname, 'upload_dir').'/item/'.$item_id.'/';
          if (file_exists($uploadDir) == false) {
              mkdir($uploadDir, 0757, true);
          }
          $uploadfile = $uploadDir.$file_id;
          if (copy($tmp_filename, $uploadfile)) {
              return true;
          }
      }

      return false;
  }

   ////////////////////////////////////////////////////////
  /**
   * abstruct function for xoonips_item_extend.
   *
   * @param int $item_id
   * @param int $group_id
   * @param string  $tableName
   * @param mixed   $value
   * @param int $occurence_number
   *
   * @return  bool true:success,false:failed
   */
  abstract protected function db_extend($item_id, $group_id, $tableName, $value, $occurence_number);

  /**
   * get group_id from group xml.
   *
   * @param type $xml group_xml
   *
   * @return exist:group_id, not exist:null
   */
  private function get_group_id_by_xml($xml)
  {
      $group = $this->item_group_bean->getGroupByXml($xml);
      if (isset($group['group_id'])) {
          return $group['group_id'];
      }

      return null;
  }

  /**
   * store xoonips_item_extend's.
   *
   * @param array $item_group_arr
   * @param array $xml_item_detail
   * @param int $item_id
   */
  protected function extend(&$item_group_arr, &$xml_item_detail, $item_id)
  {
      $group_id = $this->get_group_id_by_xml($item_group_arr['group_tag_name']);
      if (is_null($group_id)) {
          return $this->set_err_msg(400, 'Incorrect group tag name: '.$item_group_arr['group_tag_name'].'.', __LINE__);
      }

      $item_field_detail_bean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean',
                                                      $this->dirname,
                                                      $this->trustDirname);
      $item_field_detail_ids = $this->item_group_bean->getDetailIdbyXml($item_group_arr['group_tag_name']);
      $item_field_details = array();
    // Get item_field_detail info
    foreach ($item_field_detail_ids as $item_field_detail_id) {
        $item_field_details[] = $item_field_detail_bean->getItemTypeDetailById($item_field_detail_id);
    }

    // Compare xml
    foreach ($item_field_details as $db_detail) {
        if (strcmp($db_detail['xml'], $xml_item_detail['item_tag_name']) == 0) {
            $occurence_number = $xml_item_detail['attribute']['occurrence_number'];
            $rc = true;
            if (strcmp($db_detail['table_name'], 'xoonips_item_extend95') == 0) {
                $rc = $this->db_extend2($item_id,
                    $group_id,
                    $db_detail['table_name'],
                    $occurence_number);
            } else {
                $val = $this->convert_str($xml_item_detail['value']);
                $rc = $this->db_extend($item_id,
                    $group_id,
                    $db_detail['table_name'],
                    Xoonips_Utils::convertSQLStr($val),
                    $occurence_number);
            }
            if ($rc === false) {
                return $this->set_err_msg(400, "To insert or update extend fail. item_id=${item_id},table_name=".$db_detail['table_name'].'value='.$xml_item_detail['value'], __LINE__);
            }
        }
    }

      return 200;
  }

  /**
   * abstruct method db_keyword.
   *
   * @param array $keyword_arr
   *
   * @return bool
   */
  abstract protected function db_keyword(&$keyword_arr);

    protected function keyword(&$item_group_arr, &$item_detail, $item_id)
    {
        $keyword = $item_detail['child_xml_obj'];
        $keyword_id = $this->convert_str($item_detail['attribute']['keyword_id']);
        $keyword_arr = array(
      'item_id' => $item_id,
      'keyword' => "${keyword}",
      'keyword_id' => $keyword_id,
    );
        $rc = $this->db_keyword($keyword_arr);
        if ($rc === false) {
            return $this->set_err_msg(400, "To insert key fail.item_id=${item_id},keyword=${keyword},keyword_id=${keyword_id}", __LINE__);
        }

        return 200;
    }

  /**
   * abstruct function.
   *
   * @param int $item_id
   * @param int $item_field_detail_id
   * @param string  $title_value
   * @param int     $title_id
   *
   * @return bool true:Success,false:Fail
   */
  abstract protected function db_title($item_id,
                                        $item_field_detail_id,
                                        $title_value,
                                        $title_id);

  /**
   * Import title.
   *
   * @param array $item_group_arr
   * @param array $item_detail
   * @param int $item_id
   */
  protected function title(&$item_group_arr, &$item_detail, $item_id)
  {
      $ret = $this->item_group_bean->getDetailIdbyXml($item_group_arr['group_tag_name']);
      $item_field_detail_id = $ret[0];
      $title_value = $item_detail['child_xml_obj'];
      $title_id = $item_detail['attribute']['title_id'];
      $rc = $this->db_title($item_id,
                          $item_field_detail_id,
                          $this->convert_str($title_value),
                          $title_id);
      if ($rc === false) {
          return $this->set_err_msg(400, "Insert or update title fail title_value=${title_value},title_id=${title_id},item_field_detail_id=${item_field_detail_id}.", __LINE__);
      }

      return 200;
  }

  /**
   * abstruct create file array.
   *
   * @param int $item_id
   * @param int $file_id
   * @param int $item_field_detail_id
   * @param int $group_id
   *
   * @return array
   */
  abstract protected function create_file_array($item_id, $file_id, $item_field_detail_id, $group_id);

  /**
   * abstruct uploadfile info.
   *
   * @param int $file_id
   * @param array   $file
   *
   * @return bool true:Success,false:Fail
   */
  abstract protected function db_UploadFile(&$file_id, $file);

  /**
   * $file check.
   *
   * @param array $file
   * @param string $illegalname if error occured key name substute this variable
   * @param int $file_id
   *
   * @return booelan true:Success,false:Fail
   */
  abstract protected function check_file_array(&$file, &$illegalname, $file_id);

  /**
   * attach_file upload?
   *
   * @param string $file_id
   *
   * @return bool true:Success,false:Fail
   */
  abstract protected function is_attach_file($file_id);

  /**
   * Exist Attach file?
   *
   * @param int $file_id
   *
   * @return bool true:Success,false:Fail
   */
  private function exist_attach_file($file_id)
  {
      if (isset($this->tmp_file_arr[$file_id])) {
          return true;
      }

      return false;
  }

  /**
   * Import file.
   *
   * @param array $item_group_arr
   * @param array $xml_item_detail
   * @param int $item_id
   */
  protected function file(&$item_group_arr, &$xml_item_detail, $item_id)
  {
      $group_id = $this->get_group_id_by_xml($item_group_arr['group_tag_name']);
      if (is_null($group_id)) {
          return $this->set_err_msg(400, 'Incorrect group tag name: '.$item_group_arr['group_tag_name'].'.', __LINE__);
      }
      $item_field_detail_bean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean',
                                                      $this->dirname,
                                                      $this->trustDirname);
      $item_field_details = array();
    // Get item_field_detail info
    $item_field_detail_id_arr = $this->item_group_bean->getDetailIdbyXml($item_group_arr['group_tag_name']);
      foreach ($item_field_detail_id_arr as $item_field_detail_id) {
          $db_item_field_detail = $item_field_detail_bean->getItemTypeDetailById($item_field_detail_id);
          if ($db_item_field_detail === false) {
              return $this->set_err_msg(400, "item_field_detail_id = ${$item_field_detail_id} not exist.", __LINE__);
          }

      // Compare xml
      if (strcmp($db_item_field_detail['xml'], $xml_item_detail['item_tag_name']) == 0) {
          $xml_file_id = intval($xml_item_detail['child_xml_obj']->children(self::itemns)->file_id);
          $file = $this->create_file_array($item_id, $xml_file_id,
                          $db_item_field_detail['item_field_detail_id'], $group_id);
          if (empty($file)) {
              return $this->set_err_msg(400, 'file_id does not exist or xoonips_item_file in xml.'.print_r($file, true)."item_id=${item_id}", __LINE__);
          }
          foreach ($xml_item_detail['child_xml_obj']->children(self::itemns) as $k => $v) {
              $file[$k] = $this->convert_str("${v}");
          }
          if ($this->check_file_array($file, $illegalname, $xml_file_id) == false) {
              return $this->set_err_msg(400, 'XML file tag does not set '.$illegalname.' '.print_r($file, true), __LINE__);
          }
          if ($this->is_attach_file($xml_file_id) == false) {
              return $this->set_err_msg(400, 'Attach file does not exist.'.print_r($file, true), __LINE__);
          }
          $file_id = $xml_file_id;
          $rc = $this->db_UploadFile($file_id, $file);
          if ($rc === false) {
              return $this->set_err_msg(400, 'To insert file info fail.file ='.print_r($file, true), __LINE__);
          }
          if ($this->exist_attach_file($xml_file_id) &&
          $this->fileUpload($xml_file_id, $file_id, $item_id) == false) {
              return $this->set_err_msg(500, 'File upload fail ='.print_r($file, true), __LINE__);
          }
      }
      }

      return 200;
  }

  /**
   * abstruct xoonips_index_item_link db function.
   *
   * @param int $indexId
   * @param int $item_id
   * @param int $certify_state  if = 0 then not change
   * @param int $item_link_id
   *
   * @return  bool true:success,false:failed
   */
  abstract protected function db_index_item_link($indexId, $item_id, $certify_state, &$item_link_id);

  /**
   * Import index_item_link.
   *
   * @param array $item_group_arr
   * @param array $item_detail
   * @param int $item_id
   *
   * @return int 200:Success,Other:Fail
   */
  protected function index_item_link(&$item_group_arr, &$item_detail, $item_id)
  {
      $index_bean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname,
                                                      $this->trustDirname);
      $indexId = 0;
      $certify_state = 0;
      $item_link_id = 0;
      $index_title = '';

      foreach ($item_detail['child_xml_obj']->children(self::itemns) as $k => $v) {
          if (strcmp($k, 'index_id') == 0) {
              $indexId = "${v}";
          } elseif (strcmp($k, 'index_item_link_id') == 0) {
              $item_link_id = "${v}";
          } elseif (strcmp($k, 'certify_state') == 0) {
              $certify_state = "${v}";
          } elseif (strcmp($k, 'index_title') == 0) {
              $index_title = "${v}";
          }
      }
    /*
    $index_ids = $this->correct_my_accesible_indexes();
    $index_found = false;
    foreach ($index_ids as $index_id) {
      if($index_id == $indexId){
        $index_found = true;
        break;
      }
    }
    if($index_found == false){
      return $this->set_err_msg(403, "index_id ${indexId} cannot access.",__LINE__);
    }
     *
     */

    if ($index_title == null || $index_title == '') {
        return $this->set_err_msg(400, "Attribute type index_title don't specify.", __LINE__);
    }
      if (strpos($index_title, '/') !== 0 || strlen($index_title) == 1) {
          return $this->set_err_msg(400, 'Attribute type index_title must start with /', __LINE__);
      }

      $indexes_org = explode('/', $index_title);
      $root_index = 0;
      $isManager = false;
      $isModerator = false;
      if (strcmp($indexes_org[1], 'Private') == 0) {
          $indexType = 0; //Private
      $root_index = $index_bean->getPrivateIndex($this->uid);
      } else {
          $users_bean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
          $isModerator = $users_bean->isModerator($this->uid);
          $certify_state = 1;
          if (strcmp($indexes_org[1], 'Public') == 0) {
              $indexType = 1; //Public
        $root_index = $index_bean->getPublicIndex();
          } else {
              $indexType = 2; //Group
        $groupName = $indexes_org[1];
              $groupBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->dirname);
              $group = $groupBean->getGroupByName($indexes_org[1]);
              if (!empty($group) && $group['index_id'] != 0) { // group exist
          if (empty($isModerator)) {
              $isManager = $users_bean->isGroupManager($group['groupid'], $this->uid);
              $isMember = $users_bean->isGroupMember($group['groupid'], $this->uid);
              if (empty($isManager) && empty($isMember)) {
                  return $this->set_err_msg(400, 'Do not have permission to access '.$indexes_org[1].' group', __LINE__);
              }
          }
                  $root_index = $index_bean->getIndex($group['index_id']);
              } else {
                  return $this->set_err_msg(400, 'Group name '.$indexes_org[1].' not exist', __LINE__);
              }
          }
      }
      if (sizeof($indexes_org) != 2) {
          $indexes_slice = array_slice($indexes_org, 2);
          $indexId = $index_bean->getIndexID($indexes_slice, $root_index, $indexType, $isManager, $isModerator);
      } else {
          $indexId = $root_index['index_id'];
      }
    // Correct Index?
/*     $indexes = $index_bean->getIndex("${indexId}");
    if($indexes === false){
      if($indexId==0){
        return $this->set_err_msg(400, "Attribute type index_item_link don't specify.",__LINE__);
      }else{
        return $this->set_err_msg(400, "Attribute type index_item_link=${indexId} not found.",__LINE__);
      }
    } */

    if (empty($indexId)) {
        return $this->set_err_msg(400, "Attribute type index_title=${index_title} can not create.", __LINE__);
    }

    // Correct certify_state?
    if (0 > $certify_state || $certify_state > 3) {
        return $this->set_err_msg(400, 'Parameter certify_state on xml incorrect.', __LINE__);
    }

      $rc = $this->db_index_item_link($indexId, $item_id, $certify_state, $item_link_id);
      if ($rc == false) {
          return $this->set_err_msg(403, "Illegal item_link parameter in xml.indexId=${indexId},item_id=${item_id},item_link_id=${item_link_id}", __LINE__);
      }

      if ($indexType == 1) { //Public
      $dataname = Xoonips_Enum::WORKFLOW_PUBLIC_ITEMS;
          $indexItemLinkInfo = $this->item_link_bean->getInfo($item_id, $indexId);
          $indexItemLinkId = $indexItemLinkInfo['index_item_link_id'];
          $certifyName = $this->item_title_bean->getItemTitle($item_id);
          $url = XOOPS_MODULE_URL.'/'.$this->dirname.'/detail.php?item_id='.$item_id;
          $eventLogBean = Xoonips_BeanFactory::getBean('EventLogBean', $this->dirname, $this->trustDirname);
          if (Xoonips_Workflow::addItem($certifyName, $this->dirname, $dataname, $indexItemLinkId, $url)) {
              // success to register workflow task
        $eventLogBean->recordRequestCertifyItemEvent($item_id, $indexId);
          } else {
              // workflow not available - force certify automaticaly
        if (!$this->item_link_bean->update($indexId, $item_id, XOONIPS_CERTIFIED)) {
            return false;
        }
              $eventLogBean->recordRequestCertifyItemEvent($item_id, $indexId);
              $eventLogBean->recordCertifyItemEvent($item_id, $indexId);
          }
      } elseif ($indexType == 2) { //Group
      $dataname = Xoonips_Enum::WORKFLOW_GROUP_ITEMS;
          $certifyName = $groupName.':'.$this->item_title_bean->getItemTitle($item_id);
          $indexItemLinkInfo = $this->item_link_bean->getInfo($item_id, $indexId);
          $indexItemLinkId = $indexItemLinkInfo['index_item_link_id'];
          $url = XOOPS_MODULE_URL.'/'.$this->dirname.'/detail.php?item_id='.$item_id;
          $eventLogBean = Xoonips_BeanFactory::getBean('EventLogBean', $this->dirname, $this->trustDirname);
          if (Xoonips_Workflow::addItem($certifyName, $this->dirname, $dataname, $indexItemLinkId, $url)) {
              // success to register workflow task
        $eventLogBean->recordRequestGroupItemEvent($item_id, $indexId);
          } else {
              // workflow not available - force certify automaticaly
        if (!$this->item_link_bean->update($indexId, $item_id, XOONIPS_CERTIFIED)) {
            return false;
        }
              $eventLogBean->recordRequestGroupItemEvent($item_id, $indexId);
              $eventLogBean->recordCertifyGroupItemEvent($indexId, $item_id);
          }
      }

      return 200;
  }

  /**
   * abstruct xoonips_item_changelog manuplate function.
   *
   * @param array $changelog
   *
   * @return bool true:success,false:failed
   */
  abstract protected function db_changelog(&$changelog);

  /**
   * Abstruc create changelog array.
   *
   * @param int $log_id
   *
   * @return array changelog array
   */
  abstract protected function create_changelog_array($log_id);

  /**
   * Import changelog.
   *
   * @param type $item_group_arr
   * @param type $item_detail
   * @param type $item_id
   *
   * @return type
   */
  protected function changelog(&$item_group_arr, &$item_detail, $item_id)
  {
      foreach ($item_detail['child_xml_obj']->children(self::itemns)->log as $log_info) {
          $log_id = $log_info->log_id;
          $changelog = $this->create_changelog_array($log_id);
          $changelog['item_id'] = $item_id;
          $log = '';
          foreach ($log_info as $k => $v) {
              if (strcmp($k, 'log') == 0) {
                  $changelog['log'] = "${v}";
              } elseif (strcmp($k, 'log_date') == 0) {
                  $changelog['log_date'] = "${v}";
              } elseif (strcmp($k, 'uid') == 0) {
                  $changelog['uid'] = "${v}";
              }
          }
          $rc = $this->db_changelog($changelog);
          if ($rc == false) {
              return $this->set_err_msg(400, 'To insert changelog fail.file ='.print_r($changelog, true), __LINE__);
          }
      }

      return 200;
  }

  /**
   * abstrauct xoonips_item_related_to.
   *
   * @param array $related
   *
   * @return boolaen true:Sucess,false:Fail
   */
  abstract protected function db_related($related);

  /**
   * uid based to get accessibe index_id.
   *   This function use index and related_to.
   *
   * @return array index_ids
   */
  private function correct_my_accesible_indexes()
  {
      $index_ids = array();
      $grp_idx = $this->index_bean->getGroupIndexes($this->uid);
      foreach ($grp_idx as $idx) {
          $index_ids[] = $idx['index_id'];
      }

      $pub_idx = $this->index_bean->getPublicIndexes();
      foreach ($pub_idx as $idx) {
          $index_ids[] = $idx['index_id'];
      }

      $pri_idx = $this->index_bean->getPrivateIndexes($this->uid);
      foreach ($pri_idx as $idx) {
          $index_ids[] = $idx['index_id'];
      }

      return $index_ids;
  }

  /**
   * This Item Accessable?
   *
   * @param type $item_id
   *
   * @return type
   */
  private function can_get_item_id($item_id)
  {
      $index_ids = $this->correct_my_accesible_indexes();
      $item_ids = array();
      foreach ($index_ids as $index_id) {
          $item_ids = array_merge($this->index_bean->getCanViewItemIds($index_id, $this->uid), $item_ids);
      }

    // Search item_id
    foreach ($item_ids as $item) {
        if ($item == $item_id) {
            return true;
        }
    }

      return false;
  }

  /**
   * Insert or Update xoonips_item_related_to.
   *
   * @param type $item_group_arr
   * @param type $item_detail
   * @param type $item_id
   *
   * @return type
   */
  protected function related_to(&$item_group_arr, &$item_detail, $item_id)
  {
      $related = array('item_id' => $item_id,
        'child_item_id' => $item_detail['value'],
    );
      if (isset($item_detail['attribute']['original_related_to'])) {
          $related['related_to'] = $item_detail['attribute']['original_related_to'];
      }
      if ($this->users_bean->isModerator($this->uid) == false && $this->can_get_item_id($item_detail['value']) == false) {
          return $this->set_err_msg(403, 'To insert or update related_to fail.value ='.print_r($related, true), __LINE__);
      }
      $cnt = count($related);
      if ($cnt == 2 || $cnt == 3) {
          $rc = $this->db_related($related);
          if ($rc == false) {
              return $this->set_err_msg(400, 'To insert or update related_to fail.value ='.print_r($related, true), __LINE__);
          }

          return 200;
      }

      return $this->set_err_msg(400, 'Parameter child_item_id not found.', __LINE__);
  }

  /**
   * Insert or Update users_link.
   *
   * @param array $item_group_arr
   * @param int $item_detail
   * @param int $item_id
   *
   * @return int
   */
  abstract protected function users_link(&$item_group_arr, &$item_detail, $item_id);

  ////////////////////////////////////////////////////////
  /**
   * check xml exist in xoonips_item_field_detail.
   *
   * @param type $xml_tag tagname
   * @param type $item_details
   *
   * @return bool true/false
   */
  private function exist_detail_xml($xml_tag, &$item_details)
  {
      foreach ($item_details as $item_detail) {
          if (strcmp($item_detail['xml'], $xml_tag) == 0) {
              return true;
          }
      }

      return false;
  }

  /**
   * dispatch each method by type attribute on xml.
   *
   * @param type $groups_sxml
   * @param type $item_id
   *
   * @return 200 or 206:OK , other:fail
   */
  private function dispatch(&$groups_sxml, $item_id)
  {
      $detail_item_id_arr = $this->item_bean->getItemTypeDetails($item_id);
      foreach ($groups_sxml as $item_sxml) {
          $item_details = $this->get_item_info($item_sxml);
          if (empty($item_details)) {
              continue;
          }
          foreach ($item_details as $item_detail) {
              if (empty($item_detail)) {
                  continue;
              }
              if ($this->exist_detail_xml($item_detail['item_tag_name'], $detail_item_id_arr) == false) {
                  return $this->set_err_msg(400, "tag ${item_detail['item_tag_name']} illegal.xoonips_item_field_detail don't exist.", __LINE__);
              }
              $type = $item_detail['attribute']['type'];
              if (method_exists($this, $type)) {
                  $item_group_arr = $this->get_group_info($item_sxml);
                  if (empty($item_group_arr)) {
                      return $this->set_err_msg(400, "type='group' attribute don't exist.", __LINE__);
                  }
                  $rc = $this->$type($item_group_arr, $item_detail, $item_id);
                  switch ($rc) {
            case 200:
              break;
            case 206:
              break;
            default:
              return $rc;
          }
              }
          }
      }

      return 200;
  }

  /**
   * item array generate.
   *
   * @param type $item_id
   * @param type $item_type_id
   *
   * @return array read or create array
   */
  abstract protected function create_xoonips_item_array(&$item_id, $item_type_id);

  /**
   * Create xoonips_item.
   *
   * @param type $item_column
   * @param type $item_id
   *
   * @return bool true:Success,false:Fail
   */
  abstract protected function db_xoonips_item(&$item_column, &$item_id);

  /**
   * is_doi?exist doi?
   *
   * @param type $doi
   *
   * @return bool true:Success,false:Fail
   */
  abstract protected function is_doi($doi);

  /**
   * create xoonips_item.
   *
   * @param type $groups_sxml
   * @param type $item_type_id
   * @param type $item_id
   *
   * @return int 1:Success,-1:doi duplicate,-2:insert fail,-3:no item
   */
  private function create_xoonips_item(&$groups_sxml, $item_type_id, &$item_id)
  {
      $db_item_info = $this->create_xoonips_item_array($item_id, $item_type_id);
      if (is_bool($db_item_info) && $db_item_info === false) {
          return -3;
      }
      foreach ($groups_sxml as $group_dom_ele) {
          $xml_item_infos = $this->get_item_info($group_dom_ele);
          foreach ($xml_item_infos as $xml_item_info) {
              if (strcmp($xml_item_info['attribute']['type'], 'item') == 0) {
                  if (!empty($xml_item_info['value'])) {
                      if (strcmp($xml_item_info['attribute']['column_name'], 'doi') == 0 &&
                   $this->is_doi($xml_item_info['value']) == false) {
                          return -1;
                      }
                      $db_item_info[$xml_item_info['attribute']['column_name']] = $xml_item_info['value'];
                  }
              }
          }
      }

      if ($this->db_xoonips_item($db_item_info, $item_id) == false) {
          return -2;
      }

      return 1;
  }

  /**
   * check group and get info.
   *
   * array('group_tag_name' => xml in item_field_group.xml
   *       'item_field_detail_id' => item_field_group_field_detail_link.item_field_detail_id
   *       'attribute'=>XML's Attribute by Group
   *
   * @param type $group_dom
   *
   * @return type
   */
  private function get_group_info(&$group_dom)
  {
      // Does groups correct?
    $ret = array();
      $ret['group_tag_name'] = $group_dom->getName();
      foreach ($group_dom->attributes(self::itemns) as $att => $atval) {
          if (strcmp($att, 'type') != 0 || strcmp($atval, 'group') != 0) {
              continue;
          }
          $ret['attribute'][$att] = "${atval}";
      }
      if (empty($ret['attribute']['type'])) {
          return array();
      }

      return $ret;
  }

  /**
   * Get Child info,then set to array.
   *
   * Array
   * (
   * [tag_name] => tag_name
   * [attribute] => Array
   *     (
   *     )
   * [value]
   * )
   *
   * @param object $group_val
   *
   * @return array
   */
  private function get_item_info(&$group_val)
  {
      $child = $group_val->children(self::itemns);
      $ret = array();
      foreach ($child as $c) {
          $ret_item = array();
          $ret_item['item_tag_name'] = $c->getName();
          foreach ($c->attributes(self::itemns)  as $catt => $cval) {
              $ret_item['attribute'][$catt] = "${cval}";
          }
          $ret_item['value'] = "${c}";
          $ret_item['child_xml_obj'] = $c;
          $ret[] = $ret_item;
      }

      return $ret;
  }

  /**
   * Get ItemTypeName and item_id on a xml tree.
   *
   * @param object $sxml
   * @param type $sxml
   * @param type $item_id
   *
   * @return type
   */
  private function get_Item_type_name_item_id(&$sxml, &$item_id)
  {
      $ret = null;
      foreach ($sxml->attributes(self::itemns) as $key => $value) {
          if (strcmp('item_type_name', $key) == 0) {
              $ret = strval($value);
          } elseif (strcmp('item_id', $key) == 0) {
              $item_id = strval($value);
          }
      }

      return $ret;
  }

  /**
   * Get User ID on a xml tree.
   *
   * @param object $groups_sxml
   *
   * @return array $users
   */
  private function get_users_from_xml($groups_sxml)
  {
      $users = array();
      foreach ($groups_sxml as $group_dom_ele) {
          $xml_item_infos = $this->get_item_info($group_dom_ele);
          foreach ($xml_item_infos as $xml_item_info) {
              if ($xml_item_info['item_tag_name'] == 'contributor') {
                  foreach ($xml_item_info['child_xml_obj']->children(self::itemns) as $k => $v) {
                      if ($k == 'uname') {
                          $users[] = $this->convert_str("${v}");
                      }
                  }
              }
          }
      }

      return $users;
  }

  /**
   * Get Index ID on a xml tree.
   *
   * @param object $groups_sxml
   *
   * @return array $indexes
   */
  private function get_indexes_from_xml($groups_sxml)
  {
      $indexes = array();
      foreach ($groups_sxml as $group_dom_ele) {
          $xml_item_infos = $this->get_item_info($group_dom_ele);
          foreach ($xml_item_infos as $xml_item_info) {
              if ($xml_item_info['item_tag_name'] == 'index') {
                  foreach ($xml_item_info['child_xml_obj']->children(self::itemns) as $k => $v) {
                      if ($k == 'index_title') {
                          $indexes[] = $this->convert_str("${v}");
                      }
                  }
              }
          }
      }

      return $indexes;
  }

  /**
   * Get Item_type_id.
   *
   * @param string $item_type_name
   *
   * @return >0 Success,-1 Fail
   */
  private function get_item_type_id($item_type_name)
  {
      $item_type_bean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
      $item_type = $item_type_bean->getItemTypeByName($item_type_name);
      if (count($item_type) > 0 && $item_type['released'] == 1) {
          return $item_type['item_type_id'];
      } else {
          return -1;
      }
  }

  /**
   * Create row at item_users_link.
   *
   * @param int $item_id
   * @param int $uid
   *
   * @return bool true:Success,false:Fail
   */
  private function set_user_link($item_id, $uid)
  {
      $user_link_bean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
      $weight = $user_link_bean->getMaxWeight($item_id);
      $info = array(
      'weight' => $weight + 1,
      'item_id' => $item_id,
      'uid' => $uid,
    );

      return $user_link_bean->insert($info);
  }

    public function uid($uid)
    {
        $this->uid = $uid;
    }

  /**
   * Post proc.
   *
   * @param int $item_id
   *
   * @return true :Success,false:fail
   */
  abstract protected function do_set_user_link($item_id);

  /**
   * check get item_id from DB
   * - Use only update.
   *
   * @param int $item_id
   *
   * @return true :exist,false:no exist
   */
  abstract protected function is_set_xml_item_id($item_id);

  /**
   * XML from file
   * This mainly use REST API.
   *
   * @param string $fname
   * @param int $uid_chk
   * @param array $index_chk
   * @param bool $transact_flg
   *
   * @return int 200 or 206:Success,other return code:Fail
   */
  public function xml_import_by_file($fname, $uid_chk = null, $index_chk = null, $transact_flg = true, $defaultIndex = '')
  {
      try {
          libxml_use_internal_errors(true);
          $sxml = simplexml_load_file($fname);
          if ($sxml === false) {
              $xml_err = 'Maybe, this file is not a valid XML.';

              return $this->set_err_msg(400, $xml_err, __LINE__);
          }
          $sxml->registerXPathNamespace('namespace',  self::itemns);

      // get item_type_name and item_id from item_type_name attribute.
      $item_id = -1;
          $item_type_name = $this->get_Item_type_name_item_id($sxml, $item_id);
          if (is_null($item_type_name)) {
              return $this->set_err_msg(400, "item_type_name don't appear XML attribute.", __LINE__);
          }
          if ($this->is_set_xml_item_id($item_id) == false) {
              return $this->set_err_msg(400, "item_id don't exist DB.", __LINE__);
          }

      // Get All Group
      $groups_sxml = $sxml->xpath('//namespace:item/*');
          if ($groups_sxml !== false) {
              $item_type_id = $this->get_item_type_id($item_type_name);
              if ($item_type_id > 0) {
                  if ($transact_flg) {
                      $this->transaction->start();
                  }
          // Create xoonips_item
          $rc = $this->create_xoonips_item($groups_sxml, $item_type_id, $item_id);
                  if ($rc == -2) {
                      if ($transact_flg) {
                          $this->transaction->rollback();
                      }

                      return $this->set_err_msg(500, 'To create or update xoonips_item fail.', __LINE__);
                  } elseif ($rc == -1) {
                      if ($transact_flg) {
                          $this->transaction->rollback();
                      }

                      return $this->set_err_msg(400, 'doi duplicate', __LINE__);
                  } elseif ($rc == -3) {
                      if ($transact_flg) {
                          $this->transaction->rollback();
                      }

                      return $this->set_err_msg(400, 'Xoonips item info cannot get your xml item_id', __LINE__);
                  }
                  $this->create_item_id = $item_id;
                  $rc = $this->dispatch($groups_sxml, $item_id);
                  if ($rc == 200 || $rc == 206) {
                  } else {
                      if ($transact_flg) {
                          $this->transaction->rollback();
                      }

                      return $rc;
                  }

           // Set import user as contributor, ignore contributors setting in xml file
           if (!empty($defaultIndex)) {
               $this->set_user_link($item_id, $this->uid);
           }
                  if (!is_null($index_chk)) {
                      // Index base on XML
             $this->new_indexes = $this->get_indexes_from_xml($groups_sxml);
                      $hasPrivate = false;
                      foreach ($this->new_indexes as $index_path) {
                          if (strpos($index_path, '/Private') === 0) {
                              $hasPrivate = true;
                          }
                      }
                      if (empty($hasPrivate)) {
                          $privateIndex = $this->index_bean->getPrivateIndex($this->uid);
                          $indexes_org = explode('/', $defaultIndex);
                          $indexes_slice = array_slice($indexes_org, 2);
                          $index_id = $this->index_bean->getIndexID($indexes_slice, $privateIndex, 0, 0, 0);
                          $certify_state = 0;
                          $item_link_id = 0;
                          $rc = $this->db_index_item_link2($index_id, $item_id, $certify_state, $item_link_id);
                          if ($rc == false) {
                              return $this->set_err_msg(400, 'Cannot create private index', __LINE__);
                          }
                      }
                  }
                  if ($transact_flg) {
                      $this->transaction->commit();
                  }
              } else {
                  return $this->set_err_msg(400, 'Cannot get item_type_id.Do you specify correct item_type_name?', __LINE__);
              }
          } else {
              return $this->set_err_msg(400, self::itemns.' may not exist on xml.', __LINE__);
          }
      } catch (Exception $e) {
          if ($transact_flg) {
              $this->transaction->rollback();
          }

          return $this->set_err_msg(500, 'XML internal tree create error.', __LINE__);
      }

      return 200;
  }

    private function db_index_item_link2($indexId, $item_id, $certify_state, &$item_link_id)
    {
        return $this->item_link_bean->insert($indexId, $item_id, $certify_state, $item_link_id);
    }

  /**
   * Get Indexes.
   *
   * @return array $new_indexes
   */
  public function get_indexes()
  {
      return $this->new_indexes;
  }
}

////////////////////////////////////////////////////////////////////////////
class XmlItemImport extends XmlItemImportUpdate_Base
{
    public function __construct()
    {
        parent::__construct();
    }

  /**
   * Create changelog array.
   *
   * @param int $file_id
   *
   * @return array changelog array
   */
  protected function create_changelog_array($log_id)
  {
      return array(
      'item_id' => -1,
      'log_id' => $log_id,
      'log_date' => time(),
      'log' => null,
      'uid' => $this->uid,
    );
  }

  /**
   * xoonips_item_changelog manuplate function.
   *
   * @param array $changelog
   *
   * @return bool true:success,false:failed
   */
  protected function db_changelog(&$changelog)
  {
      return $this->index_change_log_bean->insert($changelog);
  }

  /**
   * abstruct create file array.
   *
   * @param int $item_id
   * @param int $file_id
   * @param int $item_field_detail_id
   * @param int $group_id
   *
   * @return array
   */
  protected function create_file_array($item_id, $file_id, $item_field_detail_id, $group_id)
  {
      return array(
          'file_id' => -1,
          'item_id' => $item_id,
          'group_id' => $group_id,
          'item_field_detail_id' => $this->convert_str($item_field_detail_id),
          'original_file_name' => null,
          'mime_type' => null,
          'file_size' => -1,
          'handle_name' => null,
          'caption' => null,
          'sess_id' => '',
          'search_module_name' => null,
          'search_module_version' => null,
          'timestamp' => time(),
          'download_count' => '',
            'occurence_number' => 1,
        );
  }

  /**
   * attach_file upload?
   *
   * @param int $file_id
   *
   * @return bool true:Success,false:Fail
   */
  protected function is_attach_file($file_id)
  {
      if (isset($this->tmp_file_arr[$file_id])) {
          return true;
      }

      return false;
  }

  /**
   * abstruct uploadfile info.
   *
   * @param int $file_id
   * @param array   $file
   *
   * @return bool true:Success,false:Fail
   */
  protected function db_UploadFile(&$file_id, $file)
  {
      return $this->item_file_bean->insertFileWithFileId($file, $file_id);
  }

  /**
   * xoonips_index_item_link db function.
   *
   * @param int $indexId
   * @param int $item_id
   * @param int $certify_state  if = 0 then not change
   * @param int $item_link_id
   *
   * @return  bool true:success,false:failed
   */
  protected function db_index_item_link($indexId, $item_id, $certify_state, &$item_link_id)
  {
      return $this->item_link_bean->insert($indexId, $item_id, $certify_state, $item_link_id);
  }

  /**
   * $file check.
   *
   * @param array $file
   * @param string $illegalname if error occured key name substute this variable
   * @param int $file_id
   *
   * @return booelan true:Success,false:Fail
   */
  protected function check_file_array(&$file, &$illegalname, $file_id)
  {
      $chk_arr = array(
     'original_file_name', 'mime_type', 'search_module_name', 'search_module_version',
    );
      foreach ($chk_arr as $key) {
          if (is_null($file[$key])) {
              $illegalname = $key;

              return false;
          }
      }
      if (preg_match('/^(file|http|https|ftp):\\/\\/(.+)$/', $file['original_file_name'], $matches)) {
          $uploadDir = Xoonips_Utils::getXooNIpsConfig($this->dirname, 'upload_dir');
          $fpath = '';
          if ($matches[1] == 'file') {
              // local file
         if (substr($matches[2], 0, 1) == '/') {
             // absolute path
            $fpath = $matches[2];
         } else {
             // relative path
            $fpath = $uploadDir.'/'.$matches[2];
         }
              $file['original_file_name'] = basename($matches[2]);
          } else {
              // TODO: download from internet
          }
          if (!file_exists($fpath)) {
              $illegalname = 'original_file_name';

              return false;
          }
          $this->tmp_file_arr[$file_id] = $fpath;
      }
      $file['mime_type'] = FileUtils::guessMimeType($this->tmp_file_arr[$file_id], $file['original_file_name']);
      $file['file_size'] = filesize($this->tmp_file_arr[$file_id]);

      return true;
  }

  /**
   * Post proc.
   *
   * @param int $item_id
   *
   * @return $rc equip value :Success,500:fail
   */
  protected function do_set_user_link($item_id)
  {
      $info = array(
        'item_id' => $item_id,
        'uid' => $this->uid,
        'weight' => 0,
    );

      return $this->item_user_link_bean->insert($info);
  }

  /**
   * Instance xoonips_item_related_to.
   *
   * @param array $related
   *
   * @return boolaen true:Sucess,false:Fail
   */
  protected function db_related($related)
  {
      return $this->related_to_bean->insert($related);
  }

  /**
   * function for xoonips_item_extend.
   *
   * @param int $item_id
   * @param int $group_id
   * @param string  $tableName
   * @param mixed   $value
   * @param int $occurence_number
   *
   * @return  bool true:success,false:failed
   */
  protected function db_extend($item_id, $group_id, $tableName, $value, $occurence_number)
  {
      return $this->item_extend_bean->insert($item_id, $tableName, $value, $occurence_number, $group_id);
  }

  /**
   * function for xoonips_item_extend2.
   *
   * @param int $item_id
   * @param int $group_id
   * @param string  $tableName
   * @param int $occurence_number
   *
   * @return  bool true:success,false:failed
   */
  protected function db_extend2($item_id, $group_id, $tableName, $occurence_number)
  {
      return $this->item_extend_bean->insert2($item_id, $tableName, $occurence_number, $group_id);
  }

  /**
   * abstruct method db_keyword.
   *
   * @param array $keyword_arr
   *
   * @return bool
   */
  protected function db_keyword(&$keyword_arr)
  {
      return $this->item_keyword_bean->insertKeyword($keyword_arr);
  }

   /**
    * abstruct function.
    *
    * @param int $item_id
    * @param int $item_field_detail_id
    * @param string  $title_value
    * @param int     $title_id
    *
    * @return bool true:Success,false:Fail
    */
   protected function db_title($item_id,
                                $item_field_detail_id,
                                $title_value,
                                $title_id)
   {
       return $this->item_title_bean->insertTitle($item_id,
                                        $item_field_detail_id,
                                        $title_value,
                                        $title_id);
   }

  /**
   * Create xoonips_item.
   *
   * @param type $item_column
   * @param type $item_id
   *
   * @return bool true:Success,false:Fail
   */
  protected function db_xoonips_item(&$item_column, &$item_id)
  {
      global $xoopsDB;
      $table = $xoopsDB->prefix($this->dirname.'_item');
      $doi = $this->convert_str($item_column['doi']);

      $sql = "INSERT INTO ${table} (".implode(',', array_keys($item_column)).') ';
      $sql .= 'VALUES(';
      $sql .= Xoonips_Utils::convertSQLNum($item_column['item_type_id']);
      $sql .= ','.Xoonips_Utils::convertSQLStr($doi);
      $sql .= ','.Xoonips_Utils::convertSQLNum($item_column['view_count']);
      $sql .= ','.Xoonips_Utils::convertSQLNum($item_column['last_update_date']);
      $sql .= ','.Xoonips_Utils::convertSQLNum($item_column['creation_date']);
      $sql .= ')';
      if (!$xoopsDB->queryF($sql)) {
          return false;
      } else {
          $item_id = $xoopsDB->getInsertId();
      }

      return true;
  }

   /**
    * is_doi?exist doi?
    *
    * @param type $doi
    *
    * @return bool true:Success,false:Fail
    */
   protected function is_doi($doi)
   {
       if (is_null($doi) == false) {
           $rc = $this->item_bean->getBydoi2($doi);
           if ($rc === false || count($rc) > 0) {
               return false;
           }
       }

       return true;
   }

  /**
   * Insert users_link.
   *
   * @param array $item_group_arr
   * @param int $item_detail
   * @param int $item_id
   *
   * @return int
   */
  protected function users_link(&$item_group_arr, &$item_detail, $item_id)
  {
      // Set import user as contributor, ignore contributors setting in xml file
    return 200;
  }

  /**
   * Create xoonips_item.
   *
   * @param type $item_column
   * @param type $item_id
   * @param type $item_type_id
   *
   * @return bool true:Success,false:Fail
   */
  protected function create_xoonips_item_array(&$item_id, $item_type_id)
  {
      return array(
      'item_type_id' => $item_type_id,
      'doi' => null,
      'view_count' => '0',
      'last_update_date' => time(),
      'creation_date' => time(),
    );
  }
  /**
   * check get item_id from DB
   * - Use only update.
   *
   * @param int $item_id
   *
   * @return true :exist,false:no exist
   */
  protected function is_set_xml_item_id($item_id)
  {
      return true;
  }
}

////////////////////////////////////////////////////////////////////////////
class XmlItemUpdate extends XmlItemImportUpdate_Base
{
    public function __construct()
    {
        parent::__construct();
    }

  /**
   * Create changelog array.
   *
   * @param int $file_id
   *
   * @return array changelog array
   */
  protected function create_changelog_array($log_id)
  {
      return $this->index_change_log_bean->getChangeLogInfo($log_id);
  }

  /**
   * xoonips_item_changelog manuplate function.
   *
   * @param array $changelog
   *
   * @return bool true:success,false:failed
   */
  protected function db_changelog(&$changelog)
  {
      if (array_key_exists('log_date', $changelog) == false) {
          $changelog['log_date'] = time();
      }
      if (array_key_exists('log', $changelog) == false) {
          $changelog['log'] = null;
      }

      if (array_key_exists('log_id', $changelog) == true) {
          if ($this->index_change_log_bean->getChangeLogInfo($changelog['log_id']) !== false) {
              return $this->index_change_log_bean->update($changelog);
          }
      }

      return $this->index_change_log_bean->insert($changelog);
  }

  /**
   * abstruct create file array.
   *
   * @param int $item_id
   * @param int $file_id
   * @param int $item_field_detail_id
   * @param int $group_id
   *
   * @return array
   */
  protected function create_file_array($item_id, $file_id, $item_field_detail_id, $group_id)
  {
      $item_files = $this->item_file_bean->getFilesByItemId($item_id, $group_id);
      if ($item_files !== false) {
          foreach ($item_files as $item_file) {
              if ($item_file['item_field_detail_id'] == $item_field_detail_id &&
        $item_file['file_id'] == $file_id) {
                  return $item_file;
              }
          }
      }

      return array();
  }

  /**
   * abstruct uploadfile info.
   *
   * @param int $file_id
   * @param array   $file
   *
   * @return bool true:Success,false:Fail
   */
  protected function db_UploadFile(&$file_id, $file)
  {
      if ($this->item_file_bean->getFile($file_id) !== false) {
          return $this->item_file_bean->updateFile2($file_id, $file);
      }

      return $this->item_file_bean->insertFile($file);
  }
  /**
   * xoonips_index_item_link db function.
   *
   * @param int $indexId
   * @param int $item_id
   * @param int $certify_state  if = 0 then not change
   * @param int $item_link_id
   *
   * @return  bool true:success,false:failed
   */
  protected function db_index_item_link($indexId, $item_id, $certify_state, &$item_link_id)
  {
      if ($item_link_id == 0) {
          return false;
      }
      $result = $this->item_link_bean->getIndexItemLinkInfoByIndexItemLinkId($item_link_id);
      if (empty($result)) {
          return false;
      }

      return $this->item_link_bean->updateIndexid($indexId, $item_id, $certify_state, $item_link_id);
  }

  /**
   * $file check.Update no check.
   *
   * @param array $file
   * @param string $illegalname if error occured key name substute this variable
   * @param int $file_id
   *
   * @return booelan true:Success,false:Fail
   */
  protected function check_file_array(&$file, &$illegalname, $file_id)
  {
      return true;
  }

  /**
   * attach_file upload?
   *
   * @param int $file_id
   *
   * @return bool true:Success,false:Fail
   */
  protected function is_attach_file($file_id)
  {
      return true;
  }

  /**
   * Post proc.
   *
   * @param int $item_id
   *
   * @return Always $rc equip value
   */
  protected function do_set_user_link($item_id)
  {
      return true;
  }
  /**
   * Instance xoonips_item_related_to.
   *
   * @param array $related
   *
   * @return boolaen true:Sucess,false:Fail
   */
  protected function db_related($related)
  {
      if (count($related) == 3) {
          return $this->related_to_bean->update($related);
      }

      return false;
  }

  /**
   * function for xoonips_item_extend.
   *
   * @param int $item_id
   * @param int $group_id
   * @param string  $tableName
   * @param mixed   $value
   * @param int $occurrence_number
   *
   * @return  bool true:success,false:failed
   */
  protected function db_extend($item_id, $group_id, $tableName, $value, $occurrence_number)
  {
      $db_extend_array = $this->item_extend_bean->getItemExtendInfo($item_id, $tableName, $group_id);
      foreach ($db_extend_array as $extend) {
          if ($extend['occurrence_number'] == $occurrence_number &&
         $extend['group_id'] == $group_id) {
              return $this->item_extend_bean->updateVal($item_id, $tableName, $value, $occurrence_number, $group_id);
          }
      }

      return $this->item_extend_bean->insert($item_id, $tableName, $value, $occurrence_number, $group_id);
  }

  /**
   * abstruct method db_keyword.
   *
   * @param array $keyword_arr
   *
   * @return bool
   */
  protected function db_keyword(&$keyword_arr)
  {
      $db_keyword_arr = $this->item_keyword_bean->getKeywords($keyword_arr['item_id']);
      foreach ($db_keyword_arr as $keyword) {
          if ($keyword['keyword_id'] == $keyword_arr['keyword_id']) {
              return $this->item_keyword_bean->updateKeywords2($keyword_arr['item_id'],
                                                    $keyword_arr['keyword_id'],
                                                    $keyword_arr['keyword']);
          }
      }

      return $this->item_keyword_bean->insertKeyword($keyword_arr);
  }

   /**
    * db_title function.
    *
    * @param int $item_id
    * @param int $item_field_detail_id
    * @param string  $title
    * @param int     $title_id
    *
    * @return bool true:Success,false:Fail
    */
   protected function db_title($item_id,
                                $item_field_detail_id,
                                $title,
                                $title_id)
   {
       $db_title_array = $this->item_title_bean->getItemTitleInfo($item_id);
       foreach ($db_title_array as $title1) {
           if ($title1['item_field_detail_id'] == $item_field_detail_id &&
          $title1['title_id'] == $title_id) {
               return $this->item_title_bean->updateTitle($item_id,
                                              $item_field_detail_id,
                                              $title,
                                              $title_id);
           }
       }

       return $this->item_title_bean->insertTitle($item_id,
                                              $item_field_detail_id,
                                              $title,
                                              $title_id);
   }

    private function update_item(&$item_column, $item_id)
    {
        global $xoopsDB;
        $table = $xoopsDB->prefix($this->dirname.'_item');

        $sql = "UPDATE ${table} SET doi=".Xoonips_Utils::convertSQLStr($item_column['doi']).
            ',view_count='.Xoonips_Utils::convertSQLNum($item_column['view_count']).
            ',creation_date='.Xoonips_Utils::convertSQLNum($item_column['creation_date']).
            ',last_update_date='.Xoonips_Utils::convertSQLNum($item_column['last_update_date']).
            " where item_id=${item_id}";
        if (!$xoopsDB->queryF($sql)) {
            return false;
        }

        return true;
    }

  /**
   * Create xoonips_item.
   *
   * @param type $item_column
   * @param type $item_id
   *
   * @return bool true:Success,false:Fail
   */
  protected function db_xoonips_item(&$item_column, &$item_id)
  {
      return $this->update_item($item_column, $item_id);
  }

  /**
   * Update users_link.
   *
   * @param array $item_group_arr
   * @param int $item_detail
   * @param int $item_id
   *
   * @return int
   */
  protected function users_link(&$item_group_arr, &$item_detail, $item_id)
  {
      $user_link_bean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
      $weight = 0;
      $uid = 0;
      foreach ($item_detail['child_xml_obj']->children(self::itemns) as $k => $v) {
          if ($k == 'uid') {
              $uid = strval($v);
          } elseif ($k == 'weight') {
              $weight = strval($v);
          }
      }

      $info = array(
      'weight' => $weight,
      'item_id' => $item_id,
      'uid' => $uid,
    );
      $db_user_link_array = $user_link_bean->getItemUsersInfo($item_id);
      if (in_array($info, $db_user_link_array)) {
          $rc = $user_link_bean->update($info);
          if ($rc === true) {
              return 200;
          }
      }
      if ($user_link_bean->insert($info) == true) {
          return 200;
      }

      return $this->set_err_msg(400, "type='users_link' create fail. ", __LINE__);
  }

  /**
   * is_doi?exist doi?
   *
   * @param type $doi
   *
   * @return bool true:Success,false:Fail
   */
  protected function is_doi($doi)
  {
      return true;
  }

  /**
   * Create xoonips_item.
   *
   * @param type $item_id
   * @param type $item_type_id
   *
   * @return bool true:Success,false:Fail
   */
  protected function create_xoonips_item_array(&$item_id, $item_type_id)
  {
      // 1st get specified item info.
    $item_column = $this->item_bean->getItemBasicInfo($item_id);
      if ($item_column === false) {
          return false;
      }

      return $item_column;
  }

  /**
   * check get item_id from DB
   * - Use only update.
   *
   * @param int $item_id
   *
   * @return true :exist,false:no exist
   */
  protected function is_set_xml_item_id($item_id)
  {
      if ($item_id != -1) {
          return true;
      }

      return false;
  }
}
