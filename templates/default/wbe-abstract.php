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

$abstract 		= $result['abstract'];

$tag = esc_attr__($template . '-' . $block);

?>

<div class="wbe-abstract <?php echo $tag ?>">
    <?php echo $abstract ?>
</div>

