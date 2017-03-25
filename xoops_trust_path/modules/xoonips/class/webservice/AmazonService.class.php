<?php

require_once dirname(dirname(__FILE__)).'/core/WebServiceBase.class.php';
require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/BeanFactory.class.php';

/**
 * The Amazon Product Advertising API data handling class.
 */
class Xoonips_AmazonService extends Xoonips_WebServiceBase
{
    /**
     * isbn.
     *
     * @var string
     */
    private $isbn = '';

    /**
     * secret access key for amazon API.
     *
     * @var string
     */
    private $secret_access_key = '';

    public function __construct()
    {
        // call parent constructor
        parent::__construct();

        // RFC3986 encode
        $this->isRFC3986 = true;

        // set fetcher conditions
        $this->fetch_url = 'http://webservices.amazon.com/onca/xml';
        $this->fetch_arguments = $this->encodeArguments(array(
            'Service' => 'AWSECommerceService',
            'Version' => '2010-09-01',
            'Operation' => 'ItemLookup',
            'IdType' => 'ISBN',
            'SearchIndex' => 'Books',
            'AssociateTag' => 'XooNIps',
            'ResponseGroup' => 'Medium',
            'AWSAccessKeyId' => $this->configBean->getConfig('access_key'),
            'Timestamp' => gmdate('Y-m-d\\TH:i:s\\Z'), )
        );
        // secret access key for amazon API
        $this->secret_access_key = $this->configBean->getConfig('secret_access_key');
    }

    /**
     * set the isbn.
     *
     * @param string $isbn isbn10 or isbn13
     *
     * @return bool true if success
     */
    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;
        $isbn = preg_replace('/[\\- ]/', '', $isbn);
        if (strlen($isbn) == 10) {
            $isbn = $this->isbn10ToIsbn13($isbn);
        }
        if (strlen($isbn) != 13) {
            return false;
        }
        $char = substr($isbn, 3, 1);
        switch ($char) {
            case '0':
            case '1':
                // us
                $this->fetch_url = 'http://webservices.amazon.com/onca/xml';
                break;
            case '2':
                // france
                $this->fetch_url = 'http://webservices.amazon.fr/onca/xml';
                break;
            case '3':
                // german
                $this->fetch_url = 'http://webservices.amazon.de/onca/xml';
                break;
            case '4':
                // japan
                $this->fetch_url = 'http://webservices.amazon.co.jp/onca/xml';
                break;
            default:
                // us
                $this->fetch_url = 'http://webservices.amazon.com/onca/xml';
                break;
        }
        $this->fetch_arguments['ItemId'] = $this->encodeUrl($isbn);

        return true;
    }

    /**
     * create url string for Amazon API.
     *
     * @return string created url string
     */
    protected function createUrl()
    {
        // create sigunature
        $arguments = array();
        foreach ($this->fetch_arguments as $key => $value) {
            $arguments[] = $key.'='.$value;
        }
        sort($arguments);
        $sign_param = implode('&', $arguments);

        $parsed_url = parse_url($this->fetch_url);
        $sign_request = "GET\n{$parsed_url['host']}\n{$parsed_url['path']}\n{$sign_param}";
        $signature = base64_encode(hash_hmac('sha256', $sign_request, $this->secret_access_key, true));

        // create request url
        $arguments[] = $this->encodeUrl('Signature').'='.$this->encodeUrl($signature);

        return $this->fetch_url.'?'.implode('&', $arguments);
    }

    /**
     * override function of start element handler.
     *
     * @param string $attribs xml attribute
     */
    public function parserStartElement($attribs)
    {
        switch ($this->parser_xpath) {
            case '/ItemLookupResponse/Items/Item':
                $this->data[$this->isbn] = array(
                    'ASIN' => '',
                    'EAN' => '',
                    'ISBN' => '',
                    'DetailPageURL' => '',
                    'Author' => array(),
                    'PublicationDate' => '',
                    'Publisher' => '',
                    'Title' => '',
                );
                break;
        }
    }

    /**
     * override function of character data handler.
     *
     * @param string $cdata character data
     */
    public function parserCharacterData($cdata)
    {
        switch ($this->parser_xpath) {
            case '/ItemLookupResponse/Items/Item/ASIN':
                // ASIN
                $this->data[$this->isbn]['ASIN'] .= $cdata;
                break;
            case '/ItemLookupResponse/Items/Item/DetailPageURL':
                // DetailPageURL
                $this->data[$this->isbn]['DetailPageURL'] .= $cdata;
                break;
            case '/ItemLookupResponse/Items/Item/ItemAttributes/Author':
                // Author
                $this->data[$this->isbn]['Author'][] .= $cdata;
                break;
            case '/ItemLookupResponse/Items/Item/ItemAttributes/EAN':
                // EAN
                $this->data[$this->isbn]['EAN'] .= $cdata;
                break;
            case '/ItemLookupResponse/Items/Item/ItemAttributes/ISBN':
                // ISBN
                $this->data[$this->isbn]['ISBN'] .= $cdata;
                break;
            case '/ItemLookupResponse/Items/Item/ItemAttributes/PublicationDate':
                // PublicationDate
                $this->data[$this->isbn]['PublicationDate'] .= $cdata;
                break;
            case '/ItemLookupResponse/Items/Item/ItemAttributes/Publisher':
                // Publisher
                $this->data[$this->isbn]['Publisher'] .= $cdata;
                break;
            case '/ItemLookupResponse/Items/Item/ItemAttributes/Title':
                // Title
                $this->data[$this->isbn]['Title'] .= $cdata;
                break;
        }
    }

    /**
     * convert isbn10 to isbn13.
     *
     * @param string $isbn10
     *
     * @return string isbn13
     */
    private function isbn10ToIsbn13($isbn10)
    {
        $digit = 0;
        $isbn13 = '978'.substr($isbn10, 0, 9);
        $arr = str_split($isbn13, 1);
        for ($i = 0; $i < 12; ++$i) {
            $digit += $arr[$i] * ($i % 2 == 0 ? 1 : 3);
        }
        $digit = 10 - ($digit % 10);
        if ($digit == 10) {
            $digit = 0;
        }

        return $isbn13.$digit;
    }
}
