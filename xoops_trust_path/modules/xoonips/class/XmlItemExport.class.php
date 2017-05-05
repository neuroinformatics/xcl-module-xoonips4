<?php

use Xoonips\Core\CacheUtils;
use Xoonips\Core\FileUtils;
use Xoonips\Core\StringUtils;
use Xoonips\Core\ZipFile;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once XOONIPS_TRUST_PATH.'/class/bean/ItemBean.class.php';

define('contentsURI', 'http://xoonips.sourceforge.jp/xoonips/item/');
define('contentsxmlns', 'xmlns:C');
define('contetsPrefix', 'C:');

class XmlItemExport
{
    private $dirname;
    private $trustDirname;
    private $dom;
    private $root;
    private $file_ids_w_detail_table_name_array;

  /**
   * construct.
   */
  public function __construct()
  {
      $this->dirname = $this->trustDirname = 'xoonips';
      $this->dom = new DOMDocument('1.0');
      $this->dom->encodeing = 'UTF-8';
      $this->dom->formatOutput = true;
      $this->root = $this->dom->createElementNS(contentsURI, contetsPrefix.'item');
      $this->dom->appendChild($this->root);
      $this->root->setAttributeNS('http://www.w3.org/2000/xmlns/', contentsxmlns, contentsURI);
  }

  /**
   * Convert from UTF-8 to Other char code.
   *
   * @param string $str
   *
   * @return string
   */
  private function convert_utf8($str)
  {
      return StringUtils::convertEncoding($str, _CHARSET, 'UTF-8', 'u');
  }

  ////////////////////////////////////////////////////////

  private function xoonips_item(&$ids,
                                  &$item_field_detail_element,
                                  &$grp_dom)
  {
      $xml = $item_field_detail_element['xml'];
      $column_name = $item_field_detail_element['column_name'];
      $item_bean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
      $item_id = $ids['item_id'];
      $item = $item_bean->getItemBasicInfo($item_id);
      $value = $item[$column_name];
      if (!empty($value)) {
          $ele = $this->dom->createElementNS(contentsURI, contetsPrefix.$xml, $value);
          $ele->setAttributeNS(contentsURI, contetsPrefix.'type', 'item');
          $ele->setAttributeNS(contentsURI, contetsPrefix.'column_name', $column_name);
          $grp_dom->appendChild($ele);
      }
  }

  /**
   * Call xoonips_item_extend.
   *
   * @param type $ids
   * @param type $item_field_detail_element
   * @param type $grp_dom
   *
   * @return type
   */
  private function xoonips_item_extend(&$ids,
                                          &$item_field_detail_element,
                                          &$grp_dom)
  {
      $xml = $item_field_detail_element['xml'];
      $table_name = $item_field_detail_element['table_name'];
      $item_extend_bean = Xoonips_BeanFactory::getBean('ItemExtendBean', $this->dirname, $this->trustDirname);
      $item_id = $ids['item_id'];
      $group_id = $ids['group_id'];
      $extends = $item_extend_bean->getItemExtendInfo($item_id, $table_name, $group_id);
      if (count($extends) > 0) {
          foreach ($extends as$extend) {
              $value = $this->convert_utf8($extend['value']);
              $ele = $this->dom->createElementNS(contentsURI, contetsPrefix.$xml, $value);
              $ele->setAttributeNS(contentsURI, contetsPrefix.'type', 'extend');
              $ele->setAttributeNS(contentsURI, contetsPrefix.'occurrence_number', $extend['occurrence_number']);
              $grp_dom->appendChild($ele);
          }
      }
  }

  /**
   *Call xoonips_item_keyword.
   *
   * @param type $ids
   * @param type $item_field_detail_element
   * @param type $grp_dom
   */
  private function xoonips_item_keyword(&$ids, &$item_field_detail_element, &$grp_dom)
  {
      $xml = $item_field_detail_element['xml'];
      $item_keyword_bean = Xoonips_BeanFactory::getBean('ItemKeywordBean', $this->dirname, $this->trustDirname);
      $item_id = $ids['item_id'];
      $keyword_arr = $item_keyword_bean->getKeywords($item_id);

      if (count($keyword_arr) > 0) {
          foreach ($keyword_arr as $key_value) {
              $ele = $this->dom->createElementNS(contentsURI,
                contetsPrefix.$xml, $this->convert_utf8($key_value['keyword']));
              $ele->setAttributeNS(contentsURI, contetsPrefix.'type', 'keyword');
              $ele->setAttributeNS(contentsURI, contetsPrefix.'keyword_id', $key_value['keyword_id']);
              $grp_dom->appendChild($ele);
          }
      }
  }

  /**
   * Call xoonips_item_related_to.
   *
   * @param type $ids
   * @param type $item_field_detail_element
   * @param type $grp_dom
   *
   * @return type
   */
  private function xoonips_item_related_to(&$ids, &$item_field_detail_element, &$grp_dom)
  {
      $xml = $item_field_detail_element['xml'];
      $item_related_to = Xoonips_BeanFactory::getBean('ItemRelatedToBean', $this->dirname, $this->trustDirname);
      $item_id = $ids['item_id'];
      $item_relateds = $item_related_to->getRelatedToInfo($item_id);
      if (count($item_relateds) > 0) {
          foreach ($item_relateds as $item_related) {
              if (isset($item_related['child_item_id'])) {
                  $ele = $this->dom->createElementNS(contentsURI, contetsPrefix.$xml);
                  $child_item_id = $this->dom->createTextNode($item_related['child_item_id']);
                  $ele->appendChild($child_item_id);
                  $ele->setAttributeNS(contentsURI, contetsPrefix.'type', 'related_to');
                  $ele->setAttributeNS(contentsURI, contetsPrefix.'original_related_to', $item_related['child_item_id']);
                  $grp_dom->appendChild($ele);
              }
          }
      }
  }

  /**
   *Call xoonips_item_title.
   *
   * @param type $item_field_detail_id
   * @param type $item_field_detail_element
   * @param type $grp_dom
   *
   * @return type
   */
  private function xoonips_item_title(&$ids, &$item_field_detail_element, &$grp_dom)
  {
      $xml = $item_field_detail_element['xml'];
      $item_title_bean = Xoonips_BeanFactory::getBean('ItemTitleBean', $this->dirname, $this->trustDirname);
      $item_id = $ids['item_id'];
      $item_field_detail_id = $ids['item_field_detail_id'];
      $title_arr = $item_title_bean->getItemTitleInfo($item_id);
      $title = null;
      if (count($title_arr) > 0) {
          foreach ($title_arr as $val) {
              if ($val['item_field_detail_id'] == $item_field_detail_id) {
                  $ele = $this->dom->createElementNS(contentsURI,
                  contetsPrefix.$xml, $this->convert_utf8($val['title']));
                  $ele->setAttributeNS(contentsURI, contetsPrefix.'type', 'title');
                  $ele->setAttributeNS(contentsURI, contetsPrefix.'title_id', $val['title_id']);
                  $ele->setAttributeNS(contentsURI, contetsPrefix.'item_field_detail_id', $val['item_field_detail_id']);
                  $grp_dom->appendChild($ele);
                  break;
              }
          }
      }
  }

  /**
   * Call xoonips_item_users_link.
   *
   * @param type $ids
   * @param type $item_field_detail_element
   * @param type $grp_dom
   *
   * @return type
   */
  private function xoonips_item_users_link(&$ids, &$item_field_detail_element, &$grp_dom)
  {
      $xml = $item_field_detail_element['xml'];
      $item_user_link = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
      $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
      $item_id = $ids['item_id'];
      $uinfos = $item_user_link->getItemUsersInfo($item_id);

      foreach ($uinfos as $uinfo) {
          $userBasicInfo = $userBean->getUserBasicInfo($uinfo['uid']);
          $weight = $uinfo['weight'];

          $ele = $this->dom->createElementNS(contentsURI, contetsPrefix.$xml);
          $ele->appendChild($this->dom->createElementNS(contentsURI, contetsPrefix.'uname', $userBasicInfo['uname']));
          $ele->appendChild($this->dom->createElementNS(contentsURI, contetsPrefix.'weight', $weight));
          $ele->setAttributeNS(contentsURI, contetsPrefix.'type', 'users_link');
          $grp_dom->appendChild($ele);
      }
  }

  /**
   * call xoonips_item_file.
   *
   * @param type $ids
   * @param type $item_field_detail_element
   * @param type $grp_dom
   */
  private function xoonips_item_file(&$ids, &$item_field_detail_element, &$grp_dom)
  {
      $xml = $item_field_detail_element['xml'];
      $column = $item_field_detail_element['column_name'];
      $item_field_detail_id = $item_field_detail_element['item_field_detail_id'];
      $item_id = $ids['item_id'];
      $group_id = $ids['group_id'];
      $item_file_bean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
      $item_files = $item_file_bean->getFilesByDetailId($item_field_detail_id, $item_id, $group_id);
      if (empty($item_files)) {
          return;
      }

      foreach ($item_files as $item_file) {
          $ele = $this->dom->createElementNS(contentsURI, contetsPrefix.$xml);
          $ele->setAttributeNS(contentsURI, contetsPrefix.'type', 'file');
          $ele->setAttributeNS(contentsURI, contetsPrefix.'column_name', $column);
          $pack_arr = array('original_file_name',
                      'file_id',
                      'item_field_detail_id',
                      'mime_type',
                      'file_size',
                      'timestamp',
                      'caption',
                      'sess_id',
                      'search_module_name',
                      'search_module_version',
                      'download_count',
                      'occurrence_number', );

          foreach ($pack_arr as $xoonips_item_file_tagname) {
              if (array_key_exists($xoonips_item_file_tagname, $item_file) == true) {
                  $v = $this->convert_utf8($item_file[$xoonips_item_file_tagname]);
                  $ele->appendChild($this->dom->createElementNS(contentsURI, contetsPrefix.$xoonips_item_file_tagname, $v));
              } else {
                  $ele->appendChild($this->dom->createElementNS(contentsURI, contetsPrefix.$xoonips_item_file_tagname));
              }
          }
          $grp_dom->appendChild($ele);
      }
  }

  /**
   *call xoonips_index_item_link.
   *
   * @param type $ids
   * @param type $item_field_detail_element
   * @param type $grp_dom
   */
  private function xoonips_index_item_link(&$ids, &$item_field_detail_element, &$grp_dom)
  {
      global $xoopsUser;
      $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;
      $xml = $item_field_detail_element['xml'];
      $item_id = $ids['item_id'];
      $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
      $item_link_bean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
      $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
      $item_link = $item_link_bean->getIndexItemLinkInfo($item_id);
      foreach ($item_link as $item_link_ele) {
          if ($item_link_ele['certify_state'] == XOONIPS_CERTIFY_REQUIRED) {
              continue;
          }
          $index = $indexBean->getIndex($item_link_ele['index_id']);
          $open_level = $index['open_level'];
          if ($open_level == XOONIPS_OL_GROUP_ONLY) { // GROUP
        $groupid = $index['groupid'];
              $isGroupManager = $userBean->isGroupManager($groupid, $uid);
              $isGroupMember = $userBean->isGroupMember($groupid, $uid);
              if ($isGroupManager == false && $isGroupMember == false) {
                  // not export group index if be not member or manager
          continue;
              }
          } elseif ($open_level == XOONIPS_OL_PRIVATE) { // private
        $root_index = $indexBean->getRootIndex($index['index_id']);
              $user = $userBean->getUserBasicInfo($uid);
              if ($root_index['title'] != $user['uname']) {
                  continue;
              }
          }

          $ele = $this->dom->createElementNS(contentsURI, contetsPrefix.$xml);
          $ele->setAttributeNS(contentsURI, contetsPrefix.'type', 'index_item_link');
          $grp_dom->appendChild($ele);
          $index_title = $indexBean->getFullPathStr($item_link_ele['index_id']);
          $ele->appendChild($this->dom->createElementNS(contentsURI, contetsPrefix.'index_title', $index_title));
/*       $ele->appendChild($this->dom->createElementNS(contentsURI, contetsPrefix.'index_id',$item_link_ele['index_id']));
      $ele->appendChild($this->dom->createElementNS(contentsURI, contetsPrefix.'index_item_link_id', $item_link_ele['index_item_link_id']));
      $ele->appendChild($this->dom->createElementNS(contentsURI, contetsPrefix.'certify_state', $item_link_ele['certify_state'])); */
      }
  }

  /**
   *call xoonips_item_changelog.
   *
   * @param type $ids
   * @param type $item_field_detail_element
   * @param type $grp_dom
   */
  private function xoonips_item_changelog(&$ids, &$item_field_detail_element, &$grp_dom)
  {
      $xml = $item_field_detail_element['xml'];
      $item_id = $ids['item_id'];
      $item_changelog_bean = Xoonips_BeanFactory::getBean('ItemChangeLogBean', $this->dirname, $this->trustDirname);
      $change_log = $item_changelog_bean->getChangeLogs($item_id);
      $type_ele = $this->dom->createElementNS(contentsURI, contetsPrefix.$xml);
      $type_ele->setAttributeNS(contentsURI, contetsPrefix.'type', 'changelog');
      $grp_dom->appendChild($type_ele);
      foreach ($change_log as $change_log_ele) {
          $ele = $this->dom->createElementNS(contentsURI, contetsPrefix.'log');
          $type_ele->appendChild($ele);
          $ele->appendChild($this->dom->createElementNS(contentsURI, contetsPrefix.'log',
                                  $this->convert_utf8($change_log_ele['log'])));
          $ele->appendChild($this->dom->createElementNS(contentsURI, contetsPrefix.'log_date', $change_log_ele['log_date']));
          $ele->appendChild($this->dom->createElementNS(contentsURI, contetsPrefix.'log_id', $change_log_ele['log_id']));
          $ele->appendChild($this->dom->createElementNS(contentsURI, contetsPrefix.'uid', $change_log_ele['uid']));
      }
  }

  ////////////////////////////////////////////////////////

  /**
   * table name dispatch each function.
   *
   * @param type $item_field_detail_id
   * @param type $item_field_detail_element
   *
   * @return type
   */
  private function dispatch_tbl(&$ids, &$item_field_detail_element, &$grp_dom)
  {
      $xml = $item_field_detail_element['xml'];
      $table_name = $item_field_detail_element['table_name'];
      $column_name = $item_field_detail_element['column_name'];

    // xoonips_item_extend detect
    $rc = strpos($table_name, 'xoonips_item_extend');
      if ($rc !== false) {
          // item_extend proc call
      $this->xoonips_item_extend($ids, $item_field_detail_element, $grp_dom);
      } else {
          // other proc call
      if (method_exists($this, $table_name) == true) {
          $this->$table_name($ids, $item_field_detail_element, $grp_dom);
      } else {
          return false;
      }
      }

      return true;
  }

  /**
   * Item_id -> item_type[name].
   *
   * @param int $item_id
   *
   * @return string item_type[name]
   */
  private function get_item_name($item_id)
  {
      $item_bean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
      $item = $item_bean->getItemBasicInfo($item_id);

      $item_type_bean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
      $item_type = $item_type_bean->getItemTypeInfo($item['item_type_id']);

      return $item_type['name'];
  }

  /**
   * @param type $conv_grp_list
   *
   * @return type
   */
  private function mk_dom(&$conv_grp_list, $item_id)
  {
      $grp_dom = null;
      $item_field_detail_bean = Xoonips_BeanFactory::getBean('ItemFieldDetailBean', $this->dirname, $this->trustDirname);
      $this->root->setAttributeNS(contentsURI, contetsPrefix.'item_type_name', $this->get_item_name($item_id));
      $this->root->setAttributeNS(contentsURI, contetsPrefix.'item_id', $item_id);
      foreach ($conv_grp_list as $group_id => $grp_val) {
          $grp_ele = $this->dom->createElementNS(contentsURI, contetsPrefix.$grp_val['xml']);
          $grp_ele->setAttributeNS(contentsURI, contetsPrefix.'type', 'group');
          $group_id = $grp_val['field_group']['group_id'];
          $grp_ele->setAttributeNS(contentsURI, contetsPrefix.'group_id', $group_id);
          $grp_dom = $this->root->appendChild($grp_ele);
          foreach ($grp_val['item_field_detail_id'] as $item_field_detail_id) {
              $item_field_detail = $item_field_detail_bean->getItemTypeDetailById($item_field_detail_id);
              $ids = array(
          'item_id' => $item_id,
          'item_field_detail_id' => $item_field_detail_id,
          'group_id' => $group_id,
        );
              $rc = $this->dispatch_tbl($ids, $item_field_detail, $grp_dom);
              if ($rc == false) {
                  return false;
              }
          }
      }

      return true;
  }

  /**
   * getItemFieldGroup result convert original array form below.
   *
   * [group_id] => array(xml,field_group,array(item_field_detail_ids))
   *
   *
   * @param array $item_field_detail
   *
   * @return array
   */
  private function convInternalgroup(&$item_field_detail)
  {
      $ret = array();
      foreach ($item_field_detail as $value) {
          if (empty($ret[$value['group_id']])) {
              $ret[$value['group_id']] = array(
           'xml' => $value['xml'],
           'field_group' => $value,
           'item_field_detail_id' => array($value['item_field_detail_id']),
        );
          } else {
              array_push($ret[$value['group_id']]['item_field_detail_id'], $value['item_field_detail_id']);
          }
      }

      return $ret;
  }

  /**
   * Export for XML(Text).
   *
   * @param int $item_id
   *
   * @return object;
   */
  public function get_xml($item_id)
  {
      $this->get_dom($item_id);

      return $this->dom->saveXML();
  }

  /**
   * Get Export for PHP Dom.
   *
   * @param int $item_id
   *
   * @return object;
   */
  public function get_dom($item_id)
  {
      $xml = null;

    // All of xoonips_item_field_detail
    $item_bean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
      $grp_list = $item_bean->getItemFieldGroup($item_id);
      $conv_grp_list = $this->convInternalgroup($grp_list);
      $this->mk_dom($conv_grp_list, $item_id);

      return $this->dom;
  }

  /**
   * Export zip file.
   *
   * @param array $items
   */
  public function export_zip($items, $index_id = 0)
  {
      global $xoopsUser;
      $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : XOONIPS_UID_GUEST;

      $upload_dir = Xoonips_Utils::getXooNIpsConfig($this->dirname, 'upload_dir');
      $tmpitem = $upload_dir.'/item';

        // create temporary directry
        $tmp = '/tmp';
      $time = date('YmdHis');
      $tmpdir1 = "${tmp}/${time}-ex1";
      if (!mkdir($tmpdir1, 0755)) {
          die("can't create directry '${tmpdir1}'.");
      }
      $tmpdir2 = "${tmp}/${time}-ex2";
      if (!mkdir($tmpdir2, 0755)) {
          die("can't create directry '${tmpdir2}'.");
      }

        // export zip file ready
        $this->export_zip_ready($items, $tmpdir1, $tmpdir2, $tmpitem);

        // export index
        if ($index_id != 0) {
            $my_indexes = array();
            $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
            $childIndexes = $indexBean->getAllChildIndexes($index_id);
            if (count($childIndexes) > 0) {
                foreach ($childIndexes as $index) {
                    $cid = $index['index_id'];
                    $ctitle = $index['title'];
                    $itemIds = $indexBean->getCanViewItemIds($cid, $uid);
                    $id_path = $indexBean->getIndexIDPath($cid, $index_id);
                    $tmpdir3 = "${tmpdir2}/${id_path}";

                    if (!is_dir($tmpdir3)) {
                        if (!mkdir($tmpdir3, 0755, true)) {
                            die("can't create directry '${tmpdir3}'.");
                        }
                    }
                    $this->export_zip_ready($itemIds, $tmpdir1, $tmpdir3, $tmpitem);
                }
            }
        }

        // all item zip
        $zip = $time.'.zip';
      $zipfile = "${tmp}/${zip}";
      $zipClass = new ZipFile();
      if ($zipClass->open($zipfile)) {
          foreach ($items as $item_id) {
              $zipClass->add($tmpdir2.'/'.$item_id.'.zip', $item_id.'.zip');
          }
          $zipClass->close();
      } else {
          die("can't create zip file ".$zipfile);
      }

      FileUtils::deleteDirectory($tmpdir1);
      FileUtils::deleteDirectory($tmpdir2);

      FileUtils::deleteFileOnExit($zipfile);
      CacheUtils::downloadFile(false, false, 'application/x-zip', $zipfile, $zip, _CHARSET);
  }

  /**
   * Export zip file ready.
   *
   * @param array $items, string $tmpdir1, string $tmpdir2, string tmpitem
   */
  private function export_zip_ready($items, $tmpdir1, $tmpdir2, $tmpitem)
  {
      $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
      $zipClass = new ZipFile();
      foreach ($items as $item_id) {
          $zippedFiles = array();
            // generate xml
            $this->__construct();
          $xml = $this->get_xml($item_id);

            // create xml file
            $xmlname = "${item_id}.xml";
          $tmpfile = "${tmpdir1}/${xmlname}";
          $zippedFiles[] = $xmlname;
          $fhdl = fopen($tmpfile, 'aw');
          if (!$fhdl) {
              die("can't open file '${tmpfile}' for write.");
          }
          if (!fwrite($fhdl, $xml)) {
              die("can't write '${tmpfile}'.");
          }
          fclose($fhdl);

            // copy temp file
            $files = $fileBean->getFilesByItemId($item_id);
          foreach ($files as $file) {
              $fileid = $file['file_id'];
                // FIXME!! take care filesystem encoding
                $filename = mb_convert_encoding($file['original_file_name'], 'SJIS', 'UTF-8');
              mkdir("${tmpdir1}/${fileid}");
              $src = $tmpitem.'/'.$item_id.'/'.$fileid;
              $dst = $tmpdir1.'/'.$fileid.'/'.$filename;
              copy($src, $dst);
              $zippedFiles[] = $fileid.'/'.$filename;
          }

            // item zip
            $itemzip = $item_id.'.zip';
          if ($zipClass->open($tmpdir2.'/'.$itemzip)) {
              foreach ($zippedFiles as $fname) {
                  $zipClass->add($tmpdir1.'/'.$fname, $fname);
              }
              $zipClass->close();
          } else {
              die("can't create zip file ".$tmpdir2.'/'.$itemzip);
          }

          $tmpdir_chk = explode('/', $tmpdir1);
          if (count($tmpdir_chk) > 1) {
              FileUtils::emptyDirectory($tmpdir1);
          }
      }
  }
}
