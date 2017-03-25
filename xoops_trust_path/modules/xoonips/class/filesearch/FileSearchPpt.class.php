<?php

/**
 * file search plugin class for PowerPoint.
 */
class Xoonips_FileSearchPpt extends Xoonips_FileSearchBase
{
    /**
     * constractor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->is_xml = true;
        $this->is_utf8 = true;
        $this->is_file = false;
    }

    /**
     * get definition of PowerPoint file search.
     *
     * @return array definition of PowerPoint file search
     */
    public function getSearchInstance()
    {
        return array(
            'name' => 'ppt',
            'display_name' => 'MS-PowerPoint 95/97/2000/XP/2003',
            'mime_type' => array('application/vnd.ms-powerpoint', 'application/vnd.ms-office'),
            'extensions' => array('ppt'),
            'version' => '2.0',
        );
    }

    /**
     * open file or process resource.
     *
     * @acccess protected
     *
     * @param string $filename file name
     */
    protected function openImpl($filename)
    {
        parent::openImpl(sprintf('ppthtml %s', escapeshellarg($filename)));
    }
}
