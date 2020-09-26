<?php

/**
 * admin policy index form.
 */
class Xoonips_Admin_PolicyIndexForm extends Xoonips_AbstractActionForm
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
        $constpref = '_AD_'.strtoupper($this->mDirname);

        return [
            'index_upload_dir' => [
                'type' => self::TYPE_STRING,
                's',
                'label' => constant($constpref.'_SYSTEM_BASIC_UPLOAD_DIR_TITLE'),
                'depends' => [
                    'required' => true,
                ],
            ],
        ];
    }

    /**
     * validate index upload dir.
     */
    public function validateIndex_upload_dir()
    {
        $constpref = '_AD_'.strtoupper($this->mDirname);
        $value = trim($this->get('index_upload_dir'));
        $is_windows = ('WIN' === strtoupper(substr(PHP_OS, 0, 3)));
        if (empty($value)) {
            return;
        }
        if ($is_windows) {
            $value = str_replace('\\', '/', $value);
        }
        if ('/' != $value && '/' == substr($value, -1)) {
            $value = substr($value, 0, -1);
        }
        if (!is_dir($value)) {
            $this->addErrorMessage(constant($constpref.'_ERROR_UPLOAD_DIRECTORY'));
        } elseif (!self::is_writable($value) || !is_readable($value) || (!$is_windows && !is_executable($value))) {
            $this->addErrorMessage(constant($constpref.'_ERROR_UPLOAD_DIRECTORY'));
        } elseif (!is_dir($value.'/item')) {
            if (!@mkdir($value.'/item')) {
                $this->addErrorMessage(constant($constpref.'_ERROR_UPLOAD_DIRECTORY'));
            }
        }
        $this->set('index_upload_dir', $value);
    }

    /**
     * tells whether the filename is writable.
     *
     * @param string $path
     *
     * @return bool false if writable
     * @see: http://www.php.net/manual/en/function.is-writable.php
     */
    public static function is_writable($path)
    {
        $is_windows = ('WIN' === strtoupper(substr(PHP_OS, 0, 3)));
        if (!$is_windows) {
            return is_writable($path);
        } // it works if not IIS
        // will work in despite of Windows ACLs bug
        // NOTE: use a trailing slash for folders!!!
        // see http://bugs.php.net/bug.php?id=27609
        // see http://bugs.php.net/bug.php?id=30931
        if ('/' == substr($path, -1)) {
            // recursively return a temporary file path
            return self::is_writable($path.uniqid(mt_rand()).'.tmp');
        } elseif (is_dir($path)) {
            return self::is_writable($path.'/'.uniqid(mt_rand()).'.tmp');
        }
        // check tmp file for read/write capabilities
        $rm = file_exists($path);
        if (false === ($f = @fopen($path, 'a'))) {
            return false;
        }
        fclose($f);
        if (!$rm) {
            @unlink($path);
        }

        return true;
    }
}
