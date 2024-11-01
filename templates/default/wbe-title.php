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

$title 			= $result['title'];
$top_title      = $title;

if( count( $blocks ) == 1 && $lightbox && $block == 'title'){
	$top_title  = '<a href="#" data-featherlight="#abstract-' . $resnum . '" data-featherlight-variant="fixwidth" style="text-decoration: none;">'. $title .'</a>';
}

$tag = $template . '-' . $block;

?>

<div class="wbe-title <?php echo esc_attr__($tag) ?>">
	<?php echo $top_title ?>
</div>
