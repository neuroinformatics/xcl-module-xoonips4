<?php

namespace Xoonips\Core;

/**
 * string utility class.
 */
class StringUtils
{
    /**
     * character entity references in HTML 4.
     *
     * @var array
     */
    private static $mCharEntRef = array(
        '&quot;',   '&amp;',    '&apos;',    '&lt;',       '&gt;',
        '&nbsp;',   '&iexcl;',  '&cent;',    '&pound;',    '&curren;',
        '&yen;',    '&brvbar;', '&sect;',    '&uml;',      '&copy;',
        '&ordf;',   '&laquo;',  '&not;',     '&shy;',      '&reg;',
        '&macr;',   '&deg;',    '&plusmn;',  '&sup2;',     '&sup3;',
        '&acute;',  '&micro;',  '&para;',    '&middot;',   '&cedil;',
        '&sup1;',   '&ordm;',   '&raquo;',   '&frac14;',   '&frac12;',
        '&frac34;', '&iquest;', '&Agrave;',  '&Aacute;',   '&Acirc;',
        '&Atilde;', '&Auml;',   '&Aring;',   '&AElig;',    '&Ccedil;',
        '&Egrave;', '&Eacute;', '&Ecirc;',   '&Euml;',     '&Igrave;',
        '&Iacute;', '&Icirc;',  '&Iuml;',    '&ETH;',      '&Ntilde;',
        '&Ograve;', '&Oacute;', '&Ocirc;',   '&Otilde;',   '&Ouml;',
        '&times;',  '&Oslash;', '&Ugrave;',  '&Uacute;',   '&Ucirc;',
        '&Uuml;',   '&Yacute;', '&THORN;',   '&szlig;',    '&agrave;',
        '&aacute;', '&acirc;',  '&atilde;',  '&auml;',     '&aring;',
        '&aelig;',  '&ccedil;', '&egrave;',  '&eacute;',   '&ecirc;',
        '&euml;',   '&igrave;', '&iacute;',  '&icirc;',    '&iuml;',
        '&eth;',    '&ntilde;', '&ograve;',  '&oacute;',   '&ocirc;',
        '&otilde;', '&ouml;',   '&divide;',  '&oslash;',   '&ugrave;',
        '&uacute;', '&ucirc;',  '&uuml;',    '&yacute;',   '&thorn;',
        '&yuml;',   '&OElig;',  '&oelig;',   '&Scaron;',   '&scaron;',
        '&Yuml;',   '&fnof;',   '&circ;',    '&tilde;',    '&Alpha;',
        '&Beta;',   '&Gamma;',  '&Delta;',   '&Epsilon;',  '&Zeta;',
        '&Eta;',    '&Theta;',  '&Iota;',    '&Kappa;',    '&Lambda;',
        '&Mu;',     '&Nu;',     '&Xi;',      '&Omicron;',  '&Pi;',
        '&Rho;',    '&Sigma;',  '&Tau;',     '&Upsilon;',  '&Phi;',
        '&Chi;',    '&Psi;',    '&Omega;',   '&alpha;',    '&beta;',
        '&gamma;',  '&delta;',  '&epsilon;', '&zeta;',     '&eta;',
        '&theta;',  '&iota;',   '&kappa;',   '&lambda;',   '&mu;',
        '&nu;',     '&xi;',     '&omicron;', '&pi;',       '&rho;',
        '&sigmaf;', '&sigma;',  '&tau;',     '&upsilon;',  '&phi;',
        '&chi;',    '&psi;',    '&omega;',   '&thetasym;', '&upsih;',
        '&piv;',    '&ensp;',   '&emsp;',    '&thinsp;',   '&zwnj;',
        '&zwj;',    '&lrm;',    '&rlm;',     '&ndash;',    '&mdash;',
        '&lsquo;',  '&rsquo;',  '&sbquo;',   '&ldquo;',    '&rdquo;',
        '&bdquo;',  '&dagger;', '&Dagger;',  '&bull;',     '&hellip;',
        '&permil;', '&prime;',  '&Prime;',   '&lsaquo;',   '&rsaquo;',
        '&oline;',  '&frasl;',  '&euro;',    '&image;',    '&weierp;',
        '&real;',   '&trade;',  '&alefsym;', '&larr;',     '&uarr;',
        '&rarr;',   '&darr;',   '&harr;',    '&crarr;',    '&lArr;',
        '&uArr;',   '&rArr;',   '&dArr;',    '&hArr;',     '&forall;',
        '&part;',   '&exist;',  '&empty;',   '&nabla;',    '&isin;',
        '&notin;',  '&ni;',     '&prod;',    '&sum;',      '&minus;',
        '&lowast;', '&radic;',  '&prop;',    '&infin;',    '&ang;',
        '&and;',    '&or;',     '&cap;',     '&cup;',      '&int;',
        '&there4;', '&sim;',    '&cong;',    '&asymp;',    '&ne;',
        '&equiv;',  '&le;',     '&ge;',      '&sub;',      '&sup;',
        '&nsub;',   '&sube;',   '&supe;',    '&oplus;',    '&otimes;',
        '&perp;',   '&sdot;',   '&lceil;',   '&rceil;',    '&lfloor;',
        '&rfloor;', '&lang;',   '&rang;',    '&loz;',      '&spades;',
        '&clubs;',  '&hearts;', '&diams;',
    );

    /**
     * numeric entity references in HTML 4.
     *
     * @var array
     */
    private static $mNumericEntRef = array(
        '&#34;',   '&#38;',   '&#39;',   '&#60;',   '&#62;',
        '&#160;',  '&#161;',  '&#162;',  '&#163;',  '&#164;',
        '&#165;',  '&#166;',  '&#167;',  '&#168;',  '&#169;',
        '&#170;',  '&#171;',  '&#172;',  '&#173;',  '&#174;',
        '&#175;',  '&#176;',  '&#177;',  '&#178;',  '&#179;',
        '&#180;',  '&#181;',  '&#182;',  '&#183;',  '&#184;',
        '&#185;',  '&#186;',  '&#187;',  '&#188;',  '&#189;',
        '&#190;',  '&#191;',  '&#192;',  '&#193;',  '&#194;',
        '&#195;',  '&#196;',  '&#197;',  '&#198;',  '&#199;',
        '&#200;',  '&#201;',  '&#202;',  '&#203;',  '&#204;',
        '&#205;',  '&#206;',  '&#207;',  '&#208;',  '&#209;',
        '&#210;',  '&#211;',  '&#212;',  '&#213;',  '&#214;',
        '&#215;',  '&#216;',  '&#217;',  '&#218;',  '&#219;',
        '&#220;',  '&#221;',  '&#222;',  '&#223;',  '&#224;',
        '&#225;',  '&#226;',  '&#227;',  '&#228;',  '&#229;',
        '&#230;',  '&#231;',  '&#232;',  '&#233;',  '&#234;',
        '&#235;',  '&#236;',  '&#237;',  '&#238;',  '&#239;',
        '&#240;',  '&#241;',  '&#242;',  '&#243;',  '&#244;',
        '&#245;',  '&#246;',  '&#247;',  '&#248;',  '&#249;',
        '&#250;',  '&#251;',  '&#252;',  '&#253;',  '&#254;',
        '&#255;',  '&#338;',  '&#339;',  '&#352;',  '&#353;',
        '&#376;',  '&#402;',  '&#710;',  '&#732;',  '&#913;',
        '&#914;',  '&#915;',  '&#916;',  '&#917;',  '&#918;',
        '&#919;',  '&#920;',  '&#921;',  '&#922;',  '&#923;',
        '&#924;',  '&#925;',  '&#926;',  '&#927;',  '&#928;',
        '&#929;',  '&#931;',  '&#932;',  '&#933;',  '&#934;',
        '&#935;',  '&#936;',  '&#937;',  '&#945;',  '&#946;',
        '&#947;',  '&#948;',  '&#949;',  '&#950;',  '&#951;',
        '&#952;',  '&#953;',  '&#954;',  '&#955;',  '&#956;',
        '&#957;',  '&#958;',  '&#959;',  '&#960;',  '&#961;',
        '&#962;',  '&#963;',  '&#964;',  '&#965;',  '&#966;',
        '&#967;',  '&#968;',  '&#969;',  '&#977;',  '&#978;',
        '&#982;',  '&#8194;', '&#8195;', '&#8201;', '&#8204;',
        '&#8205;', '&#8206;', '&#8207;', '&#8211;', '&#8212;',
        '&#8216;', '&#8217;', '&#8218;', '&#8220;', '&#8221;',
        '&#8222;', '&#8224;', '&#8225;', '&#8226;', '&#8230;',
        '&#8240;', '&#8242;', '&#8243;', '&#8249;', '&#8250;',
        '&#8254;', '&#8260;', '&#8364;', '&#8465;', '&#8472;',
        '&#8476;', '&#8482;', '&#8501;', '&#8592;', '&#8593;',
        '&#8594;', '&#8595;', '&#8596;', '&#8629;', '&#8656;',
        '&#8657;', '&#8658;', '&#8659;', '&#8660;', '&#8704;',
        '&#8706;', '&#8707;', '&#8709;', '&#8711;', '&#8712;',
        '&#8713;', '&#8715;', '&#8719;', '&#8721;', '&#8722;',
        '&#8727;', '&#8730;', '&#8733;', '&#8734;', '&#8736;',
        '&#8743;', '&#8744;', '&#8745;', '&#8746;', '&#8747;',
        '&#8756;', '&#8764;', '&#8773;', '&#8776;', '&#8800;',
        '&#8801;', '&#8804;', '&#8805;', '&#8834;', '&#8835;',
        '&#8836;', '&#8838;', '&#8839;', '&#8853;', '&#8855;',
        '&#8869;', '&#8901;', '&#8968;', '&#8969;', '&#8970;',
        '&#8971;', '&#9001;', '&#9002;', '&#9674;', '&#9824;',
        '&#9827;', '&#9829;', '&#9830;',
    );

    /**
     * convert encoding.
     *
     * @param string $text          input text
     * @param string $to_encoding   encoding of output text
     * @param string $from_encoding encoding of input text
     * @param string $fallback      unmapped character encoding method
     *                              'h' : encode to HTML numeric entities
     *                              'u' : encode to UTF-8 based url encoded string
     *                              'n' : no output
     *
     * @return string converted string
     */
    public static function convertEncoding($text, $to_encoding, $from_encoding, $fallback)
    {
        // convert encoding to 'UTF-8'
        if ('UTF-8' != $from_encoding) {
            $text = mb_convert_encoding($text, 'UTF-8', $from_encoding);
        }
        // normalize utf8
        if (class_exists('\Normalizer')) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_C);
        }
        // html character entity reference to html numeric entity reference
        $text = str_replace(self::$mCharEntRef, self::$mNumericEntRef, $text);
        // convert '&' to '&amp;' for mb_decode_numericentity()
        $text = str_replace('&', '&amp;', $text);
        // convert numeric entity of hex type to dec type
        $text = preg_replace_callback(
            '/&amp;#x([0-9a-f]+);/i',
            function ($matches) {
                return '&#x'.strtoupper($matches[1]).';';
            },
            $text
        );
        $text = preg_replace_callback(
            '/&amp;#([0-9]+);/',
            function ($matches) {
                return '&#x'.strtoupper(dechex($matches[1])).';';
            },
            $text
        );
        // decode numeric entity
        $text = mb_decode_numericentity($text, array(0x0, 0x100000, 0, 0xffffff), 'UTF-8');
        // convert &amp; to '&' for htmlspecialchars()
        $text = str_replace('&amp;', '&', $text);
        if ('UTF-8' != $to_encoding) {
            // backup substitute character
            $subst = mb_substitute_character();
            // set substitute character to entity
            if ('n' == $fallback) {
                mb_substitute_character('none');
            } else {
                mb_substitute_character('entity');
            }
            // convert encoding
            $text = mb_convert_encoding($text, $to_encoding, 'UTF-8');
            if ($subst) {
                // restore substitute character
                mb_substitute_character($subst);
            }
            if ('h' == $fallback) {
                $text = preg_replace_callback(
                    '/&#x[0-9a-f]+;/i',
                    function ($matches) {
                        return '&#'.hexdec($matches[0]).';';
                    },
                    $text
                );
            } elseif ('u' == $fallback) {
                // replace substitute entity chacter to url encoded string
                $text = preg_replace_callback(
                    '/&#x[0-9a-f]+;/i',
                    function ($matches) {
                        return urlencode(mb_decode_numericentity($matches[0], array(0x0, 0x100000, 0, 0xffffff), 'UTF-8'));
                    },
                    $text
                );
            }
        }

        return $text;
    }

    /**
     * convert encoding to client.
     *
     * @param string $text          input text
     * @param string $from_encoding encoding of input text
     * @param string $fallback      unmapped character encoding method
     *                              'h' : encode to HTML numeric entities
     *                              'u' : encode to UTF-8 based url encoded string
     *                              'n' : no output
     *
     * @return string
     */
    public static function convertEncodingToClient($text, $from_encoding, $fallback)
    {
        return self::convertEncoding($text, self::_detectClientEncoding(), $from_encoding, $fallback);
    }

    /**
     * convert encoding to client file system.
     *
     * @param string $text          input text
     * @param string $from_encoding encoding of input text
     *
     * @return string
     */
    public static function convertEncodingToClientFileSystem($text, $from_encoding)
    {
        return self::convertEncoding($text, self::_detectClientFileSystemEncoding(), $from_encoding, 'u');
    }

    /**
     * escape html special characters
     * this function will convert text to follow some rules:
     * - '&' => '&amp;'
     * - '"' => '&quot;'
     * - ''' => '&apos;'
     * - '<' => '&lt;'
     * - '>' => '&gt;'
     * - numeric entity reference => (pass)
     * - character entity reference => (pass).
     *
     * @param string $text text string
     *
     * @return string escaped text string
     */
    public static function htmlSpecialChars($text)
    {
        $text = preg_replace('/&amp;#([xX][0-9a-fA-F]+|[0-9]+);/', '&#\\1;', htmlspecialchars($text, ENT_QUOTES));
        $text = preg_replace_callback('/&amp;([a-zA-Z][0-9a-zA-Z]+);/', 'self::_htmlSpecialCharsHelper', $text);

        return $text;
    }

    /**
     * convert text to UTF-8 string with predefined five xml entitities
     * - predefined five xml entities are: &amp; &lt; &gt; &apos; &quot;.
     *
     * @param string $text     text string
     * @param string $encoding text encoding
     *
     * @return string UTF-8 string with predefined five xml entities
     */
    public static function xmlSpecialChars($text, $encoding)
    {
        $text = self::convertEncoding($text, 'UTF-8', $encoding, 'h');
        $text = self::htmlSpecialChars($text);

        return $text;
    }

    /**
     * convert Japanese Kana to Roma-ji.
     *
     * @param string $text     text string
     * @param string $encoding text encoding
     *
     * @return string
     */
    public static function convertKana2Roma($text, $encoding)
    {
        // convert encoding to UTF-8
        $text = self::convertEncoding($text, 'UTF-8', $encoding, 'h');
        // convert hankaku alphabets/numbers/katakana to zenkaku, zenkaku hiragana to zenkaku katakana
        $text = mb_convert_kana($text, 'AKCV', 'UTF-8');
        // convert non ascii character to html numeric entities
        $text = self::convertEncoding($text, 'ASCII', 'UTF-8', 'h');
        // replace corner brackets, comma, full stop, sound mark, zenkaku white space
        $text = str_replace(
            array('&#12300;', '&#12301;', '&#12302;', '&#12303;', '&#12289;', '&#12290;', '&#12540;',  '&#12288;'),
            array('&#x201C;', '&#x201D;', '&#x2018;', '&#x2019;', ', ',       '. ',       '^',         ' '),
            $text
        );
        // replace katakana with roma-ji
        $text = str_replace(
            array(
                '&#12461;&#12515;', '&#12461;&#12517;', '&#12461;&#12519;', '&#12461;&#12455;', // kya, kyu, kyo, (kye)
                '&#12463;&#12449;',                                                             // kwa
                '&#12471;&#12515;', '&#12471;&#12517;', '&#12471;&#12519;', '&#12471;&#12455;', // sha, shu, sho, (she)
                '&#12481;&#12515;', '&#12481;&#12517;', '&#12481;&#12519;', '&#12481;&#12455;', // cha, chu, cho, (che)
                '&#12486;&#12451;', '&#12488;&#12453;',                                         // (ti), (tu)
                '&#12486;&#12515;', '&#12486;&#12517;', '&#12486;&#12519;',                     // (tya), (tyu), (tyo)
                '&#12491;&#12515;', '&#12491;&#12517;', '&#12491;&#12519;', '&#12491;&#12455;', // nya, nyu, nyo, (nye)
                '&#12498;&#12515;', '&#12498;&#12517;', '&#12498;&#12519;', '&#12498;&#12455;', // hya, hyu, hyo, (hye)
                '&#12501;&#12449;', '&#12501;&#12451;', '&#12501;&#12455;', '&#12501;&#12457;', // (fa), (fi), (fe), (fo)
                '&#12501;&#12515;', '&#12501;&#12517;', '&#12501;&#12519;',                     // (fya), (fyu), (fyo)
                '&#12511;&#12515;', '&#12511;&#12517;', '&#12511;&#12519;', '&#12511;&#12455;', // mya, myu, myo, (mye)
                '&#12522;&#12515;', '&#12522;&#12517;', '&#12522;&#12519;', '&#12522;&#12455;', // rya, ryu, ryo, (rye)
                '&#12462;&#12515;', '&#12462;&#12517;', '&#12462;&#12519;', '&#12462;&#12455;', // gya, gyu, gyo, (gye)
                '&#12464;&#12449;',                                                             // gwa
                '&#12472;&#12515;', '&#12472;&#12517;', '&#12472;&#12519;', '&#12472;&#12455;', // ja, ju, jo, (je)
                '&#12482;&#12515;', '&#12482;&#12517;', '&#12482;&#12519;', '&#12482;&#12455;', // (ja), (ju), (jo), (je)
                '&#12487;&#12451;', '&#12489;&#12453;',                                         // (di), (du)
                '&#12487;&#12515;', '&#12487;&#12517;', '&#12487;&#12519;',                     // (dya), (dyu), (dyo)
                '&#12532;&#12449;', '&#12532;&#12451;', '&#12532;&#12455;', '&#12532;&#12457;', // (va), (vi), (ve), (vo)
                '&#12532;&#12515;', '&#12532;&#12517;', '&#12532;&#12519;',                     // (vya), (vyu), (vyo)
                '&#12499;&#12515;', '&#12499;&#12517;', '&#12499;&#12519;', '&#12499;&#12455;', // bya, byu, byo, (bye)
                '&#12500;&#12515;', '&#12500;&#12517;', '&#12500;&#12519;', '&#12500;&#12455;', // pya, pyu, pyo, (pye)
                '&#12450;', '&#12452;', '&#12454;', '&#12456;', '&#12458;', // a, i, u, e, o
                '&#12459;', '&#12461;', '&#12463;', '&#12465;', '&#12467;', // ka, ki, ku, ke, ko
                '&#12469;', '&#12471;', '&#12473;', '&#12475;', '&#12477;', // sa, shi, su, se, so
                '&#12479;', '&#12481;', '&#12484;', '&#12486;', '&#12488;', // ta, chi, tsu, te, to
                '&#12490;', '&#12491;', '&#12492;', '&#12493;', '&#12494;', // na, ni, nu, ne, no
                '&#12495;', '&#12498;', '&#12501;', '&#12504;', '&#12507;', // ha, hi, fu, he, ho
                '&#12510;', '&#12511;', '&#12512;', '&#12513;', '&#12514;', // ma, mi, mu, me, mo
                '&#12516;',             '&#12518;',             '&#12520;', // ya, yu, yo
                '&#12521;', '&#12522;', '&#12523;', '&#12524;', '&#12525;', // ra, ri, ru, re, ro
                '&#12527;', '&#12528;',             '&#12529;', '&#12530;', // wa, (i), (e), wo
                '&#12460;', '&#12462;', '&#12464;', '&#12466;', '&#12468;', // ga, gi, gu, ge, go
                '&#12470;', '&#12472;', '&#12474;', '&#12476;', '&#12478;', // za, ji, zu, ze, zo
                '&#12480;', '&#12482;', '&#12485;', '&#12487;', '&#12489;', // da, ji, zu, de, do
                                        '&#12532;',                         // (vu)
                '&#12496;', '&#12499;', '&#12502;', '&#12505;', '&#12508;', // ba, bi, bu, be, bo
                '&#12497;', '&#12500;', '&#12503;', '&#12506;', '&#12509;', // pa, pi, pu, pe, po
                '&#12449;', '&#12451;', '&#12453;', '&#12455;', '&#12457;', // a, i, u, e, o
                '&#12533;',                         '&#12534;',             // ka, ke
                '&#12515;', '&#12517;', '&#12519;',                         // ya, yu, yo
                '&#12526;',                                                 // wa
            ),
            array(
                'kya', 'kyu', 'kyo', 'kye',
                'kwa',
                'sha', 'shu', 'sho', 'she',
                'cha', 'chu', 'cho', 'che',
                'ti',  'tu',
                'tya', 'tyu', 'tyo',
                'nya', 'nyu', 'nyo', 'nye',
                'hya', 'hyu', 'hyo', 'hye',
                'fa',  'fi',  'fe',  'fo',
                'fya', 'fyu', 'fyo',
                'mya', 'myu', 'myo', 'mye',
                'rya', 'ryu', 'ryo', 'rye',
                'gya', 'gyu', 'gyo', 'gye',
                'gwa',
                'ja',  'ju',  'jo',  'je',
                'ja',  'ju',  'jo',  'je',
                'di',  'du',
                'dya', 'dyu', 'dyo',
                'va',  'vi',  've',  'vo',
                'vya', 'vyu', 'vyo',
                'bya', 'byu', 'byo', 'bye',
                'pya', 'pyu', 'pyo', 'pye',
                'a',   'i',   'u',   'e',   'o',
                'ka',  'ki',  'ku',  'ke',  'ko',
                'sa',  'shi', 'su',  'se',  'so',
                'ta',  'chi', 'tsu', 'te',  'to',
                'na',  'ni',  'nu',  'ne',  'no',
                'ha',  'hi',  'fu',  'he',  'ho',
                'ma',  'mi',  'mu',  'me',  'mo',
                'ya',         'yu',         'yo',
                'ra',  'ri',  'ru',  're',  'ro',
                'wa',  'i',          'e',   'wo',
                'ga',  'gi',  'gu',  'ge',  'go',
                'za',  'ji',  'zu',  'ze',  'zo',
                'da',  'ji',  'zu',  'de',  'do',
                              'vu',
                'ba',  'bi',  'bu',  'be',  'bo',
                'pa',  'pi',  'pu',  'pe',  'po',
                'a',   'i',   'u',   'e',   'o',
                'ka',                'ke',
                'ya',         'yu',         'yo',
                'wa',
            ),
            $text
        );
        // replace hatsu-on : KATAKANA LETER N
        $text = str_replace(
            array(
                '&#12531;a', '&#12531;i', '&#12531;u', '&#12531;e', '&#12531;o', '&#12531;y',
                '&#12531;b', '&#12531;p', '&#12531;m', '&#12531;',
            ),
            array(
                'n\'a', 'n\'i', 'n\'u', 'n\'e', 'n\'o', 'n\'y',
                'mb', 'mp', 'mm', 'n',
            ),
            $text
        );
        // replace soku-on : KATAKANA LETTER SMALL TU
        $text = str_replace(
            array(
                '&#12483;cha', '&#12483;chi', '&#12483;chu', '&#12483;cho',
                '&#12483;k', '&#12483;s', '&#12483;t',              '&#12483;h',
                '&#12483;m', '&#12483;y', '&#12483;r', '&#12483;w',
                '&#12483;g', '&#12483;z', '&#12483;d', '&#12483;b', '&#12483;p',
                '&#12483;q', '&#12483;j', '&#12483;f', '&#12483;v', '&#12483;',
            ),
            array(
                'tcha', 'tchi', 'tchu', 'tcho',
                'kk', 'ss', 'tt',       'hh',
                'mm', 'yy', 'rr', 'ww',
                'gg', 'zz', 'dd', 'bb', 'pp',
                'qq', 'jj', 'ff', 'vv', 'tsu',
            ),
            $text
        );
        // replace cho-on
        $text = str_replace(
            array('aa', 'ii', 'uu', 'ee', 'oo', 'ou'),
            array('a^', 'i^', 'u^', 'e^', 'o^', 'o^'),
            $text
        );
        // capiatlize word
        $text = preg_replace_callback('/^[a-z]/', function ($matches) {
            return strtoupper($matches[0]);
        }, $text);
        $text = preg_replace_callback('/\. +([a-z])/', function ($matches) {
            return '. '.strtoupper($matches[1]);
        }, $text);
        // trim road sign (hepburn system)
        $text = str_replace('^', '', $text);
        // decode html numeric entities
        $text = mb_decode_numericentity($text, array(0x0, 0x100000, 0, 0xffffff), 'UTF-8');
        // convert zenkaku alphabets/numbers to hankaku
        $text = mb_convert_kana($text, 'a', $encoding);
        // convert encoding to original
        $text = self::convertEncoding($text, $encoding, 'ASCII', 'h');

        return $text;
    }

    /**
     * detect text encoding.
     *
     * @param string $text input text
     *
     * @return string encoding name
     */
    public static function detectTextEncoding($text)
    {
        $mb_ienc = mb_internal_encoding();
        $mb_lang = mb_language();
        mb_internal_encoding('UTF-8');
        mb_language('Japanese');
        $encoding = mb_detect_encoding($text, 'auto');
        if (false === $encoding) {
            $encoding = 'ASCII';
        }
        if ($mb_ienc) {
            mb_internal_encoding($mb_ienc);
        }
        if ($mb_lang) {
            mb_language($mb_lang);
        }

        return $encoding;
    }

    /**
     * truncate text string.
     *
     * @param string $text   source string
     * @param int    $length maximum charactor width
     * @param string $etc    appending text if $text truncated
     *
     * @return string dist
     */
    public function truncate($text, $length, $etc = '...')
    {
        // multi language extension support - strip ml tags
        if (defined('XOOPS_CUBE_LEGACY')) {
            // cubeutil module
            if (isset($GLOBALS['cubeUtilMlang'])) {
                $text = $GLOBALS['cubeUtilMlang']->obFilter($text);
            }
        } else {
            // sysutil module
            if (function_exists('sysutil_get_xoops_option')) {
                if (sysutil_get_xoops_option('sysutil', 'sysutil_use_ml')) {
                    if (function_exists('sysutil_ml_filter')) {
                        $text = sysutil_ml_filter($text);
                    }
                }
            }
        }
        $olen = strlen($text);
        // trim width
        if (XOOPS_USE_MULTIBYTES) {
            $text = mb_strimwidth($text, 0, $length, '', self::detectTextEncoding($text));
        } else {
            $text = substr($text, 0, $length);
        }
        // remove broken html entity from trimed strig
        $text = preg_replace('/&[^;]*$/s', '', $text);
        // append $etc char if text is trimed
        if ($olen != strlen($text)) {
            $text .= $etc;
        }

        return $text;
    }

    /**
     * detect client file system encoding.
     *
     * @return string encoding name
     */
    private static function _detectClientFileSystemEncoding()
    {
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $lang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
        if (strstr($ua, 'Windows')) {
            static $langMap = array(
                'SJIS-win' => array('ja'),
                'Windows-1252' => array('ca', 'da', 'de', 'en', 'es', 'fi', 'fr', 'id', 'is', 'it', 'ms', 'nb', 'nl', 'pt', 'sv'),
                'ISO-8859-2' => array('cs', 'hr', 'hu', 'pl', 'ro', 'sk', 'sl', 'sq'),
                'Windows-1251' => array('be', 'bg', 'mk', 'ru', 'sr', 'uk'),
                'ISO-8859-7' => array('el'),
                'ISO-8859-9' => array('tr'),
                'ISO-8859-8' => array('he'),
            );
            foreach ($langMap as $encoding => $langs) {
                foreach ($langs as $_lang) {
                    if (strstr($lang, $_lang)) {
                        return $encoding;
                    }
                }
            }
        } elseif (strstr($ua, 'Mac') || strstr($ua, 'Linux') || strstr($ua, 'BSD') || strstr($ua, 'SunOS')) {
            return 'UTF-8';
        }

        return 'ASCII';
    }

    /**
     * detect client encoding.
     *
     * @return string encoding name
     */
    private static function _detectClientEncoding()
    {
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $lang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';

        return (strstr($ua, 'Mac') && strstr($lang, 'ja')) ? 'SJIS-win' : self::_detectClientFileSystemEncoding();
    }

    /**
     * helper function for htmlSpecialChars().
     *
     * @param array $m match condition
     *
     * @return string
     */
    private static function _htmlSpecialCharsHelper($m)
    {
        return in_array('&'.$m[1].';', self::$mCharEntRef) ? '&'.$m[1].';' : '&amp;'.$m[1].';';
    }
}
