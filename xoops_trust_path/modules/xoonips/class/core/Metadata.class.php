<?php

use Xoonips\Core\StringUtils;

require_once XOOPS_TRUST_PATH.'/modules/xoonips/class/core/BeanFactory.class.php';
require_once 'Item.class.php';

class Xoonips_Metadata
{
    private $dirname;
    private $trustDirname;

    /** metadataPrefix */
    private $metadataPrefix;

    /** OAI-PMH Schema List */
    private $schemaList;

    /** OAI-PMH Schema Selectable Value List */
    private $valueSetList;

    /** OAI-PMH Schema and Item Type Link Table */
    private $linkBean;

    /* item handler */
    private $itemHandler;

    /* item file handler */
    private $itemFileHandler;

    /**
     * Constructor.
     *
     * @param string $dirname module dirname
     **/
    public function __construct($dirname, $trustDirname, $metadataPrefix)
    {
        $this->dirname = $dirname;
        $this->trustDirname = $trustDirname;
        $this->metadataPrefix = $metadataPrefix;
        $oaipmhSchemaBean = Xoonips_BeanFactory::getBean('OaipmhSchemaBean', $this->dirname, $this->trustDirname);
        $this->schemaList = $oaipmhSchemaBean->getSchemaList($this->metadataPrefix);
        $this->valueSetList = $oaipmhSchemaBean->getSchemaValueSetList($this->metadataPrefix);
        $this->linkBean = Xoonips_BeanFactory::getBean('OaipmhSchemaItemtypeLinkBean', $this->dirname, $this->trustDirname);
        $this->itemHandler = Xoonips_Utils::getTrustModuleHandler('Item', $this->dirname, $this->trustDirname);
        $this->itemFileHandler = Xoonips_Utils::getTrustModuleHandler('ItemFile', $this->dirname, $this->trustDirname);
    }

    public function getMetadata($item_type_id, $item_id)
    {
        $lines = array();
        // if junii2
        if ($this->metadataPrefix == 'junii2') {
            $lines[] = '<metadata>';
            $lines[] = '<junii2 xmlns="http://irdb.nii.ac.jp/oai" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://irdb.nii.ac.jp/oai http://irdb.nii.ac.jp/oai/junii2-3-1.xsd">';
            $this->getContents($item_type_id, $item_id, $lines);
            $lines[] = '</junii2>';
            $lines[] = '</metadata>';
            // if oai_dc
        } elseif ($this->metadataPrefix == 'oai_dc') {
            $lines[] = '<metadata>';
            $lines[] = '<oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns="http://purl.org/dc/elements/1.1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">';
            $this->getContents($item_type_id, $item_id, $lines);
            $lines[] = '</oai_dc:dc>';
            $lines[] = '</metadata>';
        }

        return implode("\n", $lines);
    }

    public static function supportMetadataFormat($metadataPrefix)
    {
        if ($metadataPrefix == 'oai_dc' || $metadataPrefix == 'junii2') {
            return true;
        }

        return false;
    }

    private function getContents($item_type_id, $item_id, &$lines)
    {
        $links = $this->linkBean->get($this->metadataPrefix, $item_type_id);
        $itemtype = new Xoonips_Item($item_type_id, $this->dirname, $this->trustDirname);
        $itemtype->setId($item_id);
        foreach ($this->schemaList as $schema) {
            $flg = false;
            foreach ($links as $link) {
                if ($schema['schema_id'] == $link['schema_id']) {
                    $flg = true;
                    $metadata = array();
                    $detail_ids = explode(',', $link['item_field_detail_id']);
                    $group_ids = explode(',', $link['group_id']);
                    foreach ($detail_ids as $index => $detail_id) {
                        $valueset = false;
                        foreach ($this->valueSetList as $obj) {
                            if ($obj['schema_id'] == $schema['schema_id'] && $obj['seq_id'] == $detail_id) {
                                $valueset = $obj['value'];
                                $metadata[] = $valueset;
                                break;
                            }
                        }
                        if ($valueset == false) {
                            $itemBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
                            $item = $itemBean->getItemBasicInfo($item_id);
                            $doi = $item['doi'];
                            if ($detail_id == 'http://') {
                                $itemObj = &$this->itemHandler->get($item_id);
                                $metadata[] = $itemObj->getUrl();
                            } elseif ($detail_id == 'ID') {
                                $database_id = Xoonips_Utils::getXooNIpsConfig($this->dirname, XOONIPS_CONFIG_REPOSITORY_NIJC_CODE);
                                if ($doi == null) {
                                    $metadata[] = "$database_id/$item_type_id.$item_id";
                                } else {
                                    $metadata[] = "$database_id:".XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME."/$doi";
                                }
                            } elseif ($detail_id == 'meta_author') {
                                $moduleHandler = &xoops_gethandler('module');
                                $legacyRender = &$moduleHandler->getByDirname('legacyRender');
                                $configHandler = &xoops_gethandler('config');
                                $configs = &$configHandler->getConfigsByCat(0, $legacyRender->get('mid'));
                                $metadata[] = $configs['meta_author'];
                            } elseif ($detail_id == 'itemtype') {
                                $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
                                $itemTypeInfo = $itemTypeBean->getItemTypeInfo($item_type_id);
                                $metadata[] = $itemTypeInfo['name'];
                            } elseif ($detail_id == 'owner') {
                                $itemUsersBean = Xoonips_BeanFactory::getBean('ItemUsersLinkBean', $this->dirname, $this->trustDirname);
                                $itemUsersInfo = $itemUsersBean->getItemUsersInfo($item_id);
                                $userBean = Xoonips_BeanFactory::getBean('UsersBean', $this->dirname, $this->trustDirname);
                                foreach ($itemUsersInfo as $userInfo) {
                                    $owner = $userBean->getUserBasicInfo($userInfo['uid']);
                                    if (!empty($owner['name'])) {
                                        $metadata[] = $owner['name'];
                                    } else {
                                        $metadata[] = $owner['uname'];
                                    }
                                }
                            } elseif ($detail_id == 'full_text') {
                                $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
                                $fileInfo = $fileBean->getFilesByItemId($item_id);
                                if (count($fileInfo) > 0) {
                                    foreach ($fileInfo as $file) {
                                        if ($file['mime_type'] != 'application/pdf') {
                                            continue;
                                        }
                                        $itemFileObj = &$this->itemFileHandler->get($file['file_id']);
                                        $metadata[] = $itemFileObj->getDownloadUrl(2);
                                    }
                                }
                            } elseif ($detail_id == 'fixed_value') {
                                if (!empty($link['value'])) {
                                    $metadata[] = $link['value'];
                                }
                            } else {
                                $group_id = $group_ids[$index];
                                $temp = $itemtype->getMetadata($detail_id, $group_id);
                                if ($schema['name'] == 'format') {
                                    if (is_array($temp)) {
                                        $metadata[] = $temp[1];
                                    } else {
                                        $metadata[] = $temp;
                                    }
                                } else {
                                    if (is_array($temp)) {
                                        foreach ($temp as $t) {
                                            $metadata[] = $t;
                                        }
                                    } else {
                                        $metadata[] = $temp;
                                    }
                                }
                            }
                        }
                    }

                    //In use more 2 value,all value none contine loop.
                    /*
                    if (count($metadata)>1) {
                        $allnone = false;
                        foreach ($metadata as $value) {
                            if (strlen($value) == 0) {
                                $allnone = true;
                                break;
                            }
                        }
                        if ($allnone == true) continue;
                    }
                    */
                    //FIXME
                    unset($temp);
                    if (!empty($link['value'])) {
                        $temp = self::convert($metadata, $link['value']);
                        $temp = StringUtils::convertEncoding($temp, 'UTF-8', _CHARSET, 'h');
                    }
                    if ($temp != 'null') {
                        if (strlen($temp) > 0) {
                            if (strcmp($schema['metadata_prefix'], 'oai_dc') == 0 && strcmp($schema['name'], 'type:NIItype') == 0) {
                                $lines[] = '<type>NIIType:'.StringUtils::xmlSpecialChars($temp, _CHARSET).'</type>';
                            } else {
                                $lines[] = '<'.$schema['name'].'>'.StringUtils::xmlSpecialChars($temp, _CHARSET).'</'.$schema['name'].'>';
                            }
                        } elseif (is_array($metadata)) {
                            foreach ($metadata as $m) {
                                if (!empty($m)) {
                                    $m = StringUtils::convertEncoding($m, 'UTF-8', _CHARSET, 'h');
                                    if (strcmp($schema['metadata_prefix'], 'oai_dc') == 0 && strcmp($schema['name'], 'type:NIItype') == 0) {
                                        $lines[] = '<type>NIIType:'.StringUtils::xmlSpecialChars($m, _CHARSET).'</type>';
                                    } else {
                                        $lines[] = '<'.$schema['name'].'>'.StringUtils::xmlSpecialChars($m, _CHARSET).'</'.$schema['name'].'>';
                                    }
                                }
                            }
                        }
                    }
                } elseif ($flg == true) {
                    break;
                }
            }
        }
    }

    private static function convert($metadata, $convertString)
    {
        //return fixed value
        if (strcmp($metadata[0], $convertString) == 0) {
            return $metadata[0];
        }
        if ($convertString != '') {
            preg_match_all("/@(\d+)/", $convertString, $matches);
            if (empty($matches[0])) {
                return false;
            }
            foreach ($matches[0] as $key => $value) {
                $c = '$metadata['.(int) ($matches[1][$key] - 1).']';
                $convertString = str_replace($value, $c, $convertString);
            }
            $convertString = $convertString.';';
            //FIXME XSS
            eval(" \$ret = $convertString");
        }

        if ($ret == null) {
            $ret = 'null';
        }

        return $ret;
    }
}
