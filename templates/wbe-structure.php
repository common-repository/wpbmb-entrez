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

$resnum         = $this->resnum;
$structitle     = $result['structitle'];
$pdbid          = $result['pdbid'];
$resolution     = empty($result['resolution']) ? 'N/A' : $result['resolution'] . ' Å';

$lcpdbid        = strtolower($pdbid);
$lcsubpdb       = substr( $lcpdbid, 1, 2 );

$img_link       = "//cdn.rcsb.org/images/rutgers/{$lcsubpdb}/{$lcpdbid}/{$lcpdbid}.pdb1-500.jpg";

$blocks = ['title','links']; //dummy variable for the title partial
$lightbox = false;

?>

<div class="wbe-entry">

    <div class="wbe-str-wrap">
        <div class="wbe-str-img-wrap"><img src="<?php echo esc_url($img_link) ?>" class="wbe-str-img"></div>
        <div class="wbe-str-info">
            <div class="wbe-str-title"><?php echo esc_attr($structitle) ?></div>
            <div class="wbe-str-details">
                <span class="wbe-str-subtitle">PDB ID: </span><?php echo esc_html($pdbid) ?>
                <span class="wbe-str-subtitle">Resolution: </span><?php echo esc_html($resolution) ?>
            </div>
            <div class="wbe-str-pheader">Primary Citation:</div>
            <?php
                foreach ( $blocks as $block ){
                    $partials_file = wbe_partials_file( $template, $block );
                    if ( ! empty( $partials_file ) ){
                        include( $partials_file );
                    }
                }
            ?>
        </div>
    </div>
</div>

