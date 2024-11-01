=== WPBMB Entrez ===
Contributors: David Gohara
Tags: Pubmed, NCBI Entrez, Bibliography, RCSB
Tested up to: 5.2.1
Stable tag: 1.2.0
License: MIT License
License URI: https://opensource.org/licenses/MIT

A plugin that interfaces with the NCBI Entrez databases for listing/updating publications.

== Description ==
This plugin allows you to insert references to articles in NCBI Entrez databases (https://www.ncbi.nlm.nih.gov) using a shortcode. It supports any query or search syntax you would use on the main NCBI site. For single sites with simple requirements the options on the settings pages may be enough. For more advanced requirements, such as, multiple unique shortcodes, parameter overrides and custom templates, shortcodes can be defined using the builder or dynamically by just inserting a shortcode in a post or page.

The plugin has a built in caching mechanism with cache life settings (if desired) to reduce page load times. Search terms that are dynamic (for example Author queries) will update automatically on the next page load if not in cache or if the cache for that shortcode is expired.

A default set of templates and stylings are included. Custom styles (either in the settings or via stylesheet) and templates are also supported using the standard Wordpress styles/templates overrides in child-themes. Documentation for the various shortcode options is available. For site developers the plugin also includes documentation on the available filter and action hooks.

= This plugin relies on the 3rd party services of: =

National Center for Biotechnology Information (NCBI) databases at: https://www.ncbi.nlm.nih.gov
    - The plugin utilizes the NCBI Entrez Programming Utilities API (https://www.ncbi.nlm.nih.gov/home/develop/api/)
    - The only data sent to this resource is the search query you are retrieving references for (the same query you would type into their search box)
    - The databases return scientific publication identifiers and records available to the public

RCSB Protein Data Bank (commonly referred to as 'The PDB') databases at: https://rcsb.org
    - The plugin utilizes The RCSB PDB RESTful Web Service Interface: http://www.rcsb.org/pdb/software/rest.do
    - The only data sent are the PDB identifiers (PDB ID) for one or more protein structures related to the NCBI search query
    - The database returns a summary of the structure(s) and also displays the protein structure image associated with a given PDB entry.

No other information is sent or received from sources other than the services listed above.

= Features =

* Supports: Pubmed and Structure NCBI Entrez databases
* Uses the same query terms as the NCBI Entrez databases
* Automatic updating of publication lists (for dynamic searches)
* Static lists of publications or structures
* Aggregation of multiple search terms/shortcodes
* Caching of search results for a specified time
* Custom styling via the settings or with a stylsheet through a child-theme
* Multiple templates: Lightbox, Compact Lightbox, Inline Abstract, Title Only, Reference List, Structure
* Developer hooks and documentation for creating user defined templates and partials

This plugin gratefully acknowledges the use of Featherlight Lightbox (see http://noelboss.github.io/featherlight/)

== Frequently Asked Questions ==

= What databases does the plugin support? =
Currently, support for the Pubmed and Structure databases is available. The plugin can, in theory support any of the queryable databases, but templates and parsers are not provided at the moment.

= I just want to show my publication list on a single page. How can I do that? =
* Install the plugin
* Set the options you want in the General and Display tabs
* Add the shortcode [wpbmb] to your page or post

= Can multiple/different shortcodes be used on the same page? =
Yes.

= I want to aggregate a bunch of different search queries into a single list. Is that possible? =
Yes. The plugin has support for combining multiple shortcodes by either ID or by custom assigned tags to the shortcode (see tags, use_tags and sid on the Shortcode tab in the admin settings.

== Installation ==

1. Upload the zip file using the plugin page (if not installing directly from the Wordpress repository)
2. Activate the plugin
3. Go to WPBMB Entrez Settings on the left hand menu on the admin pages

== Screenshots ==

1. General Settings
2. Display Settings
3. Shortcode Builder
4. Active Shortcodes Table and Reference
5. Developer Reference
6. Lightbox Template
7. Lightbox Template Abstract
8. Keyword Highlight Template (background highlight) 
9. Keyword Highlight Template (bold)
10. RCSB Structure Template


== Changelog ==
= 1.1.0 =
Added new template References w/ Links

= 1.0.0 =
* Initial Release
