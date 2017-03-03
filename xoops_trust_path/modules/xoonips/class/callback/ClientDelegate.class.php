<?php

require_once dirname(dirname(__FILE__)) . '/core/WorkflowClientFactory.class.php';

/**
 * workflow client delegate class
 */
class Xoonips_WorkflowClientDelegate implements Legacy_iWorkflowClientDelegate {

	/**
	 * data names
	 * @var array
	 */
	private static $mDatanames = array(
		Xoonips_Enum::WORKFLOW_USER => array(
			'label' => '_LANG_WORKFLOW_USER',
			'hasGroupAdmin' => false,
		),
		Xoonips_Enum::WORKFLOW_GROUP_REGISTER => array(
			'label' => '_LANG_WORKFLOW_GROUP_REGISTER',
			'hasGroupAdmin' => false,
		),
		Xoonips_Enum::WORKFLOW_GROUP_DELETE => array(
			'label' => '_LANG_WORKFLOW_GROUP_DELETE',
			'hasGroupAdmin' => false,
		),
		Xoonips_Enum::WORKFLOW_GROUP_OPEN => array(
			'label' => '_LANG_WORKFLOW_GROUP_OPEN',
			'hasGroupAdmin' => false,
		),
		Xoonips_Enum::WORKFLOW_GROUP_CLOSE => array(
			'label' => '_LANG_WORKFLOW_GROUP_CLOSE',
			'hasGroupAdmin' => false,
		),
		Xoonips_Enum::WORKFLOW_PUBLIC_ITEMS => array(
			'label' => '_LANG_WORKFLOW_PUBLIC_ITEMS',
			'hasGroupAdmin' => false,
		),
		Xoonips_Enum::WORKFLOW_PUBLIC_ITEMS_WITHDRAWAL => array(
			'label' => '_LANG_WORKFLOW_PUBLIC_ITEMS_WITHDRAWAL',
			'hasGroupAdmin' => false,
		),
		Xoonips_Enum::WORKFLOW_GROUP_JOIN => array(
			'label' => '_LANG_WORKFLOW_GROUP_JOIN',
			'hasGroupAdmin' => true,
		),
		Xoonips_Enum::WORKFLOW_GROUP_LEAVE => array(
			'label' => '_LANG_WORKFLOW_GROUP_LEAVE',
			'hasGroupAdmin' => true,
		),
		Xoonips_Enum::WORKFLOW_GROUP_ITEMS => array(
			'label' => '_LANG_WORKFLOW_GROUP_ITEMS',
			'hasGroupAdmin' => true,
		),
		Xoonips_Enum::WORKFLOW_GROUP_ITEMS_WITHDRAWAL => array(
			'label' => '_LANG_WORKFLOW_GROUP_ITEMS_WITHDRAWAL',
			'hasGroupAdmin' => true,
		),
	);

	/**
	 * get client list
	 *   'Legacy_WorkflowClient.GetClientList' delegete
	 *
	 * @param mixed[] &$list
	 *  $list[]['dirname']       dirname
	 *  $list[]['dataname']      dataname
	 *  $list[]['label']         (Xworkflow extended)
	 *  $list[]['hasGroupAdmin'] (Xworkflow extended)
	 */
	public static function getClientList(&$list) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		foreach ($dirnames as $dirname) {
			XCube_Root::getSingleton()->mLanguageManager->loadModuleMessageCatalog($dirname);
			$constpref = '_MD_' . strtoupper($dirname);
			foreach (self::$mDatanames as $dataname => $info) {
				$list[] = array(
					'dirname' => $dirname,
					'dataname' => $dataname,
					'label' => constant($constpref . $info['label']),
					'hasGroupAdmin' => $info['hasGroupAdmin'],
				);
			}
		}
	}

	/**
	 * updateStatus
	 *   'Legacy_WorkflowClient.UpdateStatus' delegete
	 *
	 * @param string &$result
	 * @param string $dirname
	 * @param string $dataname
	 * @param int $data_id
	 * @param Lenum_WorkflowStatus::Enum $status
	 */
	public static function updateStatus(&$result, $dirname, $dataname, $data_id, $status) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		if (!in_array($dirname, $dirnames))
			return;
		XCube_Root::getSingleton()->mLanguageManager->loadModuleMessageCatalog($dirname);
		$workflow = Xoonips_WorkflowClientFactory::getWorkflow($dataname, $dirname, $trustDirname);
		if ($status == Lenum_WorkflowStatus::FINISHED) {
			$workflow->doCertify($data_id, $result);
		} elseif ($status == Lenum_WorkflowStatus::PROGRESS) {
			$workflow->doProgress($data_id);
		} elseif ($status == Lenum_WorkflowStatus::REJECTED) {
			$workflow->doRefuse($data_id, $result);
		}
	}

	/**
	 * get target group id
	 *   'Xleprogress_WorkflowClient.GetTargetGroupId' delegete
	 *
	 * @param int &$result
	 * @param string $dirname
	 * @param string $dataname
	 * @param int $target_id
	 */
	public static function getTargetGroupId(&$gid, $dirname, $dataname, $target_id) {
		$trustDirname = basename(dirname(dirname(dirname(__FILE__))));
		$dirnames = Legacy_Utils::getDirnameListByTrustDirname($trustDirname);
		if (!in_array($dirname, $dirnames))
			return;
		switch ($dataname) {
		case Xoonips_Enum::WORKFLOW_GROUP_JOIN:
		case Xoonips_Enum::WORKFLOW_GROUP_LEAVE:
			// groups_users_link
			$groupsUsersLinkBean = Xoonips_BeanFactory::getBean('GroupsUsersLinkBean', $dirname, $trustDirname);
			if (($info = $groupsUsersLinkBean->getGroupUserLinkInfoByLinkId($target_id)) !== false && !empty($info))
				$gid = $info['groupid'];
			break;
		case Xoonips_Enum::WORKFLOW_GROUP_ITEMS:
		case Xoonips_Enum::WORKFLOW_GROUP_ITEMS_WITHDRAWAL:
			// item_index_link
			$indexItemLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $dirname, $trustDirname);
			$indexBean = Xoonips_BeanFactory::getBean('IndexBean', $dirname, $trustDirname);
			if (($info = $indexItemLinkBean->getIndexItemLinkInfoByIndexItemLinkId($target_id)) !== false && !empty($info))
				if (($info = $indexBean->getIndex($info['index_id'])) !== false)
					$gid = $info['groupid'];
			break;
		}
	}

}

