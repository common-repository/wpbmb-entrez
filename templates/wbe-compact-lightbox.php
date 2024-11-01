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
$blocks = [ 'title' ];

$lightbox = true;

?>

<div class="wbe-entry">

	<?php

	// Do the regular pass first. For this template we skip 'abstract'
	foreach ( $blocks as $block ) {

		if ( $block == 'abstract' ) {
			continue;
		}

		$partials_file = wbe_partials_file( $template, $block );
		if ( ! empty( $partials_file ) ) {
			include( $partials_file );
		}

	}

	?>
    <div class="lightbox" id="abstract-<?php echo $resnum ?>">
		<?php
        $blocks = [ 'title', 'authors', 'abstract', 'links' ];
		foreach ( $blocks as $block ) {

			$partials_file = wbe_partials_file( $template, $block );
			if ( ! empty( $partials_file ) ) {
				include( $partials_file );
			}

		}

		?>
    </div>


