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

//The 'title' partial needs these two values defined. So we just set them
//to keep it from throwing an error.
$blocks = [ 'title' ];
$block = $blocks[0];

$lightbox = false;

?>

<div class="wbe-entry">

	<?php

	$partials_file = wbe_partials_file( $template, $block );
	if ( ! empty( $partials_file ) ) {
		include( $partials_file );
	}

	?>
</div>


