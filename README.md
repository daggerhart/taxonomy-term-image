# Taxonomy Term Image

An example plugin for adding an image upload field to taxonomy term edit pages, and an example of the new taxonomy term meta data added in WordPress 4.4. This example IS NOT compatible with any version of WordPress lower than 4.4, it is meant to be used as an example only.

### How to use within a theme or plugin:

**Setup file:**

1. Delete plugin meta data at the top of taxonomy-term-image.php
1. include_once taxonomy-term-image.php

### Hooks

**filter `taxonomy-term-image-taxonomy`**:

Change the taxonomy targeted by the plugin. By default, the `category` taxonomy is the only taxonomy targeted. You can change this to tags if you'd like following the example below:

```php
	function the_term_image_taxonomy( $taxonomy ) {
		// use for tags instead of categories
		return 'post_tag';
	}
	add_filter( 'taxonomy-term-image-taxonomy', 'the_term_image_taxonomy' );
```

Alternatively, the plugin can target more than one taxonomy by providing it an array of taxonomy slugs:

```php
	function the_term_image_taxonomy( $taxonomy ) {
		// use for tags and categories
		return array( 'post_tag', 'category' );
	}
	add_filter( 'taxonomy-term-image-taxonomy', 'the_term_image_taxonomy' );
```

**filter `taxonomy-term-image-labels`**:

Change the field and button text.

```php
	function the_taxonomy_term_image_labels( $labels ) {
		$labels['fieldTitle'] = __( 'My Super Rad Plugin', 'yourdomain' );
		$labels['fieldDescription'] = __( 'This plugin is cool, and does neat stuff.', 'yourdomain' );

		return $labels;
	}
	add_filter( 'taxonomy-term-image-labels', 'the_taxonomy_term_image_labels' );
```

**filter `taxonomy-term-image-meta-key`**:

Change the meta key used to save the image ID in the term meta data

```php
	function the_taxonomy_term_image_meta_key( $option_name ) {
		// store in term meta where term meta key is = 'my_term_meta_key'
		return 'my_term_meta_key';
	}
	add_filter( 'taxonomy-term-image-meta-key', 'the_taxonomy_term_image_meta_key' );
```

**filter `taxonomy-term-image-js-dir-url`**:

Change where the js file is located. (no trailing slash)

```php
	function my_taxonomy_term_image_js_dir_url( $option_name ) {
		// change the js directory to a subdirectory of this hook
		return plugin_dir_url( __FILE__ ) . '/js';
	}
	add_filter( 'taxonomy-term-image-js-dir-url', 'my_taxonomy_term_image_js_dir_url' );
```

**show image on archive template**

Term Image IDs are automatically attached to terms that are passed through the `get_term` and `get_terms` filters as the `->term_image` property.

```php
	$term = get_term( 123, 'category' );

	if ( $term->term_image ) {
		echo wp_get_attachment_image( $term->term_image, 'full' );
	}
```

In order to retrieve the term image on an archive page:

```php
	$term = get_queried_object();

	if ( $term->term_image ) {
	    echo wp_get_attachment_image( $term->term_image, 'full' );
    }
```

### References:

**Articles:**

* [Using Media Uploader in plugins](http://mikejolley.com/2012/12/using-the-new-wordpress-3-5-media-uploader-in-plugins/)
* [Introduction to WordPress term meta](http://themehybrid.com/weblog/introduction-to-wordpress-term-meta)

**Code References:**

* Plugin Hooks
    * action [admin_init](http://codex.wordpress.org/Plugin_API/Action_Reference/admin_init)
    * action [admin_enqueue_scripts](http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts)
	* function [wp_register_script](https://developer.wordpress.org/reference/functions/wp_register_script/)
	* function [wp_localize_script](https://developer.wordpress.org/reference/functions/wp_localize_script/)
	* function [wp_enqueue_script](https://developer.wordpress.org/reference/functions/wp_enqueue_script/)
* Term Meta Data
    * function [register_meta](https://developer.wordpress.org/reference/functions/register_meta/)
    * function [get_term_meta](https://make.wordpress.org/core/2015/10/23/4-4-taxonomy-roundup/)
    * function [update_term_meta](https://make.wordpress.org/core/2015/10/23/4-4-taxonomy-roundup/)
    * function [delete_term_meta](https://make.wordpress.org/core/2015/10/23/4-4-taxonomy-roundup/)
* Taxonomy Hooks
    * action [create_{$taxonomy}](https://developer.wordpress.org/reference/hooks/create_taxonomy/)
    * action [edit_{$taxonomy}](https://developer.wordpress.org/reference/hooks/edit_taxonomy/)
    * action [{$taxonomy}_add_form_fields](https://developer.wordpress.org/reference/hooks/taxonomy_add_form_fields/)
    * action [{$taxonomy}_edit_form_fields](https://developer.wordpress.org/reference/hooks/taxonomy_edit_form_fields/)
    * filter [get_term](https://developer.wordpress.org/reference/hooks/get_term/)
    * filter [get_terms](https://developer.wordpress.org/reference/hooks/get_terms/)
    * filter [get_object_terms](https://developer.wordpress.org/reference/hooks/get_object_terms/)

