<?php

use Xoonips\Core\FileUtils;
use Xoonips\Core\Functions;
use Xoonips\Core\UnzipFile;
use Xoonips\Core\XoopsUtils;

require_once dirname(__DIR__).'/core/ActionBase.class.php';
require_once dirname(__DIR__).'/XmlItemImport.class.php';
require_once dirname(dirname(__DIR__)).'/class/core/Item.class.php';

class Xoonips_ItemImportAction extends Xoonips_ActionBase
{
    protected function doInit(&$request, &$response)
    {
        // init import
        $this->doImport($request, $response);
        $response->setForward('import_success');

        return true;
    }

    protected function doLog(&$request, &$response)
    {
        // select order
        $import_id = intval($request->getParameter('import_id'));
        if (0 < $import_id) {
            // import log detail
            $this->doLogdetail($request, $response);
            $response->setForward('logdetail_success');

            return true;
        } else {
            // import log
            $this->doLogInit($request, $response);
            $response->setForward('log_success');

            return true;
        }
    }

    private function doImport(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();

        // token ticket
        $token_ticket = $this->createToken($this->modulePrefix('do_item_import'));

        // get parameter
        $index_select = $request->getParameter('index_select');

        // breadcrumbs
        $breadcrumbs = [
            ['name' => _MI_XOONIPS_USER_IMPORT_ITEM],
        ];

        // select index
        $index_select_arr = [];
        $is['value'] = 'file';
        $is['label'] = _MD_XOONIPS_ITEM_IMPORT_INDEX_SELECT_MSG2;
        $is['selected'] = ('file' == $index_select) ? 'yes' : 'no';
        $index_select_arr[] = $is;
        $is['value'] = 'self';
        $is['label'] = _MD_XOONIPS_ITEM_IMPORT_INDEX_SELECT_MSG1;
        $is['selected'] = ('self' == $index_select) ? 'yes' : 'no';
        $index_select_arr[] = $is;

        // index tree
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
        $is_admin = $userBean->isModerator($uid);

        $publicGroupIndexes = [];
        $groupIndexes = [];
        $privateIndex = false;
        $publicIndex = $indexBean->getPublicIndex();
        if ($is_admin) {
            $publicGroupIndexes = $indexBean->getPublicGroupIndex();
        }

        if (XOONIPS_UID_GUEST != $uid) {
            $groupIndexes = $indexBean->getGroupIndex($uid);
            $privateIndex = $indexBean->getPrivateIndex($uid);
        }
        $groupIndexes = $indexBean->mergeIndexes($publicGroupIndexes, $groupIndexes);
        $indexes = [];
        $trees = [];
        $url = false;
        // public index
        if ($publicIndex) {
            $indexes[] = $publicIndex;
            $tree = [];
            $tree['index_id'] = $publicIndex['index_id'];
            $trees[] = $tree;
        }
        // group index
        if ($groupIndexes) {
            foreach ($groupIndexes as $index) {
                $indexes[] = $index;
                $tree = [];
                $tree['index_id'] = $index['index_id'];
                $trees[] = $tree;
            }
        }
        // private index
        if ($privateIndex) {
            $privateIndex['title'] = 'Private';
            $indexes[] = $privateIndex;
            $tree = [];
            $tree['index_id'] = $privateIndex['index_id'];
            $trees[] = $tree;
        }

        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['token_ticket'] = $token_ticket;
        $viewData['select_tab'] = 1;
        $viewData['index_select_arr'] = $index_select_arr;
        $viewData['index_self'] = ('self' == $index_select) ? true : false;
        $viewData['indexes'] = $indexes;
        $viewData['trees'] = $trees;
        $viewData['dirname'] = $this->dirname;
        $response->setViewData($viewData);
    }

    protected function doImportsave(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();

        // check token ticket
        if (!$this->validateToken($this->modulePrefix('do_item_import'))) {
            $my_indexes = null;

            return false;
        }

        // get parameter
        $index_select = $request->getParameter('index_select');

        // for self index
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $req_indexes = [];
        $my_indexes = null;
        if ('self' == $index_select) {
            $req_indexes = explode(',', $request->getParameter('checked_indexes'));
            if (0 == count($req_indexes)) {
                $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/itemimport.php';
                $viewData['redirect_msg'] = _MD_XOONIPS_ITEM_IMPORT_FAILURE;
                $response->setViewData($viewData);
                $response->setForward('importsave_success');

                return true;
            }
        } else {
            $my_indexes = [];
            $publicGroupIndexes = [];
            $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname);
            $is_admin = $userBean->isModerator($uid);
            if ($is_admin) {
                $publicGroupIndexes = $indexBean->getPublicGroupIndex();
            }
            $MyIndexes = $indexBean->mergeIndexes($publicGroupIndexes, $indexBean->getGroupIndex($uid));
            $MyIndexes[] = $indexBean->getPublicIndex(); //Allow import item to public index for all user
            $MyIndexes[] = $indexBean->getPrivateIndex($uid);

            foreach ($MyIndexes as $mindex) {
                $my_indexes[] = $mindex['index_id'];
                $child_arr = $indexBean->getAllChildIds($mindex['index_id']);
                foreach ($child_arr as $child_id) {
                    if (!in_array($child_id, $my_indexes)) {
                        $my_indexes[] = $child_id;
                    }
                }
            }
        }

        $importfile = $request->getFile('import_file');

        if (empty($importfile) || empty($importfile['name']) || 0 == $importfile['size']) {
            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/itemimport.php';
            $viewData['redirect_msg'] = _MD_XOONIPS_ITEM_IMPORT_FILE_NONE;
            $response->setViewData($viewData);
            $response->setForward('importsave_success');

            return true;
        }

        // check file exists
        if (!file_exists($importfile['tmp_name'])) {
            die("don't exist temporary file '".$importfile['tmp_name']."'.");
        }

        // create temporary directry
        $upload_dir = Functions::getXoonipsConfig($this->dirname, 'upload_dir');
        $tmpdir1 = FileUtils::makeTempDirectory($upload_dir, 'im1');
        if (false === $tmpdir1) {
            die('failed to create temporary directry');
        }
        $tmpdir2 = FileUtils::makeTempDirectory($upload_dir, 'im2');
        if (false === $tmpdir2) {
            die('failed to create temporary directry');
        }

        // transaction
        $err_chk = false;
        $transaction = Xoonips_Transaction::getInstance();
        $transaction->start();

        // unzip
        $unzip = new UnzipFile();
        if ($unzip->open($importfile['tmp_name'])) {
            $files = $unzip->getFileList();
            foreach ($files as $file) {
                if (!$unzip->extractFile($file, $tmpdir2)) {
                    die('extract ERROR: '.$file);
                }
            }
            $unzip->close();
        } else {
            die('failed to open zip file '.$importfile['tmp_name']);
        }
        $this->flatDirectryInZipFile($tmpdir2, $tmpdir2);

        // Import Log
        $import = [
            'uid' => $uid,
            'result' => 0,
            'log' => "[Import Item] Begin\n",
            'timestamp' => time(),
        ];

        //date_default_timezone_set('asia/tokyo');
        $date = date('YmdHis');
        $defaultIndex = '/Private/import'.$date;

        // Import Log
        $import['log'] .= '[Import Item] Zip File : '.$importfile['name']."\n";

        $num = 0;
        $items = [];
        $res_dir = opendir($tmpdir2);
        while (false !== ($itemzip = readdir($res_dir))) {
            if ('.' == $itemzip || '..' == $itemzip) {
                continue;
            }

            if ($unzip->open($tmpdir2.'/'.$itemzip)) {
                $files = $unzip->getFileList();
                foreach ($files as $file) {
                    if (!$unzip->extractFile($file, $tmpdir1)) {
                        die('extract ERROR: '.$file);
                    }
                }
                $unzip->close();
            } else {
                die('failed to open zip file '.$tmpdir2.'/'.$itemzip);
            }
            $itemid = str_replace('.zip', '', $itemzip);
            $items[$itemid] = [
                'xml' => '',
                'tmpinfo' => [],
                'tmpfiles' => [],
                'error' => '',
            ];

            $xmlimport = new XmlItemImport();
            $xmlimport->uid($uid);

            ++$num;
            $res_dir2 = opendir($tmpdir1);
            while (false !== ($file = readdir($res_dir2))) {
                if ('.' == $file || '..' == $file) {
                    continue;
                }

                // temp file
                $path = $tmpdir1.'/'.$file;
                if (is_dir($path)) {
                    $res_dir3 = opendir($path);
                    while (false !== ($tmpfile = readdir($res_dir3))) {
                        if ('.' == $tmpfile || '..' == $tmpfile) {
                            continue;
                        }

                        $tmpfile_u = mb_convert_encoding($tmpfile, 'UTF-8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS');
                        $items[$itemid]['tmpinfo'][] = [
                            'id' => $file,
                            'name' => $tmpfile_u,
                        ];
                        $items[$itemid]['tmpfiles'][$file] = "${tmpdir1}/${file}/$tmpfile";

                        // Import Log
                        $import['log'] .= "[Import Item No.${num}] Temp File : ".$tmpfile_u."\n";
                    }
                    closedir($res_dir3);
                // xml file
                } else {
                    $xml_file = $path;
                    $items[$itemid]['xml'] = $xml_file;

                    // Import Log
                    $import['log'] .= "[Import Item No.${num}] XML File : ".$file."\n";

                    $fp = fopen($xml_file, 'r');
                    $tfp = fopen($xml_file.'.tmp', 'aw');
                    while ($line = fgets($fp, 4096)) {
                        $line_chk = trim($line);

                        // omit index tag
                        if ('self' == $index_select && (preg_match('/^<C:index*/', $line_chk) || preg_match('/^<\/C:index*/', $line_chk))) {
                            continue;
                        }
                        fwrite($tfp, $line);

                        // Import Log
                        $import['log'] .= "$line";
                    }
                    fclose($fp);
                    fclose($tfp);
                    unlink($xml_file);
                    rename($xml_file.'.tmp', $xml_file);
                }
            }
            closedir($res_dir2);

            // import item
            $xmlimport->set_tmp_file_array($items[$itemid]['tmpfiles']);
            $ret = $xmlimport->xml_import_by_file($items[$itemid]['xml'], $my_indexes, false, $defaultIndex);

            FileUtils::emptyDirectory($tmpdir1);

            $new_id = $xmlimport->get_create_item_id();

            if (!($new_id > 0) || !('200' == $ret || '206' == $ret)) {
                $transaction->rollback();

                $items[$itemid]['error'] = $xmlimport->get_err_code();
                $items[$itemid]['err_line'] = $xmlimport->get_line_no();
                $items[$itemid]['err_msg'] = $xmlimport->get_err_msg();
                $err_chk = true;

                // Import Log
                $import['log'] .= "[Import Item No.${num}] Result : Error (Code : ".$items[$itemid]['error'].")\n";
                $import['log'] .= "[Import Item No.${num}] Result : Error (Message : ".$items[$itemid]['err_msg'].' ['.$items[$itemid]['err_line']."])\n";

                break;
            } elseif ($new_id > 0) {
                if ('self' == $index_select) {
                    $index_bean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
                    $privateIndex = $index_bean->getPrivateIndex($uid);
                    $hasPrivate = false;
                    $new_indexes = '';
                    foreach ($req_indexes as $index) {
                        if (empty($hasPrivate)) {
                            $root_index = $index_bean->getRootIndex($index);
                            if ($root_index['index_id'] == $privateIndex['index_id']) {
                                $hasPrivate = true;
                            }
                        }
                        $new_indexes .= (0 == strlen($new_indexes)) ? $index : ','.$index;
                        // Import Log
                        $import['log'] .= "[Import Item No.${num}] Index ID : $index]\n";
                    }

                    $linkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
                    $linkBean->delete($new_id);

                    if (empty($hasPrivate)) {
                        $indexes_org = explode('/', $defaultIndex);
                        $indexes_slice = array_slice($indexes_org, 2);
                        $index_id = $index_bean->getIndexID($indexes_slice, $privateIndex, 0, 0, 0);
                        $new_indexes .= ','.$index_id;
                    }

                    if (!$this->forceEditIndex($new_id, $new_indexes)) {
                        $transaction->rollback();
                        // Import Log
                        $import['log'] .= "[Import Item No.${num}] Result : Error (Code : 400)\n";
                        $import['log'] .= "[Import Item No.${num}] Result : Error (Message : Couldn't register index.)\n";
                        $err_chk = true;
                        break;
                    }
                } else {
                    $itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
                    if ($itemBean->canView($new_id, XOONIPS_UID_GUEST)) {
                        // register OaipmhItemStatus
                        $itemStatusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $this->dirname, $this->trustDirname);
                        $itemStatusBean->updateItemStatus($new_id);
                    }
                }
            }

            // Import Log
            $import['log'] .= "[Import Item No.${num}] Result : Success (Item ID : $new_id)]\n";
        }
        closedir($res_dir);

        // success
        if (!$err_chk) {
            $transaction->commit();
        }

        // Import Log
        $import['result'] = ($err_chk) ? 0 : 1;
        $import['log'] .= '[Import Item] End';
        $import_id = $this->setLogDB($import, $items);

        if ($err_chk) {
            $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/itemimport.php?op=log&import_id='.$import_id;
            $viewData['redirect_msg'] = _MD_XOONIPS_ITEM_IMPORT_FAILURE;
            $response->setViewData($viewData);
            $response->setForward('importsave_success');

            return true;
        }

        $viewData['url'] = XOOPS_URL.'/modules/'.$this->dirname.'/itemimport.php?op=log&import_id='.$import_id;
        $viewData['redirect_msg'] = _MD_XOONIPS_ITEM_IMPORT_SUCCESS;
        $response->setViewData($viewData);
        $response->setForward('importsave_success');

        return true;
    }

    private function doLogInit(&$request, &$response)
    {
        $uid = XoopsUtils::getUid();

        // breadcrumbs
        $breadcrumbs = [
            ['name' => _MI_XOONIPS_USER_IMPORT_ITEM],
        ];

        // get item import log
        $logBean = Xoonips_BeanFactory::getBean('ItemImportLogBean', $this->dirname, $this->trustDirname);
        $logs = $logBean->getImportLogByUID($uid);

        foreach ($logs as $key => $log) {
            $logs[$key]['display_time'] = date('Y-m-d H:i:s', $log['timestamp']);

            $items = $logBean->getImportLogItems($log['item_import_log_id']);
            $logs[$key]['items'] = count($items);
        }

        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['select_tab'] = 2;
        $viewData['logs'] = $logs;
        $viewData['dirname'] = $this->dirname;
        $response->setViewData($viewData);
    }

    private function doLogdetail(&$request, &$response)
    {
        // get parameter
        $import_id = intval($request->getParameter('import_id'));

        // breadcrumbs
        $breadcrumbs = [
            [
                'name' => _MD_XOONIPS_ITEM_IMPORT_LOG_TITLE,
                'url' => XOOPS_URL.'/modules/'.$this->dirname.'/itemimport.php?op=log',
            ],
            [
                'name' => _MD_XOONIPS_ITEM_IMPORT_LOGDETAIL_TITLE,
            ],
        ];

        // get item import log
        $logBean = Xoonips_BeanFactory::getBean('ItemImportLogBean', $this->dirname, $this->trustDirname);
        $import = $logBean->getImportLogInfo($import_id);
        if (empty($import) || $import['uid' != $uid]) {
            $response->setSystemError(_NOPERM);

            return false;
        }

        $import['display_time'] = date('Y-m-d H:i:s', $import['timestamp']);

        $items = $logBean->getImportLogItems($import_id);
        $import['items'] = count($items);

        $import['log'] = strlen($import['log']) > 65536 ? substr($import['log'], 0, 65536).'...' : $import['log'];
        $viewData['xoops_breadcrumbs'] = $breadcrumbs;
        $viewData['select_tab'] = 2;
        $viewData['import'] = $import;
        $viewData['dirname'] = $this->dirname;
        $response->setViewData($viewData);
    }

    private function setLogDB($import, $items)
    {
        $import_id = 0;
        $logBean = Xoonips_BeanFactory::getBean('ItemImportLogBean', $this->dirname, $this->trustDirname);

        $logBean->insert($import);
        $import_id = $logBean->getInsertId();

        if (1 == $import['result']) {
            foreach ($items as $item_id => $val) {
                $logBean->insertLink($import_id, $item_id);
            }
        }

        return $import_id;
    }

    /**
     * Force Edit Index.
     *
     * @param int    $item_id
     * @param string $checkedIndexes
     *
     * @return bool true
     */
    private function forceEditIndex($itemId, $checkedIndexes)
    {
        $ret = false;

        $certify_msg = '';
        $bean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        $result = $bean->getItemBasicInfo($itemId);
        $itemtypeId = $result['item_type_id'];
        $item = new Xoonips_Item($itemtypeId, $this->dirname, $this->trustDirname);
        $certify = Functions::getXoonipsConfig($this->dirname, 'certify_item');
        $ret = $item->doIndexEdit($itemId, $checkedIndexes, $certify_msg, $certify);

        return $ret;
    }

    private function flatDirectryInZipFile($base_dir, $dir)
    {
        $dir_chk = false;
        $dir_arr = [];
        $o_dir = opendir($dir);
        while (false !== ($name = readdir($o_dir))) {
            if ('.' == $name || '..' == $name) {
                continue;
            }
            if (stripos($name, '.zip')) {
                rename($dir.'/'.$name, $base_dir.'/'.$name);
            } else {
                $dir_chk = true;
                $dir_arr[] = $name;
            }
        }
        closedir($o_dir);
        if ($dir_chk) {
            foreach ($dir_arr as $name) {
                $next_dir = $dir.'/'.$name;
                $this->flatDirectryInZipFile($base_dir, $next_dir);
                FileUtils::deleteDirectory($next_dir);
            }
        }
    }
}
