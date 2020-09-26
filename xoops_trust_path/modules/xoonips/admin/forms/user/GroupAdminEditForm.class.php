<?php

require_once XOOPS_ROOT_PATH.'/core/XCube_ActionForm.class.php';
require_once XOOPS_MODULE_PATH.'/legacy/class/Legacy_Validator.class.php';

/**
 * group admin edit form.
 */
class Xoonips_GroupAdminEditForm extends XCube_ActionForm
{
    protected $mName;
    protected $mType;

    protected $mDirname = '';
    protected $mTrustDirname = '';

    /**
     * constructor.
     *
     * @param string $dirname
     * @param string $trustDirname
     */
    public function __construct($dirname, $trustDirname)
    {
        parent::__construct();
        $this->mDirname = $dirname;
        $this->mTrustDirname = $trustDirname;
    }

    /**
     * get form params.
     *
     * @return array
     */
    public function getFormParams()
    {
        static $params = null;
        if (null !== $params) {
            return $params;
        }
        $constpref = '_AD_'.strtoupper($this->mDirname);
        $params = [
            'groupid' => [
                'type' => 'i',
                'label' => _AD_USER_LANG_GROUP_GID,
                'depends' => [
                    'required' => true,
                ],
            ],
            // activate
            'name' => [
                'type' => 's',
                'label' => _AD_USER_LANG_GROUP_NAME,
                'depends' => [
                    'required' => true,
                    'maxlength' => 50,
                ],
            ],
            'description' => [
                'type' => 't',
            ],
            // icon
            // mime_type
            'is_public' => [
                'type' => 'i',
            ],
            'can_join' => [
                'type' => 'i',
            ],
            'is_hidden' => [
                'type' => 'i',
            ],
            'member_accept' => [
                'type' => 'i',
            ],
            'item_accept' => [
                'type' => 'i',
            ],
            'item_number_limit' => [
                'type' => 'i',
            ],
            'index_number_limit' => [
                'type' => 'i',
            ],
            'item_storage_limit' => [
                'type' => 'f',
            ],
            // index_id
            'group_type' => [
                'type' => 's',
                'label' => _AD_USER_LANG_GROUP_TYPE,
                'depends' => [
                    'required' => true,
                    'maxlength' => 10,
                ],
            ],
            // additional informations
            'adminUids' => [
                'type' => 'ai',
            ],
            'iconDelete' => [
                'type' => 'i',
            ],
            'iconFile' => [
                'type' => 'I',
            ],
        ];

        return $params;
    }

    /**
     * get token name.
     *
     * @return string
     */
    public function getTokenName()
    {
        $name = str_replace(ucfirst($this->mDirname).'_', '', get_class($this));

        return 'module.'.$this->mDirname.'.'.$name.'.TOKEN';
    }

    /**
     * prepare.
     */
    public function prepare()
    {
        $constpref = '_AD_'.strtoupper($this->mDirname);
        // set form properties
        $params = $this->getFormParams();
        foreach ($params as $key => $info) {
            switch ($info['type']) {
            case 'i':
                $this->mFormProperties[$key] = new XCube_IntProperty($key);
                break;
            case 'f':
                $this->mFormProperties[$key] = new XCube_FloatProperty($key);
                break;
            case 's':
                $this->mFormProperties[$key] = new XCube_StringProperty($key);
                break;
            case 't':
                $this->mFormProperties[$key] = new XCube_TextProperty($key);
                break;
            case 'F':
                $this->mFormProperties[$key] = new XCube_FileProperty($key);
                break;
            case 'I':
                $this->mFormProperties[$key] = new XCube_ImageFileProperty($key);
                break;
            case 'ai':
                $this->mFormProperties[$key] = new XCube_IntArrayProperty($key);
                break;
            case 'af':
                $this->mFormProperties[$key] = new XCube_FloatArrayProperty($key);
                break;
            case 'as':
                $this->mFormProperties[$key] = new XCube_StringArrayProperty($key);
                break;
            case 'at':
                $this->mFormProperties[$key] = new XCube_TextArrayProperty($key);
                break;
            case 'aF':
                $this->mFormProperties[$key] = new XCube_FileArrayProperty($key);
                break;
            case 'aI':
                $this->mFormProperties[$key] = new XCube_ImageFileArrayProperty($key);
                break;
            }
        }
        // set field properties
        foreach ($params as $key => $info) {
            if (isset($info['depends']) && is_array($info['depends'])) {
                $depends = $info['depends'];
                $this->mFieldProperties[$key] = new XCube_FieldProperty($this);
                $this->mFieldProperties[$key]->setDependsByArray(array_keys($depends));
                if (isset($depends['required']) && $depends['required']) {
                    $this->mFieldProperties[$key]->addMessage('required', constant($constpref.'_ERROR_REQUIRED'), $info['label']);
                }
                if (isset($depends['minlength']) && $depends['minlength'] > 0) {
                    $this->mFieldProperties[$key]->addMessage('minlength', constant($constpref.'_ERROR_MINLENGTH'), $info['label'], $depends['minlength']);
                    $this->mFieldProperties[$key]->addVar('minlength', $depends['minlength']);
                }
                if (isset($depends['maxlength']) && $depends['maxlength'] > 0) {
                    $this->mFieldProperties[$key]->addMessage('maxlength', constant($constpref.'_ERROR_MAXLENGTH'), $info['label'], $depends['maxlength']);
                    $this->mFieldProperties[$key]->addVar('maxlength', $depends['maxlength']);
                }
                if (isset($depends['min']) && $depends['min'] > 0) {
                    $this->mFieldProperties[$key]->addMessage('min', constant($constpref.'_ERROR_MIN'), $info['label'], $depends['min']);
                    $this->mFieldProperties[$key]->addVar('min', $depends['min']);
                }
                if (isset($depends['max']) && $depends['max'] > 0) {
                    $this->mFieldProperties[$key]->addMessage('max', constant($constpref.'_ERROR_MAX'), $info['label'], $depends['max']);
                    $this->mFieldProperties[$key]->addVar('max', $depends['max']);
                }
            }
        }
    }

    /**
     * validate group name.
     */
    public function validateName()
    {
        $name = trim($this->get('name'));
        if ($this->mName != $name) {
            $groupsBean = Xoonips_BeanFactory::getBean('GroupsBean', $this->mDirname, $this->mTrustDirname);
            if ($groupsBean->existsGroup($name)) {
                $constpref = '_AD_'.strtoupper($this->mDirname);
                $this->addErrorMessage(XCube_Utils::formatString(constant($constpref.'_ERROR_DUPLICATED'), _AD_USER_LANG_GROUP_NAME));
            }
        }
    }

    /**
     * validate group admin uids.
     */
    public function validateAdminUids()
    {
        $type = $this->get('group_type');
        $uids = $this->get('adminUids');
        if (Xoonips_Enum::GROUP_TYPE == $type && empty($uids)) {
            $constpref = '_AD_'.strtoupper($this->mDirname);
            $this->addErrorMessage(XCube_Utils::formatString(constant($constpref.'_ERROR_REQUIRED'), constant($constpref.'_USER_LANG_GROUP_ADMINS')));
        }
    }

    /**
     * validate group icon file.
     */
    public function validateIconFile()
    {
        $iconFile = $this->get('iconFile');
        if (isset($_FILES['iconFile']['error']) && UPLOAD_ERR_OK == $_FILES['iconFile']['error'] && null === $iconFile) {
            $constpref = '_AD_'.strtoupper($this->mDirname);
            $this->addErrorMessage(XCube_Utils::formatString(constant($constpref.'_ERROR_INPUTFILE'), constant($constpref.'_USER_LANG_GROUP_ICON')));
        }
    }

    /**
     * validate is hidden.
     */
    public function validateIs_hidden()
    {
        if (1 == $this->get('is_hidden')) {
            $this->set('is_public', 0);
            $this->set('can_join', 0);
        }
    }

    /**
     * validate group type.
     */
    public function validateGroup_type()
    {
        $type = trim($this->get('group_type'));
        if ($this->mType != $type) {
            $constpref = '_AD_'.strtoupper($this->mDirname);
            $this->addErrorMessage(XCube_Utils::formatString(constant($constpref.'_ERROR_INPUTVALUE'), _AD_USER_LANG_GROUP_TYPE));
        }
    }

    /**
     * load.
     *
     * @param array &$params
     */
    public function load(&$params)
    {
        $skipTypes = ['F', 'I', 'aF', 'aI'];
        $keys = $this->getFormParams();
        foreach (array_keys($params) as $key) {
            if (isset($keys[$key]) && !in_array($keys[$key]['type'], $skipTypes)) {
                $this->set($key, $params[$key]);
            }
        }
        $this->mName = trim($this->get('name'));
        $this->mType = trim($this->get('group_type'));
    }

    /**
     * update.
     *
     * @param array &$params
     */
    public function update(&$params)
    {
        $skipKeysOnPending = ['is_public', 'can_join', 'is_hidden'];
        $isPending = (Xoonips_Enum::GROUP_TYPE == $params['group_type'] && Xoonips_Enum::GRP_CERTIFIED != $params['activate']);
        $keys = $this->getFormParams();
        foreach ($keys as $key => $info) {
            if (!$isPending || !in_array($key, $skipKeysOnPending)) {
                $params[$key] = $this->get($key);
            }
        }
    }
}
