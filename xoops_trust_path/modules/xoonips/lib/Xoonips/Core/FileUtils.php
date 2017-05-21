<?php

namespace Xoonips\Core;

/**
 * file utility class.
 */
class FileUtils
{
    /**
     * deletion file list on shutdown.
     *
     * @var array
     */
    public static $mDeleteFiles = array();

    /**
     * fallback mime type string for mime type detection failure.
     *
     * @var string
     */
    private static $_mimeTypeFallback = 'application/octet-stream';

    /**
     * alias list of mime type.
     *
     * @var array
     */
    private static $_mimeTypeAlias = array(
        'application/x-zip' => 'application/zip',
        'application/x-zip-compressed' => 'application/zip',
        'multipart/x-zip' => 'application/zip',
    );

    /**
     * mime type map.
     *
     * @var array
     */
    private static $_mimeTypeMap = array(
        '' => array(
            'dtd' => 'application/xml-dtd',
            'jnlp' => 'application/x-java-jnlp-file',
            'html' => 'text/html',
            'xhtml' => 'application/xhtml+xml',
            'xml' => 'application/xml',
            'xsl' => 'application/xml',
        ),
        'application/msword' => array(
            'ppt' => 'application/vnd.ms-powerpoint',
            'xls' => 'application/vnd.ms-excel',
        ),
        'application/zip' => array(
            'jar' => 'x-java-archive',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'odc' => 'application/vnd.oasis.opendocument.chart',
            'odb' => 'application/vnd.oasis.opendocument.database',
            'odf' => 'application/vnd.oasis.opendocument.formula',
            'odg' => 'application/vnd.oasis.opendocument.graphics',
            'otg' => 'application/vnd.oasis.opendocument.graphics-template',
            'odi' => 'application/vnd.oasis.opendocument.image',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'otp' => 'application/vnd.oasis.opendocument.presentation-template',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'odm' => 'application/vnd.oasis.opendocument.text-master',
            'ott' => 'application/vnd.oasis.opendocument.text-template',
            'oth' => 'application/vnd.oasis.opendocument.text-web',
            'sxw' => 'application/vnd.sun.xml.writer',
            'stw' => 'application/vnd.sun.xml.writer.template',
            'sxc' => 'application/vnd.sun.xml.calc',
            'stc' => 'application/vnd.sun.xml.calc.template',
            'sxd' => 'application/vnd.sun.xml.draw',
            'std' => 'application/vnd.sun.xml.draw.template',
            'sxi' => 'application/vnd.sun.xml.impress sxi',
            'sti' => 'application/vnd.sun.xml.impress.template',
            'sxg' => 'application/vnd.sun.xml.writer.global',
            'sxm' => 'application/vnd.sun.xml.math',
        ),
        'application/octet-stream' => array(
            'wmv' => 'video/x-ms-wmv',
        ),
        'text/html' => array(
            'css' => 'text/css',
            'dtd' => 'application/xml-dtd',
            'sgml' => 'text/sgml',
            'sgm' => 'text/sgml',
            'xml' => 'application/xml',
            'xsl' => 'application/xml',
        ),
        'text/plain' => array(
            'c' => 'text/x-c',
            'cc' => 'text/x-c++',
            'cpp' => 'text/x-c++',
            'css' => 'text/css',
            'cxx' => 'text/x-c++',
            'dtd' => 'application/xml-dtd',
            'htm' => 'text/html',
            'html' => 'text/html',
            'js' => 'application/x-javascript',
            'php' => 'text/html',
            'sh' => 'application/x-shellscript',
            'sgml' => 'text/sgml',
            'sgm' => 'text/sgml',
            'tex' => 'application/x-tex',
            'xml' => 'application/xml',
            'xsl' => 'application/xml',
        ),
        'text/x-c' => array(
            'cc' => 'text/x-c++',
            'cpp' => 'text/x-c++',
            'css' => 'text/css',
            'cxx' => 'text/x-c++',
            'dtd' => 'application/xml-dtd',
            'htm' => 'text/html',
            'html' => 'text/html',
            'js' => 'application/x-javascript',
            'php' => 'text/html',
            'sgml' => 'text/sgml',
            'sgm' => 'text/sgml',
            'xml' => 'application/xml',
            'xsl' => 'application/xml',
        ),
        'text/x-c++' => array(
            'c' => 'text/x-c',
            'css' => 'text/css',
            'dtd' => 'application/xml-dtd',
            'htm' => 'text/html',
            'html' => 'text/html',
            'js' => 'application/x-javascript',
            'php' => 'text/html',
            'sgml' => 'text/sgml',
            'sgm' => 'text/sgml',
            'xml' => 'application/xml',
            'xsl' => 'application/xml',
        ),
    );

    /**
     * guess mime type.
     *
     * @param string $fpath file path
     * @param string $fname original file name
     *
     * @return false|string detected mime type, false if failure
     */
    public static function guessMimeType($fpath, $fname)
    {
        // get mime type
        if (!file_exists($fpath)) {
            return false;
        }
        if ($fname === false) {
            $fname = basename($fpath);
        }
        if (version_compare(PHP_VERSION, '5.3.0') > 0) {
            $finfo = @finfo_open();
            if ($finfo === false) {
                return false;
            }
            $mime = finfo_file($finfo, $fpath, FILEINFO_MIME);
            finfo_close($finfo);
        } else {
            $mime = mime_content_type($fpath);
        }
        // trim additional information
        $mime = preg_replace('/[,; ].*$/', '', $mime);
        // replace alias mime type
        if (array_key_exists($mime, self::$_mimeTypeAlias)) {
            $mime = self::$_mimeTypeAlias[$mime];
        }
        // get original extension
        $pinfo = pathinfo($fname);
        $ext = isset($pinfo['extension']) ? $pinfo['extension'] : '';
        // override mime type
        if (array_key_exists($mime, self::$_mimeTypeMap) && array_key_exists($ext, self::$_mimeTypeMap[$mime])) {
            $mime = self::$_mimeTypeMap[$mime][$ext];
        }
        // fallback unknown mime type
        if ($mime == '') {
            $mime = self::$_mimeTypeFallback;
        }

        return $mime;
    }

    /**
     * get file info.
     *
     * @param string $fpath
     * @param string $fname
     *
     * @return array
     */
    public static function getFileInfo($fpath, $fname)
    {
        static $imageMime = array('image/jpeg', 'image/gif', 'image/png');
        if (!file_exists($fpath)) {
            return false;
        }
        $mtime = filemtime($fpath);
        $length = filesize($fpath);
        $mime = self::guessMimeType($fpath, $fname);
        $etag = md5($fpath.$length.$mtime.$mime);
        $ret = array(
        'mime' => $mime,
            'length' => $length,
            'mtime' => $mtime,
            'etag' => $etag,
        );
        if (in_array($mime, $imageMime)) {
            $info = @getimagesize($fpath);
            if ($info !== false) {
                $ret['width'] = $info[0];
                $ret['height'] = $info[1];
            }
        }

        return $ret;
    }

    /**
     * make directory recursivery.
     *
     * @param string $fpath
     *
     * @return bool false if failure
     */
    public static function makeDirectory($fpath)
    {
        // normalize file path
        $fpath = rtrim(str_replace('\\', '/', trim($fpath)), '/');
        if (substr($fpath, 0, 1) != '/') {
            // not absolute path
            return false;
        }
        $dpath = '';
        foreach (explode('/', $fpath) as $dirname) {
            if ($dirname == '') {
                // continue if '//' directry spearator found
                continue;
            }
            if ($dirname == '.' || $dirname == '..') {
                // error if '.' or '..' directory name found.
                return false;
            }
            $dpath .= '/'.$dirname;
            if (!is_dir($dpath)) {
                if (@mkdir($dpath) === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * make temporary directory, this directory will delete on exit.
     *
     * @param string $dir
     * @param string $prefix
     *
     * @return string|bool temporary directory, false if failure
     */
    public static function makeTempDirectory($dir, $prefix)
    {
        static $init = false;
        if (!is_dir($dir)) {
            return false;
        }
        if (!preg_match('/^[a-z0-9_.-]+$/i', $prefix)) {
            return false;
        }
        if ($init === false) {
            list($usec, $sec) = explode(' ', microtime());
            mt_srand($sec + $usec * 1000000);
            $init = true;
        }
        for ($try = 0; $try < 3; ++$try) {
            $rand = substr(md5(uniqid().mt_rand()), 0, 6);
            $path = $dir.'/'.$prefix.$rand;
            if (!file_exists($path)) {
                if (@mkdir($path)) {
                    self::deleteFileOnExit($path);

                    return $path;
                }
            }
        }

        return false;
    }

    /**
     * empty directory.
     *
     * @param string $fpath
     *
     * @return bool false if failure
     */
    public static function emptyDirectory($fpath)
    {
        $ret = true;
        if ($dh = @opendir($fpath)) {
            while ($fname = @readdir($dh)) {
                if ($fname == '.' || $fname == '..') {
                    continue;
                }
                $sfpath = $fpath.'/'.$fname;
                $ret = is_dir($sfpath) ? self::deleteDirectory($sfpath) : @unlink($sfpath);
                if ($ret === false) {
                    break;
                }
            }
            closedir($dh);
        } else {
            $ret = false;
        }

        return $ret;
    }

    /**
     * delete directory recursivery.
     *
     * @param string $fpath
     *
     * @return bool false if failure
     */
    public static function deleteDirectory($fpath)
    {
        $ret = true;
        if (self::emptyDirectory($fpath) !== false) {
            $ret = @rmdir($fpath);
        }

        return $ret;
    }

    /**
     * register deletion file on exit.
     *
     * @param string $fpath
     */
    public static function deleteFileOnExit($fpath)
    {
        if (empty(self::$mDeleteFiles)) {
            register_shutdown_function(array(get_class(), 'onShutdown'));
        }
        self::$mDeleteFiles[] = $fpath;
    }

    /**
     * on shutdown function.
     *
     * @param string $fpath
     */
    public static function onShutdown()
    {
        foreach (self::$mDeleteFiles as $fpath) {
            if (is_dir($fpath)) {
                self::deleteDirectory($fpath);
            } else {
                @unlink($fpath);
            }
        }
        self::$mDeleteFiles = array();
    }
}
