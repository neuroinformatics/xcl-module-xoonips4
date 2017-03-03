<?php

/**
 * admin index action
 */
class Xoonips_Admin_IndexAction extends Xoonips_AbstractAction {

	/**
	 * get style sheet
	 * 
	 * @return string
	 */
	protected function _getStylesheet() {
		return '/modules/' . $this->mAsset->mDirname . '/admin/index.php/css/admin_style.css';
	}

	/**
	 * getDefaultView
	 * 
	 * @return Enum
	 */
	public function getDefaultView() {
		return $this->_getFrameViewStatus('INDEX');
	}

	/**
	 * executeViewIndex
	 * 
	 * @param XCube_RenderTarget &$render
	 */
	public function executeViewIndex(&$render) {
		$dirname = $this->mAsset->mDirname;
		$constpref = '_AD_' . strtoupper($dirname);
		$render->setTemplateName('admin_menu.html');
		$render->setAttribute('title', constant($constpref . '_TITLE'));
		$render->setAttribute('adminMenu', $this->mModule->getAdminMenu());

		// toptab
                $mHandler =& xoops_gethandler('module');
                if ($mHandler->getByDirname('altsys')) {
			$altsysAdminUrl = XOOPS_URL . '/modules/altsys/admin/index.php';
			$toptab = array(
				array(
					'title' => _MI_ALTSYS_MENU_MYBLOCKSADMIN,
					'link' => $altsysAdminUrl . '?mode=admin&lib=altsys&page=myblocksadmin&dirname='.$dirname,
					'class' => 'blocksAdmin'
				),
				array(
					'title' => _MI_ALTSYS_MENU_MYTPLSADMIN,
					'link' => $altsysAdminUrl . '?mode=admin&lib=altsys&page=mytplsadmin&dirname='.$dirname,
					'class' => 'tplsAdmin'
				),
				array(
					'title' => _MI_ALTSYS_MENU_MYLANGADMIN,
					'link' => $altsysAdminUrl . '?mode=admin&lib=altsys&page=mylangadmin&dirname='.$dirname,
					'class' => 'langAdmin'
				),
			);
			$render->setAttribute('toptab', $toptab);
		}
	}
}

