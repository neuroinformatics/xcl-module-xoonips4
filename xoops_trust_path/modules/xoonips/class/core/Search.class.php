<?php

use Xoonips\Core\StringUtils;

/**
 * search class.
 */
class Xoonips_Search
{
    /**
     * constant value for fulltext search data.
     *
     * @var string
     */
    private $WINDOW_SIZE = Xoonips_Enum::XOONIPS_WINDOW_SIZE;

    /**
     * regex patterns.
     *
     * @var array
     */
    private $patterns;

    /**
     * constractor.
     */
    public function __construct()
    {
        $this->_initializePatterns();
    }

    /**
     * get instance
     * return {Trustdirname}_Search.
     */
    public static function &getInstance()
    {
        static $instance;
        if ($instance == null) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * get search sql by query string.
     *
     * @param string $field    column name of table
     * @param string $query    search query
     * @param string $encoding text encoding of search query
     * @param object $dataType DataType class
     * @param bool   $isExact  true:exact search
     *
     * @return string search sql
     */
    public function getSearchSql($field, $query, $encoding, $dataType, $isExact)
    {
        // convert query encoding to 'UTF-8'
        $query = StringUtils::convertEncoding($query, 'UTF-8', $encoding, 'h');
        // backup multi byte regex encoding
        $regex_encoding = mb_regex_encoding();
        // set multi byte regex encoding
        mb_regex_encoding('UTF-8');
        // normalize string for fulltext search
        $query = $this->_normalizeString($query);
        // create fulltext search part of SQL
        $sql = $this->makeSearchSql($field, $query, $dataType, $isExact);
        // convert UTF-8 sql to each encodings
        $query = StringUtils::convertEncoding($query, $encoding, 'UTF-8', 'n');
        // restore original multi byte regex encoding
        mb_regex_encoding($regex_encoding);

        return $sql;
    }

    /**
     * get fulltext search sql by query string.
     *
     * @param string $field    column name of table
     * @param string $query    search query
     * @param string $encoding text encoding of search query
     *
     * @return string fulltext search sql
     */
    public function getFulltextSearchSql($field, $query, $encoding)
    {
        // convert query encoding to 'UTF-8'
        $query = StringUtils::convertEncoding($query, 'UTF-8', $encoding, 'h');
        //  backup multi byte regex encoding
        $regex_encoding = mb_regex_encoding();
        // set multi byte regex encoding
        mb_regex_encoding('UTF-8');
        // normalize string for fulltext search
        $query = $this->_normalizeString($query);
        //create fulltext search part of SQL
        $sql = $this->makeFulltextSearchSql($field, $query);
        // restore original multi byte regex encoding
        mb_regex_encoding($regex_encoding);

        return $sql;
    }

    /**
     * get fulltext data for storing into database.
     *
     * @param string $text UTF-8 encoded text
     *
     * @return string UTF-8 encoded fulltext data
     */
    public function getFulltextData($text)
    {
        // backup multi byte regex encoding
        $regex_encoding = mb_regex_encoding();
        // set multi byte regex encoding
        mb_regex_encoding('UTF-8');
        // normalize string for fulltext search
        $text = $this->_normalizeString($text);
        // split text to search tokens
        $tokens = $this->_splitIntoTokens($text);
        // get fulltext search data
        $data = $this->_makeFulltextSearchData($tokens);
        // restore original multi byte regex encoding
        mb_regex_encoding($regex_encoding);

        return $data;
    }

    /**
     * normalize string for fulltext search.
     *
     * @param string $text UTF-8 encoded input text
     *
     * @return string normalized string
     */
    private function _normalizeString($text)
    {
        // sanitize non printable characters
        $pattern = sprintf('%s+', $this->patterns['noprint']);
        $text = mb_ereg_replace($pattern, ' ', $text);
        // normalize Japanese characters
        // - 's'  - zenkaku space to hankaku space
        $text = mb_convert_kana($text, 's', 'UTF-8');
        // convert latin1 suppliment characters to html numeric entities
        $text = mb_encode_numericentity($text, array(0x0080, 0x00ff, 0, 0xffff), 'UTF-8');
        // trim string
        $text = trim($text);

        return $text;
    }

    /**
     * make search sql.
     *
     * @param string $field    column name of table
     * @param string $query    UTF-8 encoded search query
     * @param object $dataType DataType class
     * @param bool   $isExact  true:exact search
     *
     * @return string search sql
     */
    private function makeSearchSql($field, $query, $dataType, $isExact)
    {
        if ($isExact) {
            $v = $dataType->convertSQLStr($query);

            return '("t1"'.$field.'=\''.$v.'\')';
        }
        $search_query = new Xoonips_Search_Query($query);
        if ($search_query->parse()) {
            $sql = $search_query->stack->render($field, $dataType);
        } else {
            $sql = "('1' = '0')";
        } // TODO: Is this reasonable?
        return $sql;
    }

    /**
     * make fulltext search sql.
     *
     * @param string $field column name of table
     * @param string $query UTF-8 encoded search query
     *
     * @return string fulltext search sql
     */
    private function makeFulltextSearchSql($field, $query)
    {
        $search_query = new Xoonips_Search_Query($query);
        if ($search_query->parse()) {
            $sql = $search_query->stack->renderFulltext($field);
        } else {
            $sql = "('1' = '0')";
        } // TODO: Is this reasonable?
        return $sql;
    }

    /**
     * split text into tokens.
     *
     * @param string $text UTF-8 encoded text
     *
     * @return array array of token
     */
    private function _splitIntoTokens($text)
    {
        $pattern = sprintf('%s|%s', $this->patterns['sbword'], $this->patterns['mbword']);
        mb_ereg_search_init($text, $pattern);
        $tokens = array();
        $len = strlen($text);
        for ($i = 0; $i < $len; $i = mb_ereg_search_getpos()) {
            mb_ereg_search_setpos($i);
            $regs = mb_ereg_search_regs();
            if ($regs === false) {
                break;
            }
            $tokens[] = $regs[0];
        }

        return $tokens;
    }

    /**
     * make fulltext search data.
     *
     * @param array $tokens UTF-8 encoded fulltext search tokens
     *
     * @return string UTF-8 encoded fulltext search data
     */
    private function _makeFulltextSearchData($tokens)
    {
        $ngram = array();
        $trailing = ($this->WINDOW_SIZE > 2);
        foreach ($tokens as $token) {
            if ($this->_isMultibyteWord($token)) {
                $ngramtokens = $this->_ngram($token, $this->WINDOW_SIZE, false, $trailing);
                foreach ($ngramtokens as $ngramtoken) {
                    $ngram[] = bin2hex($ngramtoken);
                }
            } else {
                $ngram[] = $token;
            }
        }

        return implode(' ', $ngram);
    }

    /**
     * get array of N-gram applied string.
     *
     * @param string $word     input string
     * @param int    $n        window size
     * @param bool   $leading  flag for output leading
     * @param bool   $trailing flag for output trailing
     *
     * @return array array of N-gram applied string
     */
    private static function _ngram($word, $n, $leading, $trailing)
    {
        $words = array();
        $word = trim($word);
        if (empty($word) || $n < 1) {
            return $words;
        }
        $len = mb_strlen($word, 'UTF-8');
        $wsize = min($len, $n);
        $lsize = $wsize - 1;
        $bsize = $len - $lsize;
        // leading
        if ($leading) {
            for ($i = 1; $i <= $lsize; ++$i) {
                $words[] = mb_substr($word, 0, $i, $encoding);
            }
        }
        // body
        for ($i = 0; $i + $n <= $bsize; ++$i) {
            $words[] = mb_substr($word, $i, $wsize, 'UTF-8');
        }
        // trailing
        if ($trailing) {
            for ($i = $lsize; $i > 0; --$i) {
                $words[] = mb_substr($word, $bsize + $lsize - $i, $i, 'UTF-8');
            }
        }

        return $words;
    }

    /**
     * return true if multibyte word.
     *
     * @param string $token 'UTF-8' encoded word
     *
     * @return bool true if multibyte word
     */
    private function _isMultibyteWord($token)
    {
        $result = mb_ereg($this->patterns['mbword'], $token);

        return $result !== false;
    }

    /**
     * initialize regex patterns.
     */
    private function _initializePatterns()
    {
        $mb_delimiter = array(
            array(0xe3, 0x80, 0x81), // ,
            array(0xe3, 0x80, 0x82), // .
            array(0xe2, 0x80, 0x99), // '
            array(0xe2, 0x80, 0x9d), // "
            array(0xe3, 0x83, 0xbb), // centered dot
            array(0xe3, 0x80, 0x8a), // case arc
            array(0xe3, 0x80, 0x8b), // case arc
            array(0xe3, 0x80, 0x8c), // case arc
            array(0xe3, 0x80, 0x8d), // case arc
            array(0xe3, 0x80, 0x8e), // case arc
            array(0xe3, 0x80, 0x8f), // case arc
            array(0xe3, 0x80, 0x90), // case arc
            array(0xe3, 0x80, 0x91), // case arc
            array(0xe3, 0x80, 0x94), // case arc
            array(0xe3, 0x80, 0x95),  // case arc
        );
        // non printable characters
        $patterns['noprint'] = sprintf('[\\x00-\\x1f\\x7f%s]', $this->_getStringFromLatin1Code(0x80, 0x9f));
        // single byte word
        $patterns['sbword'] = sprintf('[0-9a-zA-Z\\x27%s%s%s]+', $this->_getStringFromLatin1Code(0xc0, 0xd6), $this->_getStringFromLatin1Code(0xd8, 0xf6), $this->_getStringFromLatin1Code(0xf8, 0xff));
        // multi byte word
        $patterns['mbword'] = sprintf('[^\\x00-\\x7f%s%s]+', $this->_getStringFromLatin1Code(0x80, 0xff), $this->_getStringFromUtf8Code($mb_delimiter));
        // case arc
        $patterns['casearc'] = '\\x22[^\\x22]+\\x22|\\x28[^\\x28\\x29]+\\x29';
        $this->patterns = $patterns;
    }

    /**
     * get utf8 string from latin1 character.
     *
     * @param int $from
     * @param int $to
     */
    private function _getStringFromLatin1Code($from, $to)
    {
        $chars = array();
        for ($i = $from; $i <= $to; ++$i) {
            $chars[] = chr($i);
        }

        return StringUtils::convertEncoding(implode('', $chars), 'UTF-8', 'ISO-8859-1', 'h');
    }

    /**
     * get utf8 string from latin1 character.
     *
     * @param array $code_set
     */
    private function _getStringFromUtf8Code($code_set)
    {
        $chars = array();
        foreach ($code_set as $code) {
            if (count($code) == 2) {
                $chars[] = pack('C*', $code[0], $code[1]);
            } elseif (count($code) == 3) {
                $chars[] = pack('C*', $code[0], $code[1], $code[2]);
            } elseif (count($code) == 4) {
                $chars[] = pack('C*', $code[0], $code[1], $code[2], $code[3]);
            }
        }

        return implode('', $chars);
    }
}

/**
 * search query element abstract class.
 */
abstract class Xoonips_Search_Query_Element_Base
{
    /**
     * render.
     *
     * @param string                  $field
     * @param {Trustdirname}_DataType $dataType
     *
     * @return string
     */
    abstract public function render($field, $dataType);

    /**
     * render fulltext.
     *
     * @param string $field
     *
     * @return string
     */
    abstract public function renderFulltext($field);
}

/**
 * search query element class.
 */
class Xoonips_Search_Query_Element extends Xoonips_Search_Query_Element_Base
{
    /**
     * value.
     *
     * @var mixed
     */
    private $value;

    /**
     * constructor.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * render.
     *
     * @param string                  $field
     * @param {Trustdirname}_DataType $dataType
     *
     * @return string
     */
    public function render($field, $dataType)
    {
        switch (true) {
        case $dataType->isLikeSearch():
            $ret = ' ("t1".'.$field." LIKE '%".$dataType->convertSQLStrLike($this->value)."%') ";
            break;
        case $dataType->isNumericSearch():
            $ret = ' ("t1".'.$field."='".$dataType->convertSQLNum($this->value)."') ";
            break;
        default:
            $ret = ' ("t1".'.$field."='".$dataType->convertSQLStr($this->value)."') ";
            break;
        }

        return $ret;
    }

    /**
     * render fulltext.
     *
     * @param string $field
     *
     * @return string
     */
    public function renderFulltext($field)
    {
        $search = Xoonips_Search::getInstance();
        $data = $search->getFulltextData($this->value);
        $ret = sprintf(' MATCH (%s) AGAINST (%s IN BOOLEAN MODE) ', $field, Xoonips_Utils::convertSQLStr($data));

        return $ret;
    }
}

/**
 * search query element component class.
 */
class Xoonips_Search_Query_Component extends Xoonips_Search_Query_Element_Base
{
    private $elements = array();
    private $conditions = array();

    /**
     * constructor.
     *
     * @param {Trustdirname}_Search_Query_Element $ele
     * @param string                              $condition
     */
    public function __construct($ele = null, $condition = 'AND')
    {
        if (isset($ele) && is_object($ele)) {
            $this->add($ele, $condition);
        }
    }

    /**
     * add element.
     *
     * @param {Trustdirname}_Search_Query_Element $ele
     * @param string                              $condition
     */
    public function add(&$ele, $condition)
    {
        $this->elements[] = &$ele;
        $this->conditions[] = $condition;
    }

    /**
     * render.
     *
     * @param string                  $field
     * @param {Trustdirname}_DataType $dataType
     *
     * @return string
     */
    public function render($field, $dataType)
    {
        $ret = '';
        $cnt = count($this->elements);
        if ($cnt > 0) {
            $ret = '('.$this->elements[0]->render($field, $dataType);
            for ($i = 1; $i < $cnt; ++$i) {
                $ret .= ' '.$this->conditions[$i].' '.$this->elements[$i]->render($field, $dataType);
            }
            $ret .= ')';
        }

        return $ret;
    }

    /**
     * render fulltext.
     *
     * @param string $field
     *
     * @return string
     */
    public function renderFulltext($field)
    {
        $ret = '';
        $cnt = count($this->elements);
        if ($cnt > 0) {
            $ret = '('.$this->elements[0]->renderFulltext($field);
            for ($i = 1; $i < $cnt; ++$i) {
                $ret .= ' '.$this->conditions[$i].' '.$this->elements[$i]->renderFulltext($field);
            }
            $ret .= ')';
        }

        return $ret;
    }
}

/**
 * search query parser class.
 */
class Xoonips_Search_Query
{
    public $stack;
    private $lex_str = '';
    private $lex_strlen = 0;
    private $lex_pos = 0;
    private $lex_retmean = 'EOF';
    private $lex_retval = '';

    /**
     * constructor.
     *
     * @param string $text
     */
    public function __construct($text)
    {
        $this->lex_str = $text;
        $this->lex_strlen = strlen($text);
    }

    /**
     * parse text.
     *
     * @return bool
     */
    public function parse()
    {
        $op = null;
        $brstack = array();
        $brstack_pos = 0;
        while ($this->lex()) {
            switch ($this->lex_retmean) {
            case 'WORD':
                $val = new Xoonips_Search_Query_Element($this->lex_retval);
                if ($brstack_pos == 0) {
                    ++$brstack_pos;
                    $brstack[$brstack_pos] = new Xoonips_Search_Query_Component($val, 'AND');
                } else {
                    if (is_null($op)) {
                        $op = 'AND';
                    }
                    $brstack[$brstack_pos]->add($val, $op);
                    unset($val);
                    $op = null;
                }
                break;
            case 'OR':
                $op = 'OR';
                break;
            case 'AND':
                $op = 'AND';
                break;
            case 'RIGHTBR':
                $tmp_stack = new Xoonips_Search_Query_Component();
                if ($brstack_pos == 0) {
                    ++$brstack_pos;
                    $brstack[$brstack_pos] = &$tmp_stack;
                } else {
                    if (is_null($op)) {
                        $op = 'AND';
                    }
                    $brstack[$brstack_pos]->add($tmp_stack, $op);
                    ++$brstack_pos;
                    $brstack[$brstack_pos] = &$tmp_stack;
                }
                unset($tmp_stack);
                break;
            case 'LEFTBR':
                if (!is_null($op)) {
                    return false;
                } // error LEFTBR 1
                if ($brstack_pos < 2) {
                    return false;
                } // error LEFTBR 2
                unset($brstack[$brstack_pos]);
                --$brstack_pos;
                break;
            default:
                return false;
            }
        }
        if ($brstack_pos != 1) {
            return false;
        }
        $this->stack = &$brstack[1];

        return true;
    }

    /**
     * lexical analyzer.
     *
     * @return bool
     */
    private function lex()
    {
        $mean = 'EOF';
        $ret = null;
        $in_quote = false;
        $in_escape = false;
        $continue = true;
        $pop_require = false;
        for ($pos = $this->lex_pos; $continue && $pos < $this->lex_strlen; ++$pos) {
            $c = $this->lex_str[$pos];
            if ($in_quote) {
                if ($in_escape) {
                    if ($c == '"' || $c == '\\') {
                        $ret .= $c;
                        $in_escape = false;
                    }
                } else {
                    if ($c == '"') {
                        $in_quote = false;
                    } elseif ($c == '\\') {
                        $in_escape = true;
                    } else {
                        $ret .= $c;
                    }
                }
            } else {
                if ($c == ')' || $c == '(') {
                    if ($ret == null) {
                        $ret = $c;
                        $mean = ($c == ')') ? 'LEFTBR' : 'RIGHTBR';
                        $continue = false;
                    } else {
                        $continue = false;
                        $pop_require = true;
                    }
                } elseif ($c == '"') {
                    if ($ret == null) {
                        $in_quote = true;
                        $mean = 'PHRASE';
                        $ret = '';
                    } else {
                        $continue = false;
                        $pop_require = true;
                    }
                } else {
                    if ($c == ' ') {
                        if ($ret != null) {
                            $continue = false;
                        }
                    } else {
                        if ($ret == null) {
                            $ret = $c;
                        } else {
                            $ret .= $c;
                        }
                        $mean = 'WORD';
                    }
                }
            }
        }
        if ($pop_require) {
            --$pos;
        }
        if ($mean == 'WORD') {
            switch (strtoupper($ret)) {
            case 'AND':
                $mean = 'AND';
                break;
            case 'OR':
                $mean = 'OR';
                break;
            }
        } elseif ($mean == 'PHRASE') {
            $mean = 'WORD';
        }
        $this->lex_pos = $pos;
        $this->lex_retmean = $mean;
        $this->lex_retval = $ret;

        return $mean != 'EOF';
    }
}
