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

$resnum = $this->resnum;
$blocks = ['title','authors','abstract','links'];

$lightbox = false;

?>

<div class="wbe-entry">

    <?php

    // Do the regular pass first. For this template we skip 'abstract'
    foreach ( $blocks as $block ){

	    $partials_file = wbe_partials_file( $template, $block );
	    if ( ! empty( $partials_file ) ){
		    include( $partials_file );
	    }

    }

    if ( count($this->results) != ($resnum + 1) ){
    ?>
    <div class="wbe-spacer-dash"></div>
    <?php } ?>
</div>

