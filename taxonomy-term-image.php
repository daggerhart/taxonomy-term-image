<?php
/*
Plugin Name: Taxonomy Term Image
Plugin URI: http://daggerhart.com
Description: Example plugin for adding an image upload field to a taxonomy term edit page
Author: daggerhart
Version: 1.0
Author URI: http://daggerhart.com
*/

class Taxonomy_Term_Image {
  
  // the taxonomy we are targeting
  private $taxonomy = 'category';
  
  // location of our plugin as a url
  private $plugin_url;
  
  // where we will store our term_data
  private $option_name = 'custom_taxonomy_term_images';
  
  // array of key value pairs:  term_id => image_id
  private $term_images = array();

  /**
   * Init the plugin and hook into WordPress
   */
  function __construct() {
    // get our plugin location for enqueing scripts and styles
    $this->plugin_url = plugin_dir_url( __FILE__ );
    
    // load our data
    $this->term_images = get_option( $this->option_name, $this->term_images );
    
    // enqueue our scripts carefully
    add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
    
    // add a new row to category_edit_form_fields
    add_action( $this->taxonomy.'_edit_form_fields', array( $this, 'taxonomy_term_form'), 10, 2);
  }

  /**
   * WordPress action "admin_init"
   */
  function action_admin_init(){
    add_action( 'edited_term', array( $this, 'taxonomy_term_form_save' ) );
  }

  /**
   * WordPress action "admin_enqueue_scripts"
   */
  function action_admin_enqueue_scripts(){
    $screen = get_current_screen();
    // edit-category
    if ( $screen->id == 'edit-' . $this->taxonomy ){
      // WP core thickmox & media scripts
      wp_enqueue_script( 'thickbox' );
      wp_enqueue_style( 'thickbox' );
      wp_enqueue_script( 'media-upload' );
      wp_enqueue_media();
      
      // our custom script
      wp_enqueue_script( 'taxonomy-term-image-js', $this->plugin_url . '/js/taxonomy-term-image.js', array('jquery'), JBMLC_VERSION, true );
    }
  }

  /**
   * Add a new row to the taxonomy term form for our chosen taxonomy
   * 
   * @param $tag
   * @param $taxonomy
   */
  function taxonomy_term_form( $tag, $taxonomy ){
    // default values
    $image_ID = '';
    $image_src = array();

    // look for existing data for this term
    if ( isset( $this->term_images[ $tag->term_id ] ) ){
      $image_ID  = $this->term_images[ $tag->term_id ];
      $image_src =  wp_get_attachment_image_src( $image_ID, 'thumbnail' );
    }
    ?>
      <tr class="form-field">
        <th scope="row" valign="top"><label><?php _e('Taxonomy Term Image'); ?></label></th>
        <td class="taxonomy-term-image-row">
          <input type="button" class="taxonomy-term-image-attach button" value="<?php _e("Select Image"); ?>" />
          <input type="hidden" id="taxonomy-term-image-id" name="taxonomy-term-image[<?php print $tag->term_id; ?>]" value="<?php print $image_ID; ?>" />
          <p class="description"><?php _e("Select which image should represent this category."); ?></p>
  
          <p id="taxonomy-term-image-container">
            <?php if ( isset( $image_src[0] ) ): ?>
              <img class="taxonomy-term-image-attach" src="<?php print $image_src[0]; ?>" />
            <?php endif; ?>
          </p>
        </td>
      </tr>
    <?php
  }

  /**
   * Handle saving our custom taxonomy data
   * 
   * @param $term_id
   */
  function taxonomy_term_save( $term_id ){
    if ( $_POST['taxonomy'] == 'category' && isset( $_POST['taxonomy-term-image'] ) ) {

      if ( isset( $_POST['taxonomy-term-image'][ $term_id ] ) ) {
        // set the image in the term_data array, and save the settings
        $this->term_images[ $term_id ] = absint( $_POST['taxonomy-term-image'][ $term_id ] );
        update_option( $this->option_name, $this->term_images );
      }
    }
  }
}

new Taxonomy_Term_Image();