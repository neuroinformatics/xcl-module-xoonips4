<?php

namespace Xoonips\Core;

/**
 * contents cache utility class.
 */
class CacheUtils
{
    const CACHE_MAXAGE = 3600; // 1 hour = 60 * 60
    const OUTPUT_BLOCK_SIZE = 8388608; // 8 MB = 8 * 1024 * 1024

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
            self::_outputHttpStatusCodeHeader(304, false);
            self::_outputCacheHeader($mtime, $etag);
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
        self::downloadData($mtime, $etag, $mime, $data, '', '');
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
        self::_prepareOutput();
        self::_outputCacheHeader($mtime, $etag);
        header('Content-Type: '.$mime);
        header('Content-Length: '.strlen($data));
        self::_outputDispositionHeader($fname, $encoding);
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
        self::_downloadFile(false, $mtime, $etag, $mime, $fpath, '', '');
    }

    /**
     * output file with HTTP_RANGE support.
     *
     * @param int    $mitme
     * @param string $etag
     * @param string $mime
     * @param string $fpath
     */
    public static function outputFileWithRange($mtime, $etag, $mime, $fpath)
    {
        self::_downloadFile(true, $mtime, $etag, $mime, $fpath, '', '');
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
        self::_downloadFile(false, $mtime, $etag, $mime, $fpath, $fname, $encoding);
    }

    /**
     * download file with HTTP_RANGE support.
     *
     * @param int    $mtime
     * @param string $etag
     * @param string $mime
     * @param string $fpath
     * @param string $fname
     * @param string $encoding
     */
    public static function downloadFileWithRange($mtime, $etag, $mime, $fpath, $fname, $encoding)
    {
        self::_downloadFile(true, $mtime, $etag, $mime, $fpath, $fname, $encoding);
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
     * download file content.
     *
     * @param bool   $useRange
     * @param int    $mtime
     * @param string $etag
     * @param string $mime
     * @param string $fpath
     * @param string $fname
     * @param string $encoding
     */
    protected static function _downloadFile($useRange, $mtime, $etag, $mime, $fpath, $fname, $encoding)
    {
        set_time_limit(0);
        self::_prepareOutput();
        self::_outputRangeHeader($useRange, filesize($fpath), $offset, $limit);
        self::_outputCacheHeader($mtime, $etag);
        header('Content-Type: '.$mime);
        header('Content-Length: '.($limit - $offset + 1));
        self::_outputDispositionHeader($fname, $encoding);
        self::_outputPartialFile($fpath, $offset, $limit);
        self::_cleanupOutput();
    }

    /**
     * output partial file.
     *
     * @param string $fpath
     * @param int    $offset
     * @param int    $limit
     */
    protected static function _outputPartialFile($fpath, $offset, $limit)
    {
        $clen = $limit - $offset + 1;
        $fp = fopen($fpath, 'rb');
        if ($offset > 0) {
            fseek($fp, $offset);
        }
        for ($len = $clen; $len >= self::OUTPUT_BLOCK_SIZE; $len -= self::OUTPUT_BLOCK_SIZE) {
            echo fread($fp, self::OUTPUT_BLOCK_SIZE);
        }
        if ($len > 0) {
            echo fread($fp, $len);
        }
        fclose($fp);
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
     * output range header.
     *
     * @param bool $useRange
     * @param int  $len
     * @param int  &$offset
     * @param int  &$limit
     */
    protected static function _outputRangeHeader($useRange, $len, &$offset, &$limit)
    {
        $offset = 0;
        $limit = $len - 1;
        if ($useRange && isset($_SERVER['HTTP_RANGE']) && preg_match('/^bytes=(\d+)-(\d+)?$/', $_SERVER['HTTP_RANGE'], $matches)) {
            $offset = $matches[1];
            if (!empty($matches[2])) {
                $limit = $matches[2];
            }
            self::_outputHttpStatusCodeHeader(206, false);
            $range = sprintf('bytes %u-%u/%u', $offset, $limit, $len);
            header('Accept-Ranges: bytes');
            header('Content-Range: '.$range);
        }
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
     * output content despotition header.
     *
     * @param int    $fname
     * @param string $encoding
     */
    protected static function _outputDispositionHeader($fname, $encoding)
    {
        if ($fname != '') {
            if ($encoding != 'UTF-8') {
                $fname = StringUtils::convertEncoding($fname, 'UTF-8', $encoding, 'h');
            }
            header('Content-Disposition: attachment; filename*=UTF-8\'\''.rawurlencode($fname));
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
            206 => 'Partial Content',
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
