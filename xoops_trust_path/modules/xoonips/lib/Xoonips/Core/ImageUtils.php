<?php

namespace Xoonips\Core;

/**
 * image utility class.
 */
class ImageUtils
{
    const THUMBNAIL_ICON_DIR = 'images/thumbnails';
    const THUMBNAIL_MIN_WIDTH = 120;
    const THUMBNAIL_MIN_HEIGHT = 120;

    /**
     * show image file.
     *
     * @param string $fpath
     */
    public static function showImage($fpath, $fname)
    {
        $info = FileUtils::getFileInfo($fpath, $fname);
        if (false === $info) {
            CacheUtils::errorExit(404);
        }
        CacheUtils::check304($info['mtime'], $info['etag']);
        if (array_key_exists('width', $info)) {
            CacheUtils::outputFile($info['mtime'], $info['etag'], $info['mime'], $fpath);
        } else {
            self::_showThumbnailIcon($info['mtime'], $info['etag'], $info['mime'], self::THUMBNAIL_MIN_WIDTH, self::THUMBNAIL_MIN_HEIGHT);
        }
    }

    /**
     * show thumbnail.
     *
     * @param string $fpath
     * @param string $fname
     * @param int    $maxWidth
     * @param int    $maxHeight
     */
    public static function showThumbnail($fpath, $fname, $maxWidth, $maxHeight)
    {
        $info = FileUtils::getFileInfo($fpath, $fname);
        if (false === $info) {
            CacheUtils::errorExit(404);
        }
        $etag = md5($info['etag'].$maxWidth.$maxHeight);
        CacheUtils::check304($info['mtime'], $info['etag']);
        if (array_key_exists('width', $info)) {
            if ($info['width'] <= $maxWidth && $info['height'] <= $maxHeight) {
                CacheUtils::outputFile($info['mtime'], $info['etag'], $info['mime'], $fpath);
            } else {
                self::_showResizedImage($info['mtime'], $etag, $info['mime'], $fpath, $info['width'], $info['height'], $maxWidth, $maxHeight);
            }
        } else {
            self::_showThumbnailIcon($info['mtime'], $info['etag'], $info['mime'], $maxWidth, $maxHeight);
        }
    }

    /**
     * show resized image.
     *
     * @param int    $mtime
     * @param string $etag
     * @param string $mime
     * @param string $fpath
     * @param int    $srcWidth
     * @param int    $srcHeight
     * @param int    $maxWidth
     * @param int    $maxHeight
     */
    protected static function _showResizedImage($mtime, $etag, $mime, $fpath, $srcWidth, $srcHeight, $maxWidth, $maxHeight)
    {
        switch ($mime) {
        case 'image/jpeg':
            $srcImage = imagecreatefromjpeg($fpath);
        break;
        case 'image/gif':
            $srcImage = imagecreatefromgif($fpath);
        break;
        case 'image/png':
            $srcImage = imagecreatefrompng($fpath);
        break;
        default:
            CacheUtils::errorExit(500);
        }
        $width = $srcWidth;
        $height = $srcHeight;
        if ($width > $maxWidth) {
            $height = intval($height * $maxWidth / $width);
            $width = $maxWidth;
        }
        if ($height > $maxHeight) {
            $width = intval($width * $maxHeight / $height);
            $height = $maxHeight;
        }
        $image = imagecreatetruecolor($width, $height);
        imagecopyresampled($image, $srcImage, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
        imagedestroy($srcImage);
        CacheUtils::outputImagePng($mtime, $etag, $image);
    }

    /**
     * show thumbnail icon image.
     *
     * @param int    $mtime
     * @param string $etag
     * @param string $mime
     * @param int    $width
     * @param int    $height
     */
    protected static function _showThumbnailIcon($mtime, $etag, $mime, $width, $height)
    {
        if (!preg_match('/^([^\\/]+)\\/(.+)$/', $mime, $matches)) {
            CacheUtils::errorExit(500);
        }
        if (self::THUMBNAIL_MIN_WIDTH > $width) {
            $width = self::THUMBNAIL_MIN_WIDTH;
        }
        if (self::THUMBNAIL_MIN_HEIGHT > $height) {
            $height = self::THUMBNAIL_MIN_HEIGHT;
        }
        // detect icon type
        $type = 'unknown';
        if (in_array($matches[1], ['audio', 'image', 'video', 'text'])) {
            $type = $matches[1];
        } elseif ('application' == $matches[1]) {
            $type = 'application';
            $subtypes = [
                'text' => [
                    'pdf',
                    'xml',
                    'msword',
                    'vnd.ms-excel',
                ],
                'image' => [
                    'vnd.ms-powerpoint',
                    'postscript',
                ],
                'audio' => [
                    'vnd.rn-realmedia',
                ],
            ];
            foreach ($subtypes as $map => $subtype) {
                if (in_array($matches[2], $subtype)) {
                    $type = $map;
                    break;
                }
            }
        }
        $basedir = dirname(dirname(__DIR__));
        if (!file_exists($ipath = sprintf('%s/%s/%s.png', $basedir, self::THUMBNAIL_ICON_DIR, $type))) {
            CacheUtils::errorExit(500);
        }
        $label = $mime;
        // create image resource
        $image = imagecreatetruecolor($width, $height);
        $font = 2; // font number
        $padding = 5; // label padding
        $fw = imagefontwidth($font); // font width
        $fh = imagefontheight($font); // font height
        $fmaxlen = ($width - $padding * 2) / $fw; // max label length
        $llen = strlen($label);
        if ($llen > $fmaxlen) {
            $label = substr($label, 0, $fmaxlen - 3).'...';
            $llen = strlen($label);
        }
        $lx = ($width - $llen * $fw) / 2;
        $ly = $height - $fh - $padding;
        // change alpha attributes and create transparent color
        if (function_exists('imageantialias')) {
            imageantialias($image, true);
        }
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 0);
        $col_white = imagecolorallocate($image, 255, 255, 255);
        $col_gray = imagecolorallocate($image, 127, 127, 127);
        $col_black = imagecolorallocate($image, 0, 0, 0);
        // fill all area with transparent color
        imagefill($image, 0, 0, $col_white);
        imagealphablending($image, true);
        $imageIcon = imagecreatefrompng($ipath);
        imagecopy($image, $imageIcon, $width / 2 - 48 / 2, $height / 2 - 48 / 2, 0, 0, 48, 48);
        imagedestroy($imageIcon);
        imagepolygon($image, [0, 0, $width - 1, 0, $width - 1, $height - 1, 0, $height - 1], 4, $col_gray);
        imagestring($image, $font, $lx, $ly, $label, $col_black);
        CacheUtils::outputImagePng($mtime, $etag, $image);
    }
}
