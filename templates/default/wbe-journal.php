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

$journalabbrev  = "<span style='font-style: italic;'>{$result['journalabbrev']}.</span> ";
$year           = empty($result['year']) ? "" : "({$result['year']}) ";
$volume         = empty($result['volume']) ? "" : "{$result['volume']}, ";
$pages          = empty($result['pages']) ? "" : "{$result['pages']}";
$journal_link	= '//doi.org/' . $result['elocationid'];

$journal = "{$year}{$journalabbrev}<strong>{$volume}</strong>{$pages}";

$tag = esc_attr__($template . '-' . $block);

?>

<div class="wbe-journal <?php echo esc_attr__($tag) ?>">
	<?php echo esc_html__( $journal ) ?>
</div>

