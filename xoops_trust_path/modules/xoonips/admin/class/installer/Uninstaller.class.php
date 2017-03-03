<?php

require_once dirname(__FILE__) . '/InstallUtils.class.php';

/**
 * Xoonips_Uninstaller
 */
class Xoonips_Uninstaller {
	/**
	 * module install log
	 * @var Legacy_ModuleInstallLog
	 */
	public $mLog = null;

	/**
	 * flag for force mode
	 * @var bool
	 */
	private $_mForceMode = false;

	/**
	 * xoops module
	 * @var XoopsModule
	 */
	private $_mXoopsModule = null;

	/**
	 * constructor
	 */
	public function __construct() {
		$this->mLog = new Legacy_ModuleInstallLog();
	}

	/**
	 * set current xoops module
	 *
	 * @param XoopsModule &$xoopsModule
	 */
	public function setCurrentXoopsModule(&$xoopsModule) {
		$this->_mXoopsModule =& $xoopsModule;
	}

	/**
	 * set force mode
	 *
	 * @param bool $isForceMode
	 */
	public function setForceMode($isForceMode) {
		$this->_mForceMode = $isForceMode;
	}

	/**
	 * uninstall module
	 */
	private function _uninstallModule() {
		$dirname = $this->_mXoopsModule->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$moduleHandler =& Xoonips_Utils::getXoopsHandler('module');
		if ($moduleHandler->delete($this->_mXoopsModule)) {
			$this->mLog->addReport(constant($constpref . '_INSTALL_MSG_MODULE_INFORMATION_DELETED'));
		} else {
			$this->mLog->addError(constant($constpref . '_INSTALL_ERROR_MODULE_INFORMATION_DELETED'));
		}
	}

	/**
	 * uninstall tables
	 */
	private function _uninstallTables() {
		$dirname = $this->_mXoopsModule->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$root =& XCube_Root::getSingleton();
		$db =& $root->mController->getDB();
		//get max item_extend table id
		$maxExtendId = $this->_getMaxExtendId($db, $dirname);
		$tables =& $this->_mXoopsModule->getInfo('tables');
		if (is_array($tables)) {
			foreach($tables as $table) {
				$tableName = str_replace(array('{prefix}', '{dirname}'), array(XOOPS_DB_PREFIX, $dirname), $table);
				$sql = sprintf('DROP TABLE `%s`;', $tableName);
				if ($db->query($sql)) {
					$this->mLog->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_TABLE_DOROPPED'), $tableName));
				} else {
					$this->mLog->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_TABLE_DOROPPED'), $tableName));
				}
			}
		}
		// extend tables
		for ($i=0; $i<=$maxExtendId; $i++) {
			$tableName = XOOPS_DB_PREFIX.'_'.$dirname.'_item_extend'.$i;
			$sql = sprintf('DROP TABLE IF EXISTS `%s`;', $tableName);
			if ($db->query($sql))
				$this->mLog->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_TABLE_DOROPPED'), $tableName));
		}
	}

	/**
	 * get max extend id
	 *
	 * @param object $db
	 * @param string $dirname
	 * @return int $maxId
	 */
	private function _getMaxExtendId(&$db, $dirname) {
		$maxId = 0;
		$tableName = $db->prefix($dirname.'_item_field_detail');
		$sql = "SELECT MAX(item_field_detail_id) FROM " . $tableName;
		$result = $db->query($sql);
		while ($row = $db->fetchRow($result))
			$maxId = $row[0];
		return $maxId;
	}

	/**
	 * uninstall templates
	 */
	private function _uninstallTemplates() {
		Xoonips_InstallUtils::uninstallAllOfModuleTemplates($this->_mXoopsModule, $this->mLog, false);
	}

	/**
	 * uninstall blocks
	 */
	private function _uninstallBlocks() {
		$dirname = $this->_mXoopsModule->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		Xoonips_InstallUtils::uninstallAllOfBlocks($this->_mXoopsModule, $this->mLog);
		$tplHandler =& Xoonips_Utils::getXoopsHandler('tplfile');
		$cri = new Criteria('tpl_module', $dirname);
		if (!$tplHandler->deleteAll($cri)) {
			$this->mLog->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_TPL_DELETED'), $tplHandler->db->error()));
		}
	}

	/**
	 * uninstall preferences
	 */
	private function _uninstallPreferences() {
		Xoonips_InstallUtils::uninstallAllOfConfigs($this->_mXoopsModule, $this->mLog);
		Xoonips_InstallUtils::deleteAllOfNotifications($this->_mXoopsModule, $this->mLog);
		Xoonips_InstallUtils::deleteAllOfComments($this->_mXoopsModule, $this->mLog);
	}

	/**
	 * process report
	 */
	private function _processReport() {
		$dirname = $this->_mXoopsModule->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		if (!$this->mLog->hasError()) {
			$this->mLog->add(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_MODULE_UNINSTALLED'), $this->_mXoopsModule->get('name')));
		} else if (is_object($this->_mXoopsModule)) {
			$this->mLog->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_MODULE_UNINSTALLED'), $this->_mXoopsModule->get('name')));
		} else {
			$this->mLog->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_MODULE_UNINSTALLED'), 'something'));
		}
	}

	/**
	 * execute uninstall
	 *
	 * @return bool
	 */
	public function executeUninstall() {
		$this->_uninstallTables();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		if ($this->_mXoopsModule->get('mid') != null) {
			$this->_uninstallModule();
			if (!$this->_mForceMode && $this->mLog->hasError()) {
				$this->_processReport();
				return false;
			}
			$this->_uninstallTemplates();
			if (!$this->_mForceMode && $this->mLog->hasError()) {
				$this->_processReport();
				return false;
			}
			$this->_uninstallBlocks();
			if (!$this->_mForceMode && $this->mLog->hasError()) {
				$this->_processReport();
				return false;
			}
			$this->_uninstallPreferences();
			if (!$this->_mForceMode && $this->mLog->hasError()) {
				$this->_processReport();
				return false;
			}
			$this->_processScript();
			if (!$this->_mForceMode && $this->mLog->hasError()) {
				$this->_processReport();
				return false;
			}
		}
		$this->_processReport();
		return true;
	}
	
	function _processScript() {
		if (!$this->moduleUninstall( $this->_mXoopsModule)) {
			$this->mLog->addError(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_FAILED_TO_EXECUTE_CALLBACK, 'moduleUninstall'));
		}
	}

	function moduleUninstall($xoopsMod) {
		$mydirname = $xoopsMod->getVar('dirname');
		$uid = $GLOBALS['xoopsUser']->getVar('uid', 'n');
		$mid = $xoopsMod->getVar('mid', 'n');
		// show original 'user' and 'login' blocks
		$sys_blocks = array('user' => array(), 'login' => array());
		if (defined('XOOPS_CUBE_LEGACY')) {
			// for XOOPS Cube Legacy 2.2
			$sys_blocks['user'][] = array('legacy', 'b_legacy_usermenu_show');
			$sys_blocks['login'][] = array('user', 'b_user_login_show');
		}
		$sys_blocks['user'][] = array('system', 'b_system_user_show');
		$sys_blocks['login'][] = array('system', 'b_system_login_show');
		foreach ($sys_blocks as $type => $sys_type_blocks) {
			foreach ($sys_type_blocks as $sys_block) {
				list($dirname, $show_func) = $sys_block;
				$sysmid = Xoonips_InstallUtils::getModuleId($dirname);
				if ($sysmid === false) {
					continue; // module not found
				}
				$bids = Xoonips_InstallUtils::getBlockIds($sysmid, $show_func);
				foreach ($bids as $bid) {
					Xoonips_InstallUtils::setBlockPosition($bid, true, 0, 0);
				}
				if (count($bids) != 0) {
					break; // found this type's block
				}
			}
		}
		return true;
	}

}

