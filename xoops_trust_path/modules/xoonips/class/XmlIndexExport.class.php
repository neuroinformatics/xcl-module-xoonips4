<?php

class XmlIndexExport
{
    private $indexBean;

  /**
   * Public/Private/Group Index Search common sub routine.
   *
   * @param array $top_arr        elminate top
   * @param array $indexes        all elemnt fot this index
   * @param array $index_path_arr indexpath chage array
   *
   * @return array success,empty array fail
   */
  private function sub_walk_index(&$top_arr, &$indexes, &$index_path_arr)
  {
      $title = $top_arr['title'];
      if (strcmp($index_path_arr[0], $title) != 0) {
          return array();
      }

      $result = $top_arr;
      array_shift($index_path_arr); // top directory elminate

    $parent_index_id = $top_arr['index_id'];
      foreach ($index_path_arr as $path) {
          $child = $this->indexBean->getChilds($indexes, $parent_index_id);
          $parent_index_id = -1;
          foreach ($child as $child_index) {
              $ret = $this->indexBean->getIndex($child_index);
              if (strcmp($ret['title'], $path) == 0) {
                  $parent_index_id = $ret['index_id'];
                  $result = $ret;
              }
          }
          if ($parent_index_id == -1) {
              return array();
          }
      }

      return $result;
  }

  /**
   * Item Ids gets from DB.
   *
   * @param type $index_id
   * @param type $uid
   *
   * @return type
   */
  private function get_item_ids($index_id, $uid)
  {
      return $this->indexBean->getCanViewItemIds($index_id, $uid);
  }

  /**
   * Walk Private Index.
   *
   * @param int   $uid
   * @param array $index_path_arr
   *
   * @return array
   */
  private function walk_private_index($uid, &$index_path_arr)
  {
      $pri_top_arr = $this->indexBean->getPrivateIndex($uid);
      $indexes = $this->indexBean->getPrivateIndexes($uid);
      $priindex = $this->sub_walk_index($pri_top_arr, $indexes, $index_path_arr);

      $item_ids = array();
      $children = array();
      if (empty($priindex['index_id']) == false) {
          $item_ids = $this->get_item_ids($priindex['index_id'], $uid);
          $children_index = $this->indexBean->getChilds($indexes, $priindex['index_id']);
          foreach ($children_index as $child_index) {
              $children[] = $this->indexBean->getIndex($child_index);
          }
      }

      return array('current' => $priindex,
                'items' => $item_ids,
                'children' => $children,
        );
  }

  /**
   * Walk Public Index.
   *
   * @param type $index_path_arr
   *
   * @return type
   */
  private function walk_public_index($uid, &$index_path_arr)
  {
      $pub_top_arr = $this->indexBean->getPublicIndex();
      $indexes = $this->indexBean->getPublicIndexes();
      $pubindex = $this->sub_walk_index($pub_top_arr, $indexes, $index_path_arr);
      $item_ids = array();
      $children = array();
      if (empty($pubindex['index_id']) == false) {
          $item_ids = $this->get_item_ids($pubindex['index_id'], $uid);
          $children_index = $this->indexBean->getChilds($indexes, $pubindex['index_id']);

          $children = array();
          foreach ($children_index as $child_index) {
              $children[] = $this->indexBean->getIndex($child_index);
          }
      }

      return array('current' => $pubindex,
                'items' => $item_ids,
                'children' => $children,
        );
  }

  /**
   * Walk Group Index.
   *
   * @param type $uid
   * @param type $index_path_arr
   *
   * @return type
   */
  private function walk_group_index($uid, &$index_path_arr)
  {
      $grp_top_arrs = $this->indexBean->getGroupIndex($uid);
      $indexes = $this->indexBean->getGroupIndexes($uid);
      foreach ($grp_top_arrs as $grp_top_arr) {
          $grpindex = $this->sub_walk_index($grp_top_arr, $indexes, $index_path_arr);
          if (empty($grpindex)) {
              continue;
          }
          $item_ids = array();
          $children = array();
          if (empty($grpindex['index_id']) == false) {
              $item_ids = $this->get_item_ids($grpindex['index_id'], $uid);
              $children_index = $this->indexBean->getChilds($indexes, $grpindex['index_id']);
              foreach ($children_index as $child_index) {
                  $children[] = $this->indexBean->getIndex($child_index);
              }
          }

          return array('current' => $grpindex,
                  'items' => $item_ids,
                  'children' => $children,
          );
      }
  }

  /**
   * Path split array.
   *
   * @param type $indexpath
   *
   * @return type
   */
  private function getPathArray($indexpath)
  {
      $explode = explode('/', $indexpath);
      $index_path_arr = array_merge(array_diff($explode, array('')));

      return $index_path_arr;
  }

    private function mkxml(&$arr)
    {
        $dom = $this->mkdom($arr);
        if (!is_null($dom)) {
            return $dom->saveXML();
        }

        return null;
    }

    private function mkdom(&$arr)
    {
        $url = 'http://xoonips.sourceforge.jp/xoonips/index/';
        $dom = new DOMDocument('1.0');
        $dom->encodeing = 'UTF-8';
        $dom->formatOutput = true;

    // Index
    $root = $dom->createElementNS($url, 'I:index');
        $index_data = $dom->appendChild($root);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:I', $url);
        if (empty($arr['current']) == false) {
            $cuuent = $index_data->appendChild($dom->createElementNS($url, 'I:'.'current'));
            $node_arr = array('title', 'index_id', 'description', 'open_level', 'weight');
            foreach ($node_arr as $value) {
                $cuuent->appendChild($dom->createElementNS($url, 'I:'.$value, $arr['current'][$value]));
            }
        }

    // Item
    $items = $index_data->appendChild($dom->createElementNS($url, 'I:items'));
        if (empty($arr['items']) == false) {
            foreach ($arr['items'] as $val) {
                $items->appendChild($dom->createElementNS($url, 'I:item_id', $val));
            }
        }

    // SubIndex
    if (empty($arr['children']) == false) {
        foreach ($arr['children'] as $value) {
            $subindex = $index_data->appendChild($dom->createElementNS($url, 'I:subindex'));
            $node_arr = array('title', 'index_id', 'description', 'open_level', 'weight');
            foreach ($node_arr as $key) {
                $subindex->appendChild($dom->createElementNS($url, 'I:'.$key, $value[$key]));
            }
        }
    } else {
        // Add Empty Node
      $subindex = $index_data->appendChild($dom->createElementNS($url, 'I:subindex'));
    }

        return $dom;
    }

  /**
   * Get Result XML.
   *
   *
   * @param type $indexpath
   * @param type $uid
   *
   * @return type
   */
  public function getXml($indexpath, $uid, $user)
  {
      $dom = $this->getDom($indexpath, $uid, $user);
      if (!is_null($dom)) {
          return $dom->saveXML();
      }

      return null;
  }

  /**
   * Get Result PHP Dom.
   *
   * @param type $indexpath
   * @param type $uid
   * @param type $user
   *
   * @return type
   */
  public function getDom($indexpath, $uid, $user)
  {
      $dirname = $trustDirname = 'xoonips';
      $this->indexBean = Xoonips_BeanFactory::getBean('IndexBean', $dirname, $trustDirname);

      $index_path_arr = $this->getPathArray($indexpath);

      if (strcmp($index_path_arr[0], 'Public') == 0) {
          // Public Index
      $pub_arr = $this->walk_public_index($uid, $index_path_arr);
          if (empty($pub_arr['current'])) {
              return null;
          }

          return $this->mkdom($pub_arr);
      } elseif (strcmp($index_path_arr[0], $user) == 0) {
          // Private Index
      $pri_arr = $this->walk_private_index($uid, $index_path_arr);
          if (empty($pri_arr['current'])) {
              return null;
          }

          return $this->mkdom($pri_arr);
      } else {
          // Group Index
      $grp_arr = $this->walk_group_index($uid, $index_path_arr);
          if (empty($grp_arr['current'])) {
              return null;
          }

          return $this->mkdom($grp_arr);
      }

      return null;
  }
}
