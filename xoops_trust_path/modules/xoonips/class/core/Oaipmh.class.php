<?php

use Xoonips\Core\Functions;
use Xoonips\Core\StringUtils;

define('XOONIPS_METADATA_CATEGORY_ID', 'ID');
define('XOONIPS_METADATA_CATEGORY_TITLE', 'TITLE');
define('XOONIPS_METADATA_CATEGORY_CREATOR', 'CREATOR');
define('XOONIPS_METADATA_CATEGORY_RESOURCE_LINK', 'RESOURCE_LINK');
define('XOONIPS_METADATA_CATEGORY_LAST_UPDATE_DATE', 'LAST_UPDATE_DATE');
define('XOONIPS_METADATA_CATEGORY_CREATION_DATE', 'CREATION_DATE');
define('XOONIPS_METADATA_CATEGORY_DATE', 'DATE');
define('XOONIPS_CONFIG_REPOSITORY_NIJC_CODE', 'repository_nijc_code');
define('REPOSITORY_RESPONSE_LIMIT_ROW', 100);
define('REPOSITORY_RESUMPTION_TOKEN_EXPIRE_TERM', 1000);

require_once __DIR__.'/Metadata.class.php';

class Xoonips_Oaipmh
{
    /** root path module directory **/
    private $dirname;

    /** trust path module directory */
    private $trustDirname;

    /** base URL of repository */
    private $baseURL;

    /** name of repository */
    private $repositoryName;

    /** id of repository */
    private $repositoryId;

    /** repository deletion track */
    private $repositoryDeletionTrack;

    /** moderator's group id */
    private $moderator_gid;

    /** limit row */
    private $limit_row = REPOSITORY_RESPONSE_LIMIT_ROW;

    /** expire time */
    private $expire_term = REPOSITORY_RESUMPTION_TOKEN_EXPIRE_TERM;

    /** Oaipmh Item Status Table */
    private $itemStatusBean;

    /** Item Table */
    private $itemBean;

    /** Metadata generation class */
    private $metadata;

    /** common header */
    private $headerXml = '<?xml version="1.0" encoding="UTF-8" ?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
<responseDate>%s</responseDate>';

    /** identify */
    private $identifyXml = '<Identify>
<repositoryName>%s</repositoryName>
<baseURL>%s</baseURL>
<protocolVersion>2.0</protocolVersion>
<deletedRecord>%s</deletedRecord>
<granularity>YYYY-MM-DDThh:mm:ssZ</granularity>
<earliestDatestamp>%s</earliestDatestamp>';

    /** oaidc metadataFormat */
    private $oaidcXml = '<metadataFormat>
<metadataPrefix>oai_dc</metadataPrefix>
<schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>
<metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>
</metadataFormat>';

    /** junii2 metadataFormat */
    private $junii2Xml = '<metadataFormat>
<metadataPrefix>junii2</metadataPrefix>
<schema>http://irdb.nii.ac.jp/oai/junii2-3-1.xsd</schema>
<metadataNamespace>http://irdb.nii.ac.jp/oai</metadataNamespace>
</metadataFormat>';

    /**
     * Constructor.
     *
     * @param string $dirname module dirname
     */
    public function __construct($dirname, $trustDirname)
    {
        $this->dirname = $dirname;
        $this->trustDirname = $trustDirname;
        $this->baseURL = XOOPS_URL.'/modules/'.$this->dirname.'/oai.php';
        $this->repositoryName = Functions::getXoonipsConfig($this->dirname, 'repository_name');
        $this->repositoryId = Functions::getXoonipsConfig($this->dirname, XOONIPS_CONFIG_REPOSITORY_NIJC_CODE);
        $this->repositoryDeletionTrack = Functions::getXoonipsConfig($this->dirname, 'repository_deletion_track');
        $this->moderator_gid = Functions::getXoonipsConfig($this->dirname, 'moderator_gid');
    }

    /**
     * return oai-pmh repository.
     *
     * @param array $args hash variable for parameters
     *                    array('verb' => 'Ldentify')
     *
     * @return string xml
     */
    public function exec($args)
    {
        $xml = '';
        if (!isset($args['verb'])) {
            $xml = $this->error('badVerb', 'no verb');
        } elseif ('GetRecord' == $args['verb']) {
            $xml = $this->getRecord($args);
        } elseif ('Identify' == $args['verb']) {
            $xml = $this->identify($args);
        } elseif ('ListIdentifiers' == $args['verb']) {
            $xml = $this->listIdentifiers($args);
        } elseif ('ListMetadataFormats' == $args['verb']) {
            $xml = $this->listMetadataFormats($args);
        } elseif ('ListRecords' == $args['verb']) {
            $xml = $this->listRecords($args);
        } elseif ('ListSets' == $args['verb']) {
            $xml = $this->listSets($args);
        } else {
            $xml = $this->error('badVerb', 'illegal verb');
        }
        header('Content-Type: application/xml');
        echo $this->header().$this->request($args).$xml.$this->footer();
    }

    /**
     * generate <GetRecord>.
     *
     * @param array $args hash variable for parameters
     *                    array('verb' => 'Ldentify')
     *
     * @return <GetREcord>
     */
    private function getRecord($args)
    {
        // parameters check
        $error = '';
        if (!isset($args['identifier'])) {
            $error = $this->error('badArgument', 'identifier is not specified');
        } elseif (!isset($args['metadataPrefix'])) {
            $error = $this->error('badArgument', '');
        } elseif (!Xoonips_Metadata::supportMetadataFormat($args['metadataPrefix'])) {
            $error = $this->error('cannotDisseminateFormat', '');
        } elseif (isset($args['set']) || isset($args['from']) || isset($args['until'])
            || isset($args['resumptionToken'])) {
            $error = $this->error('badArgument', '');
        } elseif (empty($this->repositoryId)) {
            $error = $this->error('idDoesNotExist', 'it maps to no known item');
        }
        if ('' != $error) {
            return $error;
        }

        $this->itemStatusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $this->dirname, $this->trustDirname);
        $this->itemBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);
        $this->metadata = new Xoonips_Metadata($this->dirname, $this->trustDirname, $args['metadataPrefix']);

        // identifier exist check
        $parsed = $this->parseIdentifier($this->convertIdentifierFormat($args['identifier']));
        if (false !== $parsed) {
            $identifiers = $this->itemStatusBean->getOpenItem4Oaipmh(0, 0, null, $parsed['item_id'], 1, $this->repositoryDeletionTrack, null);
        }
        if (!$parsed || !$identifiers || 0 == count($identifiers)) {
            return $this->error('idDoesNotExist', 'it maps to no known item');
        }

        $result = false;
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $index_tree_list = $indexBean->getPublicFullPath(true);
        $identifiers[0]['nijc_code'] = $parsed['nijc_code'];
        $identifiers[0]['item_type_id'] = $parsed['item_type_id'];
        list($xml, $result) = $this->record($identifiers[0], $index_tree_list);
        if ($result) {
            $xml = "<GetRecord>\n".$xml."</GetRecord>\n";
        }

        return $xml;
    }

    /**
     * generate <Identify>.
     *
     * @param array $args hash variable for parameters
     *                    array('verb' => 'Ldentify')
     *
     * @return <Identify>
     */
    private function identify($args)
    {
        // parameters check
        if (isset($args['metadataPrefix']) || isset($args['set']) || isset($args['from'])
            || isset($args['until']) || isset($args['identifier']) || isset($args['resumptionToken'])) {
            return $this->error('badArgument', '');
        }

        // retrieve admin's e-mail
        $adminEmails = [];
        $member_handler = &xoops_gethandler('member');
        $members = $member_handler->getUsersByGroup($this->moderator_gid, false);
        foreach ($members as $userid) {
            $user = &$member_handler->getUser($userid);
            $adminEmails[] = $user->getVar('email');
        }
        $deletedRecord = ($this->repositoryDeletionTrack > 0) ? 'transient' : 'no';
        $xml = sprintf($this->identifyXml."\n", $this->repositoryName, $this->baseURL, $deletedRecord, gmdate("Y-m-d\TH:i:s\Z", 0));
        foreach ($adminEmails as $email) {
            $xml .= "<adminEmail>$email</adminEmail>\n";
        }
        $xml .= "</Identify>\n";

        return $xml;
    }

    /**
     * generate <ListIdentifiers>.
     *
     * @param array $args hash variable for parameters
     *                    array('verb' => 'Ldentify')
     *
     * @return <ListIdentifiers>
     */
    private function listIdentifiers($args)
    {
        //expire RexumpionToken remove
        $this->expireResumptionToken();

        $this->itemStatusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $this->dirname, $this->trustDirname);
        $this->itemBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);

        // parameters check
        list($error, $metadataPrefix, $result) = $this->checkParameters($args);
        if ('' != $error) {
            return $error;
        }

        list($error, $cursor, $identifiers) = $this->listIdentifiersAndRecords($args, $result);
        if ('' != $error) {
            return $error;
        }

        $cnt = count($identifiers);
        if ($cnt > $cursor + $this->limit_row) {
            $last_item_id = $identifiers[$cursor + $this->limit_row - 1]['item_id'];
        }
        $resumptionToken_xml = $this->resumptionTokenXml($cnt, $cursor, $args, $metadataPrefix, $last_item_id);

        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $index_tree_list = $indexBean->getPublicFullPath(true);
        $headers = [];
        for ($i = $cursor; $i < ($cursor + $this->limit_row) && $i < $cnt; ++$i) {
            $headers[] = $this->oaipmhHeader($identifiers[$i], $index_tree_list);
        }
        $xml = "<ListIdentifiers>\n".implode("\n", $headers).$resumptionToken_xml."</ListIdentifiers>\n";

        return $xml;
    }

    /**
     * generate <ListMetadataFormats>.
     *
     * @param array $args hash variable for parameters
     *                    array('verb' => 'Ldentify')
     *
     * @return <ListMetadataFormats>
     */
    private function listMetadataFormats($args)
    {
        // parameters check
        if (isset($args['metadataPrefix']) || isset($args['set']) || isset($args['from'])
            || isset($args['until']) || isset($args['resumptionToken'])) {
            return $this->error('badArgument', '');
        } elseif (isset($args['identifier']) && empty($this->repositoryId)) {
            return $this->error('idDoesNotExist', 'it maps to no known item');
        }

        $this->itemStatusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $this->dirname, $this->trustDirname);
        $this->itemBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);

        // identifier exist check
        if (isset($args['identifier'])) {
            $parsed = $this->parseIdentifier($this->convertIdentifierFormat($args['identifier']));
            if (false !== $parsed) {
                $identifiers = $this->itemStatusBean->getOpenItem4Oaipmh(0, 0, null, $parsed['item_id'], 1, $this->repositoryDeletionTrack, null);
            }
            if (!$parsed || !$identifiers || 0 == count($identifiers)) {
                return $this->error('idDoesNotExist', 'it maps to no known item');
            }
        }
        $lines = [$this->junii2Xml, $this->oaidcXml];
        $xml = "<ListMetadataFormats>\n".implode("\n", $lines)."</ListMetadataFormats>\n";

        return $xml;
    }

    /**
     * generate <ListRecords>.
     *
     * @param args: hash variable for parameters
     *              array('verb' => 'Ldentify')
     *
     * @return <ListRecords>
     */
    private function listRecords($args)
    {
        //expire RexumpionToken remove
        $this->expireResumptionToken();
        // parameters check
        $this->itemStatusBean = Xoonips_BeanFactory::getBean('OaipmhItemStatusBean', $this->dirname, $this->trustDirname);
        $this->itemBean = Xoonips_BeanFactory::getBean('ItemBean', $this->dirname, $this->trustDirname);

        list($error, $metadataPrefix, $result) = $this->checkParameters($args);
        if ('' != $error) {
            return $error;
        }
        $this->metadata = new Xoonips_Metadata($this->dirname, $this->trustDirname, $metadataPrefix);

        list($error, $cursor, $identifiers) = $this->listIdentifiersAndRecords($args, $result);
        if ('' != $error) {
            return $error;
        }

        $cnt = count($identifiers);
        if ($cnt > $cursor + $this->limit_row) {
            $last_item_id = $identifiers[$cursor + $this->limit_row - 1]['item_id'];
        }
        $resumptionToken_xml = $this->resumptionTokenXml($cnt, $cursor, $args, $metadataPrefix, $last_item_id);

        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $index_tree_list = $indexBean->getPublicFullPath(true);

        $records = [];
        for ($i = $cursor; $i < ($cursor + $this->limit_row) && $i < $cnt; ++$i) {
            list($xml, $result) = $this->record($identifiers[$i], $index_tree_list);
            if ($result) {
                $records[] = $xml;
            }
        }
        $xml = "<ListRecords>\n".implode("\n", $records).$resumptionToken_xml."</ListRecords>\n";

        return $xml;
    }

    /**
     * generate <ListSets>.
     *
     * @param array $args hash variable for parameters
     *                    array('verb' => 'Ldentify')
     *
     * @return <ListSets>
     */
    private function listSets($args)
    {
        $this->expireResumptionToken();
        // parameters check
        if (isset($args['metadataPrefix']) || isset($args['set']) || isset($args['from'])
            || isset($args['until']) || isset($args['identifier'])) {
            return $this->error('badArgument', '');
        }

        $cursor = 0;
        // get item type list
        $itemTypeBean = Xoonips_BeanFactory::getBean('ItemTypeBean', $this->dirname, $this->trustDirname);
        $itemtypes = $itemTypeBean->getItemTypeList();
        // get index list
        $indexBean = Xoonips_BeanFactory::getBean('IndexBean', $this->dirname, $this->trustDirname);
        $index_list = $indexBean->getPublicFullPath();
        // check resumptionToken parameter.
        if (isset($args['resumptionToken'])) {
            $error = '';
            $result = $this->getResumptionToken($args['resumptionToken']);
            if (count($result) > 0) {
                if (isset($result['last_item_id'])) {
                    $cursor = $result['last_item_id'];
                }
                if (isset($result['args']['item_all_count'])) {
                    $set_count = $result['args']['item_all_count'];
                }
                //expire resumptionToken if index tree is modified after resumptionToken has published
                if (!$index_list || (count($index_list) + count($itemtypes)) != $set_count) {
                    $this->deleteResumptionToken($args['resumptionToken']);
                    $error = $this->error('badResumptionToken', 'repository has been modified');
                }
            } else {
                $error = $this->error('badResumptionToken', '');
            }
            if ('' != $error) {
                return $error;
            }
        }

        // create string by item index and item type
        $xml = '';
        if (!empty($this->repositoryId)) {
            $set_list = $index_list;
            // add item type
            if ($itemtypes) {
                foreach ($itemtypes as $itemtype) {
                    $set_list[] = $itemtype;
                }
            }

            $cnt = count($set_list);
            $end_index = min($cursor + $this->limit_row, $cnt);
            for ($i = $cursor; $i < $end_index; ++$i) {
                $set = $set_list[$i];
                if (isset($set['id_fullpath'])) {
                    $spec = 'index'.implode(':index', explode(',', $set['id_fullpath']));
                    $name = $set['fullpath'];
                } else {
                    $spec = $set['name'];
                    $name = $set['description'];
                }
                $spec = StringUtils::convertEncoding($spec, 'UTF-8', _CHARSET, 'h');
                $name = StringUtils::convertEncoding($name, 'UTF-8', _CHARSET, 'h');
                $name = htmlspecialchars($name, ENT_QUOTES);
                $xml .= "    <set>\n"
                    .'        <setSpec>'.$spec."</setSpec>\n"
                    .'        <setName>'.$name."</setName>\n"
                    ."    </set>\n";
            }
        } else {
            $cnt = 0;
        }

        // set or expire resumption token
        if ($cnt > $cursor + $this->limit_row) {
            $args['item_all_count'] = $cnt;      // set optional value(count).
        }
        $resumptionToken_xml = $this->resumptionTokenXml($cnt, $cursor, $args, '', $end_index);
        $xml = "<ListSets>\n".$xml.$resumptionToken_xml."</ListSets>\n";

        return $xml;
    }

    /**
     * divide $identifier into nijc_code, item_id, item_type_id
     *   support format: [nijc code]/[item type id].[item id].
     *
     * @param string $identifier id
     *
     * @return array('nijc_code' => NIJC code, 'item_type_id' => id of itemtype, 'item_id' => id of item)
     * @return false:            failure to divide
     */
    private function parseIdentifier($identifier)
    {
        if (0 == preg_match("/([^\/]+)\/([0-9]+)\.([0-9]+)/", $identifier, $match)) {
            return false;
        }

        return ['nijc_code' => $match[1], 'item_type_id' => $match[2], 'item_id' => $match[3]];
    }

    /**
     * divide $identifier $identifier into nijc_code, doi
     *   support format: [nijc code]:[XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME]/[doi].
     *
     * @param string $identifier id
     *
     * @return array('nijc_code' => NIJC code, 'doi' => doi of item
     * @return false:            failure to divide
     */
    private function parseIdentifierDoi($identifier)
    {
        if (0 == preg_match("/([^\/]+):".XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME.'\\/([^<>]+)/', $identifier, $match)) {
            return false;
        }

        return ['nijc_code' => $match[1], 'doi' => $match[2]];
    }

    /**
     * convert id format from [nijc_code]:[XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME]/[doi] to [nijc_code]/[item type id].[item id].
     *
     * @param string $id id
     *
     * @return string converted id
     * @return '':    nijc_code not equal repository_nijc_code or doi above XOONIPS_CONFIG_DOI_FIELD_PARAM_MAXLEN
     */
    private function convertIdentifierFormat($id)
    {
        $parsed = $this->parseIdentifierDoi($id);
        if ($parsed) {
            if (strlen($parsed['doi']) > XOONIPS_CONFIG_DOI_FIELD_PARAM_MAXLEN) {
                return '';
            }
            // check valid support nijc_code.
            if ($parsed['nijc_code'] != $this->repositoryId) {
                return '';
            }

            $item_info = $this->itemBean->getBydoi($parsed['doi']);
            if ($item_info) {
                return $parsed['nijc_code'].'/'.$item_info['item_type_id'].'.'.$item_info['item_id'];
            } else {
                return '';
            }
        }

        return $id;
    }

    /**
     * check parameters for ListIdentifiers, ListRecords.
     *
     * @param args: hash variable for parameters
     *              array('verb' => 'Ldentify')
     *
     * @return array $error, $metadataPrefix, record of resumptionToken
     */
    private function checkParameters($args)
    {
        $error = '';
        $metadataPrefix = '';
        $result = '';
        if (!isset($args['metadataPrefix']) && !isset($args['resumptionToken'])) {
            $error = $this->error('badArgument', '');
        } elseif (isset($args['identifier'])) {
            $error = $this->error('badArgument', '');
        } elseif (isset($args['metadataPrefix'])) {
            if (isset($args['resumptionToken'])) {
                $error = $this->error('badArgument', '');
            } elseif (isset($args['from']) && isset($args['until'])) {
                $from_type = $this->typeOfISO8601($args['from']);
                $until_type = $this->typeOfISO8601($args['until']);
                if ($from_type != $until_type || 0 == $from_type) {
                    $error = $this->error('badArgument', '');
                }
            } elseif (isset($args['from']) && 0 == $this->typeOfISO8601($args['from'])) {
                $error = $this->error('badArgument', '');
            } elseif (isset($args['until']) && 0 == $this->typeOfISO8601($args['until'])) {
                $error = $this->error('badArgument', '');
            } elseif (empty($this->repositoryId)) {
                $error = $this->error('noRecordsMatch', 'database_id is not configured');
            } elseif (!Xoonips_Metadata::supportMetadataFormat($args['metadataPrefix'])) {
                $error = $this->error('cannotDisseminateFormat', '');
            }
            $metadataPrefix = $args['metadataPrefix'];
        } elseif (isset($args['resumptionToken'])) {
            if (isset($args['metadataPrefix']) || isset($args['set']) || isset($args['from']) || isset($args['until'])) {
                $error = $this->error('badArgument', '');
            } else {
                $result = $this->getResumptionToken($args['resumptionToken']);
                if (0 == count($result) || false == $result['args']) {
                    $this->deleteResumptionToken($resumptionToken);
                    $error = $this->error('badResumptionToken', '');
                } elseif (isset($result['publish_date'])) {
                    //expire resumptionToken if repository is modified after resumptionToken has published
                    $identifiers = $this->itemStatusBean->getOpenItem4Oaipmh((int) $result['publish_date'], 0, null, 0, 1, $this->repositoryDeletionTrack, null);
                    if ($identifiers && count($identifiers) > 0) {
                        $this->deleteResumptionToken($resumptionToken);
                        $error = $this->error('badResumptionToken', 'repository has been modified');
                    }
                }
            }
            $metadataPrefix = $result['metadata_prefix'];
        }

        return [$error, $metadataPrefix, $result];
    }

    /**
     * get ISO8601 type.
     *
     * @param string $str ISO8601 format string
     *
     * @return 0:not ISO8601 1:ISO8601 date 2:ISO8601 datetime
     */
    private function typeOfISO8601($str)
    {
        if (1 == preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $str)) {
            return 1;
        } elseif (1 == preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z$/', $str)) {
            return 2;
        } else {
            return 0;
        }
    }

    /**
     * common process for ListIdentifiers, ListRecords.
     *
     * @param array $args   hash variable for parameters
     *                      array('verb' => 'Ldentify')
     * @param array $result record of resumptionToken
     *
     * @return array $error, $metadataPrefix
     */
    private function listIdentifiersAndRecords($args, $result)
    {
        $error = '';
        $from = 0;
        $until = 0;
        $set = null;
        if (isset($args['resumptionToken'])) {
            if (isset($result['args']['from'])) {
                $from = $this->ISO8601toUTC($result['args']['from']);
            }
            if (isset($result['args']['until'])) {
                $until = $this->ISO8601toUTC($result['args']['until']);
            }
            if (isset($result['args']['set'])) {
                $set = $result['args']['set'];
            }
            if (isset($result['last_item_id'])) {
                $last_item_id = $result['last_item_id'];
            }
        } else {
            if (!is_null($args['from'])) {
                $from = $this->ISO8601toUTC($args['from']);
            }
            if (!is_null($args['until'])) {
                $until = $this->ISO8601toUTC($args['until']);
            }
            if (!is_null($args['set'])) {
                $set = $args['set'];
            }
            if (!is_null($args['setSpec'])) {
                $setSpec = $args['setSpec'];
            }
        }
        $identifiers = $this->itemStatusBean->getOpenItem4Oaipmh((int) $from, (int) $until, $set, 0, 0, $this->repositoryDeletionTrack, $setSpec);
        if (!$identifiers || 0 == count($identifiers)) {
            $error = $this->error('noRecordsMatch', '');
        }

        $cursor = 0;
        if (isset($last_item_id)) {
            foreach ($identifiers as $key => $value) {
                if ($last_item_id == intval($value['item_id'])) {
                    $cursor = $key + 1;
                }
            }
        }

        return [$error, $cursor, $identifiers];
    }

    /**
     * generate <header> of item.
     *
     * @param string $identifier      item_id, item_type_id of item
     * @param array  $index_tree_list list of public indexes
     *
     * @return string <header>
     */
    private function oaipmhHeader($identifier, $index_tree_list)
    {
        global $xoopsDB;
        $lines = [];
        $status = [];

        $status = $this->itemStatusBean->select($identifier['item_id']);
        if ($status) {
            if (1 == $status[0]['is_deleted']) {
                if (time() > ($status[0]['deleted_timestamp'] + 60 * 60 * 24 * $this->repositoryDeletionTrack)) {
                    return '';
                }
                $lines[] = '<header status="deleted">';
            } else {
                $lines[] = '<header>';
            }
            $item = $this->itemBean->getItemBasicInfo(intval($identifier['item_id']));
            if ($item && '' != $item['doi'] && !empty($this->repositoryId)) {
                $id = "$this->repositoryId:".XOONIPS_CONFIG_DOI_FIELD_PARAM_NAME.'/'.$item['doi'];
            } else {
                $id = $this->repositoryId.'/'.intval($identifier['item_type_id']).'.'.intval($identifier['item_id']);
            }
            $lines[] = "<identifier>$id</identifier>";
            $datestamp = max($status[0]['created_timestamp'], $status[0]['modified_timestamp'], $status[0]['deleted_timestamp']);
            $lines[] = '<datestamp>'.gmdate("Y-m-d\TH:i:s\Z", $datestamp).'</datestamp>';
            $sql = 'SELECT link.index_id FROM ('
                .$xoopsDB->prefix($this->dirname.'_index_item_link').' as link LEFT JOIN '
                .$xoopsDB->prefix($this->dirname.'_index').' as idx on link.index_id=idx.index_id) '
                .' LEFT JOIN '.$xoopsDB->prefix('groups').' as grp on idx.groupid=grp.groupid '
                .' WHERE link.item_id='.intval($identifier['item_id'])
                .' AND (link.certify_state='.XOONIPS_CERTIFIED.' OR link.certify_state='.XOONIPS_WITHDRAW_REQUIRED
                .') AND (open_level='.XOONIPS_OL_PUBLIC.' OR (open_level='.XOONIPS_OL_GROUP_ONLY
                .' AND (grp.activate='.Xoonips_Enum::GRP_PUBLIC.' OR grp.activate='.Xoonips_Enum::GRP_CLOSE_REQUIRED.')))';

            $result = $xoopsDB->query($sql);
            if ($result) {
                while (list($xid) = $xoopsDB->fetchRow($result)) {
                    $path = 'index'.implode(':index', explode(',', $index_tree_list[$xid]['id_fullpath']));
                    $path = StringUtils::convertEncoding($path, 'UTF-8', _CHARSET, 'h');
                    $lines[] = '<setSpec>'.$path.'</setSpec>';
                }
            }
            $item_type_name = StringUtils::convertEncoding($identifier['item_type_display'], 'UTF-8', _CHARSET, 'h');
            $lines[] = '<setSpec>'.$item_type_name.'</setSpec>';
            $lines[] = '</header>';

            return implode("\n", $lines);
        }
    }

    /**
     * generate <record> of item.
     *
     * @param string $identifier      item_id, item_type_id of item
     * @param array  $index_tree_list list of public indexes
     * @param string $metadataPrefix  metadataPrefix
     *
     * @return string <record>
     */
    private function record($identifier, $index_tree_list)
    {
        //return only header if item is deleted
        if (1 == $identifier['is_deleted']) {
            return ["<record>\n".$this->oaipmhHeader($identifier, $index_tree_list)
                ."</record>\n", true, ];
        }

        $item = [];
        $item = $this->itemBean->getItemBasicInfo($identifier['item_id']);
        //return error if item_type_id mismatched
        if ($item['item_type_id'] != $identifier['item_type_id']) {
            return [$this->error('idDoesNotExist', 'item_type_id not found'), false];
        }

        return ["<record>\n".$this->oaipmhHeader($identifier, $index_tree_list)
            .$this->metadata->getMetadata($identifier['item_type_id'], $identifier['item_id'])
            ."</record>\n", true, ];
    }

    private function resumptionTokenXml($cnt, $cursor, $args, $metadataPrefix, $last_item_id)
    {
        // set or expire resumption token
        $resumptionToken_xml = '';
        $now = time();
        if ($cnt > $cursor + $this->limit_row) {
            $resumptionToken = uniqid(session_id());
            $expire = gmdate("Y-m-d\TH:i:s\Z", $now + $this->expire_term);
            $this->setResumptionToken($resumptionToken, $metadataPrefix, $args['verb'], $args, $last_item_id, $now);
            $resumptionToken_xml = "<resumptionToken expirationDate=\"$expire\" completeListSize=\"$cnt\" cursor=\"$cursor\">$resumptionToken</resumptionToken>\n";
        } elseif ($args['resumptionToken']) {
            $result = $this->getResumptionToken($args['resumptionToken']);
            if ($result['expire_date'] < $now) {
                $this->deleteResumptionToken($args['resumptionToken']);
            }
            $resumptionToken_xml = "<resumptionToken completeListSize=\"$cnt\" cursor=\"$cursor\"/>\n";
        }

        return $resumptionToken_xml;
    }

    /**
     * delete resumptionToken record before expiretime.
     *
     * @param string $resumptionToken
     */
    private function deleteResumptionToken($resumptionToken)
    {
        $ortHandler = Functions::getXoonipsHandler('OaipmhResumptionTokenObject', $this->dirname);
        $criteria = new Criteria('resumption_token', $resumptionToken);
        $ortHandler->deleteAll($criteria, true);
    }

    /**
     * delete expired resumptionToken.
     */
    private function expireResumptionToken()
    {
        $ortHandler = Functions::getXoonipsHandler('OaipmhResumptionTokenObject', $this->dirname);
        $criteria = new Criteria('expire_date', time(), '<');
        $ortHandler->deleteAll($criteria, true);
    }

    /**
     * register resumptionToken record.
     *
     * @param string $resumption_token
     * @param string $metadata_prefix
     * @param string $verb
     * @param string $args
     * @param string $last_item_id
     * @param int    $publish_date
     *
     * @return bool
     */
    private function setResumptionToken($resumption_token, $metadata_prefix, $verb, $args, $last_item_id, $publish_date)
    {
        $ortHandler = Functions::getXoonipsHandler('OaipmhResumptionTokenObject', $this->dirname);
        $ortObj = $ortHandler->create();
        $ortObj->set('resumption_token', $resumption_token);
        $ortObj->set('metadata_prefix', $metadata_prefix);
        $ortObj->set('verb', $verb);
        $ortObj->set('args', json_encode($args));
        $ortObj->set('last_item_id', $last_item_id);
        $ortObj->set('limit_row', $this->limit_row);
        $ortObj->set('publish_date', $publish_date);
        $ortObj->set('expire_date', $publish_date + $this->expire_term);

        return $ortHandler->insert($ortObj, true);
    }

    /**
     * get resumptionToken record.
     *
     * @param string $resumption_token
     */
    private function getResumptionToken($resumption_token)
    {
        global $xoopsDB;
        $ortHandler = Functions::getXoonipsHandler('OaipmhResumptionTokenObject', $this->dirname);
        $ortObj = $ortHandler->get($resumption_token);
        $ret = $ortObj->getArray();
        $ret['args'] = json_decode($ret['args'], true);

        return $ret;
    }

    private function ISO8601toUTC($str)
    {
        if (0 == preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})(T([0-9]{2}):([0-9]{2}):([0-9]{2})Z)?/', $str, $match)) {
            return 0;
        }
        if (!isset($match[5])) {
            $match[5] = 0;
            $match[6] = 0;
            $match[7] = 0;
        }

        return gmmktime($match[5], $match[6], $match[7], $match[2], $match[3], $match[1]);
    }

    /**
     * return common header.
     *
     * @return common header, <OAI-PMH>, <responseDate>
     */
    private function header()
    {
        return sprintf($this->headerXml."\n", gmdate("Y-m-d\TH:i:s\Z", time()));
    }

    /**
     * generate <request>tag.
     *
     * @param attrs: hash variable for parameters
     *               array('verb' => 'Ldentify')
     *
     * @return <request>tag
     */
    private function request($attrs)
    {
        $request = '<request';
        foreach ($attrs as $key => $value) {
            if (!is_null($value)) {
                $request .= " $key=\"".$value.'"';
            }
        }
        $request .= '>';
        $request .= $this->baseURL;
        $request .= "</request>\n";

        return $request;
    }

    /**
     * return common footer.
     *
     * @return </OAI-PMH>
     */
    private function footer()
    {
        return '</OAI-PMH>';
    }

    /**
     * return error.
     *
     * @param string $errorcode errorcode
     * @param string $text      error
     */
    private function error($errorcode, $text)
    {
        return "<error code='$errorcode'>$text</error>\n";
    }
}
