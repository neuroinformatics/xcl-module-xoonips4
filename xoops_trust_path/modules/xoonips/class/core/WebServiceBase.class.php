<?php

use Xoonips\Core\Functions;

require_once XOOPS_ROOT_PATH.'/class/xml/saxparser.php';
require_once XOOPS_ROOT_PATH.'/class/snoopy.php';

class Xoonips_WebServiceBase extends SaxParser
{
    /**
     * the xml data, fetch() function will set results to this variable.
     *
     * @var string
     */
    private $xml_data = '';

    /**
     * the fetcher target url.
     *
     * @var string
     */
    protected $fetch_url = '';

    /**
     * the fetcher arguments.
     *
     * @var array
     */
    protected $fetch_arguments = [];

    /**
     * the xml document type.
     *
     * @var string
     */
    protected $parser_doctype = '';

    /**
     * if url encoding is RFC3986, true.
     *
     * @var bool
     */
    protected $isRFC3986 = false;

    /**
     * the public id of xml document.
     *
     * @var string
     */
    protected $parser_public_id = '';

    /**
     * the system id of xml document.
     *
     * @var string
     */
    protected $parser_system_id = '';

    /**
     * the perser xpath.
     *
     * @var string
     */
    protected $parser_xpath = '';

    /**
     * parsed data.
     *
     * @var array
     */
    public $data = [];

    /**
     * snoopy.
     *
     * @var object
     */
    private $snoopy;

    /**
     * config table class.
     *
     * @var object
     */
    protected $configBean;

    /**
     * constructor
     * normally, the is called from child classes only.
     */
    public function __construct()
    {
        global $xoopsModule;
        $dirname = strtolower($xoopsModule->getVar('dirname'));
        $trustDirname = $xoopsModule->getVar('trust_dirname');

        $this->snoopy = new Snoopy();

        // get proxy config
        $this->snoopy->proxy_host = Functions::getXoonipsConfig($dirname, 'proxy_host');
        $this->snoopy->proxy_port = Functions::getXoonipsConfig($dirname, 'proxy_port');
        $this->snoopy->proxy_user = Functions::getXoonipsConfig($dirname, 'proxy_user');
        $this->snoopy->proxy_pass = Functions::getXoonipsConfig($dirname, 'proxy_pass');

        // disable gzip support.
        // Snoopy 2.0.0 can not handle 'content-encoding: gzip' lower case header.
        $this->snoopy->use_gzip = false;
    }

    /**
     * fetch the xml data from target url.
     *
     * @return bool false if failure
     */
    public function fetch()
    {
        // create fetch url
        $url = $this->createUrl();

        // fetch data using snoopy class
        if (!$this->snoopy->fetch($url)) {
            return false;
        }
        $this->xml_data = &$this->snoopy->results;

        return true;
    }

    /**
     * parse the xml data.
     *
     * @return bool false if failure
     */
    public function parse()
    {
        if (!$this->parserCheckDoctype()) {
            return false;
        }
        $this->parser_xpath = '';
        $this->data = [];
        // call parent constructor
        parent::__construct($this->xml_data);
        parent::parse();
        parent::free();

        return true;
    }

    /**
     * create url string.
     *
     * @return string created url string
     */
    protected function createUrl()
    {
        $arguments = [];
        if (!empty($this->fetch_arguments)) {
            foreach ($this->fetch_arguments as $key => $value) {
                $arguments[] = $key.'='.$value;
            }

            return $this->fetch_url.'?'.implode('&', $arguments);
        } else {
            return $this->fetch_url;
        }
    }

    /**
     * encode url.
     *
     * @return string encoded url
     */
    protected function encodeUrl($str)
    {
        if ($this->isRFC3986) {
            return str_replace('%7E', '~', rawurlencode($str));
        } else {
            return urlencode($str);
        }
    }

    /**
     * encode arguments.
     *
     * @param array $arguments
     *
     * @return array encoded arguments
     */
    protected function encodeArguments($arguments)
    {
        $encode_arguments = [];
        foreach ($arguments as $key => $value) {
            $encode_arguments[$this->encodeUrl($key)] = $this->encodeUrl($value);
        }

        return $encode_arguments;
    }

    /**
     * virtual function of the start elemnt handler.
     *
     * @param string $attribs xml attribute
     */
    protected function parserStartElement($attribs)
    {
    }

    /**
     * virtual function of the end elemnt handler.
     */
    protected function parserEndElement()
    {
    }

    /**
     * virtual function of the character data handler.
     *
     * @param string $cdata character data
     */
    protected function parserCharacterData($cdata)
    {
    }

    /**
     * check doctype of xml
     * this function is a part of parse() function.
     *
     * @return bool false if failure
     */
    private function parserCheckDoctype()
    {
        if (empty($this->xml_data)) {
            return false;
        }
        $public_search = '/<!DOCTYPE\\s+(\\w+)\\s+PUBLIC\\s"([^"]+)"\\s+"([^"]+)"\\s*>/is';
        $system_search = '/<!DOCTYPE\\s+(\\w+)\\s+SYSTEM\\s"([^"]+)"\\s*>/is';
        if (preg_match($public_search, $this->xml_data, $matches)) {
            $name = $matches[1];
            $public_id = $matches[2];
            $system_id = $matches[3];
        } elseif (preg_match($system_search, $this->xml_data, $matches)) {
            $name = $matches[1];
            $public_id = '';
            $system_id = $matches[2];
        } else {
            // doctype not found
            $name = '';
            $public_id = '';
            $system_id = '';
        }

        // compare doctype
        if (!empty($this->parser_doctype) && $name != $this->parser_doctype) {
            return false;
        }
        // compare public id
        if (!empty($this->parser_public_id) && $public_id != $this->parser_public_id) {
            return false;
        }
        // compare system id
        if (!empty($this->parser_system_id) && $system_id != $this->parser_system_id) {
            return false;
        }

        return true;
    }

    /**
     * callback handler of start element of xml data
     * this function is a part of parse() function.
     *
     * @param resource $parser  parser resource
     * @param string   $name    xml element tag
     * @param string   $attribs xml attributes
     */
    public function handleBeginElementDefault($parser, $name, $attribs)
    {
        // xpath
        $this->parser_xpath .= '/'.$name;
        // call start element handler
        $this->parserStartElement($attribs);
    }

    /**
     * callback handler of end element of xml data
     * this function is a part of parse() function.
     *
     * @param resource $parser parser resource
     * @param string   $name   xml element tag
     */
    public function handleEndElementDefault($parser, $name)
    {
        // call end element handler
        $this->parserEndElement();
        // xpath
        $all_len = strlen($this->parser_xpath);
        $tag_len = strlen($name);
        $this->parser_xpath = substr($this->parser_xpath, 0, $all_len - $tag_len - 1);
    }

    /**
     * callback handler of character data handler of xml data
     * this function is a part of parse() function.
     *
     * @param resource $parser parser resource
     * @param string   $cdata  character data
     */
    public function handleCharacterDataDefault($parser, $cdata)
    {
        $this->parserCharacterData(trim($cdata));
    }
}
