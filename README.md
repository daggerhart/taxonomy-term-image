# Taxonomy Term Image

An example plugin for adding an image upload field to taxonomy term edit pages in WordPress.

### How to use within a theme or plugin:

**Setup file:**

1. Delete plugin meta data at the top of taxonomy-term-image.php
1. include_once taxonomy-term-image.php

### Hooks

**filter 'taxonomy-term-image-taxonomy'**:

Change the taxonomy targeted by the plugin.

    function my_taxonomy_term_image_taxonomy( $taxonomy ) {
        // use for tags instead of categories
        return 'post_tag';
    }
    add_filter( 'taxonomy-term-image-taxonomy', 'my_taxonomy_term_image_taxonomy' );

**filter 'taxonomy-term-image-labels'**:

Change the field and button text.

    function my_taxonomy_term_image_labels( $labels ) {
        $labels['fieldTitle'] = __( 'My Super Rad Plugin', 'yourdomain' );
        $labels['fieldDescription'] = __( 'This plugin is cool, and does neat stuff.', 'yourdomain' );
        
        return $labels;
    }
    add_filter( 'taxonomy-term-image-labels', 'my_taxonomy_term_image_labels' );


**filter 'taxonomy-term-image-taxonomy'**:

Change where the data is saved in the options table.

    function my_taxonomy_term_image_option_name( $option_name ) {
        // store in wp_options where option_name = 'my_super_rad_plugin'
        return 'my_super_rad_plugin';
    }
    add_filter( 'taxonomy-term-image-option-name', 'my_taxonomy_term_image_option_name' );

**filter 'taxonomy-term-image-js-dir-url'**:

Change where the js file is located. (no trailing slash)

    function my_taxonomy_term_image_js_dir_url( $option_name ) {
        // change the js directory to a subdirectory of this hook
        return plugin_dir_url( __FILE__ ) . '/js';
    }
    add_filter( 'taxonomy-term-image-js-dir-url', 'my_taxonomy_term_image_js_dir_url' );


**show image on archive template**

    $term = get_queried_object();
                    
    if ( $term->term_image ){
        echo wp_get_attachment_image( $term->term_image, 'thumbnail' );
    }


### References:

* action [admin_init](http://codex.wordpress.org/Plugin_API/Action_Reference/admin_init)
* action [admin_enqueue_scripts](http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts)
    * function [wp_register_script](https://developer.wordpress.org/reference/functions/wp_register_script/)
    * function [wp_localize_script](https://developer.wordpress.org/reference/functions/wp_localize_script/)
    * function [wp_enqueue_script](https://developer.wordpress.org/reference/functions/wp_enqueue_script/)
* action [created_term](http://wpseek.com/hook/created_term/)
* action [edited_term](https://developer.wordpress.org/reference/hooks/edited_term/)
* action [delete_term](https://developer.wordpress.org/reference/hooks/delete_term/)
* action [{$taxonomy}_add_form_fields](https://developer.wordpress.org/reference/hooks/taxonomy_add_form_fields/)
* action [{$taxonomy}_edit_form_fields](https://developer.wordpress.org/reference/hooks/taxonomy_edit_form_fields/)
* [Using Media Uploader in plugins](http://mikejolley.com/2012/12/using-the-new-wordpress-3-5-media-uploader-in-plugins/)


