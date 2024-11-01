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
$title 			= $result['title'];
$authors        = "{$result['author_string']}. ";

$journalabbrev  = "<span style='font-style: italic;'>{$result['journalabbrev']}.</span> ";
$year           = empty($result['year']) ? "" : "({$result['year']}) ";
$volume         = empty($result['volume']) ? "" : "{$result['volume']}, ";
$pages          = empty($result['pages']) ? "" : "{$result['pages']}";
$journal_link	= '//doi.org/' . $result['elocationid'];

$journal = "{$year}{$journalabbrev}<strong>{$volume}</strong>{$pages}";

?>

<div class="wbe-entry">

    <div class="wbe-references">

        <span class="wbe-authors <?php echo esc_attr__($template ) . '-authors' ?>"><?php echo $authors ?></span>
        <span class="wbe-title <?php echo esc_attr__($template ) . '-title' ?>"><?php echo $title ?>. </span>
        <span class="wbe-journal <?php echo esc_attr__($template ) . '-journal' ?>"><?php echo $journal ?></span>
		
		<?php
		
		$partials_file = wbe_partials_file( $template, 'links-inline' );
		if ( ! empty( $partials_file ) ) {
			include( $partials_file );
		}
		
		?>
    </div>

</div>

