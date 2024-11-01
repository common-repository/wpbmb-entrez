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

$authors 		= $result['author_string'];

$tag = esc_attr__($template . '-' . $block);

?>

<div class="wbe-authors <?php echo $tag ?>">
	<?php echo $authors ?>
</div>
