<?php

class Xoonips_UserFilterForm extends User_UserFilterForm {

	function fetch() {
		parent::fetch();
		$root =& XCube_Root::getSingleton();
		$option_field = $root->mContext->mRequest->getRequest('option_field'); 
		if (isset($_REQUEST['option_field'])) {
 			if ( $option_field == 'active' ) {
				// only activated users
				$this->_mCriteria->add(new Criteria('level', '2', '<'));
			} else if ( $option_field == 'certified' ) {
				// certified users
				$this->_mCriteria->add(new Criteria('level', '1', '>'));
 			}
		}
	}

}

