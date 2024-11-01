<?php

/**
 * Partial template file for rendering the search output
 *
 * @package WPBMB Entrez
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

$journal_abbr 	= $result['journalabbrev'];

$pdbid = '';
$pdb_link = '';
if ( isset( $result['pdbid'] ) && ! empty($result['pdbid'] ) ){
    $pdbid = $result['pdbid'];
	$pdb_link = "//www.rcsb.org/pdb/explore/explore.do?structureId={$pdbid}";
}

$database		= empty( $pdbid ) ? ucfirst($this->atts['db']) : 'Pubmed';

$dblink			= "//www.ncbi.nlm.nih.gov/{$this->atts['db']}/" . $result['pmid'];
$journal_link	= '//doi.org/' . $result['doi'];
$pmcid          = $result['pmcid'];
$pmcid_link     = '//www.ncbi.nlm.nih.gov/pmc/articles/' . $pmcid;

?>

<span class="wbe-links-inline">
	<?php if ( ! empty( $pdb_link ) ){ ?>
        <span class="wbe-journal wbe-default">
            <a href="<?php echo esc_attr__( $pdb_link ) ?>" class="wbe-links" target='_blank'>RCSB</a>
        </span>
	<?php } ?>
    <span class="wbe-db wbe-default">
        <a href="<?php echo esc_attr__( $dblink ) ?>" class="wbe-links" target='_blank'><?php echo esc_html__($database) ?></a>
    </span>
    <?php if ( ! empty( $journal_link ) ){ ?>
        <span class="wbe-journal wbe-default">
            <a href="<?php echo esc_attr__( $journal_link ) ?>" class="wbe-links" target='_blank'><?php echo esc_html__($journal_abbr) ?></a>
        </span>
    <?php } ?>
    <?php if ( ! empty( $pmcid ) ){ ?>
        <span class="wbe-pmc wbe-default">
         <a href="<?php echo esc_attr__( $pmcid_link) ?>" class="wbe-links" target='_blank'>PMC Article</a>
        </span>
    <?php } ?>
</span>

<?php
