<?php

use Xoonips\Core\StringUtils;

/**
 * file search plugin class for TEXT.
 */
class Xoonips_FileSearchText extends Xoonips_FileSearchBase
{
    /**
     * constractor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->is_xml = false;
        $this->is_file = true;
    }

    /**
     * get definition of Text file search.
     *
     * @return array definition of Text file search
     */
    public function getSearchInstance()
    {
        return [
            'name' => 'text',
            'display_name' => 'Plain Text',
            'mime_type' => ['text/plain'],
            'extensions' => ['txt', 'text'],
            'version' => '2.0',
        ];
    }

    /**
     * fetch 'UTF-8' text from file or process.
     *
     * @param string $text fetched data
     *
     * @return string processed fetched data
     */
    protected function fetchImpl($text)
    {
        $encoding = StringUtils::detectTextEncoding($text);
        $text = StringUtils::convertEncoding($text, 'UTF-8', $encoding, 'h');

        return $text;
    }
}
