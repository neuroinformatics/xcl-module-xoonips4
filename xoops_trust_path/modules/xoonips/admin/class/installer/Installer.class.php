<?php

require_once dirname(__FILE__) . '/InstallUtils.class.php';

require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/core/BeanFactory.class.php';
require_once XOOPS_TRUST_PATH . '/modules/xoonips/class/Enum.class.php';

/**
 * module installer class
 */
class Xoonips_Installer {

	/**
	 * module log
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
	 * install tables information
	 *
	 * @return bool
	 */
	private function _installTables() {
		if (!Xoonips_InstallUtils::installSQLAutomatically($this->_mXoopsModule, $this->mLog)) {
			return false;
		}
		if (!Xoonips_InstallUtils::installSQLAlterTables($this->_mXoopsModule, $this->mLog)) {
			return false;
		}
		if (!Xoonips_InstallUtils::installDataAutomatically($this->_mXoopsModule, $this->mLog)) {
			return false;
		}
		return true;
	}

	/**
	 * install module information
	 *
	 * @return bool
	 */
	private function _installModule() {
		$dirname = $this->_mXoopsModule->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$moduleHandler =& Xoonips_Utils::getXoopsHandler('module');
		if (!$moduleHandler->insert($this->_mXoopsModule)) {
			$this->mLog->addError(constant($constpref . '_INSTALL_ERROR_MODULE_INSTALLED'));
			return false;
		}
		$gpermHandler =& Xoonips_Utils::getXoopsHandler('groupperm');
		if ($this->_mXoopsModule->getInfo('hasAdmin')) {
			$adminPerm =& $this->_createPermission(XOOPS_GROUP_ADMIN);
			$adminPerm->set('gperm_name', 'module_admin');
			if (!$gpermHandler->insert($adminPerm))
				$this->mLog->addError(constant($constpref . '_INSTALL_ERROR_PERM_ADMIN_SET'));
		}
		if ($this->_mXoopsModule->getInfo('hasMain')) {
			if ($this->_mXoopsModule->getInfo('read_any')) {
				$memberHandler =& Xoonips_Utils::getXoopsHandler('member');
				$groupObjects =& $memberHandler->getGroups();
				foreach ($groupObjects as $group) {
					$readPerm =& $this->_createPermission($group->get('groupid'));
					$readPerm->set('gperm_name', 'module_read');
					if (!$gpermHandler->insert($readPerm))
						$this->mLog->addError(constant($constpref . '_INSTALL_ERROR_PERM_READ_SET'));
				}
			} else {
				$root =& XCube_Root::getSingleton();
				$groups = $root->mContext->mXoopsUser->getGroups(new Criteria('group_type', Xoonips_Enum::GROUP_TYPE, '<>'));
				foreach ($groups as $group) {
					$readPerm =& $this->_createPermission($group);
					$readPerm->set('gperm_name', 'module_read');
					if (!$gpermHandler->insert($readPerm))
						$this->mLog->addError(constant($constpref . '_INSTALL_ERROR_PERM_READ_SET'));
				}
			}
		}
		return true;
	}

	/**
	 * create permission
	 *
	 * @param int $gid
	 * @return XoopsGroupPerm&
	 */
	private function &_createPermission($gid) {
		$gpermHandler =& Xoonips_Utils::getXoopsHandler('groupperm');
		$perm =& $gpermHandler->create();
		$perm->set('gperm_groupid', $gid);
		$perm->set('gperm_itemid', $this->_mXoopsModule->get('mid'));
		$perm->set('gperm_modid', 1);
		return $perm;
	}

	/**
	 * install templates
	 */
	private function _installTemplates() {
		Xoonips_InstallUtils::installAllOfModuleTemplates($this->_mXoopsModule, $this->mLog);
	}

	/**
	 * install blocks
	 */
	private function _installBlocks() {
		Xoonips_InstallUtils::installAllOfBlocks($this->_mXoopsModule, $this->mLog);
	}

	/**
	 * install preferences
	 */
	private function _installPreferences() {
		Xoonips_InstallUtils::installAllOfConfigs($this->_mXoopsModule, $this->mLog);
	}

	/**
	 * process report
	 */
	private function _processReport() {
		$dirname = $this->_mXoopsModule->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		if (!$this->mLog->hasError()) {
			$this->mLog->add(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_MODULE_INSTALLED'), $this->_mXoopsModule->getInfo('name')));
		} else if (is_object($this->_mXoopsModule)) {
			$this->mLog->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_MODULE_INSTALLED'), $this->_mXoopsModule->getInfo('name')));
		} else {
			$this->mLog->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_MODULE_INSTALLED'), 'something'));
		}
	}


	function _processScript() {
		$dirname = $this->_mXoopsModule->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$result = $this->moduleInstall($this->_mXoopsModule);
		if (!$result) {
			$this->mLog->addError(
				XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_MODULE_INSTALLED'), $this->_mXoopsModule->getInfo('name')));
		}
		//mkdir group icon
		$uploadDir = XOOPS_ROOT_PATH . '/uploads/xoonips';
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir);
		}
		if (!is_dir($uploadDir . '/group')) {
			mkdir($uploadDir . '/group');
		}
	}

	/**
	 * execute install
	 *
	 * @return bool
	 */
	public function executeInstall() {
		set_time_limit(240);
		$this->_installTables();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_installModule();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_installTemplates();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_installBlocks();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_installPreferences();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_processScript();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_processReport();
		return true;
	}

	private function moduleInstall($xoopsMod) {
		$mydirname = $xoopsMod->get('dirname');
		$uid = $GLOBALS['xoopsUser']->get('uid');
		$mid = $xoopsMod->get('mid');

		// fix invalid group permissions
		if (!Xoonips_InstallUtils::fixGroupPermissions()) {
			return false;
		}

		// define groups
		$member_handler =& Xoonips_Utils::getXoopsHandler('member');
		$groups = $member_handler->getGroupList();
		$gids = array_keys($groups);

		$ogids = array_diff($gids, array(XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS));
		
		// set module access permission to all known groups
		foreach ($gids as $gid) {
			$right = in_array($gid, $ogids) ? false : true;
			Xoonips_InstallUtils::setModuleReadRight($mid, $gid, $right);
		}

		// set block parameters (read permissions and positions)
		$block_params = array(
			'b_xoonips_quick_search_show' => array(
				'rights' => array(true, true, true, false),
				'positions' => array(true, 0, 10, true)
			),
			'b_xoonips_tree_show' => array(
				'rights' => array(true, true, true, false),
				'positions' => array(true, 0, 20, true)
			),
			'b_xoonips_login_show' => array(
				'rights' => array(true, true, true, false),
				'positions' => array(true, 0, 0, true)
			),
			'b_xoonips_user_show' => array(
				'rights' => array(true, false, true, false),
				'positions' => array(true, 1, 0, true)
			),
			'b_xoonips_itemtypes_show' => array(
				'rights' => array(true, true, true, false),
				'positions' => array(true, 5, 20, false)
			)
		);
		foreach ($block_params as $show_func => $block_param) {
			$bids = Xoonips_InstallUtils::getBlockIds($mid, $show_func);
			foreach ($bids as $bid) {
				// - rights
				$rights = $block_param['rights'];
				list($uright, $gright, $mright, $oright) = $block_param['rights'];
				Xoonips_InstallUtils::setBlockReadRight($bid, XOOPS_GROUP_USERS, $uright);
				Xoonips_InstallUtils::setBlockReadRight($bid, XOOPS_GROUP_ANONYMOUS, $gright);
				foreach ($ogids as $gid) {
					Xoonips_InstallUtils::setBlockReadRight($bid, $gid, $oright);
				}
				// - positions
				list($visible, $side, $weight, $allpage) = $block_param['positions'];
				Xoonips_InstallUtils::setBlockPosition($bid, $visible, $side, $weight);
				Xoonips_InstallUtils::setBlockShowPage($bid, 0, $allpage);
				if ($allpage) {
					// unset top page
					Xoonips_InstallUtils::setBlockShowPage($bid, - 1, false);
				} else {
					Xoonips_InstallUtils::setBlockShowPage($bid, - 1, true);
				}
			}
		}

		// hide 'user' and 'login' blocks
		$sys_blocks = array();
		$sys_blocks[] = array('system', 'b_system_user_show');
		$sys_blocks[] = array('system', 'b_system_login_show');
		if (defined('XOOPS_CUBE_LEGACY')) {
			// for XOOPS Cube Legacy 2.2
			$sys_blocks[] = array('legacy', 'b_legacy_usermenu_show');
			$sys_blocks[] = array('user', 'b_user_login_show');
		}
		foreach ($sys_blocks as $sys_block) {
			list($dirname, $show_func) = $sys_block;
			$sysmid = Xoonips_InstallUtils::getModuleId($dirname);
			if ($sysmid === false) {
				// this case will occur when system module does not installed on
				// XOOPS Cube Legacy 2.1
				continue;
			}
			$bids = Xoonips_InstallUtils::getBlockIds($sysmid, $show_func);
			foreach ($bids as $bid) {
				Xoonips_InstallUtils::setBlockPosition($bid, false, 0, 0);
			}
		}
		
		// omit guest from mainmenu of legacy
		if (defined('XOOPS_CUBE_LEGACY')) {
			$sysmid = Xoonips_InstallUtils::getModuleId('legacy');
			if ($sysmid !== false)  {
				$bids = Xoonips_InstallUtils::getBlockIds($sysmid, 'b_legacy_mainmenu_show');
				foreach ($bids as $bid) {
					Xoonips_InstallUtils::setBlockReadRight($bid, XOOPS_GROUP_ANONYMOUS, false);
				}
			}
		}

		// register my xoonips user information
		if (!Xoonips_InstallUtils::pickupXoopsUser($uid, Xoonips_Enum::USER_CERTIFIED, $mydirname, true)) {
			return false;
		}
		$userBean = Xoonips_BeanFactory::getBean('UsersBean', $mydirname);

		foreach ($userBean->getAllUsers() as $userInfo) {
			if ($userInfo['uid'] != $uid) {
				$activate = $userInfo['level'];
				if ($activate > Xoonips_Enum::USER_CERTIFIED) {
					$activate = Xoonips_Enum::USER_CERTIFIED;
				}
				if (!Xoonips_InstallUtils::pickupXoopsUser($userInfo['uid'], $activate, $mydirname, false)) {
					return false;
				}
			}
		}

		// set notifications
		$uids = array_keys($member_handler->getUsers(null, true));
		$info = $xoopsMod->getInfo('notification');
		$notifications = array();
		foreach ($info['category'] as $category) {
			$events = array();
			foreach ($info['event'] as $event) {
				if ($event['category'] == $category['name']) {
					$events[] = $event['name'];
				}
			}
			$notifications[$category['name']] = $events;
		}
		foreach ($notifications as $category => $events) {
			foreach ($events as $event) {
				// enable event
				Xoonips_InstallUtils::enableNotification($mid, $category, $event);
				// subscribe all notifications to all users
				foreach ($uids as $uid) {
					Xoonips_InstallUtils::subscribeNotification($mid, $uid, $category, $event);
				}
			}
		}
		return true;
	}

}

