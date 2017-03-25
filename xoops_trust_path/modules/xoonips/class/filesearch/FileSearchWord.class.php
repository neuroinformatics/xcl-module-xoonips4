<?php

class Xoonips_FileSearchWord extends Xoonips_FileSearchBase
{
    /**
     * temporary file path for wv output data.
     *
     * @var string temporary file path
     */
    private $tmpfile = '';

    /**
     * flag to use antiword for file reader.
     *
     * @var bool true if use antiword
     */
    private $use_antiword = false;

    /**
     * environemnt variable for antword 'ANTIWORDHOME'.
     *
     * @var string
     */
    private $antiwordhome = '/usr/local/antiword';

    /**
     * constractor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->is_xml = false;
        $this->is_file = true;
        // for antiword
        // $this->use_antiword = true;
        // $this->antiwordhome = '/foo/bar';
    }

    /**
     * get definition of Word file search.
     *
     * @return array definition of Word file search
     */
    public function getSearchInstance()
    {
        return array(
            'name' => 'word',
            'display_name' => 'MS-Word 95/97/2000/XP/2003',
            'mime_type' => array('application/msword'),
            'extensions' => array('doc'),
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
        if ($this->use_antiword) {
            // for antiword
            putenv('ANTIWORDHOME='.$this->antiwordhome);
            parent::openImpl(sprintf('antiword -t -m UTF-8.txt %s', $filename));
        } else {
            // for wv
            $this->tmpfile = tempnam(sys_get_temp_dir(), 'FileSearchWord');
            $cmd = $this->bin_dir.sprintf('wvText %s %s', escapeshellarg($filename), escapeshellarg($this->tmpfile));
            // set LANG to UTF-8 for wvText(elinks)
            $lang = getenv('LANG');
            putenv('LANG=en_US.UTF-8');
            // execute wvText command
            system($cmd);
            // restore original lang
            putenv('LANG='.($lang === false ? '' : $lang));
            parent::openImpl($this->tmpfile);
        }
    }

    /**
     * close file or process resource.
     *
     * @acccess protected
     */
    protected function closeImpl()
    {
        parent::closeImpl();
        if ($this->use_antiword) {
            putenv('ANTWORDHOME=');
        } else {
            @unlink($this->tmpfile);
        }
    }
}
