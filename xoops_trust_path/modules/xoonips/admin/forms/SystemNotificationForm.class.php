<?php

/**
 * admin system notification form.
 */
class Xoonips_Admin_SystemNotificationForm extends Xoonips_AbstractActionForm
{
    /**
     * is admin mode.
     *
     * @return bool
     */
    protected function _isAdminMode()
    {
        return true;
    }

    /**
     * get form params.
     *
     * @return array
     */
    protected function _getFormParams()
    {
        return array(
            'notification_enabled' => array(
                'type' => self::TYPE_INT,
            ),
            'notification_events' => array(
                'type' => self::TYPE_STRINGARRAY,
            ),
        );
    }

    /**
     * load.
     *
     * @param XoopsSimpleObject &$obj
     */
    public function load(&$configs)
    {
        $this->set('notification_enabled', $configs['notification_enabled']->get('conf_value'));
        $this->set('notification_events', unserialize($configs['notification_events']->get('conf_value')));
    }

    /**
     * update.
     *
     * @param array &$configs
     */
    public function update(&$configs)
    {
        $configs['notification_enabled']->set('conf_value', $this->get('notification_enabled'));
        $configs['notification_events']->set('conf_value', serialize($this->get('notification_events')));
    }
}
