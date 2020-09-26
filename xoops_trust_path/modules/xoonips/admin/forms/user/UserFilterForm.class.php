<?php

class Xoonips_UserFilterForm extends User_UserFilterForm
{
    public function fetch()
    {
        parent::fetch();
        $root = &XCube_Root::getSingleton();
        $option_field = $root->mContext->mRequest->getRequest('option_field');
        if (isset($_REQUEST['option_field'])) {
            if ('active' == $option_field) {
                // only activated users
                $this->_mCriteria->add(new Criteria('level', '2', '<'));
            } elseif ('certified' == $option_field) {
                // certified users
                $this->_mCriteria->add(new Criteria('level', '1', '>'));
            }
        }
    }
}
