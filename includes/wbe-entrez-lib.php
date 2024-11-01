<?php

/**
 * Shortcode and class for access Entrez (pubmed default)
 *
 * @package WPBMB Entrez
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WBE_Lib
 */
class WBE_Lib {

	/**
	 * Parameters used by class
	 *
	 * @since 1.0.0
	 */
	private $count = 0;        // Count of the number of items returned

	public $term   = '';
	public $db     = 'pubmed';
	public $retmax = 10;    // Return no more than $retmax results

	private $retmode  = 'xml';
	private $retstart = 0;

	private $esearch  = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?';
	private $efetch   = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?';
	private $esummary = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?';
	private $elink    = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi?';
	public  $einfo    = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/einfo.fcgi';

	private $rcsb = 'https://www.rcsb.org/pdb/rest/customReport.xml?';

	/**
	 * Holds an instance of the object
	 *
	 * @var WBE_Lib
	 */
	protected static $instance = null;

	/**
	 * Returns the running object
	 *
	 * @return WBE_Lib
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Main query function for retreiving Entrez results (default pubmed)
	 *
	 * @since 1.0.0
	 */
	public function query() {
		$xmlout      = $this->entrez_esearch();
		$this->count = (int) $xmlout->Count;

		// esearch returns a list of IDs so we have to concatenate the list and do an efetch
		$results = array();
		if ( isset( $xmlout->IdList->Id ) && ! empty( $xmlout->IdList->Id ) ) {
			$ids = array();
			foreach ( $xmlout->IdList->children() as $id ) {
				$ids[] = (string) $id;
			}

			$ids     = implode( ',', $ids );
			$results = $this->query_by_id( $ids );
		}

		return $results;
	}

	/**
	 * Secondary query function to retrieve the actual article data
	 *
	 * @since 1.0.0
	 */
	public function query_by_id( $pmid ) {
		$xmlout = '';

		if ( $this->db == 'structure' ) {
			$xmlout = $this->entrez_esummary( $pmid );
		} else {
			$xmlout = $this->entrez_efetch( $pmid );
		}

		return $this->parse_xml( $xmlout );
	}

	/**
	 * Perform the esearch in Entrez
	 *
	 * @since 1.0.0
	 */
	public function entrez_esearch() {
		// Setup the URL for esearch
		$query_array = array();
		$args        = array(
			'db'       => $this->db,
			'retmode'  => $this->retmode,
			'retmax'   => $this->retmax,
			'retstart' => $this->retstart,
			'term'     => urlencode( $this->term )
		);

		foreach ( $args as $key => $value ) {
			$query_array[] = $key . '=' . $value;
		}

		$webquery = implode( '&', $query_array );
		$url      = $this->esearch . $webquery;
		$xmlout   = $this->simplexml_load_file( $url );

		return $xmlout;
	}

	/**
	 * Perform the efetch in Entrez
	 *
	 * @since 1.0.0
	 */
	public function entrez_efetch( $id, $db = null ) {
		// Setup the URL for efetch
		$db   = empty( $db ) ? $this->db : $db;
		$args = array(
			'db'      => $db,
			'retmode' => $this->retmode,
			'retmax'  => $this->retmax,
			'id'      => (string) $id
		);

		$query_array = array();
		foreach ( $args as $key => $value ) {
			$query_array[] = $key . '=' . $value;
		}

		$httpquery = implode( '&', $query_array );
		$url       = $this->efetch . $httpquery;
		$xmlout    = $this->simplexml_load_file( $url );

		return $xmlout;
	}

	/**
	 * Perform an esummary in Entrez
	 *
	 * @since 1.0.0
	 */
	public function entrez_esummary( $id ) {
		// Setup the URL for efetch
		$args = array(
			'db'      => $this->db,
			'retmode' => $this->retmode,
			'retmax'  => $this->retmax,
			'id'      => (string) $id
		);

		$query_array = array();
		foreach ( $args as $key => $value ) {
			$query_array[] = $key . '=' . $value;
		}

		$httpquery = implode( '&', $query_array );
		$url       = $this->esummary . $httpquery;
		$xmlout    = $this->simplexml_load_file( $url );

		return $xmlout;
	}

	/**
	 * Perform an elink in Entrez
	 *
	 * @since 1.0.0
	 */
	public function entrez_elink( $id, $from, $to ) {
		// Setup the URL for efetch
		$args = array(
			'dbfrom'  => $from,
			'db'      => $to,
			'retmode' => $this->retmode,
			'retmax'  => $this->retmax,
			'id'      => (string) $id
		);

		$query_array = array();
		foreach ( $args as $key => $value ) {
			$query_array[] = $key . '=' . $value;
		}

		$httpquery = implode( '&', $query_array );
		$url       = $this->elink . $httpquery;
		$xmlout    = $this->simplexml_load_file( $url );

		return $xmlout;
	}

	/**
	 * rcsb_data
	 *
	 * @param $id
	 *
	 * @return SimpleXMLElement|string|XMLReader
	 *
	 * @since 1.0.0
	 */
	public function rcsb_data( $id ) {

		// Setup the URL for efetch
		$args = array(
			'service'             => 'wfsile',
			'pdbids'              => (string) $id,
			'primaryOnly'         => '1',
			'customReportColumns' => 'structureId,structureTitle,releaseDate,resolution,abstractTextShort,journalName,doi,pmc,title,pubmedId,firstPage,lastPage,volumeId',
		);

		$query_array = array();
		foreach ( $args as $key => $value ) {
			$query_array[] = $key . '=' . $value;
		}

		$httpquery = implode( '&', $query_array );
		$url       = $this->rcsb . $httpquery;
		$xmlout    = $this->simplexml_load_file( $url );

		return $xmlout;

	}

	/**
	 * Read the XML information and return as an array
	 *
	 * @since 1.0.0
	 */
	public function parse_xml( $xmlin, $db = null ) {
		$db = empty( $db ) ? $this->db : $db;

		$dbprocessor = "parse_{$db}_xml";

		$data = "Notice: the database selected ({$this->db}) is currently unsupported.";
		if ( method_exists( $this, $dbprocessor ) ) {
			$data = $this->$dbprocessor( $xmlin );
		}

		return $data;
	}

	/**
	 * Use simplexml to get construct a request and perform it
	 *
	 * @since 1.0.0
	 */
	public function simplexml_load_file( $url ) {
		$xml_string = '';
		ini_set( 'user_agent', $_SERVER['HTTP_USER_AGENT'] );
		$xml_string = self::load_xml_from_url( $url );

		if ( empty( $xml_string ) && strpos( $url, 'jstor' ) !== false ) {
			$xml_string = new XMLReader();
			$xml_string->open( $url );
		}

		return $xml_string;
	}

	/**
	 * data_from_url
	 *
	 * @param $url
	 *
	 * @return mixed
	 *
	 * @since 1.0.0
	 */
	public function data_from_url( $url ) {

		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_REFERER, '' );
		$str = curl_exec( $curl );
		curl_close( $curl );

		return $str;
	}

	/**
	 * load_xml_from_url
	 *
	 * @param $url
	 *
	 * @return SimpleXMLElement
	 *
	 * @since 1.0.0
	 */
	public function load_xml_from_url( $url ) {
		return simplexml_load_string( $this->data_from_url( $url ) );
	}

	/**
	 * parse_pubmed_xml
	 *
	 * @param $xmlin
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	protected function parse_pubmed_xml( $xmlin ) {

		$data = array();
		foreach ( $xmlin->PubmedArticle as $article ) {

			// Construct an authors array with last name and initials (format the output elsewhere)
			$authors = array();
			if ( isset( $article->MedlineCitation->Article->AuthorList->Author ) ) {

				foreach ( $article->MedlineCitation->Article->AuthorList->Author as $key => $value ) {
					$authors[] = (string) $value->LastName . ' ' . (string) $value->Initials;
				}

			}

			$pmcid = '';
			$doi   = '';
			foreach ( $article->PubmedData->ArticleIdList->ArticleId as $key => $value ) {

				$type = $value['IdType'];
				if ( $type == 'pmc' ) {
					$pmcid = (string) $value;
				} elseif ( $type == 'doi' ) {
					$doi = (string) $value;
				}

			}

			$data[] = array(
				'title'         => (string) $article->MedlineCitation->Article->ArticleTitle,
				'abstract'      => (string) $article->MedlineCitation->Article->Abstract->AbstractText,
				'elocationid'   => (string) $article->MedlineCitation->Article->ELocationID,
				'pmid'          => (string) $article->MedlineCitation->PMID,
				'volume'        => (string) $article->MedlineCitation->Article->Journal->JournalIssue->Volume,
				'issue'         => (string) $article->MedlineCitation->Article->Journal->JournalIssue->Issue,
				'year'          => (string) $article->MedlineCitation->Article->Journal->JournalIssue->PubDate->Year,
				'month'         => (string) $article->MedlineCitation->Article->Journal->JournalIssue->PubDate->Month,
				'pages'         => (string) $article->MedlineCitation->Article->Pagination->MedlinePgn,
				'issn'          => (string) $article->MedlineCitation->Article->Journal->ISSN,
				'journal'       => (string) $article->MedlineCitation->Article->Journal->Title,
				'journalabbrev' => (string) $article->MedlineCitation->Article->Journal->ISOAbbreviation,
				'pmcid'         => $pmcid,
				'doi'           => $doi,
				'authors'       => $authors
			);

		}

		return $data;
	}

	/**
	 * parse_structure_xml
	 *
	 * @param $xmlin
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	protected function parse_structure_xml( $xmlin ) {

		$count  = 0;
		$pdbids = array();
		foreach ( $xmlin->DocSum as $docsum ) {

			$pdbids[] = (string) $docsum->Item[0];

		}
		$pdbids = implode( ',', $pdbids );

		$data   = array();
		$xmlout = $this->rcsb_data( $pdbids );

		foreach ( $xmlout->record as $record ) {
			$data[] = array(
				'title'         => (string) $record->{'dimStructure.title'},
				'abstract'      => (string) $record->{'dimStructure.abstractTextShort'},
				'elocationid'   => (string) $record->{'dimStructure.doi'},
				'pmid'          => (string) $record->{'dimStructure.pubmedId'},
				'volume'        => (string) $record->{'dimStructure.volumeId'},
				'issue'         => '',
				'year'          => (string) $record->{'dimStructure.releaseDate'},
				'month'         => '',
				'pages'         => (string) $record->{'dimStructure.firstPage'} . '-' . (string) $record->{'dimStructure.lastPage'},
				'issn'          => '',
				'journal'       => (string) $record->{'dimStructure.journalName'},
				'journalabbrev' => (string) $record->{'dimStructure.journalName'},
				'pmcid'         => (string) $record->{'dimStructure.pmc'},
				'doi'           => (string) $record->{'dimStructure.doi'},
				'authors'       => array(),
				'resolution'    => (string) $record->{'dimStructure.resolution'},
				'pdbid'         => (string) $record->{'dimStructure.structureId'},
				'structitle'    => (string) $record->{'dimStructure.structureTitle'}
			);
		}

		return $data;
	}


} // end class

/**
 * Helper function to get/return the WBE_Lib object
 * @since  0.1.0
 * @return WBE_Lib object
 */
function wbe_lib() {
	return WBE_Lib::get_instance();
}

/**
 * Helper function to get a list of databases from Entrez
 *
 * @return array
 *
 * @since 1.0.0
 */
function wbe_lib_get_dbs() {

	$wbe_lib = new WBE_Lib();
	$xmlout  = $wbe_lib->load_xml_from_url( $wbe_lib->einfo );

	$dbs = array();
	foreach ( $xmlout->DbList->DbName as $key => $value ) {
		$dbs[] = (string) $value;
	}

	return $dbs;
}

// Get it started
wbe_lib();

