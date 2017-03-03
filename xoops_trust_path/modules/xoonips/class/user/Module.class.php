<?php

require_once XOOPS_MODULE_PATH . '/user/class/Module.class.php';

/**
 * user module
 */
class Xoonips_UserModule extends User_Module {

	/**
	 * flag for admin page
	 * @var bool
	 */
	public $mAdminFlag = false;

	/**
	 * set admin mode
	 * 
	 * @param bool $flag
	 */
	public function setAdminMode($flag) {
		$this->mAdminFlag = $flag;
	}

       /**
	 * get render system name
	 * 
	 * @return string
	 */
	public function getRenderSystemName() {
		static $isFirst = true;
		if (!$this->mAdminFlag)
			return parent::getRenderSystemName();
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$adminRenderSystem =  ucfirst($trustDirname) . '_UserAdminRenderSystem';
		if ($isFirst) {
			// register self admin render system at once
			$root =& XCube_Root::getSingleton();
			$root->overrideSiteConfig(
				array(
					'RenderSystems' => array(
						$adminRenderSystem => $adminRenderSystem
					),
					$adminRenderSystem => array(
						'root' => XOOPS_TRUST_PATH . '/modules/' . $trustDirname,
						'path' => '/admin/class/user/AdminRenderSystem.class.php',
						'class' => $adminRenderSystem
					)
				)
			);
			$isFirst = false;
		}
		return $adminRenderSystem;
	}

}

