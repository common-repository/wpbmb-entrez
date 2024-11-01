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

$tag = esc_attr__($template . '-' . $block);

?>

<div class="wbe-links <?php echo $tag ?>">
    <?php if ( $lightbox == true ){ ?>
    <div class="wbe-abstract wbe-default">
        <a href="#" class="wbe-links" data-featherlight="#abstract-<?php echo $resnum ?>" data-featherlight-variant="fixwidth">Abstract</a>
    </div>
    <?php }?>
    <div class="wbe-viewmore wbe-default">View more: </div>
	<?php if ( ! empty( $pdb_link ) ){ ?>
        <div class="wbe-journal wbe-default">
            <a href="<?php echo esc_attr__( $pdb_link ) ?>" class="wbe-links" target='_blank'>RCSB</a>
        </div>
	<?php } ?>
    <div class="wbe-db wbe-default">
        <a href="<?php echo esc_attr__( $dblink ) ?>" class="wbe-links" target='_blank'><?php echo esc_html__($database) ?></a>
    </div>
    <?php if ( ! empty( $journal_link ) ){ ?>
        <div class="wbe-journal wbe-default">
            <a href="<?php echo esc_attr__( $journal_link ) ?>" class="wbe-links" target='_blank'><?php echo esc_html__($journal_abbr) ?></a>
        </div>
    <?php } ?>
    <?php if ( ! empty( $pmcid ) ){ ?>
        <div class="wbe-pmc wbe-default">
         <a href="<?php echo esc_attr__( $pmcid_link) ?>" class="wbe-links" target='_blank'>PMC Article</a>
        </div>
    <?php } ?>
</div>

<?php
