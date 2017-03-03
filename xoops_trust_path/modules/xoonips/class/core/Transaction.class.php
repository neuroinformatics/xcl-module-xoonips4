<?php

if (!defined('XOOPS_ROOT_PATH')) exit();

/**
 * XooNIps transaction class.
 *
 * Don't call constructor. Use {@link XoonipsTransaction::getInstance()} to get instance.
 */
class Xoonips_Transaction {
  var $db;

  /**
   * constractor
   *
   * @access public
   * @param object &$db XoopsDatabase
   */
  function __construct(&$db) {
    $this->db =& $db;
  }

  /**
   * start transaction
   *
   * @access public
   */
  function start() {
    $this->db->queryF('START TRANSACTION');
  }

  /**
   * commit
   *
   * @access public
   */
  function commit() {
    $this->db->queryF('COMMIT');
  }

  /**
   * rollback
   *
   * @access public
   */
  function rollback() {
    $this->db->queryF('ROLLBACK');
  }

  /**
   * get object instance
   * 
   * @access public
   * @return object instance of XoonipsTransaction
   */
  static function &getInstance() {
    static $singleton = null;
    if (!isset($singleton)) {
      $singleton = new Xoonips_Transaction($GLOBALS['xoopsDB']);
    }
    return $singleton;
  }
}

