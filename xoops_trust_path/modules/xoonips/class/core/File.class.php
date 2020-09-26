<?php

use Xoonips\Core\FileUtils;
use Xoonips\Core\Functions;
use Xoonips\Core\StringUtils;

require_once __DIR__.'/Search.class.php';
require_once __DIR__.'/Request.class.php';

class Xoonips_File
{
    private $file_name;
    private $file_type;
    private $file_size;
    public $fsearch_plugins;

    private $dirname;
    private $trustDirname;

    /**
     * File bean.
     *
     * @var object
     */
    private $fileBean;
    /**
     * Search Text bean.
     *
     * @var object
     */
    private $searchTextBean;

    /**
     * Constructor.
     *
     * @param string $dirname
     * @param string $trustDirname
     * @param bool   $execFileSearch
     **/
    public function __construct($dirname, $trustDirname, $execFileSearch = false)
    {
        if (is_null($dirname)) {
            return;
        }
        $this->dirname = $dirname;
        $this->trustDirname = $trustDirname;
        $this->fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
        if ($execFileSearch) {
            $this->searchTextBean = Xoonips_BeanFactory::getBean('SearchTextBean', $this->dirname, $this->trustDirname);
            $this->loadFileSearchPlugins();
        }
    }

    // upload file
    public function uploadFile($fileName, $viewType, $itemId, $itemTypeDetailId, $group_id = 0)
    {
        //global $xoopsDB;

        $request = new Xoonips_Request();
        $file = $request->getFile($fileName, $this->dirname, $this->trustDirname);
        $itemtypeId = $request->getParameter('itemtype_id');
        $xnpsid = session_id();

        if (empty($file)) {
            return 'none';
        }

        global $xoopsUser;
        $uid = $xoopsUser->getVar('uid');
        $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $privateItemLimit = $itemBean->getPrivateItemLimit($uid);
        $filesizes = $itemBean->getFilesizePrivate($uid);
        if ($filesizes > $privateItemLimit['itemStorage'] && $privateItemLimit['itemStorage'] > 0) {
            return 'limit';
        }

        $this->file_name = $file['name'];
        $this->file_type = $file['type'];
        $this->file_size = $file['size'];

        $fs_name = $this->detectFileSearchPlugin($this->file_name, $this->file_type);
        $fs_version = is_null($fs_name) ? null : $this->fsearch_plugins[$fs_name]['version'] * 100;

        $fileInfo = ['item_id' => $itemId, 'item_field_detail_id' => $itemTypeDetailId,
            'original_file_name' => $this->file_name, 'mime_type' => $this->file_type,
            'file_size' => $this->file_size, 'sess_id' => $xnpsid,
            'search_module_name' => $fs_name, 'search_module_version' => $fs_version, 'group_id' => $group_id, ];

        $file_id = '';
        $this->fileBean->insertUploadFile($fileInfo, $file_id);

        $uploadDir = Functions::getXoonipsConfig($this->dirname, 'upload_dir');
        $uploadfile = $uploadDir.'/'.(int) $file_id;

        if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
            if (!is_null($fs_name)) {
                // insert n-gram strings of full text into table
                $this->insertSearchText($file_id, $fs_name, $uploadfile);
            }

            return $file_id;
        }

        return 'none';
    }

    // insert n-gram strings of full text into table
    private function insertSearchText($file_id, $fs_name, $file_path)
    {
        set_time_limit(0);
        // fetch plain text string using file search plugins
        $indexer = $this->fsearch_plugins[$fs_name]['instance'];
        $indexer->open($file_path);
        $text = $indexer->fetch();
        $indexer->close();

        // get n-gram strings
        $search = &Xoonips_Search::getInstance();
        $text = $search->getFulltextData($text);

        // open temporary file
        $tmpfile = tempnam('/tmp', 'XooNIpsSearch');
        $fp = fopen($tmpfile, 'w');
        if (false === $fp) {
            return false;
        }

        // register callback function to remove temporary file
        FileUtils::deleteFileOnExit($tmpfile);

        // write first field 'file_id'
        fwrite($fp, $file_id."\t");
        // dump hashed search text to temporary file
        fwrite($fp, StringUtils::convertEncoding($text, _CHARSET, 'UTF-8', 'h'));
        fclose($fp);

        // insert search text
        $esc_tmpfile = addslashes($tmpfile);
        $this->searchTextBean->insert($esc_tmpfile);
    }

    // detect file search plugin
    private function detectFileSearchPlugin($file_name, $file_mimetype)
    {
        $file_pathinfo = pathinfo($file_name);
        $file_ext = isset($file_pathinfo['extension']) ? $file_pathinfo['extension'] : '';
        $fs_name = null;
        foreach ($this->fsearch_plugins as $module) {
            if (in_array($file_ext, $module['extensions']) && in_array($file_mimetype, $module['mime_type'])) {
                $fs_name = $module['name'];
                break;
            }
        }

        return $fs_name;
    }

    // load file search plugins
    private function loadFileSearchPlugins()
    {
        if ($this->fsearch_plugins) {
            return true;
        }
        $this->fsearch_plugins = [];
        require_once __DIR__.'/FileSearchBase.class.php';

        $fsearch_dir = dirname(__DIR__).'/filesearch';
        if ($fsearch_handle = opendir($fsearch_dir)) {
            while ($file = readdir($fsearch_handle)) {
                if (preg_match('/^FileSearch.+\\.class.php$/', $file)) {
                    require_once $fsearch_dir.'/'.$file;
                    $className = 'Xoonips_'.substr($file, 0, strlen($file) - 10);
                    $fileSearch = new $className();
                    $searchInstance = $fileSearch->getSearchInstance();
                    $searchInstance['instance'] = $fileSearch;
                    $this->fsearch_plugins[$searchInstance['name']] = $searchInstance;
                }
            }
            closedir($fsearch_handle);
        }

        uasort($this->fsearch_plugins, [&$this, 'sortFileSearchPlugins']);

        return true;
    }

    private function sortFileSearchPlugins($a, $b)
    {
        return strcmp($a['display_name'], $b['display_name']);
    }

    // get file path
    public function getFilePath($opTp, $file_id)
    {
        $uploadDir = Functions::getXoonipsConfig($this->dirname, 'upload_dir');
        $fileInfo = $this->fileBean->getFile($file_id);
        if (isset($fileInfo['item_id']) && empty($fileInfo['item_id'])) {
            $file_path = $uploadDir.'/'.(int) $file_id;
        } else {
            $file_path = $uploadDir.'/'.$opTp.'/'.$fileInfo['item_id'].'/'.(int) $file_id;
        }

        return $file_path;
    }

    // update file information
    public function updateFileInfo($file_id)
    {
        $file_path = $this->getFilePath('item', $file_id);
        $fileInfo = $this->fileBean->getFile($file_id);
        if (!file_exists($file_path) || !$fileInfo) {
            // file or object not found
            return false;
        }
        $file_name = $fileInfo['original_file_name'];
        $fileInfo['mime_type'] = FileUtils::guessMimeType($file_path, $file_name);

        return $this->fileBean->updateFile($file_id, $fileInfo);
    }

    // update file search text
    public function updateFileSearchText($file_id, $force)
    {
        $fileInfo = $this->fileBean->getFile($file_id);
        if (!$fileInfo) {
            return false;
        }
        $file_name = $fileInfo['original_file_name'];
        $file_mimetype = $fileInfo['mime_type'];
        $file_path = $this->getFilePath('item', $file_id);
        $fs_name = $this->detectFileSearchPlugin($file_name, $file_mimetype);
        $fs_version = is_null($fs_name) ? null : $this->fsearch_plugins[$fs_name]['version'];
        if (!$force) {
            // plugin version check
            $old_fs_name = $fileInfo['search_module_name'];
            $old_fs_version = $fileInfo['search_module_version'];
            if ($fs_name == $old_fs_name) {
                if (is_null($fs_name)) {
                    // file search is not supported
                    return true;
                }
                if (floatval($fs_version) <= floatval($old_fs_version)) {
                    // no need to update search text
                    return true;
                }
            }
        }

        // delete search text at once
        $this->searchTextBean->delete($file_id);

        if (!is_readable($file_path) || is_null($fs_name)) {
            // clear search plugin informations
            $fileInfo['search_module_name'] = null;
            $fileInfo['search_module_version'] = null;

            return $this->fileBean->updateFile($file_id, $fileInfo);
        }

        // insert n-gram strings of full text into table
        $this->insertSearchText($file_id, $fs_name, $file_path);

        // update file search plugin information
        $fileInfo['search_module_name'] = $fs_name;
        $fileInfo['search_module_version'] = $fs_version;

        return $this->fileBean->updateFile($file_id, $fileInfo);
    }
}
