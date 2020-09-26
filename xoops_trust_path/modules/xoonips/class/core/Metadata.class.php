<?php

use Xoonips\Core\Functions;
use Xoonips\Core\StringUtils;

require_once __DIR__.'/Item.class.php';

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
        $this->itemHandler = Functions::getXoonipsHandler('Item', $this->dirname);
        $this->itemFileHandler = Functions::getXoonipsHandler('ItemFile', $this->dirname);
    }

    public function getMetadata($item_type_id, $item_id)
    {
        $lines = [];
        // if junii2
        if ('junii2' == $this->metadataPrefix) {
            $lines[] = '<metadata>';
            $lines[] = '<junii2 xmlns="http://irdb.nii.ac.jp/oai" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://irdb.nii.ac.jp/oai http://irdb.nii.ac.jp/oai/junii2-3-1.xsd">';
            $this->getContents($item_type_id, $item_id, $lines);
            $lines[] = '</junii2>';
            $lines[] = '</metadata>';
        // if oai_dc
        } elseif ('oai_dc' == $this->metadataPrefix) {
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
        if ('oai_dc' == $metadataPrefix || 'junii2' == $metadataPrefix) {
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
                    $metadata = [];
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
                        if (false == $valueset) {
                            $itemBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
                            $item = $itemBean->getItemBasicInfo($item_id);
                            $doi = $item['doi'];
                            if ('http://' == $detail_id) {
                                $itemObj = &$this->itemHandler->get($item_id);
                                $metadata[] = $itemObj->getUrl();
                            } elseif ('ID' == $detail_id) {
                                $database_id = Functions::getXoonipsConfig($this->dirname, XOONIPS_CONFIG_REPOSITORY_NIJC_CODE);
                                if (null == $doi) {
                                    $metadata[] = "$database_id/$item_type_id.$item_id";
                                } else {
                                    $metadata[] = "$database_id:".XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME."/$doi";
                                }
                            } elseif ('meta_author' == $detail_id) {
                                $moduleHandler = &xoops_gethandler('module');
                                $legacyRender = &$moduleHandler->getByDirname('legacyRender');
                                $configHandler = &xoops_gethandler('config');
                                $configs = &$configHandler->getConfigsByCat(0, $legacyRender->get('mid'));
                                $metadata[] = $configs['meta_author'];
                            } elseif ('itemtype' == $detail_id) {
                                $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
                                $itemTypeInfo = $itemTypeBean->getItemTypeInfo($item_type_id);
                                $metadata[] = $itemTypeInfo['name'];
                            } elseif ('owner' == $detail_id) {
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
                            } elseif ('full_text' == $detail_id) {
                                $fileBean = Xoonips_BeanFactory::getBean('ItemFileBean', $this->dirname, $this->trustDirname);
                                $fileInfo = $fileBean->getFilesByItemId($item_id);
                                if (count($fileInfo) > 0) {
                                    foreach ($fileInfo as $file) {
                                        if ('application/pdf' != $file['mime_type']) {
                                            continue;
                                        }
                                        $itemFileObj = &$this->itemFileHandler->get($file['file_id']);
                                        $metadata[] = $itemFileObj->getDownloadUrl(2);
                                    }
                                }
                            } elseif ('fixed_value' == $detail_id) {
                                if (!empty($link['value'])) {
                                    $metadata[] = $link['value'];
                                }
                            } else {
                                $group_id = $group_ids[$index];
                                $temp = $itemtype->getMetadata($detail_id, $group_id);
                                if ('format' == $schema['name']) {
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
                    if ('null' != $temp) {
                        if (strlen($temp) > 0) {
                            if (0 == strcmp($schema['metadata_prefix'], 'oai_dc') && 0 == strcmp($schema['name'], 'type:NIItype')) {
                                $lines[] = '<type>NIIType:'.StringUtils::xmlSpecialChars($temp, _CHARSET).'</type>';
                            } else {
                                $lines[] = '<'.$schema['name'].'>'.StringUtils::xmlSpecialChars($temp, _CHARSET).'</'.$schema['name'].'>';
                            }
                        } elseif (is_array($metadata)) {
                            foreach ($metadata as $m) {
                                if (!empty($m)) {
                                    $m = StringUtils::convertEncoding($m, 'UTF-8', _CHARSET, 'h');
                                    if (0 == strcmp($schema['metadata_prefix'], 'oai_dc') && 0 == strcmp($schema['name'], 'type:NIItype')) {
                                        $lines[] = '<type>NIIType:'.StringUtils::xmlSpecialChars($m, _CHARSET).'</type>';
                                    } else {
                                        $lines[] = '<'.$schema['name'].'>'.StringUtils::xmlSpecialChars($m, _CHARSET).'</'.$schema['name'].'>';
                                    }
                                }
                            }
                        }
                    }
                } elseif (true == $flg) {
                    break;
                }
            }
        }
    }

    private static function convert($metadata, $convertString)
    {
        //return fixed value
        if (0 == strcmp($metadata[0], $convertString)) {
            return $metadata[0];
        }
        if ('' != $convertString) {
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

        if (null == $ret) {
            $ret = 'null';
        }

        return $ret;
    }
}
