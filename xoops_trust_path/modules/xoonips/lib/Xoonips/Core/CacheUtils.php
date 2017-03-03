<?php

namespace Xoonips\Core;

/**
 * contents cache utility class.
 */
class CacheUtils
{
    const CACHE_MAXAGE = 3600;

    /**
     * check 304.
     *
     * @param int    $mtime
     * @param string $etag
     */
    public static function check304($mtime, $etag)
    {
        $_mtime = -1;
        $_etags = array();
        if ($mtime !== false && isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $_mtime = intval(@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']));
        }
        if ($etag !== false && isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            if (preg_match_all('/"((?:\\"|[^"])*)"/U', $_SERVER['HTTP_IF_NONE_MATCH'], $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $_etags[] = stripslashes($match[1]);
                }
            }
        }
        if ($_mtime >= (int) $mtime || in_array($etag, $_etags)) {
            self::_prepareOutput();
            self::_outputCacheHeader($mtime, $etag);
            self::_outputHttpStatusCodeHeader(304);
            self::_cleanupOutput();
        }
    }

    /**
     * error exit.
     *
     * @param int $code
     */
    public static function errorExit($code)
    {
        self::_prepareOutput();
        self::_outputHttpStatusCodeHeader($code, true);
        self::_cleanupOutput();
    }

    /**
     * output data.
     *
     * @param int    $mitme
     * @param string $etag
     * @param string $mime
     * @param string $data
     */
    public static function outputData($mtime, $etag, $mime, $data)
    {
        self::_prepareOutput();
        self::_outputCacheHeader($mtime, $etag);
        header('Content-Type: '.$mime);
        header('Content-Length: '.strlen($data));
        echo $data;
        self::_cleanupOutput();
    }

    /**
     * output file.
     *
     * @param int    $mitme
     * @param string $etag
     * @param string $mime
     * @param string $fpath
     */
    public static function outputFile($mtime, $etag, $mime, $fpath)
    {
        self::_prepareOutput();
        self::_outputCacheHeader($mtime, $etag);
        header('Content-Type: '.$mime);
        header('Content-Length: '.filesize($fpath));
        readfile($fpath);
        self::_cleanupOutput();
    }

    /**
     * output image png.
     *
     * @param int    $mitme
     * @param string $etag
     * @param string $im
     */
    public static function outputImagePng($mtime, $etag, $im)
    {
        self::_prepareOutput();
        self::_outputCacheHeader($mtime, $etag);
        header('Content-Type: image/png');
        imagepng($im);
        imagedestroy($im);
        self::_cleanupOutput();
    }

    /**
     * output callback.
     *
     * @param int      $mitme
     * @param string   $etag
     * @param string   $mime
     * @param callable $func
     * @param array    $params
     */
    public static function outputCallback($mtime, $etag, $mime, $func, $params)
    {
        self::_prepareOutput();
        self::_outputCacheHeader($mtime, $etag);
        header('Content-Type: '.$mime);
        if (is_null($params)) {
            call_user_func($func);
        } else {
            call_user_func_array($func, $params);
        }
        self::_cleanupOutput();
    }

    /**
     * download data.
     *
     * @param int    $mtime
     * @param string $etag
     * @param string $mime
     * @param string $data
     * @param string $fname
     * @param string $encoding
     */
    public static function downloadData($mtime, $etag, $mime, $data, $fname, $encoding)
    {
        set_time_limit(0);
        if ($encoding != 'UTF-8') {
            $fname = StringUtils::convertEncoding($fname, 'UTF-8', $encoding, 'h');
        }
        self::_prepareOutput();
        self::_outputCacheHeader($mtime, $etag);
        header('Content-Type: '.$mime);
        header('Content-Length: '.strlen($data));
        header('Content-Disposition: attachment; filename*=UTF-8\'\''.rawurlencode($fname));
        echo $data;
        self::_cleanupOutput();
    }

    /**
     * download file.
     *
     * @param int    $mtime
     * @param string $etag
     * @param string $mime
     * @param string $fpath
     * @param string $fname
     * @param string $encoding
     */
    public static function downloadFile($mtime, $etag, $mime, $fpath, $fname, $encoding)
    {
        set_time_limit(0);
        if ($encoding != 'UTF-8') {
            $fname = StringUtils::convertEncoding($fname, 'UTF-8', $encoding, 'h');
        }
        self::_prepareOutput();
        self::_outputCacheHeader($mtime, $etag);
        header('Content-Type: '.$mime);
        header('Content-Length: '.filesize($fpath));
        header('Content-Disposition: attachment; filename*=UTF-8\'\''.rawurlencode($fname));
        readfile($fpath);
        self::_cleanupOutput();
    }

    /**
     * download callback.
     *
     * @param int      $mtime
     * @param string   $etag
     * @param string   $mime
     * @param callable $func
     * @param array    $params
     * @param string   $fname
     * @param string   $encoding
     */
    public static function downloadCallback($mtime, $etag, $mime, $func, $params, $fname, $encoding)
    {
        set_time_limit(0);
        if ($encoding != 'UTF-8') {
            $fname = StringUtils::convertEncoding($fname, 'UTF-8', $encoding, 'h');
        }
        self::_prepareOutput();
        self::_outputCacheHeader($mtime, $etag);
        header('Content-Type: '.$mime);
        header('Content-Disposition: attachment; filename*=UTF-8\'\''.rawurlencode($fname));
        if (is_null($params)) {
            call_user_func($func);
        } else {
            call_user_func_array($func, $params);
        }
        self::_cleanupOutput();
    }

    /**
     * prepare output.
     */
    protected static function _prepareOutput()
    {
        // session cache policy
        session_cache_limiter('public');
        // disable mb_http_output
        if (function_exists('mb_http_output')) {
            mb_http_output('pass');
        }
        // clear ob filters
        self::_clearFilters();
    }

    /**
     * cleanup output.
     */
    protected static function _cleanupOutput()
    {
        register_shutdown_function(array(get_class(), 'onShutdown'));
        ob_start();
        exit();
    }

    /**
     * output cache header.
     *
     * @param int    $mtime
     * @param string $etag
     */
    protected static function _outputCacheHeader($mtime, $etag)
    {
        if ($mtime === false || $etag === false) {
            return;
        }
        header('Cache-Control: public, max-age='.self::CACHE_MAXAGE);
        if ($mtime) {
            header('Expires: '.gmdate('D, d M Y H:i:s', time() + self::CACHE_MAXAGE).' GMT');
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', $mtime).' GMT');
        }
        if ($etag) {
            header('ETag: "'.$etag.'"');
        }
    }

    /**
     * on shutdown callback handler.
     */
    public static function onShutdown()
    {
        self::_clearFilters();
    }

    /**
     * output http status code header.
     *
     * @param int  $code
     * @param bool $doEcho
     */
    protected static function _outputHttpStatusCodeHeader($code, $doEcho = false)
    {
        $messages = array(
            304 => 'Not Modified',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
        );
        $text = sprintf('%s %u %s', $_SERVER['SERVER_PROTOCOL'], $code, $messages[$code]);
        header($text);
        if ($doEcho) {
            echo $text;
        }
    }

    /**
     * clear ob filters.
     */
    protected static function _clearFilters()
    {
        $handlers = ob_list_handlers();
        while (!empty($handlers)) {
            ob_end_clean();
            $handlers = ob_list_handlers();
        }
    }
}
