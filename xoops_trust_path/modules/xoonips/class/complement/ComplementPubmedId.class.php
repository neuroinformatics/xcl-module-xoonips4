<?php

use Xoonips\Core\StringUtils;

require_once dirname(__FILE__).'/Complement.class.php';
require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/class/webservice/PubmedService.class.php';

/**
 * pubmed id comlement class.
 */
class Xoonips_ComplementPubmedId extends Xoonips_Complement
{
    /**
     * do complement.
     *
     * @param {Trustdirname}_ItemField $field
     * @param string                   $id
     * @param array                    &$data
     *
     * @return bool
     */
    public function complete($field, $id, &$data)
    {
        $ids = explode(Xoonips_Enum::ITEM_ID_SEPARATOR, $id);
        $complementId = $this->mId;
        $itemtypeId = $field->getItemTypeId();

        $pubmedId = $data[$id];
        $pubmedData = $this->getPubmedData($pubmedId);
        if (empty($pubmedData)) {
            return false;
        }

        $manager = new Xoonips_ItemComplementManager($this->mDirname);
        $complementItems = $manager->getItemComplement($complementId, $itemtypeId, $ids[2], $ids[0]);
        if (!$complementItems) {
            return false;
        }

        foreach ($complementItems as $comp) {
            $detailId = $comp['item_field_detail_id'];
            $groupId = $comp['group_id'];
            $param = $comp['code'];
            if (is_array($pubmedData[$param])) {
                $int = 1;
                for ($i = 1; $i <= count($pubmedData[$param]); ++$i) {
                    if ($param == 'keyword') {
                        $keywords = $pubmedData[$param][$i - 1];
                        $key = $groupId.Xoonips_Enum::ITEM_ID_SEPARATOR.$int.Xoonips_Enum::ITEM_ID_SEPARATOR.$detailId;
                        $data[$key] = StringUtils::convertEncoding(trim($keywords), _CHARSET, 'UTF-8', 'h');
                        ++$int;
                    } else {
                        $key = $groupId.Xoonips_Enum::ITEM_ID_SEPARATOR.$i.Xoonips_Enum::ITEM_ID_SEPARATOR.$detailId;
                        $data[$key] = StringUtils::convertEncoding($pubmedData[$param][$i - 1], _CHARSET, 'UTF-8', 'h');
                    }
                }
            } else {
                $key = $groupId.Xoonips_Enum::ITEM_ID_SEPARATOR.'1'.Xoonips_Enum::ITEM_ID_SEPARATOR.$detailId;
                $data[$key] = StringUtils::convertEncoding($pubmedData[$param], _CHARSET, 'UTF-8', 'h');
            }
        }

        return true;
    }

    /**
     * get pubmed data.
     *
     * @param string $pmid
     *
     * @return &array
     */
    private function &getPubmedData($pmid)
    {
        $ret = array();
        $pubmed = new Xoonips_PubmedService();
        $pubmed->setId($pmid);
        if (!$pubmed->fetch() || !$pubmed->parse() || !isset($pubmed->data[$pmid])) {
            return $ret;
        }
        $article = &$pubmed->data[$pmid];
        // pubmed id
        $ret = array(
            'pmid' => $pmid,
            'title' => $article['ArticleTitle'],
            'volume' => $article['Journal']['Volume'],
            'number' => $article['Journal']['Issue'],
            'publicationyear' => $article['Journal']['Year'],
            'journal' => $article['Journal']['Title'],
            'page' => $article['MedlinePgn'],
            'abstract' => implode(' ', $article['AbstractText']),
            'author' => array(),
            'keyword' => array(),
        );
        // title
        if (preg_match('/^\\[(.*)\\]$/', $ret['title'], $matches)) {
            $ret['title'] = $matches[1];
        }
        // publication_year
        if (empty($ret['publicationyear']) && preg_match('/(\\d\\d\\d\\d)\\s.*/', $article['Journal']['MedlineDate'], $matches)) {
            $ret['publicationyear'] = $matches[1];
        }
        // journal
        if (empty($ret['journal']) && $article['MedlineTA'] != '') {
            $journal_esearch = new Xoonips_PubmedService_JournalEsearch();
            $journal_esearch->setTerm($article['MedlineTA']);
            if ($journal_esearch->fetch() && $journal_esearch->parse() && isset($journal_esearch->data['Id'])) {
                $jids = &$journal_esearch->data['Id'];
                $journal_esummary = new Xoonips_PubmedService_JournalEsummary();
                $journal_esummary->setId(implode(',', $jids));
                if ($journal_esummary->fetch() && $journal_esummary->parse()) {
                    foreach ($jids as $jid) {
                        if (isset($journal_esummary->data[$jid])) {
                            $docsum = &$journal_esummary->data[$jid];
                            if ($docsum['MedAbbr'] == $article['MedlineTA']) {
                                $ret['journal'] = $docsum['Title'];
                            }
                        }
                    }
                }
            }
        }
        // abstract
        if (empty($ret['abstract']) && !empty($article['OtherAbstractText'])) {
            $ret['abstract'] = implode(' ', $article['OtherAbstractText']);
        }
        // author
        if (!empty($article['AuthorList'])) {
            foreach ($article['AuthorList'] as $author) {
                $str = $author['LastName'].' ';
                if ($author['Initials'] != '') {
                    $str .= $author['Initials'];
                } elseif ($author['ForeName'] != '') {
                    $str .= $author['ForeName'];
                }
                $ret['author'][] = $str;
            }
        }
        // keyword
        if (!empty($article['MeshHeadingList'])) {
            foreach ($article['MeshHeadingList'] as $meshheading) {
                $str = $meshheading['DescriptorName'];
                if (!empty($meshheading['QualifierName'])) {
                    $str .= '('.implode(',', $meshheading['QualifierName']).')';
                }
                $ret['keyword'][] = $str;
            }
        }

        return $ret;
    }
}
