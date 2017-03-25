<?php

/**
 * file search plugin class for PDF.
 */
class Xoonips_FileSearchPdf extends Xoonips_FileSearchBase
{
    /**
     * constractor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->is_xml = false;
        $this->is_utf8 = true;
        $this->is_file = false;
    }

    /**
     * get definition of PDF file search.
     *
     * @return array definition of PDF file search
     */
    public function getSearchInstance()
    {
        return array(
            'name' => 'pdf',
            'display_name' => 'PDF',
            'mime_type' => array('application/pdf'),
            'extensions' => array('pdf'),
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
        parent::openImpl(sprintf('pdftotext -q -enc UTF-8 %s -', escapeshellarg($filename)));
    }
}
