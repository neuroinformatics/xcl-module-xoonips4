<?php

/**
 * XooNIps transaction class.
 *
 * Don't call constructor. Use {@link XoonipsTransaction::getInstance()} to get instance.
 */
class Xoonips_Transaction
{
    public $db;

  /**
   * constractor.
   *
   * @param object &$db XoopsDatabase
   */
  public function __construct(&$db)
  {
      $this->db = &$db;
  }

  /**
   * start transaction.
   */
  public function start()
  {
      $this->db->queryF('START TRANSACTION');
  }

  /**
   * commit.
   */
  public function commit()
  {
      $this->db->queryF('COMMIT');
  }

  /**
   * rollback.
   */
  public function rollback()
  {
      $this->db->queryF('ROLLBACK');
  }

  /**
   * get object instance.
   *
   * @return object instance of XoonipsTransaction
   */
  public static function &getInstance()
  {
      static $singleton = null;
      if (!isset($singleton)) {
          $singleton = new self($GLOBALS['xoopsDB']);
      }

      return $singleton;
  }
}
