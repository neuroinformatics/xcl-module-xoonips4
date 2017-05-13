<?php

require_once __DIR__.'/ItemEntity.class.php';

class Xoonips_Sitemaps
{
    /* sitemap request url */
    const PING_URL = 'http://www.google.com/webmasters/sitemaps/ping?sitemap=';

    /* request timeout */
    const TIMEOUT = '5000';

    /* dirname */
    private $dirname;

    /* trust dirname */
    private $trustDirname;

    /* sitemaps path */
    private $sitemapsPath;

    /* template */
    private $template;

    /* indexBean */
    protected $indexBean;

    /* indexBean */
    protected $indexLinkBean;

    /* itemBean */
    protected $itemBean;

    /* itemEntity */
    protected $itemEntity;

    /**
     * Constructor.
     *
     *  @param string $dirname module dirname
     */
    public function __construct($dirname, $trustDirname)
    {
        $this->dirname = $dirname;
        $this->trustDirname = $trustDirname;
        $this->sitemapsPath = XOOPS_TRUST_PATH.'/modules/'.$this->trustDirname.'/sitemaps';
        $this->template = XOOPS_TRUST_PATH.'/modules/'.$this->trustDirname.'/templates/sitemaps.html';
        $this->indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $this->indexLinkBean = Xoonips_BeanFactory::getBean('IndexItemLinkBean', $this->dirname, $this->trustDirname);
        $this->itemBean = Xoonips_BeanFactory::getBean('ItemVirtualBean', $this->dirname, $this->trustDirname);
        $this->itemEntity = new Xoonips_ItemEntity($this->dirname, $this->trustDirname);
    }

    /**
     * get all public indexes.
     */
    private function getAllPublicIndexes()
    {
        $index_ids = array();
        $public_index = $this->indexBean->getPublicIndex();
        $index_ids[] = $public_index['index_id'];
        foreach ($this->indexBean->getAllChildIndexes($public_index['index_id']) as $child_index) {
            $index_ids[] = $child_index['index_id'];
        }

        return $index_ids;
    }

    /**
     * get all public group indexes.
     */
    private function getAllPublicGroupIndexes()
    {
        $index_ids = array();
        foreach ($this->indexBean->getPublicGroupIndexes() as $index) {
            $index_id = $index['index_id'];
            $index_ids[] = $index_id;
        }

        return $index_ids;
    }

    /**
     * get file info.
     *
     * @param int $item_id
     *
     * @return array fileInfo
     */
    private function getFileInfo($item_id)
    {
        $fileInfo = array();
        global $xoopsDB;
        $tableName = XOOPS_DB_PREFIX.'_'.$this->dirname.'_item_file';
        $sql = sprintf('SELECT * FROM `%s` WHERE `item_id` = %d', $tableName, $item_id);
        $result = $xoopsDB->query($sql);
        if ($result == false) {
            return;
        }
        while (false != ($row = $xoopsDB->fetchArray($result))) {
            if ($row['mime_type'] == 'application/pdf') {
                $fileInfo['file_id'] = $row['file_id'];
                $fileInfo['original_file_name'] = $row['original_file_name'];
            }
        }

        return $fileInfo;
    }

    /**
     * output sitemaps xml.
     *
     * @param int index_id
     *
     * @return string xml
     */
    public function output($index_id)
    {
        $template = '';

        if (empty($index_id)) {
            $template = $this->sitemapsPath.'/sitemapindex.xml';
        } else {
            $template = $this->sitemapsPath.'/sitemaps'.$index_id.'.xml';
        }

        if (!file_exists($template)) {
            die('sitemaps is not exist.');
        }

        global $xoopsTpl;
        $tpl = new xoopsTpl();
        $tpl->assign($xoopsTpl->get_template_vars());
        $xml = $tpl->fetch($template);

        header('Content-Type: application/xml');
        echo $xml;
        exit;
    }

    /**
     * create sitemaps xml.
     *
     * @return bool
     */
    public function create()
    {
        $index_ids = [];

        // mkdir sitemaps file path
        if (!is_dir($this->sitemapsPath)) {
            mkdir($this->sitemapsPath);
        }

        // create sitemaps file
        $index_ids = array_merge($this->getAllPublicIndexes(), $this->getAllPublicGroupIndexes());
        foreach ($index_ids as $index_id) {
            if (!$this->createSitemaps($index_id)) {
                return false;
            }
        }

        // create sitemaps index
        if (!$this->createSitemapsIndex($index_ids)) {
            return false;
        }

        return true;
    }

    /**
     * ping.
     *
     * @return bool
     */
    public function ping()
    {
        $sitemapindex = XOOPS_URL.'/'.$this->dirname.'/sitemaps.php/sitemap.xml';
        $url = self::PING_URL.urlencode($sitemapindex);
        $conn = curl_init();
        $option = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_URL => $url,
            CURLOPT_CONNECTTIMEOUT_MS => self::GOOGLE_TMOUT,
        );
        curl_setopt_array($conn, $option);
        $response = curl_exec($conn);
        $errno = curl_errno($conn);
        if ($errno == 0) {
            $header = curl_getinfo($conn);
            curl_close($conn);
            if ($header['http_code'] == 200) {
                return true;
            }
        }

        return false;
    }

    /**
     * create sitemaps index xml.
     *
     * @param int index_ids
     *
     * @return bool
     */
    private function createSitemapsIndex($index_ids)
    {
        $indexes = array();
        foreach ($index_ids as $index_id) {
            $linked_item_ids = $this->indexLinkBean->getIndexItemLinkInfo2($index_id);
            if (empty($linked_item_ids)) {
                continue;
            }
            $indexes[] = $index_id;
        }

        $sitemapsindex_fname = $this->sitemapsPath.'/sitemapindex.xml';

        $now = new DateTime();
        $lastmod = $this->getLastMod($now->getTimestamp());

        global $xoopsTpl;
        $tpl = new xoopsTpl();
        $tpl->assign($xoopsTpl->get_template_vars());
        $tpl->assign('dirname', $this->dirname);
        $tpl->assign('indexes', $indexes);
        $tpl->assign('lastmod', $lastmod);
        $template = XOOPS_TRUST_PATH.'/modules/'.$this->trustDirname.'/templates/sitemapindex.html';
        $xml = $tpl->fetch($template);
        if (!file_put_contents($sitemapsindex_fname, $xml)) {
            return false;
        }

        return true;
    }

    /**
     * create sitemap xml.
     *
     * @param int index_id
     *
     * @return bool
     */
    private function createSitemaps($index_id)
    {
        $items = array();

        $linked_item_ids = $this->indexLinkBean->getIndexItemLinkInfo2($index_id);

        if (empty($linked_item_ids)) {
            return true;
        }

        foreach ($linked_item_ids as $linked_item_id) {
            $item = array();
            if ($linked_item_id['certify_state'] == 2 || $linked_item_id['certify_state'] == 3) {
                $item_id = $linked_item_id['item_id'];
                $itemInfo = $this->itemBean->getItem2($item_id);
                $this->itemEntity->setData($itemInfo);
                $dl_limit_check = $this->itemEntity->get('download_limitation', 'attachment_dl_limit');
                if ($dl_limit_check != false) {
                    if ($dl_limit_check[0] === '1') {
                        continue;
                    }
                }
                $item['item_id'] = $item_id;
                $item['detail'] = $this->itemEntity->getItemUrl();
                //get file
                $fileInfo = $this->getFileInfo($item_id);
                if (!empty($fileInfo)) {
                    $item['file_id'] = $fileInfo['file_id'];
                    $item['original_file_name'] = $fileInfo['original_file_name'];
                }
                //get last mod
                $item['lastmod'] = $this->getLastMod($this->itemEntity->get('last_update_date', 'last_update_date'));
                $items[] = $item;
            }
        }

        $sitemaps_fname = $this->sitemapsPath.'/sitemaps'.$index_id.'.xml';

        global $xoopsTpl;
        $tpl = new xoopsTpl();
        $tpl->assign($xoopsTpl->get_template_vars());
        $tpl->assign('dirname', $this->dirname);
        $tpl->assign('items', $items);
        $xml = $tpl->fetch($this->template);

        if (!file_put_contents($sitemaps_fname, $xml)) {
            return false;
        }

        return true;
    }

    /**
     * get last mod.
     *
     * @param unixtime date
     *
     * @return date W3C format date
     */
    private function getLastMod($date)
    {
        $w3cdate = new DateTime();
        $w3cdate->setTimestamp($date);

        return $w3cdate->format(DateTime::W3C);
    }
}
