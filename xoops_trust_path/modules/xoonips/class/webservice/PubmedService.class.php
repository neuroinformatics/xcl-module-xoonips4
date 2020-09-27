<?php

require_once dirname(__DIR__).'/core/WebServiceBase.class.php';

/**
 * The PubMed Article Set data handling class
 * this class will works under following DTDs
 *  http://www.ncbi.nlm.nih.gov/entrez/query/DTD/pubmed_110101.dtd
 *  http://www.ncbi.nlm.nih.gov/entrez/query/DTD/nlmmedlinecitationset_110101.dtd.
 */
class Xoonips_PubmedService extends Xoonips_WebServiceBase
{
    /**
     * pubmed id.
     *
     * @var string
     */
    private $pmid = '';

    /**
     * parsing data.
     *
     * @var array
     */
    private $tmpdata = [];

    public function __construct()
    {
        // call parent constructor
        parent::__construct();
        // set fetcher conditions
        $this->fetch_url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi';
        $this->fetch_arguments = $this->encodeArguments([
            'db' => 'pubmed',
            'id' => '',
            'retmode' => 'xml', ]
        );
        // set parser conditions
        $this->parser_doctype = 'PubmedArticleSet';
    }

    /**
     * set the pubmed id.
     */
    public function setId($pmid)
    {
        $this->fetch_arguments['id'] = $this->encodeUrl($pmid);
    }

    /**
     * override function of start element handler.
     *
     * @param string $attribs xml attribute
     */
    public function parserStartElement($attribs)
    {
        switch ($this->parser_xpath) {
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/PMID':
            $this->pmid = '';
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article':
            $this->data[$this->pmid] = [
                'Journal' => [
                    'Volume' => '',
                    'Issue' => '',
                    'Year' => '',
                    'MedlineDate' => '',
                    'Title' => '',
                ],
                'ArticleTitle' => '',
                'MedlinePgn' => '',
                'AbstractText' => [],
                'OtherAbstractText' => [],
                'AuthorList' => [],
                'MedlineTA' => '',
                'MeshHeadingList' => [],
            ];
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author':
            $this->tmpdata['Author'] = [
                'LastName' => '',
                'ForeName' => '',
                'Initials' => '',
                'Suffix' => '',
            ];
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/MeshHeadingList/MeshHeading':
            $this->tmpdata['MeshHeading'] = [
                'DescriptorName' => '',
                'QualifierName' => [],
            ];
            break;
        }
    }

    /**
     * override function of end element handler.
     */
    public function parserEndElement()
    {
        switch ($this->parser_xpath) {
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author':
            $this->data[$this->pmid]['AuthorList'][] = $this->tmpdata['Author'];
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/MeshHeadingList/MeshHeading':
            $this->data[$this->pmid]['MeshHeadingList'][] = $this->tmpdata['MeshHeading'];
            break;
        }
    }

    /**
     * override function of character data handler.
     *
     * @param string $cdata character data
     */
    public function parserCharacterData($cdata)
    {
        switch ($this->parser_xpath) {
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/PMID':
            // PMID
            $this->pmid .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/Volume':
            // Volume?
            $this->data[$this->pmid]['Journal']['Volume'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/Issue':
            // Issue?
            $this->data[$this->pmid]['Journal']['Issue'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/PubDate/Year':
            // (Year, ((Month, Day?) | Season)?) | MedlineDate)
            $this->data[$this->pmid]['Journal']['Year'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/PubDate/MedlineDate':
            // (Year, ((Month, Day?) | Season)?) | MedlineDate)
            $this->data[$this->pmid]['Journal']['MedlineDate'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/Title':
            // Title?
            $this->data[$this->pmid]['Journal']['Title'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/ArticleTitle':
            // ArticleTitle
            $this->data[$this->pmid]['ArticleTitle'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Pagination/MedlinePgn':
            // (StartPage, EndPage?, MedlinePgn?) | MedlinePgn)
            $this->data[$this->pmid]['MedlinePgn'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Abstract/AbstractText':
            // (AbstractText,CopyrightInformation?)
            $this->data[$this->pmid]['AbstractText'][] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/OtherAbstract/AbstractText':
            // (AbstractText,CopyrightInformation?)
            $this->data[$this->pmid]['OtherAbstractText'][] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author/LastName':
            // LastName, ForeName?, Initials?, Suffix?, NameID*
            $this->tmpdata['Author']['LastName'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author/ForeName':
            // LastName, ForeName?, Initials?, Suffix?, NameID*
            $this->tmpdata['Author']['ForeName'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author/Initials':
            // LastName, ForeName?, Initials?, Suffix?, NameID*
            $this->tmpdata['Author']['Initials'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author/Suffix':
            // LastName, ForeName?, Initials?, Suffix?, NameID*
            $this->tmpdata['Author']['Suffix'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/MedlineJournalInfo/MedlineTA':
            // MedlineTA
            $this->data[$this->pmid]['MedlineTA'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/MeshHeadingList/MeshHeading/DescriptorName':
            // (DescriptorName, QualifierName*)
            $this->tmpdata['MeshHeading']['DescriptorName'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/MeshHeadingList/MeshHeading/QualifierName':
            // (DescriptorName, QualifierName*)
            $this->tmpdata['MeshHeading']['QualifierName'][] .= $cdata;
            break;
        }
    }
}

/**
 * The class for the PubMed eSearch data of the Journal Title Abbreviation
 * this class will works under following DTDs
 *  http://www.ncbi.nlm.nih.gov/entrez/query/DTD/eSearch_020511.dtd.
 */
class Xoonips_PubmedService_JournalEsearch extends Xoonips_WebServiceBase
{
    public function __construct()
    {
        // call parent constructor
        parent::__construct();
        // set fetcher conditions
        $this->fetch_url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi';
        $this->fetch_arguments = $this->encodeArguments([
            'db' => 'journals',
            'term' => '',
            'retmode' => 'xml', ]
        );
        // set parser conditions
        $this->parser_doctype = 'eSearchResult';
        $this->parser_public_id = '-//NLM//DTD eSearchResult, 11 May 2002//EN';
    }

    /**
     * set journal title.
     *
     * @param string $term journal title
     */
    public function setTerm($term)
    {
        $this->fetch_arguments['term'] = $this->encodeUrl('"'.$term.'"');
    }

    /**
     * override function of start element handler.
     *
     * @param string $attribs xml attribute
     */
    public function parserStartElement($attribs)
    {
        switch ($this->parser_xpath) {
        case '/eSearchResult/IdList':
            $this->data['Id'] = [];
            break;
        }
    }

    /**
     * override function of character data handler.
     *
     * @param string $cdata character data
     */
    public function parserCharacterData($cdata)
    {
        switch ($this->parser_xpath) {
        case '/eSearchResult/IdList/Id':
            // Id*
            $this->data['Id'][] .= $cdata;
            break;
        }
    }
}

/**
 * The class for the PubMed eSummary data of the Journal Title Abbreviation
 * this class will works under following DTDs
 *  http://www.ncbi.nlm.nih.gov/entrez/query/DTD/eSummary_041029.dtd.
 */
class Xoonips_PubmedService_JournalEsummary extends Xoonips_WebServiceBase
{
    /**
     * journal id.
     *
     * @var string
     */
    private $jid = '';

    /**
     * attribute name.
     *
     * @var string
     */
    private $attr_name = '';

    public function __construct()
    {
        // call parent constructor
        parent::__construct();
        // set fetcher conditions
        $this->fetch_url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi';
        $this->fetch_arguments = $this->encodeArguments([
            'db' => 'journals',
            'id' => '',
            'retmode' => 'xml', ]
        );
        // set parser conditions
        $this->parser_doctype = 'eSummaryResult';
        $this->parser_public_id = '-//NLM//DTD eSummaryResult, 29 October 2004//EN';
    }

    /**
     * set journal id.
     *
     * @param $id journal id
     */
    public function setId($jid)
    {
        $this->fetch_arguments['id'] = $this->encodeUrl($jid);
    }

    /**
     * override function of start element handler.
     *
     * @param string $attribs xml attribute
     */
    public function parserStartElement($attribs)
    {
        switch ($this->parser_xpath) {
        case '/eSummaryResult/DocSum/Id':
            $this->jid = '';
            break;
        case '/eSummaryResult/DocSum/Item':
            $this->attr_name = $attribs['Name'];
            break;
        }
    }

    /**
     * override function of end element handler.
     */
    public function parserEndElement()
    {
        switch ($this->parser_xpath) {
        case '/eSummaryResult/DocSum/Id':
            $this->data[$this->jid] = [
                'Title' => '',
                'MedAbbr' => '',
            ];
            break;
        }
    }

    /**
     * override function of character data handler.
     *
     * @param string $cdata character data
     */
    public function parserCharacterData($cdata)
    {
        switch ($this->parser_xpath) {
        case '/eSummaryResult/DocSum/Id':
            // Id*
            $this->jid .= $cdata;
            break;
        case '/eSummaryResult/DocSum/Item':
            switch ($this->attr_name) {
            case 'Title':
                $this->data[$this->jid]['Title'] .= $cdata;
                break;
            case 'MedAbbr':
                $this->data[$this->jid]['MedAbbr'] .= $cdata;
                break;
            }
            break;
        }
    }
}
