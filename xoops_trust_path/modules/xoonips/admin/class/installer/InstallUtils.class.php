<?php

require_once XOONIPS_TRUST_PATH . '/class/core/ImportItemtype.class.php';

/**
 * install utilities class
 */
class Xoonips_InstallUtils {

	/**
	 * install sql automatically
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installSQLAutomatically(&$module, &$log) {
		$dbTypeAliases = array(
			'mysqli' => 'mysql'
		);
		$dirname = $module->get('dirname');
		$trustDirname = $module->getInfo('trust_dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$sqlFileInfo =& $module->getInfo('sqlfile');
		$dbType = (isset($sqlfileInfo[XOOPS_DB_TYPE]) || !isset($dbTypeAliases[XOOPS_DB_TYPE])) ? XOOPS_DB_TYPE : $dbTypeAliases[XOOPS_DB_TYPE];
		if (!isset($sqlFileInfo[$dbType])) {
			return true;
		}
		$sqlFile = $sqlFileInfo[$dbType];
		$sqlFilePath = sprintf('%s/%s/%s', XOOPS_MODULE_PATH, $dirname, $sqlFile);
		if (!file_exists($sqlFilePath))
			$sqlFilePath = sprintf('%s/modules/%s/%s', XOOPS_TRUST_PATH, $trustDirname, $sqlFile);
		require_once XOOPS_MODULE_PATH . '/legacy/admin/class/Legacy_SQLScanner.class.php';
		$scanner = new Legacy_SQLScanner();
		$scanner->setDB_PREFIX(XOOPS_DB_PREFIX);
		$scanner->setDirname($dirname);
		if (!$scanner->loadFile($sqlFilePath)) {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_SQL_FILE_NOT_FOUND'), $sqlFile));
			return false;
		}
		$scanner->parse();
		$root =& XCube_Root::getSingleton();
		$db =& $root->mController->getDB();
		foreach ($scanner->getSQL() as $sql) {
			if (!$db->query($sql)) {
				$log->addError($db->error());
				return false;
			}
		}
		$log->addReport(constant($constpref . '_INSTALL_MSG_DB_SETUP_FINISHED'));
		return true;
	}

	/**
	 * DB query
	 *
	 * @param string $query
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function DBquery($query, &$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		require_once XOOPS_MODULE_PATH . '/legacy/admin/class/Legacy_SQLScanner.class.php';
		$scanner = new Legacy_SQLScanner();
		$scanner->setDB_PREFIX(XOOPS_DB_PREFIX);
		$scanner->setDirname($dirname);
		$scanner->setBuffer($query);
		$scanner->parse();
		$sqls = $scanner->getSQL();
		$root =& XCube_Root::getSingleton();
		$successFlag = true;
		foreach ($sqls as $sql) {
			if ($root->mController->mDB->query($sql)) {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_SQL_SUCCESS'), $sql));
			} else {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_SQL_ERROR'), $sql));
				$successFlag = false;
			}
		}
		return $successFlag;
	}

	/**
	 * replace dirname
	 *
	 * @param string $from
	 * @param string $dirname
	 * @param string $trustDirname
	 * @return {string 'public', string 'trust'}
	 */
	public static function replaceDirname($from, $dirname, $trustDirname = null) {
		if (strpos($from, '{dirname}') === false) {
			return array(
				'public' => $dirname . '_' . $from,
				'trust' => ($trustDirname != null) ? $from : null
			);
		}
		return array(
			'public' => str_replace('{dirname}', $dirname, $from),
			'trust' => ($trustDirname != null) ? str_replace('{dirname}', $trustDirname, $from) : null
		);
	}

	/**
	 * read template file
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @param string $filename
	 * @param bool $isBlock
	 * @return string
	 */
	public static function readTemplateFile($dirname, $trustDirname, $filename, $isBlock = false) {
		$filePath = sprintf('%s/%s/templates/%s%s', XOOPS_MODULE_PATH, $dirname, ($isBlock ? 'blocks/' : ''), $filename);
		if (!file_exists($filePath)) {
			$filePath = sprintf('%s/modules/%s/templates/%s%s', XOOPS_TRUST_PATH, $trustDirname, ($isBlock ? 'blocks/' : ''), $filename);
			if (!file_exists($filePath))
				return false;
		}
		if (!($lines = file($filePath)))
			return false;
		$tplData = '';
		foreach ($lines as $line)
			$tplData .= str_replace("\n", "\r\n", str_replace("\r\n", "\n", $line));
		return $tplData;
	}

	/**
	 * install all of module templates
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 */
	public static function installAllOfModuleTemplates(&$module, &$log) {
		$templates =& $module->getInfo('templates');
		if (is_array($templates) && count($templates) > 0) {
			foreach ($templates as $template)
				self::installModuleTemplate($module, $template, $log);
		}
	}

	/**
	 * install module template
	 *
	 * @param XoopsModule &$module
	 * @param string[] $template
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installModuleTemplate(&$module, $template, &$log) {
		$dirname = $module->get('dirname');
		$trustDirname = $module->getInfo('trust_dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$tplHandler =& Xoonips_Utils::getXoopsHandler('tplfile');
		$filename = self::replaceDirname(trim($template['file']), $dirname, $trustDirname);
		$tplData = self::readTemplateFile($dirname, $trustDirname, $filename['trust']);
		if ($tplData == false)
			return false;
		$tplFile =& $tplHandler->create();
		$tplFile->set('tpl_refid', $module->get('mid'));
		$tplFile->set('tpl_lastimported', 0);
		$tplFile->set('tpl_lastmodified', time());
		$tplFile->set('tpl_type', (substr($filename['trust'], -4) == '.css') ? 'css' : 'module');
		$tplFile->set('tpl_source', $tplData);
		$tplFile->set('tpl_module', $dirname);
		$tplFile->set('tpl_tplset', 'default');
		$tplFile->set('tpl_file', $filename['public']);
		$tplFile->set('tpl_desc', isset($template['description']) ? $template['description'] : '');
		if ($tplHandler->insert($tplFile)) {
			$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_TPL_INSTALLED'), $filename['public']));
		} else {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_TPL_INSTALLED'), $filename['public']));
			return false;
		}
		return true;
	}

	/**
	 * uninstall all of module templates
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @param bool $defaultOnly
	 */
	public static function uninstallAllOfModuleTemplates(&$module, &$log, $defaultOnly = true) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$tplHandler =& Xoonips_Utils::getXoopsHandler('tplfile');
		$delTemplates =& $tplHandler->find($defaultOnly ? 'default' : null, null, $module->get('mid'));
		if (is_array($delTemplates) && count($delTemplates) > 0) {
			$xoopsTpl = new XoopsTpl();
			$xoopsTpl->clear_cache(null, 'mod_' . $dirname);
			foreach ($delTemplates as $tpl) {
				if (!$tplHandler->delete($tpl))
					$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_TPL_UNINSTALLED'), $tpl->get('tpl_file')));
			}
		}
	}

	/**
	 * install all of blocks
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installAllOfBlocks(&$module, &$log) {
		$blocks =& $module->getInfo('blocks');
		if (is_array($blocks) && count($blocks) > 0) {
			foreach ($blocks as $num => $block) {
				if (!isset($block['func_num']));
					$block['func_num'] = $num;
				$newBlock =& self::createBlockByInfo($module, $block);
				self::installBlock($module, $newBlock, $block, $log);
			}
		}
		return true;
	}

	/**
	 * create block by info
	 *
	 * @param XoopsModule &$module
	 * @param string[] $block
	 * @return XoopsBlock
	 */
	public static function &createBlockByInfo(&$module, $block) {
		$dirname = $module->get('dirname');
		$visible = isset($block['visible']) ? $block['visible'] : (isset($block['visible_any']) ? $block['visible_any'] : 0);
		$filename = isset($block['template']) ? self::replaceDirname($block['template'], $dirname) : null;
		$blockHandler =& Xoonips_Utils::getXoopsHandler('block');
		$blockObj =& $blockHandler->create();
		$blockObj->set('mid', $module->get('mid'));
		$blockObj->set('options', isset($block['options']) ? $block['options'] : null);
		$blockObj->set('name', $block['name']);
		$blockObj->set('title', $block['name']);
		$blockObj->set('block_type', 'M');
		$blockObj->set('c_type', '1');
		$blockObj->set('isactive', 1);
		$blockObj->set('dirname', $dirname);
		$blockObj->set('func_file', $block['file']);
		if (isset($block['class'])) {
			// XCL
			$blockObj->set('show_func', 'cl::' . $block['class']);
		} else {
			// X20
			$blockObj->set('show_func', $block['show_func']);
			if (isset($block['edit_func']))
				$blockObj->set('edit_func', $block['edit_func']);
		}
		$blockObj->set('template', $filename['public']);
		$blockObj->set('last_modified', time());
		$blockObj->set('visible', $visible);
		$blockObj->set('func_num', intval($block['func_num']));
		return $blockObj;
	}

	/**
	 * install block
	 *
	 * @param XoopsModule &$module
	 * @param XoopsBlock &$blockObj
	 * @param string[] &$block
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installBlock(&$module, &$blockObj, &$block, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$isNew = $blockObj->isNew();
		$blockHandler =& Xoonips_Utils::getXoopsHandler('block');
		$autoLink = isset($block['show_all_module']) ? $block['show_all_module'] : false;
		if (!$blockHandler->insert($blockObj, $autoLink)) {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_INSTALLED'), $blockObj->get('name')));
			return false;
		}
		$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_BLOCK_INSTALLED'), $blockObj->get('name')));
		self::installBlockTemplate($blockObj, $module, $log);
		if (!$isNew)
			return true;
		if ($autoLink) {
			$sql = sprintf('INSERT INTO `%s` (`block_id`, `module_id`) VALUES (%d, 0);', $blockHandler->db->prefix('block_module_link'), $blockObj->get('bid'));
			if (!$blockHandler->db->query($sql))
				$log->addWarning(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_COULD_NOT_LINK'), $blockObj->get('name')));
		}
		$gpermHandler =& Xoonips_Utils::getXoopsHandler('groupperm');
		$perm =& $gpermHandler->create();
		$perm->set('gperm_itemid', $blockObj->get('bid'));
		$perm->set('gperm_name', 'block_read');
		$perm->set('gperm_modid', 1);
		if (isset($block['visible_any']) && $block['visible_any']) {
			$memberHandler =& Xoonips_Utils::getXoopsHandler('member');
			$groups =& $memberHandler->getGroups();
			foreach ($groups as $group) {
				$perm->set('gperm_groupid', $group->get('groupid'));
				$perm->setNew();
				if (!$gpermHandler->insert($perm))
					$log->addWarning(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_PERM_COULD_NOT_SET'), $blockObj->get('name')));
			}
		} else {
			foreach (array(XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS) as $group) {
				$perm->set('gperm_groupid', $group);
				$perm->setNew();
				if (!$gpermHandler->insert($perm))
					$log->addWarning(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_PERM_SET'), $blockObj->get('name')));
			}
		}
		return true;
	}

	/**
	 * install block template
	 *
	 * @param XoopsBlock &$block
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installBlockTemplate(&$block, &$module, &$log) {
		$dirname = $module->get('dirname');
		$trustDirname = $module->getInfo('trust_dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		if ($block->get('template') == null)
			return true;
		$info =& $module->getInfo('blocks');
		$filename = self::replaceDirname($info[$block->get('func_num')]['template'], $dirname, $trustDirname);
		$tplHandler =& Xoonips_Utils::getXoopsHandler('tplfile');
		$cri = new CriteriaCompo();
		$cri->add(new Criteria('tpl_type', 'block'));
		$cri->add(new Criteria('tpl_tplset', 'default'));
		$cri->add(new Criteria('tpl_module', $dirname));
		$cri->add(new Criteria('tpl_file', $filename['public']));
		$tpls =& $tplHandler->getObjects($cri);
		if (count($tpls) > 0) {
			$tplFile =& $tpls[0];
		} else {
			$tplFile =& $tplHandler->create();
			$tplFile->set('tpl_refid', $block->get('bid'));
			$tplFile->set('tpl_tplset', 'default');
			$tplFile->set('tpl_file', $filename['public']);
			$tplFile->set('tpl_module', $dirname);
			$tplFile->set('tpl_type', 'block');
			// $tplFile->set('tpl_desc', $block->get('description'));
			$tplFile->set('tpl_lastimported', 0);
		}
		$tplSource = self::readTemplateFile($dirname, $trustDirname, $filename['trust'], true);
		$tplFile->set('tpl_source', $tplSource);
		$tplFile->set('tpl_lastmodified', time());
		if (!$tplHandler->insert($tplFile)) {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_TPL_INSTALLED'), $filename['public']));
			return false;
		}
		$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_BLOCK_TPL_INSTALLED'), $filename['public']));
		return true;
	}

	/**
	 * uninstall all of blocks
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function uninstallAllOfBlocks(&$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$successFlag = true;
		$blockHandler =& Xoonips_Utils::getXoopsHandler('block');
		$gpermHandler =& Xoonips_Utils::getXoopsHandler('groupperm');
		$cri = new Criteria('mid', $module->get('mid'));
		$blocks =& $blockHandler->getObjectsDirectly($cri);
		foreach ($blocks as $block) {
			if ($blockHandler->delete($block)) {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_BLOCK_UNINSTALLED'), $block->get('name')));
			} else {
				$log->addWarning(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_UNINSTALLED'), $block->get('name')));
				$successFlag = false;
			}
			$cri = new CriteriaCompo();
			$cri->add(new Criteria('gperm_name', 'block_read'));
			$cri->add(new Criteria('gperm_itemid', $block->get('bid')));
			$cri->add(new Criteria('gperm_modid', 1));
			if (!$gpermHandler->deleteAll($cri)) {
				$log->addWarning(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_PERM_DELETE'), $block->get('name')));
				$successFlag = false;
			}
		}
		return $successFlag;
	}

	/**
	 * smart update all of blocks
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function smartUpdateAllOfBlocks(&$module, &$log) {
		$dirname = $module->get('dirname');
		$fileReader = new Legacy_ModinfoX2FileReader($dirname);
		$dbReader = new Legacy_ModinfoX2DBReader($dirname);
		$blocks =& $dbReader->loadBlockInformations();
		$blocks->update($fileReader->loadBlockInformations());
		$successFlag = true;
		foreach ($blocks->mBlocks as $block) {
			switch ($block->mStatus) {
			case LEGACY_INSTALLINFO_STATUS_LOADED:
				$successFlag &= self::updateBlockTemplateByInfo($block, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_UPDATED:
				$successFlag &= self::updateBlockByInfo($block, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_NEW:
				$successFlag &= self::installBlockByInfo($block, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_DELETED:
				$successFlag &= self::uninstallBlockByFuncNum($block->mFuncNum, $module, $log);
				break;
			default:
				break;
			}
		}
		return $successFlag;
	}

	/**
	 * update block template by info
	 *
	 * @param Legacy_BlockInformation &$info
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function updateBlockTemplateByInfo(&$info, &$module, &$log) {
		$dirname = $module->get('dirname');
		$blockHandler = Xoonips_Utils::getModuleHandler('newblocks', 'legacy');
		$cri = new CriteriaCompo();
		$cri->add(new Criteria('dirname', $dirname));
		$cri->add(new Criteria('func_num', $info->mFuncNum));
		$blocks =& $blockHandler->getObjects($cri);
		$successFlag = true;
		foreach ($blocks as $block) {
			$successFlag &= self::uninstallBlockTemplate($block, $module, $log, true);
			$successFlag &= self::installBlockTemplate($block, $module, $log);
		}
		return $successFlag;
	}

	/**
	 * update block by info
	 *
	 * @param Legacy_BlockInformation &$info
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function updateBlockByInfo(&$info, &$module, &$log) {
		$dirname = $module->get('dirname');
		$trustDirname = $module->getInfo('trust_dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$blockHandler = Xoonips_Utils::getModuleHandler('newblocks', 'legacy');
		$cri = new CriteriaCompo();
		$cri->add(new Criteria('dirname', $dirname));
		$cri->add(new Criteria('func_num', $info->mFuncNum));
		$blocks =& $blockHandler->getObjects($cri);
		$successFlag = true;
		foreach ($blocks as $block) {
			$filename = self::replaceDirname($info->mTemplate, $dirname, $trustDirname);
			$block->set('options', $info->mOptions);
			$block->set('name', $info->mName);
			$block->set('func_file', $info->mFuncFile);
			$block->set('show_func', $info->mShowFunc);
			// $block->set('edit_func', $info->mEditFunc);
			$block->set('template', $filename['public']);
			if ($blockHandler->insert($block)) {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_BLOCK_UPDATED'), $block->get('name')));
			} else {
				$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_UPDATED'), $block->get('name')));
				$successFlag = false;
			}
			$successFlag &= self::uninstallBlockTemplate($block, $module, $log, true);
			$successFlag &= self::installBlockTemplate($block, $module, $log);
		}
		return $successFlag;
	}

	/**
	 * install block by info
	 *
	 * @param Legacy_BlockInformation &$info
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installBlockByInfo(&$info, &$module, &$log) {
		$dirname = $module->get('dirname');
		$trustDirname = $module->getInfo('trust_dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$filename = self::replaceDirname($info->mTemplate, $dirname, $trustDirname);
		$blockHandler =& Xoonips_Utils::getXoopsHandler('block');
		$block =& $blockHandler->create();
		$block->set('mid', $module->get('mid'));
		$block->set('func_num', $info->mFuncNum);
		$block->set('options', $info->mOptions);
		$block->set('name', $info->mName);
		$block->set('title', $info->mName);
		$block->set('dirname', $dirname);
		$block->set('func_file', $info->mFuncFile);
		$block->set('show_func', $info->mShowFunc);
		// $block->set('edit_func', $info->mEditFunc);
		$block->set('template', $filename['public']);
		$block->set('block_type', 'M');
		$block->set('c_type', 1);
		if (!$blockHandler->insert($block)) {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_INSTALLED'), $block->get('name')));
			return false;
		}
		$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_BLOCK_INSTALLED'), $block->get('name')));
		self::installBlockTemplate($block, $module, $log);
		return true;
	}

	/**
	 * uninstall block by func number
	 *
	 * @param int $func_num
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function uninstallBlockByFuncNum($func_num, &$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$blockHandler = Xoonips_Utils::getModuleHandler('newblocks', 'legacy');
		$cri = new CriteriaCompo();
		$cri->add(new Criteria('dirname', $dirname));
		$cri->add(new Criteria('func_num', $func_num));
		$blocks =& $blockHandler->getObjects($cri);
		$successFlag = true;
		foreach ($blocks as $block) {
			if ($blockHandler->delete($block)) {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_BLOCK_UNINSTALLED'), $block->get('name')));
			} else {
				$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_UNINSTALLED'), $block->get('name')));
				$successFlag = false;
			}
		}
		return $successFlag;
	}

	/**
	 * uninstall block template
	 *
	 * @param XoopsBlock &$block
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @param bool $defaultOnly
	 * @return bool
	 */
	public static function uninstallBlockTemplate(&$block, &$module, &$log, $defaultOnly = false) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$tplHandler =& Xoonips_Utils::getXoopsHandler('tplfile');
		$delTemplates =& $tplHandler->find($defaultOnly ? 'default' : null, 'block', $module->get('mid'), $dirname, $block->get('template'));
		if (is_array($delTemplates) && count($delTemplates) > 0) {
			foreach ($delTemplates as $tpl) {
				if (!$tplHandler->delete($tpl))
					$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_TPL_UNINSTALLED'), $tpl->get('tpl_file')));
			}
		}
		$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_BLOCK_TPL_UNINSTALLED'), $block->get('template')));
		return true;
	}

	/**
	 * install all of configs
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installAllOfConfigs(&$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$successFlag = true;
		$configHandler =& Xoonips_Utils::getXoopsHandler('config');
		$fileReader = new Legacy_ModinfoX2FileReader($dirname);
		$preferences = $fileReader->loadPreferenceInformations();
		// Preferences
		foreach ($preferences->mPreferences as $info)
			$successFlag &= self::installConfigByInfo($info, $module, $log);
		// Comments
		foreach ($preferences->mComments as $info)
			$successFlag &= self::installConfigByInfo($info, $module, $log);
		// Notifications
		foreach ($preferences->mNotifications as $info)
			$successFlag &= self::installConfigByInfo($info, $module, $log);
		return $successFlag;
	}

	/**
	 * install config by info
	 *
	 * @param Legacy_PreferenceInformation &$info
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installConfigByInfo(&$info, &$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$configHandler =& Xoonips_Utils::getXoopsHandler('config');
		$config =& $configHandler->createConfig();
		$config->set('conf_modid', $module->get('mid'));
		$config->set('conf_catid', 0);
		$config->set('conf_name', $info->mName);
		$config->set('conf_title', $info->mTitle);
		$config->set('conf_desc', $info->mDescription);
		$config->set('conf_formtype', $info->mFormType);
		$config->set('conf_valuetype', $info->mValueType);
		$config->setConfValueForInput($info->mDefault);
		$config->set('conf_order', $info->mOrder);
		if (count($info->mOption->mOptions) > 0) {
			foreach ($info->mOption->mOptions as $opt) {
				$option = $configHandler->createConfigOption();
				$option->set('confop_name', $opt->mName);
				$option->set('confop_value', $opt->mValue);
				$config->setConfOptions($option);
				unset($option);
			}
		}
		$successFlag = true;
		if ($configHandler->insertConfig($config)) {
			$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_CONFIG_ADDED'), $config->get('conf_name')));
		} else {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_CONFIG_ADDED'), $config->get('conf_name')));
			$successFlag = false;
		}
		return $successFlag;
	}

	/**
	 * uninstall all of configs
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function uninstallAllOfConfigs(&$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$configHandler =& Xoonips_Utils::getXoopsHandler('config');
		$configs =& $configHandler->getConfigs(new Criteria('conf_modid', $module->get('mid')));
		if (count($configs) == 0)
			return true;
		$sucessFlag = true;
		foreach ($configs as $config) {
			if ($configHandler->deleteConfig($config)) {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_CONFIG_DELETED'), $config->get('conf_name')));
			} else {
				$log->addWarning(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_CONFIG_DELETED'), $config->get('conf_name')));
				$sucessFlag = false;
			}
		}
		return $sucessFlag;
	}

	/**
	 * uninstall config by order
	 *
	 * @param int $order
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function uninstallConfigByOrder($order, &$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$configHandler =& Xoonips_Utils::getXoopsHandler('config');
		$cri = new CriteriaCompo();
		$cri->add(new Criteria('conf_modid', $module->get('mid')));
		$cri->add(new Criteria('conf_catid', 0));
		$cri->add(new Criteria('conf_order', $order));
		$configs = $configHandler->getConfigs($cri);
		$successFlag = true;
		foreach ($configs as $config) {
			if ($configHandler->deleteConfig($config)) {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_CONFIG_DELETED'), $config->get('conf_name')));
			} else {
				$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_CONFIG_DELETED'), $config->get('conf_name')));
				$successFlag = false;
			}
		}
		return $successFlag;
	}

	/**
	 * smart update all of configs
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 */
	public static function smartUpdateAllOfConfigs(&$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$fileReader = new Legacy_ModinfoX2FileReader($dirname);
		$dbReader = new Legacy_ModinfoX2DBReader($dirname);
		$configs =& $dbReader->loadPreferenceInformations();
		$configs->update($fileReader->loadPreferenceInformations());
		$successFlag = true;
		// Preferences
		foreach ($configs->mPreferences as $info) {
			switch ($info->mStatus) {
			case LEGACY_INSTALLINFO_STATUS_UPDATED:
				$successFlag &= self::updateConfigByInfo($info, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_ORDER_UPDATED:
				$successFlag &= self::updateConfigOrderByInfo($info, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_NEW:
				$successFlag &= self::installConfigByInfo($info, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_DELETED:
				$successFlag &= self::uninstallConfigByOrder($info->mOrder, $module, $log);
				break;
			default:
				break;
			}
		}
		// Comments
		foreach ($configs->mComments as $info) {
			switch ($info->mStatus) {
			case LEGACY_INSTALLINFO_STATUS_UPDATED:
				$successFlag &= self::updateConfigByInfo($info, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_ORDER_UPDATED:
				$successFlag &= self::updateConfigOrderByInfo($info, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_NEW:
				$successFlag &= self::installConfigByInfo($info, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_DELETED:
				$successFlag &= self::uninstallConfigByOrder($info->mOrder, $module, $log);
				break;
			default:
				break;
			}
		}
		// Notifications
		foreach ($configs->mNotifications as $info) {
			switch ($info->mStatus) {
			case LEGACY_INSTALLINFO_STATUS_UPDATED:
				$successFlag &= self::updateConfigByInfo($info, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_ORDER_UPDATED:
				$successFlag &= self::updateConfigOrderByInfo($info, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_NEW:
				$successFlag &= self::installConfigByInfo($info, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_DELETED:
				$successFlag &= self::uninstallConfigByOrder($info->mOrder, $module, $log);
				break;
			default:
				break;
			}
		}
	}

	/**
	 * update config by info
	 *
	 * @param Legacy_PreferenceInformation &$info
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function updateConfigByInfo(&$info, &$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$configHandler =& Xoonips_Utils::getXoopsHandler('config');
		$cri = new CriteriaCompo();
		$cri->add(new Criteria('conf_modid', $module->get('mid')));
		$cri->add(new Criteria('conf_catid', 0));
		$cri->add(new Criteria('conf_name', $info->mName));
		$configs =& $configHandler->getConfigs($cri);
		if (!(count($configs) > 0 && is_object($configs[0]))) {
			$log->addError(constant($constpref . '_INSTALL_ERROR_CONFIG_NOT_FOUND'));
			return false;
		}
		$config =& $configs[0];
		$config->set('conf_title', $info->mTitle);
		$config->set('conf_desc', $info->mDescription);
		if ($config->get('conf_formtype') != $info->mFormType && $config->get('conf_valuetype') != $info->mValueType) {
			$config->set('conf_formtype', $info->mFormType);
			$config->set('conf_valuetype', $info->mValueType);
			$config->setConfValueForInput($info->mDefault);
		} else {
			$config->set('conf_formtype', $info->mFormType);
			$config->set('conf_valuetype', $info->mValueType);
		}
		$config->set('conf_order', $info->mOrder);
		$options =& $configHandler->getConfigOptions(new Criteria('conf_id', $config->get('conf_id')));
		if (is_array($options)) {
			foreach ($options as $opt)
				$configHandler->_oHandler->delete($opt);
		}
		if (count($info->mOption->mOptions) > 0) {
			foreach ($info->mOption->mOptions as $opt) {
				$option =& $configHandler->createConfigOption();
				$option->set('confop_name', $opt->mName);
				$option->set('confop_value', $opt->mValue);
				$option->set('conf_id', $option->get('conf_id'));
				$config->setConfOptions($option);
				unset($option);
			}
		}
		if (!$configHandler->insertConfig($config)) {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_CONFIG_UPDATED'), $config->get('conf_name')));
			return false;
		}
		$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_CONFIG_UPDATED'), $config->get('conf_name')));
		return true;
	}

	/**
	 * update config order by info
	 *
	 * @param Legacy_PreferenceInformation &$info
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function updateConfigOrderByInfo(&$info, &$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$configHandler =& Xoonips_Utils::getXoopsHandler('config');
		$cri = new CriteriaCompo();
		$cri->add(new Criteria('conf_modid', $module->get('mid')));
		$cri->add(new Criteria('conf_catid', 0));
		$cri->add(new Criteria('conf_name', $info->mName));
		$configs =& $configHandler->getConfigs($cri);
		if (!(count($configs) > 0 && is_object($configs[0]))) {
			$log->addError(constant($constpref . '_INSTALL_ERROR_CONFIG_NOT_FOUND'));
			return false;
		}
		$config =& $configs[0];
		$config->set('conf_order', $info->mOrder);
		if (!$configHandler->insertConfig($config)) {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_CONFIG_UPDATED'), $config->get('conf_name')));
			return false;
		}
		return true;
	}

	public static function deleteAllOfNotifications(&$module, &$log) {
		Legacy_ModuleInstallUtils::deleteAllOfNotifications($module, $log);
	}

	public static function deleteAllOfComments(&$module, &$log) {
		Legacy_ModuleInstallUtils::deleteAllOfComments($module, $log);
	}

	/**
	 * fix invalid xoops group permissions
	 *  - refer: http://www.xugj.org/modules/d3forum/index.php?topic_id=791
	 *
	 * @access public
	 * @return bool false if failure
	 */
	public static function fixGroupPermissions() {
		global $xoopsDB;
		// get invalid group ids
		$table = $xoopsDB->prefix('group_permission');
		$table2 = $xoopsDB->prefix('groups');
		$sql = sprintf('SELECT DISTINCT `gperm_groupid` FROM `%s` LEFT JOIN `%s` ON `%s`.`gperm_groupid`=`%s`.`groupid` WHERE `gperm_modid`=1 AND `groupid` IS NULL', $table, $table2, $table, $table2);
		$result = $xoopsDB->query($sql);
		if (!$result)
			return false;
		$gids = array();
		while ($myrow = $xoopsDB->fetchArray($result))
			$gids[] = $myrow['gperm_groupid'];
		$xoopsDB->freeRecordSet($result);
		// remove all invalid group id entries
		if (count($gids) != 0) {
			$sql = sprintf('DELETE FROM `%s` WHERE `gperm_groupid` IN (%s) AND `gperm_modid`=1', $table, implode(',', $gids));
			$result = $xoopsDB->query($sql);
			if (!$result)
				return false;
		}
		return true;
	}

	/**
	 * create xoops group
	 *
	 * @access public
	 * @param string $name group name
	 * @param string $description group description
	 * @return int created group id
	 */
	public static function createGroup($name, $description) {
		$member_handler =& Xoonips_Utils::getXoopsHandler('member');
		$group =& $member_handler->createGroup();
		$group->set('name', $name);
		$group->set('description', $description);
		$ret = $member_handler->insertGroup($group);
		if ($ret == false)
			return false;
		$gid = $group->get('groupid');
		return $gid;
	}

	/**
	 * delete xoops group
	 *
	 * @access public
	 * @param string $name group name
	 * @param string $description group description
	 * @return int created group id
	 */
	public static function deleteGroup($name, $description) {
		$cri = new CriteriaCompo();
		$cri->add(new Criteria('name', $name));
		$cri->add(new Criteria('description', $description));
		$grouphandler =& Xoonips_Utils::getXoopsHandler('group');
		$memberhandler =& Xoonips_Utils::getXoopsHandler('member');
		$grouppermhandler =& Xoonips_Utils::getXoopsHandler('groupperm');
		foreach ($grouphandler->getObjects($cri) as $group) {
			if (!$memberhandler->delete($group))
				return false;
			if (!$grouppermhandler->deleteByGroup($group->get('groupid')))
				return false;
		}
		return true;
	}

	/**
	 * add user to xoops group
	 *
	 * @access public
	 * @param int $gid group id
	 * @param int $uid user id
	 * @return bool false if failure
	 */
	public static function addUserToXoopsGroup($gid, $uid) {
		$member_handler =& Xoonips_Utils::getXoopsHandler('member');
		if (!$member_handler->addUserToGroup($gid, $uid))
			return false;
		$myuid = $GLOBALS['xoopsUser']->get('uid');
		if ($myuid == $uid) {
			// update group cache and session
			$mygroups = $member_handler->getGroupsByUser($uid);
			$GLOBALS['xoopsUser']->setGroups($mygroups);
			if (isset($_SESSION['xoopsUserGroups']))
				$_SESSION['xoopsUserGroups'] = $mygroups;
		}
		return true;
	}

	/**
	 * set module read right
	 *
	 * @access public
	 * @param int $mid module id
	 * @param int $gid group id
	 * @param int $right has read right ?
	 * @return bool false if failure
	 */
	public static function setModuleReadRight($mid, $gid, $right) {
		return self::setReadRight(true, $mid, $gid, $right);
	}

	/**
	 * set block read right
	 *
	 * @access public
	 * @param int $bid block id
	 * @param int $gid group id
	 * @param int $right has read right ?
	 * @return bool false if failure
	 */
	public static function setBlockReadRight($bid, $gid, $right) {
		return self::setReadRight(false, $bid, $gid, $right);
	}

	/**
	 * set xoops module/block read right
	 *
	 * @access private
	 * @param bool $is_module true is module, false is block
	 * @param int $iid module id or block id
	 * @param int $gid group id
	 * @param bool $right has read right?
	 * @return bool false if failure
	 */
	private static function setReadRight($is_module, $iid, $gid, $right) {
		$name = $is_module ? 'module_read' : 'block_read';
		$criteria = new CriteriaCompo(new Criteria('gperm_name', $name));
		$criteria->add(new Criteria('gperm_groupid', $gid));
		$criteria->add(new Criteria('gperm_itemid', $iid));
		$criteria->add(new Criteria('gperm_modid', 1));
		$gperm_handler =& Xoonips_Utils::getXoopsHandler('groupperm');
		$gperm_objs =& $gperm_handler->getObjects($criteria);
		if (count($gperm_objs) > 0) {
			// already exists
			$gperm_obj = $gperm_objs[0];
			if (!$right)
				$gperm_handler->delete($gperm_obj);
		} else {
			// not found
			if ($right)
				$gperm_handler->addRight($name, $iid, $gid);
		}
		return true;
	}

	/**
	 * set xoops module admin right
	 *
	 * @access public
	 * @param int $mid module id
	 * @param int $gid group id
	 * @return bool false if failure
	 */
	public static function setAdminRight($mid, $gid) {
		$criteria = new CriteriaCompo(new Criteria('gperm_name', 'module_admin'));
		$criteria->add(new Criteria('gperm_groupid', $gid));
		$criteria->add(new Criteria('gperm_itemid', $mid));
		$criteria->add(new Criteria('gperm_modid', 1));
		$gperm_handler =& Xoonips_Utils::getXoopsHandler('groupperm');
		$gperm_objs =& $gperm_handler->getObjects($criteria);
		if (count($gperm_objs) == 0)
			$gperm_handler->addRight('module_admin', $mid, $gid);
		return true;
	}

	/**
	 * get module block ids
	 *
	 * @access public
	 * @param int $mid block id
	 * @param int $show_func show function name
	 * @return array block ids
	 */
	public static function getBlockIds($mid, $show_func) {
		global $xoopsDB;
		$table = $xoopsDB->prefix('newblocks');
		$sql = sprintf('SELECT bid FROM `%s` WHERE `mid`=%u AND `show_func`=\'%s\'', $table, $mid, addslashes($show_func));
		$result = $xoopsDB->query($sql);
		if (!$result)
			return false;
		$ret = array();
		while ($myrow = $xoopsDB->fetchArray($result))
			$ret[] = $myrow['bid'];
		$xoopsDB->freeRecordSet($result);
		return $ret;
	}

	/**
	 * set block position
	 *
	 * @access public
	 * @param int $bid block id
	 * @param bool $visible visible flag
	 * @param int $side
	 *	0: sideblock - left
	 *	1: sideblock - right
	 *	2: sideblock - left and right
	 *	3: centerblock - left
	 *	4: centerblock - right
	 *	5: centerblock - center
	 *	6: centerblock - left, right, center
	 * @param int $weight weight
	 * @return bool false if failure
	 */
	public static function setBlockPosition($bid, $visible, $side, $weight) {
		$block = new XoopsBlock();
		$block->load($bid);
		if (!is_null($visible))
			$block->set('visible', $visible ? 1 : 0);
		if (!is_null($side))
			$block->set('side', $side);
		if (!is_null($weight))
			$block->set('weight', $weight);
		return $block->store();
	}

	/**
	 * set block show page
	 *
	 * @access public
	 * @param int $bid block id
	 * @param int $mid
	 *     -1 : top page
	 *      0 : all pages
	 *    >=1 : module id
	 * @param bool $is_show
	 * @return bool false if failure
	 */
	public static function setBlockShowPage($bid, $mid, $is_show) {
		global $xoopsDB;
		$table = $xoopsDB->prefix('block_module_link');
		// check current status
		$sql = sprintf('SELECT `block_id`, `module_id` FROM `%s` WHERE `block_id`=%u AND `module_id`=%d', $table, $bid, $mid);
		if (!$result = $xoopsDB->query($sql)) {
			return false;
		}
		$count = $xoopsDB->getRowsNum($result);
		$xoopsDB->freeRecordSet($result);
		if ($count == 0) {
			// not exists
			if ($is_show) {
				$sql = sprintf('INSERT INTO `%s` (`block_id`, `module_id`) VALUES (%u, %d)', $table, $bid, $mid);
				if (!$result = $xoopsDB->query($sql))
					return false;
			}
		} else {
			// already exists
			if (!$is_show) {
				$sql = sprintf('DELETE FROM `%s` WHERE `block_id`=%u AND `module_id`=%d', $table, $bid, $mid);
				if (!$result = $xoopsDB->query($sql))
					return false;
			}
		}
		return true;
	}

	/**
	 * get module id
	 *
	 * @access public
	 * @param string $dirname module directory name
	 * @return int module id
	 */
	public static function getModuleId($dirname) {
		$module_handler =& Xoonips_Utils::getXoopsHandler('module');
		$module =& $module_handler->getByDirname($dirname);
		if (!is_object($module))
			return false;
		$mid = $module->get('mid');
		return $mid;
	}

	/**
	 * set start module
	 *
	 * @access public
	 * @param string $dirname module directory name, '--' means no module.
	 * @return bool false if failure
	 */
	public static function setStartupPageModule($dirname) {
		$config_handler =& Xoonips_Utils::getXoopsHandler('config');
		$criteria = new CriteriaCompo(new Criteria('conf_modid', 0));
		$criteria->add(new Criteria('conf_catid', XOOPS_CONF));
		$criteria->add(new Criteria('conf_name', 'startpage'));
		$configs =& $config_handler->getConfigs($criteria);
		if (count($configs) != 1)
			return false;
		list($config) = $configs;
		$config->setConfValueForInput($dirname);
		return $config_handler->insertConfig($config);
	}

	/**
	 * enable xoops notificaiton
	 *
	 * @access public
	 * @param string $mid module id
	 * @param string $category
	 * @param string $event
	 * @return bool false if failure
	 */
	public static function enableNotification($mid, $category, $event) {
		global $xoopsDB;
		$config_handler =& Xoonips_Utils::getXoopsHandler('config');
		$criteria = new CriteriaCompo();
		$criteria->add(new Criteria('conf_name', 'notification_events'));
		$criteria->add(new Criteria('conf_modid', $mid));
		$criteria->add(new Criteria('conf_catid', 0));
		$config_items = $config_handler->getConfigs($criteria);
		if (count($config_items) != 1) {
			return false;
		} else {
			$config_item = $config_items[0];
			$option_value = $category.'-'.$event;
			$option_values = $config_item->getConfValueForOutput();
			if (!in_array($option_value, $option_values)) {
				$option_values[] = $option_value;
				$config_item->setConfValueForInput($option_values);
				$config_item_handler = new XoopsConfigItemHandler($xoopsDB);
				$config_item_handler->insert($config_item);
			}
		}
		return true;
	}

	/**
	 * subscribe user to xoops notificaiton
	 *
	 * @access public
	 * @param string $mid module id
	 * @param string $uid user id
	 * @param string $category
	 * @param string $event
	 * @return bool false if failure
	 */
	public static function subscribeNotification($mid, $uid, $category, $event) {
		$notification_handler =& Xoonips_Utils::getXoopsHandler('notification');
		$notification_handler->subscribe($category, 0, $event, null, $mid, $uid);
		return true;
	}

	/**
	 * XOOPS user pickup
	 *
	 * @access public
	 * @param int uid user id
	 * @param bool is_certified initial certification state
	 * @param string dirname
	 * @return bool false if failure
	 */
	public static function pickupXoopsUser($uid, $activate, $dirname, $isModerator) {
		// create user root index
		$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $dirname);
		$index_id = $indexBean->insertPrivateIndex($uid);
		if ($index_id === false)
			return false;
		// set xoonips user information
		$usersBean = Xoonips_BeanFactory::getBean('UsersBean', $dirname);
		$userInfo = $usersBean->getUserBasicInfo($uid);
		if ($userInfo == false)
			return false;
		$userInfo['activate'] = $activate;
		$userInfo['index_id'] = $index_id;
		if ($isModerator) {
			$userInfo['index_number_limit'] = Xoonips_Utils::getXooNIpsConfig($dirname, 'private_index_number_limit');
			$userInfo['item_number_limit'] = Xoonips_Utils::getXooNIpsConfig($dirname, 'private_item_number_limit');
			$userInfo['item_storage_limit'] = Xoonips_Utils::getXooNIpsConfig($dirname, 'private_item_storage_limit');
		}
		// record event logs
		$log = Xoonips_BeanFactory::getBean('EventLogBean', $dirname);
		$log->recordRequestInsertAccountEvent($uid);
		if ($activate == Xoonips_Enum::USER_CERTIFIED)
			$log->recordCertifyAccountEvent($uid);
		return true;
	}

	/**
	 * install sql alter tables
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installSQLAlterTables(&$module, &$log) {
		$dirname = $module->get('dirname');
		$trustDirname = $module->getInfo('trust_dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$sqlFileInfo =& $module->getInfo('sqlfile');
		if (!isset($sqlFileInfo['mysql_alter']))
			return true;
		$sqlFile = $sqlFileInfo['mysql_alter'];
		// Alter Check
		$groupbean = Xoonips_BeanFactory::getBean('GroupsBean', $dirname);
		$ret = $groupbean->alterCheck();
		if ($ret)
			return true;
		$sqlFilePath = sprintf('%s/%s/%s', XOOPS_MODULE_PATH, $dirname, $sqlFile);
		if (!file_exists($sqlFilePath))
			$sqlFilePath = sprintf('%s/modules/%s/%s', XOOPS_TRUST_PATH, $trustDirname, $sqlFile);
		require_once XOOPS_MODULE_PATH . '/legacy/admin/class/Legacy_SQLScanner.class.php';
		$scanner = new Legacy_SQLScanner();
		$scanner->setDB_PREFIX(XOOPS_DB_PREFIX);
		$scanner->setDirname($dirname);
		if (!$scanner->loadFile($sqlFilePath)) {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_SQL_FILE_NOT_FOUND'), $sqlFile));
			return false;
		}
		$scanner->parse();
		$root =& XCube_Root::getSingleton();
		$db =& $root->mController->getDB();
		foreach ($scanner->getSQL() as $sql) {
			if (!$db->query($sql)) {
				$log->addError($db->error());
				return false;
			}
		}
		$log->addReport(constant($constpref . '_INSTALL_MSG_DB_SETUP_FINISHED'));
		return true;
	}

	/**
	 * install data automatically
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installDataAutomatically(&$module, &$log) {
		$dirname = $module->get('dirname');
		$trustDirname = $module->getInfo('trust_dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		if (self::installConfig($dirname, $trustDirname)) {
			$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_DATA_INSTALLED'), $dirname . '_config'));
		} else {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_DATAL_INSTALLED'), $dirname . '_config'));
			return false;
		}
		if (self::installDataAndViewType($dirname, $trustDirname)) {
			$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_DATA_INSTALLED'), $dirname . '_view_type,' . $dirname . '_data_type'));
		} else {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_DATAL_INSTALLED'), $dirname . '_view_type,' . $dirname . '_data_type'));
			return false;
		}
		if (self::installComplement($dirname, $trustDirname)) {
			$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_DATA_INSTALLED'), $dirname . '_complement'));
		} else {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_DATAL_INSTALLED'), $dirname . '_complement'));
			return false;
		}
		if (self::installItemFieldValue($dirname, $trustDirname)) {
			$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_DATA_INSTALLED'), $dirname . '_item_field_value_set'));
		} else {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_DATAL_INSTALLED'), $dirname . '_item_field_value_set'));
			return false;
		}
		if (self::installOaipmh($dirname, $trustDirname)) {
			$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_DATA_INSTALLED'), $dirname . '_oaipmh_schema'));
		} else {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_DATAL_INSTALLED'), $dirname . '_oaipmh_schema'));
			return false;
		}
		if (self::installItemSort($dirname, $trustDirname)) {
			$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_DATA_INSTALLED'), $dirname . '_item_type_sort'));
		} else {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_DATAL_INSTALLED'), $dirname . '_item_type_sort'));
			return false;
		}
		if (self::installDefaultItemtype($dirname, $trustDirname)) {
			$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_DATA_INSTALLED'), $dirname . '_item_field_group,' . $dirname . '_item_field_detail'));
		} else {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_DATAL_INSTALLED'), $dirname . '_item_field_group,' . $dirname . '_item_field_detail'));
			return false;
		}
		if (self::installItemQuickSearchCondition($dirname, $trustDirname)) {
			$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_DATA_INSTALLED'), $dirname . '_item_type_search_condition'));
		} else {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_DATAL_INSTALLED'), $dirname . '_item_type_search_condition'));
			return false;
		}
		if (!self::installAllXMLItemtype($dirname, $trustDirname, $log)) {
			return false;
		}
		if (!self::installIndex($dirname, $trustDirname)) {
			return false;
		}
	}

	/**
	 * install configs
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @return bool
	 */
	private static function installConfig($dirname, $trustDirname) {
		$constpref = '_MI_' . strtoupper($dirname);
		$configlist = array(
			array('name' => 'moderator_gid', 'value' => '1'),
			array('name' => 'upload_dir', 'value' => '/var/tmp'),
			array('name' => 'repository_name', 'value' => ''),
			array('name' => 'repository_nijc_code', 'value' => ''),
			array('name' => 'repository_deletion_track', 'value' => '30'),
			array('name' => 'proxy_host', 'value' => ''),
			array('name' => 'proxy_port', 'value' => '80'),
			array('name' => 'proxy_user', 'value' => ''),
			array('name' => 'proxy_pass', 'value' => ''),
			array('name' => 'certify_user', 'value' => 'on'),
			array('name' => 'user_certify_date', 'value' => '0'),
			array('name' => 'private_item_number_limit', 'value' => '0'),
			array('name' => 'private_index_number_limit', 'value' => '0'),
			array('name' => 'private_item_storage_limit', 'value' => '0'),
			array('name' => 'group_making', 'value' => 'off'),
			array('name' => 'group_making_certify', 'value' => 'on'),
			array('name' => 'group_publish_certify', 'value' => 'on'),
			array('name' => 'group_item_number_limit', 'value' => '0'),
			array('name' => 'group_index_number_limit', 'value' => '0'),
			array('name' => 'group_item_storage_limit', 'value' => '0'),
			array('name' => 'certify_item', 'value' => 'on'),
			array('name' => 'download_file_compression', 'value' => 'on'),
			array('name' => 'export_enabled', 'value' => 'off'),
			array('name' => 'export_attachment', 'value' => 'off'),
			array('name' => 'private_import_enabled', 'value' => 'off'),
			array('name' => 'message_sign', 'value' => '{X_SITENAME}({X_SITEURL})' . constant($constpref . '_ISTALL_MESSAGE_SIGN') . ':{X_ADMINMAIL}'),
			array('name' => 'access_key', 'value' => ''),
			array('name' => 'secret_access_key', 'value' => ''),
            array('name' => 'index_upload_dir', 'value' => XOOPS_ROOT_PATH . '/uploads'),
		);
                $handler = Xoonips_Utils::getTrustModuleHandler('config', $dirname, $trustDirname);
                return $handler->insertConfigs($configlist);
	}

	/**
	 * install data and view type
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @return bool
	 */
	private static function installDataAndViewType($dirname, $trustDirname) {
		$datatypelist = array(
			array('name' => 'int', 'module' => 'DataTypeInt'),
			array('name' => 'float', 'module' => 'DataTypeFloat'),
			array('name' => 'double', 'module' => 'DataTypeDouble'),
			array('name' => 'char', 'module' => 'DataTypeChar'),
			array('name' => 'varchar', 'module' => 'DataTypeVarchar'),
			array('name' => 'text', 'module' => 'DataTypeText'),
			array('name' => 'date', 'module' => 'DataTypeDate'),
			array('name' => 'datetime', 'module' => 'DataTypeDatetime'),
			array('name' => 'blob', 'module' => 'DataTypeBlob'),
		);
		$viewtypelist = array(
			array('preselect' => 0, 'multi' => 1, 'name' => 'hidden', 'module' => 'ViewTypeHidden'),
			array('preselect' => 0, 'multi' => 1, 'name' => 'text', 'module' => 'ViewTypeText'),
			array('preselect' => 0, 'multi' => 1, 'name' => 'textarea', 'module' => 'ViewTypeTextArea'),
			array('preselect' => 0, 'multi' => 1, 'name' => 'radio', 'module' => 'ViewTypeRadioBox'),
			array('preselect' => 0, 'multi' => 1, 'name' => 'checkbox', 'module' => 'ViewTypeCheckBox'),
			array('preselect' => 0, 'multi' => 1, 'name' => 'select', 'module' => 'ViewTypeComboBox'),
			array('preselect' => 1, 'multi' => 0, 'name' => 'id', 'module' => 'ViewTypeId'),
			array('preselect' => 1, 'multi' => 1, 'name' => 'title', 'module' => 'ViewTypeTitle'),
			array('preselect' => 1, 'multi' => 1, 'name' => 'keyword', 'module' => 'ViewTypeKeyword'),
			array('preselect' => 1, 'multi' => 0, 'name' => 'last update', 'module' => 'ViewTypeLastUpdate'),
			array('preselect' => 1, 'multi' => 0, 'name' => 'create date', 'module' => 'ViewTypeCreateDate'),
			array('preselect' => 1, 'multi' => 0, 'name' => 'create user', 'module' => 'ViewTypeCreateUser'),
			array('preselect' => 1, 'multi' => 0, 'name' => 'change log', 'module' => 'ViewTypeChangeLog'),
			array('preselect' => 1, 'multi' => 0, 'name' => 'index', 'module' => 'ViewTypeIndex'),
			array('preselect' => 1, 'multi' => 0, 'name' => 'relation item', 'module' => 'ViewTypeRelatedTo'),
			array('preselect' => 0, 'multi' => 1, 'name' => 'date(yyyy mm dd)', 'module' => 'ViewTypeDate'),
			array('preselect' => 0, 'multi' => 0, 'name' => 'preview', 'module' => 'ViewTypePreview'),
			array('preselect' => 0, 'multi' => 1, 'name' => 'file upload', 'module' => 'ViewTypeFileUpload'),
			array('preselect' => 0, 'multi' => 1, 'name' => 'file type', 'module' => 'ViewTypeFileType'),
			array('preselect' => 0, 'multi' => 0, 'name' => 'download limit', 'module' => 'ViewTypeDownloadLimit'),
			array('preselect' => 0, 'multi' => 0, 'name' => 'download notify', 'module' => 'ViewTypeDownloadNotify'),
			array('preselect' => 0, 'multi' => 0, 'name' => 'readme', 'module' => 'ViewTypeReadme'),
			array('preselect' => 0, 'multi' => 0, 'name' => 'rights', 'module' => 'ViewTypeRights'),
			array('preselect' => 0, 'multi' => 1, 'name' => 'url', 'module' => 'ViewTypeUrl'),
			array('preselect' => 0, 'multi' => 1, 'name' => 'pubmed id', 'module' => 'ViewTypePubmedId'),
			array('preselect' => 0, 'multi' => 1, 'name' => 'isbn', 'module' => 'ViewTypeIsbn'),
			array('preselect' => 0, 'multi' => 1, 'name' => 'kana', 'module' => 'ViewTypeKana'),
		);
		$relationlist = array(
			array('view_type_id' => 'hidden', 'data_type_id' => 'int', 'data_length' => 11, 'data_decimal_places' => -1),
			array('view_type_id' => 'hidden', 'data_type_id' => 'float', 'data_length' => 24, 'data_decimal_places' => 1),
			array('view_type_id' => 'hidden', 'data_type_id' => 'double', 'data_length' => 53, 'data_decimal_places' => 1),
			array('view_type_id' => 'hidden', 'data_type_id' => 'char', 'data_length' => 10, 'data_decimal_places' => -1),
			array('view_type_id' => 'hidden', 'data_type_id' => 'varchar', 'data_length' => 255, 'data_decimal_places' => -1),
			array('view_type_id' => 'hidden', 'data_type_id' => 'text', 'data_length' => -1, 'data_decimal_places' => -1),
			array('view_type_id' => 'hidden', 'data_type_id' => 'date', 'data_length' => -1, 'data_decimal_places' => -1),
			array('view_type_id' => 'hidden', 'data_type_id' => 'datetime', 'data_length' => -1, 'data_decimal_places' => -1),
			array('view_type_id' => 'hidden', 'data_type_id' => 'blob', 'data_length' => -1, 'data_decimal_places' => -1),
			array('view_type_id' => 'text', 'data_type_id' => 'int', 'data_length' => 11, 'data_decimal_places' => -1),
			array('view_type_id' => 'text', 'data_type_id' => 'float', 'data_length' => 24, 'data_decimal_places' => 1),
			array('view_type_id' => 'text', 'data_type_id' => 'double', 'data_length' => 53, 'data_decimal_places' => 1),
			array('view_type_id' => 'text', 'data_type_id' => 'char', 'data_length' => 10, 'data_decimal_places' => -1),
			array('view_type_id' => 'text', 'data_type_id' => 'varchar', 'data_length' => 255, 'data_decimal_places' => -1),
			array('view_type_id' => 'text', 'data_type_id' => 'text', 'data_length' => -1, 'data_decimal_places' => -1),
			array('view_type_id' => 'text', 'data_type_id' => 'date', 'data_length' => -1, 'data_decimal_places' => -1),
			array('view_type_id' => 'text', 'data_type_id' => 'datetime', 'data_length' => -1, 'data_decimal_places' => -1),
			array('view_type_id' => 'text', 'data_type_id' => 'blob', 'data_length' => -1, 'data_decimal_places' => -1),
			array('view_type_id' => 'textarea', 'data_type_id' => 'varchar', 'data_length' => 255, 'data_decimal_places' => -1),
			array('view_type_id' => 'textarea', 'data_type_id' => 'text', 'data_length' => -1, 'data_decimal_places' => -1),
			array('view_type_id' => 'radio', 'data_type_id' => 'int', 'data_length' => 11, 'data_decimal_places' => -1),
			array('view_type_id' => 'radio', 'data_type_id' => 'float', 'data_length' => 24, 'data_decimal_places' => 1),
			array('view_type_id' => 'radio', 'data_type_id' => 'double', 'data_length' => 53, 'data_decimal_places' => 1),
			array('view_type_id' => 'radio', 'data_type_id' => 'char', 'data_length' => 10, 'data_decimal_places' => -1),
			array('view_type_id' => 'radio', 'data_type_id' => 'varchar', 'data_length' => 255, 'data_decimal_places' => -1),
			array('view_type_id' => 'checkbox', 'data_type_id' => 'int', 'data_length' => 11, 'data_decimal_places' => -1),
			array('view_type_id' => 'checkbox', 'data_type_id' => 'float', 'data_length' => 24, 'data_decimal_places' => 1),
			array('view_type_id' => 'checkbox', 'data_type_id' => 'double', 'data_length' => 53, 'data_decimal_places' => 1),
			array('view_type_id' => 'checkbox', 'data_type_id' => 'char', 'data_length' => 10, 'data_decimal_places' => -1),
			array('view_type_id' => 'checkbox', 'data_type_id' => 'varchar', 'data_length' => 255, 'data_decimal_places' => -1),
			array('view_type_id' => 'select', 'data_type_id' => 'int', 'data_length' => 11, 'data_decimal_places' => -1),
			array('view_type_id' => 'select', 'data_type_id' => 'float', 'data_length' => 24, 'data_decimal_places' => 1),
			array('view_type_id' => 'select', 'data_type_id' => 'double', 'data_length' => 53, 'data_decimal_places' => 1),
			array('view_type_id' => 'select', 'data_type_id' => 'char', 'data_length' => 10, 'data_decimal_places' => -1),
			array('view_type_id' => 'select', 'data_type_id' => 'varchar', 'data_length' => 255, 'data_decimal_places' => -1),
			array('view_type_id' => 'id', 'data_type_id' => 'blob', 'data_length' => -1, 'data_decimal_places' => -1),
			array('view_type_id' => 'title', 'data_type_id' => 'text', 'data_length' => -1, 'data_decimal_places' => -1),
			array('view_type_id' => 'keyword', 'data_type_id' => 'varchar', 'data_length' => 255, 'data_decimal_places' => -1),
			array('view_type_id' => 'last update', 'data_type_id' => 'int', 'data_length' => 10, 'data_decimal_places' => -1),
			array('view_type_id' => 'create date', 'data_type_id' => 'int', 'data_length' => 10, 'data_decimal_places' => -1),
			array('view_type_id' => 'create user', 'data_type_id' => 'int', 'data_length' => 10, 'data_decimal_places' => -1),
			array('view_type_id' => 'change log', 'data_type_id' => 'text', 'data_length' => -1, 'data_decimal_places' => -1),
			array('view_type_id' => 'index', 'data_type_id' => 'int', 'data_length' => 10, 'data_decimal_places' => -1),
			array('view_type_id' => 'relation item', 'data_type_id' => 'int', 'data_length' => 10, 'data_decimal_places' => -1),
			array('view_type_id' => 'date(yyyy mm dd)', 'data_type_id' => 'date', 'data_length' => -1, 'data_decimal_places' => -1),
			array('view_type_id' => 'preview', 'data_type_id' => 'varchar', 'data_length' => 255, 'data_decimal_places' => -1),
			array('view_type_id' => 'file upload', 'data_type_id' => 'varchar', 'data_length' => 255, 'data_decimal_places' => -1),
			array('view_type_id' => 'file type', 'data_type_id' => 'varchar', 'data_length' => 30, 'data_decimal_places' => -1),
			array('view_type_id' => 'download limit', 'data_type_id' => 'int', 'data_length' => 1, 'data_decimal_places' => -1),
			array('view_type_id' => 'download notify', 'data_type_id' => 'int', 'data_length' => 1, 'data_decimal_places' => -1),
			array('view_type_id' => 'readme', 'data_type_id' => 'text', 'data_length' => -1, 'data_decimal_places' => -1),
			array('view_type_id' => 'rights', 'data_type_id' => 'text', 'data_length' => -1, 'data_decimal_places' => -1),
			array('view_type_id' => 'url', 'data_type_id' => 'varchar', 'data_length' => 255, 'data_decimal_places' => -1),
			array('view_type_id' => 'pubmed id', 'data_type_id' => 'varchar', 'data_length' => 30, 'data_decimal_places' => -1),
			array('view_type_id' => 'isbn', 'data_type_id' => 'char', 'data_length' => 13, 'data_decimal_places' => -1),
			array('view_type_id' => 'kana', 'data_type_id' => 'varchar', 'data_length' => 255, 'data_decimal_places' => -1),
			array('view_type_id' => 'kana', 'data_type_id' => 'text', 'data_length' => -1, 'data_decimal_places' => -1),
		);
		$dataTypeBean = Xoonips_BeanFactory::getBean('DataTypeBean', $dirname, $trustDirname);
		$viewTypeBean = Xoonips_BeanFactory::getBean('ViewTypeBean', $dirname, $trustDirname);
		$datatypeid = array();
		foreach ($datatypelist as $datatype) {
			$id = '';
			if (!$dataTypeBean->insert($datatype, $id))
				return false;
			$datatypeid[$datatype['name']] = $id;
		}
		$viewtypeid = array();
		foreach ($viewtypelist as $viewtype) {
			$id = '';
			if (!$viewTypeBean->insert($viewtype, $id))
				return false;
			$viewtypeid[$viewtype['name']] = $id;
		}
		foreach ($relationlist as $relation) {
			$relation['view_type_id'] = $viewtypeid[$relation['view_type_id']];
			$relation['data_type_id'] = $datatypeid[$relation['data_type_id']];
			if (!$dataTypeBean->insertRelation($relation))
				return false;
		}
		return true;
	}

	/**
	 * install complement
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @return bool
	 */
	private static function installComplement($dirname, $trustDirname) {
		$constpref = '_MI_' . strtoupper($dirname);
		$viewTypeBean = Xoonips_BeanFactory::getBean('ViewTypeBean', $dirname, $trustDirname);
		$viewtypelist = $viewTypeBean->getViewtypeList();
		$viewtypeid = array();
		foreach ($viewtypelist as $viewtype)
			$viewtypeid[$viewtype['name']] = $viewtype['view_type_id'];
		$complementlist = array(
			array('view_type_id' => $viewtypeid['preview'], 'title' => 'Preview', 'module' => NULL),
			array('view_type_id' => $viewtypeid['url'], 'title' => 'URL', 'module' => NULL),
			array('view_type_id' => $viewtypeid['pubmed id'], 'title' => 'Pubmed ID', 'module' => 'ComplementPubmedId'),
			array('view_type_id' => $viewtypeid['isbn'], 'title' => 'ISBN', 'module' => 'ComplementIsbn'),
			array('view_type_id' => $viewtypeid['kana'], 'title' => 'KANA', 'module' => 'ComplementKana'),
		);
		$detaillist = array(
			array('complement_id' => 'Preview', 'code' => 'caption', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_CAPTION')),
			array('complement_id' => 'URL', 'code' => 'hits', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_HITS')),
			array('complement_id' => 'Pubmed ID', 'code' => 'title', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_TITLE')),
			array('complement_id' => 'Pubmed ID', 'code' => 'keyword', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_KEYWORD')),
			array('complement_id' => 'Pubmed ID', 'code' => 'author', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_AUTHOR')),
			array('complement_id' => 'Pubmed ID', 'code' => 'journal', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_JOURNAL')),
			array('complement_id' => 'Pubmed ID', 'code' => 'publicationyear', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_PUBLICATION_YEAR')),
			array('complement_id' => 'Pubmed ID', 'code' => 'volume', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_VOLUME')),
			array('complement_id' => 'Pubmed ID', 'code' => 'number', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_NUMBER')),
			array('complement_id' => 'Pubmed ID', 'code' => 'page', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_PAGE')),
			array('complement_id' => 'Pubmed ID', 'code' => 'abstract', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_ABSTRACT')),
			array('complement_id' => 'ISBN', 'code' => 'title', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_TITLE')),
			array('complement_id' => 'ISBN', 'code' => 'author', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_AUTHOR')),
			array('complement_id' => 'ISBN', 'code' => 'publisher', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_PUBLISHER')),
			array('complement_id' => 'ISBN', 'code' => 'publicationyear', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_PUBLICATION_YEAR')),
			array('complement_id' => 'ISBN', 'code' => 'url', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_URL')),
			array('complement_id' => 'KANA', 'code' => 'romaji', 'title' => constant($constpref . '_INSTALL_COMPLEMENT_ROMAJI')),
		);
		$complementBean = Xoonips_BeanFactory::getBean('ComplementBean', $dirname, $trustDirname);
		$complementid = array();
		foreach ($complementlist as $complement) {
			$id = '';
			if (!$complementBean->insert($complement, $id))
				return false;
			$complementid[$complement['title']] = $id;
		}
		foreach ($detaillist as $detail) {
			$id = '';
			$detail['complement_id'] = $complementid[$detail['complement_id']];
			if (!$complementBean->insertDetail($detail, $id))
				return false;
		}
		return true;
	}

	/**
	 * install item fields
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @return bool
	 */
	private static function installItemFieldValue($dirname, $trustDirname) {
		$constpref = '_MI_' . strtoupper($dirname);
		$valueList = array(
			array('select_name' => 'Language', 'title_id' => 'eng', 'title' => constant($constpref . '_INSTALL_LANG_ENGLISH')),
			array('select_name' => 'Language', 'title_id' => 'jpn', 'title' => constant($constpref . '_INSTALL_LANG_JAPANES')),
			array('select_name' => 'Language', 'title_id' => 'fra', 'title' => constant($constpref . '_INSTALL_LANG_FRENCH')),
			array('select_name' => 'Language', 'title_id' => 'deu', 'title' => constant($constpref . '_INSTALL_LANG_GERMAN')),
			array('select_name' => 'Language', 'title_id' => 'esl', 'title' => constant($constpref . '_INSTALL_LANG_SPANISH')),
			array('select_name' => 'Language', 'title_id' => 'ita', 'title' => constant($constpref . '_INSTALL_LANG_ITALIAN')),
			array('select_name' => 'Language', 'title_id' => 'dut', 'title' => constant($constpref . '_INSTALL_LANG_DUTCH')),
			array('select_name' => 'Language', 'title_id' => 'sve', 'title' => constant($constpref . '_INSTALL_LANG_SWEDISH')),
			array('select_name' => 'Language', 'title_id' => 'nor', 'title' => constant($constpref . '_INSTALL_LANG_NORWEGIAN')),
			array('select_name' => 'Language', 'title_id' => 'dan', 'title' => constant($constpref . '_INSTALL_LANG_DANISH')),
			array('select_name' => 'Language', 'title_id' => 'fin', 'title' => constant($constpref . '_INSTALL_LANG_FINNISH')),
			array('select_name' => 'Language', 'title_id' => 'por', 'title' => constant($constpref . '_INSTALL_LANG_PORTUGUESE')),
			array('select_name' => 'Language', 'title_id' => 'chi', 'title' => constant($constpref . '_INSTALL_LANG_CHINESE')),
			array('select_name' => 'Language', 'title_id' => 'kor', 'title' => constant($constpref . '_INSTALL_LANG_KOREAN')),
			//array('select_name' => 'Download limit', 'title_id' => '1', 'title' => constant($constpref . '_INSTALL_DOWNLOAD_LIMIT_LOGIN_USER')),
			//array('select_name' => 'Download limit', 'title_id' => '0', 'title' => constant($constpref . '_INSTALL_DOWNLOAD_LIMIT_EVERYONE')),
			//array('select_name' => 'Download notify', 'title_id' => '1', 'title' => constant($constpref . '_INSTALL_DOWNLOAD_NOTIFY_YES')),
			//array('select_name' => 'Download notify', 'title_id' => '0', 'title' => constant($constpref . '_INSTALL_DOWNLOAD_NOTIFY_NO')),
			array('select_name' => 'Conference file type', 'title_id' => 'powerpoint', 'title' => constant($constpref . '_INSTALL_CONFERENCE_FILE_TYPE_POWERPOINT')),
			array('select_name' => 'Conference file type', 'title_id' => 'pdf', 'title' => constant($constpref . '_INSTALL_CONFERENCE_FILE_TYPE_PDF')),
			array('select_name' => 'Conference file type', 'title_id' => 'illustrator', 'title' => constant($constpref . '_INSTALL_CONFERENCE_FILE_TYPE_ILLUSTRATOR')),
			array('select_name' => 'Conference file type', 'title_id' => 'other', 'title' => constant($constpref . '_INSTALL_CONFERENCE_FILE_TYPE_OTHER')),
			array('select_name' => 'Data type', 'title_id' => 'excel', 'title' => constant($constpref . '_INSTALL_DATA_TYPE_EXCEL')),
			array('select_name' => 'Data type', 'title_id' => 'movie', 'title' => constant($constpref . '_INSTALL_DATA_TYPE_MOVIE')),
			array('select_name' => 'Data type', 'title_id' => 'text', 'title' => constant($constpref . '_INSTALL_DATA_TYPE_TEXT')),
			array('select_name' => 'Data type', 'title_id' => 'picture', 'title' => constant($constpref . '_INSTALL_DATA_TYPE_PICTURE')),
			array('select_name' => 'Data type', 'title_id' => 'other', 'title' => constant($constpref . '_INSTALL_DATA_TYPE_OTHER')),
			array('select_name' => 'Model type', 'title_id' => 'matlab', 'title' => constant($constpref . '_INSTALL_MODAL_TYPE_MATLAB')),
			array('select_name' => 'Model type', 'title_id' => 'neuron', 'title' => constant($constpref . '_INSTALL_MODAL_TYPE_NEURON')),
			array('select_name' => 'Model type', 'title_id' => 'originalprogram', 'title' => constant($constpref . '_INSTALL_MODAL_TYPE_ORIGINALPROGRAM')),
			array('select_name' => 'Model type', 'title_id' => 'satellite', 'title' => constant($constpref . '_INSTALL_MODAL_TYPE_SATELLITE')),
			array('select_name' => 'Model type', 'title_id' => 'genesis', 'title' => constant($constpref . '_INSTALL_MODAL_TYPE_Genesis')),
			array('select_name' => 'Model type', 'title_id' => 'a-cell', 'title' => constant($constpref . '_INSTALL_MODAL_TYPE_ACELL')),
			array('select_name' => 'Model type', 'title_id' => 'other', 'title' => constant($constpref . '_INSTALL_MODAL_TYPE_OTHER')),
			array('select_name' => 'Presentation file type', 'title_id' => 'powerpoint', 'title' => constant($constpref . '_INSTALL_PRESENTATION_FILE_TYPE_POWERPOINT')),
			array('select_name' => 'Presentation file type', 'title_id' => 'illustrator', 'title' => constant($constpref . '_INSTALL_CONFERENCE_FILE_TYPE_ILLUSTRATOR')),
			array('select_name' => 'Presentation file type', 'title_id' => 'lotus', 'title' => constant($constpref . '_INSTALL_PRESENTATION_FILE_TYPE_LOTUS')),
			array('select_name' => 'Presentation file type', 'title_id' => 'justsystem', 'title' => constant($constpref . '_INSTALL_PRESENTATION_FILE_TYPE_JUSTSYSTEM')),
			array('select_name' => 'Presentation file type', 'title_id' => 'html', 'title' => constant($constpref . '_INSTALL_PRESENTATION_FILE_TYPE_HTML')),
			array('select_name' => 'Presentation file type', 'title_id' => 'pdf', 'title' => constant($constpref . '_INSTALL_PRESENTATION_FILE_TYPE_PDF')),
			array('select_name' => 'Presentation file type', 'title_id' => 'other', 'title' => constant($constpref . '_INSTALL_PRESENTATION_FILE_TYPE_OTHER')),
			array('select_name' => 'Simulator file type', 'title_id' => 'matlab', 'title' => constant($constpref . '_INSTALL_SIMULATOR_FILE_TYPE_MATLAB')),
			array('select_name' => 'Simulator file type', 'title_id' => 'mathematica', 'title' => constant($constpref . '_INSTALL_SIMULATOR_FILE_TYPE_MATHEMATICA')),
			array('select_name' => 'Simulator file type', 'title_id' => 'program', 'title' => constant($constpref . '_INSTALL_SIMULATOR_FILE_TYPE_PROGRAM')),
			array('select_name' => 'Simulator file type', 'title_id' => 'other', 'title' => constant($constpref . '_INSTALL_SIMULATOR_FILE_TYPE_OTHER')),
			array('select_name' => 'Stimulus type', 'title_id' => 'picture', 'title' => constant($constpref . '_INSTALL_STIMULUS_TYPE_PICTURE')),
			array('select_name' => 'Stimulus type', 'title_id' => 'movie', 'title' => constant($constpref . '_INSTALL_STIMULUS_TYPE_MOVIE')),
			array('select_name' => 'Stimulus type', 'title_id' => 'program', 'title' => constant($constpref . '_INSTALL_STIMULUS_TYPE_PROGRAM')),
			array('select_name' => 'Stimulus type', 'title_id' => 'other', 'title' => constant($constpref . '_INSTALL_STIMULUS_TYPE_OTHER')),
			array('select_name' => 'Tool file type', 'title_id' => 'matlab', 'title' => constant($constpref . '_INSTALL_TOOL_FILE_TYPE_MATLAB')),
			array('select_name' => 'Tool file type', 'title_id' => 'mathematica', 'title' => constant($constpref . '_INSTALL_TOOL_FILE_TYPE_MATHEMATICA')),
			array('select_name' => 'Tool file type', 'title_id' => 'program', 'title' => constant($constpref . '_INSTALL_TOOL_FILE_TYPE_PROGRAM')),
			array('select_name' => 'Tool file type', 'title_id' => 'other', 'title' => constant($constpref . '_INSTALL_TOOL_FILE_TYPE_OTHER')),
			/*
			array('select_name' => 'Rights', 'title_id' => 'Unported', 'title' => constant($constpref . '_INSTALL_RIGHTS_UNPORTED')),
			array('select_name' => 'Rights', 'title_id' => 'Argentina', 'title' => constant($constpref . '_INSTALL_RIGHTS_ARGENTINA')),
			array('select_name' => 'Rights', 'title_id' => 'Australia', 'title' => constant($constpref . '_INSTALL_RIGHTS_AUSTRALIA')),
			array('select_name' => 'Rights', 'title_id' => 'Austria', 'title' => constant($constpref . '_INSTALL_RIGHTS_AUSTRIA')),
			array('select_name' => 'Rights', 'title_id' => 'Belgium', 'title' => constant($constpref . '_INSTALL_RIGHTS_BELGIUM')),
			array('select_name' => 'Rights', 'title_id' => 'Brazil', 'title' => constant($constpref . '_INSTALL_RIGHTS_BRAZIL')),
			array('select_name' => 'Rights', 'title_id' => 'Bulgaria', 'title' => constant($constpref . '_INSTALL_RIGHTS_BULGARIA')),
			array('select_name' => 'Rights', 'title_id' => 'Canada', 'title' => constant($constpref . '_INSTALL_RIGHTS_CANADA')),
			array('select_name' => 'Rights', 'title_id' => 'Chile', 'title' => constant($constpref . '_INSTALL_RIGHTS_CHILE')),
			array('select_name' => 'Rights', 'title_id' => 'China_Mainland', 'title' => constant($constpref . '_INSTALL_RIGHTS_CHINA_MAINLAND')),
			array('select_name' => 'Rights', 'title_id' => 'Colombia', 'title' => constant($constpref . '_INSTALL_RIGHTS_COLOMBIA')),
			array('select_name' => 'Rights', 'title_id' => 'Croatia', 'title' => constant($constpref . '_INSTALL_RIGHTS_CROATIA')),
			array('select_name' => 'Rights', 'title_id' => 'Denmark', 'title' => constant($constpref . '_INSTALL_RIGHTS_DENMARK')),
			array('select_name' => 'Rights', 'title_id' => 'Ecuador', 'title' => constant($constpref . '_INSTALL_RIGHTS_ECUADOR')),
			array('select_name' => 'Rights', 'title_id' => 'Finland', 'title' => constant($constpref . '_INSTALL_RIGHTS_FINLAND')),
			array('select_name' => 'Rights', 'title_id' => 'France', 'title' => constant($constpref . '_INSTALL_RIGHTS_FRANCE')),
			array('select_name' => 'Rights', 'title_id' => 'Germany', 'title' => constant($constpref . '_INSTALL_RIGHTS_GERMANY')),
			array('select_name' => 'Rights', 'title_id' => 'Greece', 'title' => constant($constpref . '_INSTALL_RIGHTS_GREECE')),
			array('select_name' => 'Rights', 'title_id' => 'Guatemala', 'title' => constant($constpref . '_INSTALL_RIGHTS_GUATEMALA')),
			array('select_name' => 'Rights', 'title_id' => 'Hong_Kong', 'title' => constant($constpref . '_INSTALL_RIGHTS_HONG_KONG')),
			array('select_name' => 'Rights', 'title_id' => 'Hungary', 'title' => constant($constpref . '_INSTALL_RIGHTS_HUNGARY')),
			array('select_name' => 'Rights', 'title_id' => 'India', 'title' => constant($constpref . '_INSTALL_RIGHTS_INDIA')),
			array('select_name' => 'Rights', 'title_id' => 'Israel', 'title' => constant($constpref . '_INSTALL_RIGHTS_ISRAEL')),
			array('select_name' => 'Rights', 'title_id' => 'Italy', 'title' => constant($constpref . '_INSTALL_RIGHTS_ITALY')),
			array('select_name' => 'Rights', 'title_id' => 'Japan', 'title' => constant($constpref . '_INSTALL_RIGHTS_JAPAN')),
			array('select_name' => 'Rights', 'title_id' => 'Luxembourg', 'title' => constant($constpref . '_INSTALL_RIGHTS_LUXEMBOURG')),
			array('select_name' => 'Rights', 'title_id' => 'Macedonia', 'title' => constant($constpref . '_INSTALL_RIGHTS_MACEDONIA')),
			array('select_name' => 'Rights', 'title_id' => 'Malaysia', 'title' => constant($constpref . '_INSTALL_RIGHTS_MALAYSIA')),
			array('select_name' => 'Rights', 'title_id' => 'Malta', 'title' => constant($constpref . '_INSTALL_RIGHTS_MALTA')),
			array('select_name' => 'Rights', 'title_id' => 'Mexico', 'title' => constant($constpref . '_INSTALL_RIGHTS_MEXICO')),
			array('select_name' => 'Rights', 'title_id' => 'Netherlands', 'title' => constant($constpref . '_INSTALL_RIGHTS_NETHERLANDS')),
			array('select_name' => 'Rights', 'title_id' => 'New_Zealand', 'title' => constant($constpref . '_INSTALL_RIGHTS_NEW_ZEALAND')),
			array('select_name' => 'Rights', 'title_id' => 'Norway', 'title' => constant($constpref . '_INSTALL_RIGHTS_NORWAY')),
			array('select_name' => 'Rights', 'title_id' => 'Peru', 'title' => constant($constpref . '_INSTALL_RIGHTS_PERU')),
			array('select_name' => 'Rights', 'title_id' => 'Philippines', 'title' => constant($constpref . '_INSTALL_RIGHTS_PHILIPPINES')),
			array('select_name' => 'Rights', 'title_id' => 'Poland', 'title' => constant($constpref . '_INSTALL_RIGHTS_POLAND')),
			array('select_name' => 'Rights', 'title_id' => 'Portugal', 'title' => constant($constpref . '_INSTALL_RIGHTS_PORTUGAL')),
			array('select_name' => 'Rights', 'title_id' => 'Puerto_Rico', 'title' => constant($constpref . '_INSTALL_RIGHTS_PUERTO_RICO')),
			array('select_name' => 'Rights', 'title_id' => 'Romania', 'title' => constant($constpref . '_INSTALL_RIGHTS_ROMANIA')),
			array('select_name' => 'Rights', 'title_id' => 'Serbia', 'title' => constant($constpref . '_INSTALL_RIGHTS_SERBIA')),
			array('select_name' => 'Rights', 'title_id' => 'Singapore', 'title' => constant($constpref . '_INSTALL_RIGHTS_SINGAPORE')),
			array('select_name' => 'Rights', 'title_id' => 'Slovenia', 'title' => constant($constpref . '_INSTALL_RIGHTS_SLOVENIA')),
			array('select_name' => 'Rights', 'title_id' => 'South_Africa', 'title' => constant($constpref . '_INSTALL_RIGHTS_SOUTH_AFRICA')),
			array('select_name' => 'Rights', 'title_id' => 'South_Korea', 'title' => constant($constpref . '_INSTALL_RIGHTS_SOUTH_KOREA')),
			array('select_name' => 'Rights', 'title_id' => 'Spain', 'title' => constant($constpref . '_INSTALL_RIGHTS_SPAIN')),
			array('select_name' => 'Rights', 'title_id' => 'Sweden', 'title' => constant($constpref . '_INSTALL_RIGHTS_SWEDEN')),
			array('select_name' => 'Rights', 'title_id' => 'Switzerland', 'title' => constant($constpref . '_INSTALL_RIGHTS_SWITZERLAND')),
			array('select_name' => 'Rights', 'title_id' => 'Taiwan', 'title' => constant($constpref . '_INSTALL_RIGHTS_TAIWAN')),
			array('select_name' => 'Rights', 'title_id' => 'UK_England_and_Wales', 'title' => constant($constpref . '_INSTALL_RIGHTS_UK_ENGLAND_AND_WALES')),
			array('select_name' => 'Rights', 'title_id' => 'UK_Scotland', 'title' => constant($constpref . '_INSTALL_RIGHTS_UK_SCOTLAND')),
			array('select_name' => 'Rights', 'title_id' => 'United_States', 'title' => constant($constpref . '_INSTALL_RIGHTS_UNITED_STATES')),
			*/
		);
		$valueSetBean = Xoonips_BeanFactory::getBean('ItemFieldValueSetBean', $dirname, $trustDirname);
		$select_name = '';
		foreach ($valueList as $value) {
			if ($select_name != $value['select_name']) {
				$weight = 1;
				$select_name = $value['select_name'];
			} else {
				$weight = $weight + 1;
			}
			$value['weight'] = $weight;
			if (!$valueSetBean->insertValue($value))
				return false;
		}
		return true;
	}

	/**
	 * install oaipmh schemes
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @return bool
	 */
	private static function installOaipmh($dirname, $trustDirname) {
		$constpref = '_MI_' . strtoupper($dirname);
		$oaipmhlist = array(
			array('metadata_prefix' => 'junii2', 'name' => 'title', 'min_occurences' => 1, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'alternative', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'creator', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'subject', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'NDC', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'NDLC', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'NDLSH', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'BSH', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'UDC', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'MeSH', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'DDC', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'LCC', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'LCSH', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'NIIsubject', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'description', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'publisher', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'contributor', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'date', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'type', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'NIItype', 'min_occurences' => 1, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'format', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'identifier', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'URI', 'min_occurences' => 1, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'fullTextURL', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'selfDOI', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'isbn', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'issn', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'NCID', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'jtitle', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'volume', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'issue', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'spage', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'epage', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'dateofissued', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'source', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'language', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'relation', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'pmid', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'doi', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'NAID', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'ichushi', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'isVersionOf', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'hasVersion', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'isReplacedBy', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'replaces', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'isRequiredBy', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'requires', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'isPartOf', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'hasPart', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'isReferencedBy', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'references', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'isFormatOf', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'hasFormat', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'coverage', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'spatial', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'NIIspatial', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'temporal', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'NIItemporal', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'rights', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'junii2', 'name' => 'textversion', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'grantid', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'dateofgranted', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'degreename', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'junii2', 'name' => 'grantor', 'min_occurences' => 0, 'max_occurences' => 1),
			array('metadata_prefix' => 'oai_dc', 'name' => 'title', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'oai_dc', 'name' => 'creator', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'oai_dc', 'name' => 'subject', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'oai_dc', 'name' => 'description', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'oai_dc', 'name' => 'publisher', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'oai_dc', 'name' => 'contributor', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'oai_dc', 'name' => 'date', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'oai_dc', 'name' => 'type', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'oai_dc', 'name' => 'type:NIItype', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'oai_dc', 'name' => 'format', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'oai_dc', 'name' => 'identifier', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'oai_dc', 'name' => 'source', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'oai_dc', 'name' => 'language', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'oai_dc', 'name' => 'relation', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'oai_dc', 'name' => 'coverage', 'min_occurences' => 0, 'max_occurences' => 0),
			array('metadata_prefix' => 'oai_dc', 'name' => 'rights', 'min_occurences' => 0, 'max_occurences' => 0),
		);
		$oaipmhvaluelist = array(
			array('schema_id' => 'junii2:NIItype', 'value' => 'Journal Article'),
			array('schema_id' => 'junii2:NIItype', 'value' => 'Thesis or Dissertation'),
			array('schema_id' => 'junii2:NIItype', 'value' => 'Departmental Bulletin Paper'),
			array('schema_id' => 'junii2:NIItype', 'value' => 'Conference Paper'),
			array('schema_id' => 'junii2:NIItype', 'value' => 'Presentation'),
			array('schema_id' => 'junii2:NIItype', 'value' => 'Book'),
			array('schema_id' => 'junii2:NIItype', 'value' => 'Technical Report'),
			array('schema_id' => 'junii2:NIItype', 'value' => 'Research Paper'),
			array('schema_id' => 'junii2:NIItype', 'value' => 'Article'),
			array('schema_id' => 'junii2:NIItype', 'value' => 'Preprint'),
			array('schema_id' => 'junii2:NIItype', 'value' => 'Learning Material'),
			array('schema_id' => 'junii2:NIItype', 'value' => 'Data or Dataset'),
			array('schema_id' => 'junii2:NIItype', 'value' => 'Software'),
			array('schema_id' => 'junii2:NIItype', 'value' => 'Others'),
			array('schema_id' => 'junii2:textversion', 'value' => 'author'),
			array('schema_id' => 'junii2:textversion', 'value' => 'publisher'),
			array('schema_id' => 'junii2:textversion', 'value' => 'ETD'),
			array('schema_id' => 'junii2:textversion', 'value' => 'none'),
			array('schema_id' => 'oai_dc:type:NIItype', 'value' => 'Journal Article'),
			array('schema_id' => 'oai_dc:type:NIItype', 'value' => 'Thesis or Dissertation'),
			array('schema_id' => 'oai_dc:type:NIItype', 'value' => 'Departmental Bulletin Paper'),
			array('schema_id' => 'oai_dc:type:NIItype', 'value' => 'Conference Paper'),
			array('schema_id' => 'oai_dc:type:NIItype', 'value' => 'Presentation'),
			array('schema_id' => 'oai_dc:type:NIItype', 'value' => 'Book'),
			array('schema_id' => 'oai_dc:type:NIItype', 'value' => 'Technical Report'),
			array('schema_id' => 'oai_dc:type:NIItype', 'value' => 'Research Paper'),
			array('schema_id' => 'oai_dc:type:NIItype', 'value' => 'Article'),
			array('schema_id' => 'oai_dc:type:NIItype', 'value' => 'Preprint'),
			array('schema_id' => 'oai_dc:type:NIItype', 'value' => 'Learning Material'),
			array('schema_id' => 'oai_dc:type:NIItype', 'value' => 'Data or Dataset'),
			array('schema_id' => 'oai_dc:type:NIItype', 'value' => 'Software'),
			array('schema_id' => 'oai_dc:type:NIItype', 'value' => 'Others'),
		);
		$oaipmhlinklist = array(
			array('schema_id1' => 'junii2:title', 'schema_id2' => 'oai_dc:title', 'number' => 1),
			array('schema_id1' => 'junii2:alternative', 'schema_id2' => 'oai_dc:title', 'number' => 2),
			array('schema_id1' => 'junii2:creator', 'schema_id2' => 'oai_dc:creator', 'number' => 1),
			array('schema_id1' => 'junii2:subject', 'schema_id2' => 'oai_dc:subject', 'number' => 1),
			array('schema_id1' => 'junii2:NIIsubject', 'schema_id2' => 'oai_dc:subject', 'number' => 2),
			array('schema_id1' => 'junii2:NDC', 'schema_id2' => 'oai_dc:subject', 'number' => 3),
			array('schema_id1' => 'junii2:NDLC', 'schema_id2' => 'oai_dc:subject', 'number' => 4),
			array('schema_id1' => 'junii2:BSH', 'schema_id2' => 'oai_dc:subject', 'number' => 5),
			array('schema_id1' => 'junii2:NDLSH', 'schema_id2' => 'oai_dc:subject', 'number' => 6),
			array('schema_id1' => 'junii2:MeSH', 'schema_id2' => 'oai_dc:subject', 'number' => 7),
			array('schema_id1' => 'junii2:DDC', 'schema_id2' => 'oai_dc:subject', 'number' => 8),
			array('schema_id1' => 'junii2:LCC', 'schema_id2' => 'oai_dc:subject', 'number' => 9),
			array('schema_id1' => 'junii2:UDC', 'schema_id2' => 'oai_dc:subject', 'number' => 10),
			array('schema_id1' => 'junii2:LCSH', 'schema_id2' => 'oai_dc:subject', 'number' => 11),
			array('schema_id1' => 'junii2:description', 'schema_id2' => 'oai_dc:description', 'number' => 1),
			array('schema_id1' => 'junii2:publisher', 'schema_id2' => 'oai_dc:publisher', 'number' => 1),
			array('schema_id1' => 'junii2:contributor', 'schema_id2' => 'oai_dc:contributor', 'number' => 1),
			array('schema_id1' => 'junii2:date', 'schema_id2' => 'oai_dc:date', 'number' => 1),
			array('schema_id1' => 'junii2:type', 'schema_id2' => 'oai_dc:type', 'number' => 1),
			array('schema_id1' => 'junii2:NIItype', 'schema_id2' => 'oai_dc:type:NIItype', 'number' => 2),
			array('schema_id1' => 'junii2:format', 'schema_id2' => 'oai_dc:format', 'number' => 1),
			array('schema_id1' => 'junii2:identifier', 'schema_id2' => 'oai_dc:identifier', 'number' => 1),
			array('schema_id1' => 'junii2:URI', 'schema_id2' => 'oai_dc:identifier', 'number' => 2),
			array('schema_id1' => 'junii2:fullTextURL', 'schema_id2' => 'oai_dc:identifier', 'number' => 3),
			array('schema_id1' => 'junii2:selfDOI', 'schema_id2' => 'oai_dc:identifier', 'number' => 4),
			array('schema_id1' => 'junii2:isbn', 'schema_id2' => 'oai_dc:identifier', 'number' => 5),
			array('schema_id1' => 'junii2:issn', 'schema_id2' => 'oai_dc:identifier', 'number' => 6),
			array('schema_id1' => 'junii2:NCID', 'schema_id2' => 'oai_dc:identifier', 'number' => 7),
			array('schema_id1' => 'junii2:jtitle', 'schema_id2' => 'oai_dc:identifier', 'number' => 8),
			array('schema_id1' => 'junii2:volume', 'schema_id2' => 'oai_dc:identifier', 'number' => 8),
			array('schema_id1' => 'junii2:issue', 'schema_id2' => 'oai_dc:identifier', 'number' => 8),
			array('schema_id1' => 'junii2:spage', 'schema_id2' => 'oai_dc:identifier', 'number' => 8),
			array('schema_id1' => 'junii2:epage', 'schema_id2' => 'oai_dc:identifier', 'number' => 8),
			array('schema_id1' => 'junii2:dateofissued', 'schema_id2' => 'oai_dc:identifier', 'number' => 8),
			array('schema_id1' => 'junii2:dateofissued', 'schema_id2' => 'oai_dc:date', 'number' => 2),
			array('schema_id1' => 'junii2:source', 'schema_id2' => 'oai_dc:source', 'number' => 1),
			array('schema_id1' => 'junii2:language', 'schema_id2' => 'oai_dc:language', 'number' => 1),
			array('schema_id1' => 'junii2:relation', 'schema_id2' => 'oai_dc:relation', 'number' => 1),
			array('schema_id1' => 'junii2:pmid', 'schema_id2' => 'oai_dc:relation', 'number' => 2),
			array('schema_id1' => 'junii2:doi', 'schema_id2' => 'oai_dc:relation', 'number' => 3),
			array('schema_id1' => 'junii2:NAID', 'schema_id2' => 'oai_dc:relation', 'number' => 4),
			array('schema_id1' => 'junii2:ichushi', 'schema_id2' => 'oai_dc:relation', 'number' => 5),
			array('schema_id1' => 'junii2:isVersionOf', 'schema_id2' => 'oai_dc:relation', 'number' => 6),
			array('schema_id1' => 'junii2:hasVersion', 'schema_id2' => 'oai_dc:relation', 'number' => 7),
			array('schema_id1' => 'junii2:isReplacedBy', 'schema_id2' => 'oai_dc:relation', 'number' => 8),
			array('schema_id1' => 'junii2:replaces', 'schema_id2' => 'oai_dc:relation', 'number' => 9),
			array('schema_id1' => 'junii2:isRequiredBy', 'schema_id2' => 'oai_dc:relation', 'number' => 10),
			array('schema_id1' => 'junii2:requires', 'schema_id2' => 'oai_dc:relation', 'number' => 11),
			array('schema_id1' => 'junii2:isPartOf', 'schema_id2' => 'oai_dc:relation', 'number' => 12),
			array('schema_id1' => 'junii2:hasPart', 'schema_id2' => 'oai_dc:relation', 'number' => 13),
			array('schema_id1' => 'junii2:isReferencedBy', 'schema_id2' => 'oai_dc:relation', 'number' => 14),
			array('schema_id1' => 'junii2:references', 'schema_id2' => 'oai_dc:relation', 'number' => 15),
			array('schema_id1' => 'junii2:isFormatOf', 'schema_id2' => 'oai_dc:relation', 'number' => 16),
			array('schema_id1' => 'junii2:hasFormat', 'schema_id2' => 'oai_dc:relation', 'number' => 17),
			array('schema_id1' => 'junii2:coverage', 'schema_id2' => 'oai_dc:coverage', 'number' => 1),
			array('schema_id1' => 'junii2:spatial', 'schema_id2' => 'oai_dc:coverage', 'number' => 2),
			array('schema_id1' => 'junii2:NIIspatial', 'schema_id2' => 'oai_dc:coverage', 'number' => 3),
			array('schema_id1' => 'junii2:temporal', 'schema_id2' => 'oai_dc:coverage', 'number' => 4),
			array('schema_id1' => 'junii2:NIItemporal', 'schema_id2' => 'oai_dc:coverage', 'number' => 5),
			array('schema_id1' => 'junii2:rights', 'schema_id2' => 'oai_dc:rights', 'number' => 1),
			array('schema_id1' => 'junii2:grantid', 'schema_id2' => 'oai_dc:identifier', 'number' => 9),
			array('schema_id1' => 'junii2:dateofgranted', 'schema_id2' => 'oai_dc:date', 'number' => 3),
			array('schema_id1' => 'junii2:degreename', 'schema_id2' => 'oai_dc:description', 'number' => 2),
			array('schema_id1' => 'junii2:grantor', 'schema_id2' => 'oai_dc:description', 'number' => 3),
		);
		$oaipmhBean = Xoonips_BeanFactory::getBean('OaipmhSchemaBean', $dirname, $trustDirname);
		$schemaid = array();
		$metadata_prefix = '';
		foreach ($oaipmhlist as &$oaipmh) {
			if ($metadata_prefix != $oaipmh['metadata_prefix']) {
				$weight = 1;
				$metadata_prefix = $oaipmh['metadata_prefix'];
			} else {
				$weight = $weight + 1;
			}
			$oaipmh['weight'] = $weight;
			$id = '';
			if (!$oaipmhBean->insert($oaipmh, $id)) {
				return false;
			}
			$schemaid[($oaipmh['metadata_prefix'] . ':' . $oaipmh['name'])] = $id;
		}
		foreach ($oaipmhvaluelist as &$oaipmhvalue) {
			$oaipmhvalue['schema_id'] = $schemaid[$oaipmhvalue['schema_id']];
			$id = '';
			if (!$oaipmhBean->insertValue($oaipmhvalue, $id))
				return false;
		}
		foreach ($oaipmhlinklist as &$oaipmhlink) {
			$oaipmhlink['schema_id1'] = $schemaid[$oaipmhlink['schema_id1']];
			$oaipmhlink['schema_id2'] = $schemaid[$oaipmhlink['schema_id2']];
			if (!$oaipmhBean->insertLink($oaipmhlink))
				return false;
		}
		return true;
	}

	/**
	 * install item sort
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @return bool
	 */
	private static function installItemSort($dirname, $trustDirname) {
		$constpref = '_MI_' . strtoupper($dirname);
		$titles = array(
			constant($constpref . '_INSTALL_COMMON_TITLE'),
			constant($constpref . '_INSTALL_COMMON_ID'),
			constant($constpref . '_INSTALL_COMMON_LAST_UPDATE'),
			constant($constpref . '_INSTALL_COMMON_CREATION_DATE'),
		);
		$handler = Xoonips_Utils::getTrustModuleHandler('ItemSort', $dirname, $trustDirname);
		foreach ($titles as $title) {
			$obj =& $handler->create();
			$obj->set('title', $title);
			if (!$handler->insert($obj))
				return false;
		}
		return true;
	}

	/**
	 * install default item type
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @return bool
	 */
	private static function installDefaultItemtype($dirname, $trustDirname) {
		$constpref = '_MI_' . strtoupper($dirname);
		$installDefaultItemtype = new Xoonips_ImportItemType($dirname, $trustDirname);
		$xmlFile = XOONIPS_TRUST_PATH."/itemtype/Default/Default.xml";
		$defaultObj = $installDefaultItemtype->getSimpleXMLElement($xmlFile);
		if ($defaultObj === false) {
			return false;
		}
		$id = '';
		if (!$installDefaultItemtype->installDefaultItemtype($defaultObj, $id))
			return false;
		return true;
	}

	/**
	 * install item quick search conditions
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @return bool
	 */
	private static function installItemQuickSearchCondition($dirname, $trustDirname) {
		$constpref = '_MI_' . strtoupper($dirname);
		$searchConds = array(
			array(
				'condition_title' => constant($constpref . '_INSTALL_SEARCH_ALL'),
				'field_names' => array(),
			),
			array(
				'condition_title' => constant($constpref . '_INSTALL_SEARCH_TITLE_KEYWORD'),
				'field_names' => array(
					constant($constpref . '_INSTALL_COMMON_TITLE'),
					constant($constpref . '_INSTALL_COMMON_KEYWORD'),
				),
			),
		);
		$chandler = Xoonips_Utils::getTrustModuleHandler('ItemQuickSearchCondition', $dirname, $trustDirname);
		$fhandler = Xoonips_Utils::getTrustModuleHandler('ItemField', $dirname, $trustDirname);
		$conditions = $chandler->getConditions();
		foreach ($searchConds as $searchCond) {
			// install title
			$title = $searchCond['condition_title'];
			$cobj = $chandler->create();
			$cobj->set('title', $title);
			if (!$chandler->insert($cobj))
				return false;
			// install item fields
			if (empty($searchCond['field_names']))
				continue;
			$fids = array();
			$criteria = new Criteria('name', $searchCond['field_names'], 'IN');
			$fobjs = $fhandler->getObjects($criteria, null, null, true);
			if (count($fobjs) > 0) {
				$fids = array_keys($fobjs);
				if (!$chandler->updateItemFieldIds($cobj, $fids))
					return false;
			}
		}
		return true;
	}

	/**
	 * install all xml item type
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @param Legacy_ModuleInstallLog &$log
	 *
	 * @return bool
	 */
	private static function installAllXMLItemtype($dirname, $trustDirname, &$log) {
		$constpref = '_MI_' . strtoupper($dirname);
		$filePath = sprintf('%s/modules/%s/itemtype', XOOPS_TRUST_PATH, $trustDirname);
		if (!(file_exists($filePath) && is_dir($filePath)))
			return false;
		$itemtypes = array();
		$xmlDir = opendir($filePath);
		while (false !== ($filename = readdir($xmlDir))) {
			$info = pathinfo($filename);
			$itemtype = $info['filename'];
			if ($filename == '.' || $filename == '..' || preg_match("/^\.+/", $filename) || $filename == "Default")
				continue;
			$itemtypes[] = $itemtype;
		}
		closedir($xmlDir);
		asort($itemtypes);
		$installItemtype = new Xoonips_ImportItemType($dirname, $trustDirname);
		foreach ($itemtypes as $itemtype) {
			if (!file_exists($filePath."/".$itemtype."/".$itemtype.".xml"))
				continue;
			if ($installItemtype->installItemType($filePath, $itemtype)) {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_ITEMTYPE_INSTALLED'), $itemtype));
			} else {
				$log->addError(XCube_Utils::formatString(_MI_XOONIPS_INSTALL_ERROR_ITEMTYPEL_INSTALLED, $itemtype));
				return false;
			}
		}
		return true;
	}

	/**
	 * install index
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @return bool
	 */
	private static function installIndex($dirname, $trustDirname) {
		$indexlist = array(
			array('parent_index_id' => 0, 'open_level' => XOONIPS_OL_PUBLIC, 'weight' => 1, 'title' => 'Root'),
			array('parent_index_id' => 1, 'open_level' => XOONIPS_OL_PUBLIC, 'weight' => 1, 'title' => 'Public'),
		);
		$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $dirname, $trustDirname);
		foreach ($indexlist as $index) {
			$index['uid'] = NULL;
			$index['groupid'] = NULL;
			if (!$indexBean->insertIndex($index))
				return false;
		}
		return true;
	}

}

