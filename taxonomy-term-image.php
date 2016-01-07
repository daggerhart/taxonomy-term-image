<?php
/*
Plugin Name: Taxonomy Term Image
Plugin URI: https://github.com/daggerhart/taxonomy-term-image
Description: Example plugin for adding an image upload field to a taxonomy term edit page using WordPress 4.4 taxonomy term meta data
Author: daggerhart, slack
Version: 2.0.1
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Taxonomy_Term_Image' ) ) :

class Taxonomy_Term_Image {

	// object version used for enqueuing scripts
	private $version = '2.0.1';

	// url for the directory where our js is located
	private $js_dir_url;

	// array of slugs for the taxonomies we are targeting
	// api: use filter 'taxonomy-term-image-taxonomy' to override
	private $taxonomies = array( 'category' );

	// defined during __construct() for i18n reasons
	// api: use filter 'taxonomy-term-image-labels' to override
	private $labels = array();

	// @deprecated: option_name for pre-term-meta data storage
	// api: use filter 'taxonomy-term-image-option-name' to override
	private $option_name = '';

	// our term meta key
	// api: use filter 'taxonomy-term-image-meta-key' to override
	private $term_meta_key = 'term_image';

	/**
	 * Simple singleton to enforce once instance
	 *
	 * @return Taxonomy_Term_Image object
	 */
	static function instance() {
		static $object = null;
		if ( is_null( $object ) ) {
			$object = new Taxonomy_Term_Image();
		}
		return $object;
	}

	/**
	 * Init the plugin and hook into WordPress
	 */
	private function __construct() {
		// default labels
		$this->labels = array(
			'fieldTitle'       => __( 'Taxonomy Term Image' ),
			'fieldDescription' => __( 'Select which image should represent this term.' ),
			'imageButton'      => __( 'Select Image' ),
			'removeButton'     => __( 'Remove' ),
			'modalTitle'       => __( 'Select or upload an image for this term' ),
			'modalButton'      => __( 'Attach' ),
		);

		// allow overriding of the html text
		$this->labels = apply_filters( 'taxonomy-term-image-labels', $this->labels );

		// allow overriding of the target taxonomies
		$this->taxonomies = apply_filters( 'taxonomy-term-image-taxonomy', $this->taxonomies );

		if ( ! is_array( $this->taxonomies ) ) {
			$this->taxonomies = array( $this->taxonomies );
		}

		// @deprecated: allow overriding of option_name
		// default option name keyed to the taxonomy
		$this->option_name = $this->taxonomies[0] . '_term_images';
		$this->option_name = apply_filters( 'taxonomy-term-image-option-name', $this->option_name );

		// allow overriding of term_meta
		$this->term_meta_key = apply_filters( 'taxonomy-term-image-meta-key', $this->term_meta_key );

		// get our js location for enqueing scripts
		$this->js_dir_url = apply_filters( 'taxonomy-term-image-js-dir-url', plugin_dir_url( __FILE__ ) . '/js' );

		// check for updates
		$this->upgrade();

		// hook into WordPress
		$this->hook_up();
	}

	// prevent cloning
	private function __clone(){}

	// prevent unserialization
	private function __wakeup(){}

	/**
	 * Check for plugin updates
	 */
	private function upgrade(){
		$previous_version = get_option( 'taxonomy-term-image-version', '0.0.0' );

		// if the previous version is less than the current version,
		// we need to upgrade
		if ( version_compare( $previous_version, $this->version, '<' ) ){

			$old_option = get_option( $this->option_name, array() );

			// if we have data in the old (1.x) option,
			// move it to term meta and delete the old option
			if ( ! empty( $old_option ) ) {
				foreach( $old_option as $term_id => $image_id ) {
					update_term_meta( $term_id, $this->term_meta_key, $image_id );
				}

				delete_option( $this->option_name );
			}

			// modify the stored version data for future checks
			update_option( 'taxonomy-term-image-version', $this->version );
		}
	}

	/**
	 * Initialize the object
	 * - hook into WordPress admin
	 */
	private function hook_up(){
		// term meta data registration
		add_action( 'init', array( $this, 'register_term_meta' ) );

		// we only need to add most hooks on the admin side
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );

			foreach ( $this->taxonomies as $taxonomy ) {
				// add our image field to the taxonomy term forms
				add_action( $taxonomy . '_add_form_fields', array( $this, 'taxonomy_add_form' ) );
				add_action( $taxonomy . '_edit_form_fields', array( $this, 'taxonomy_edit_form' ) );

				// hook into term administration actions
				add_action( 'create_' . $taxonomy, array( $this, 'taxonomy_term_form_save' ) );
				add_action( 'edit_' . $taxonomy, array( $this, 'taxonomy_term_form_save' ) );
			}
		}

		// add our data when term is retrieved
		add_filter( 'get_term', array( $this, 'get_term' ), 10, 2 );
		add_filter( 'get_terms', array( $this, 'get_terms' ) );
		add_filter( 'get_object_terms', array( $this, 'get_terms' ) );
	}

	/**
	 * Register our term meta and sanitize as an integer
	 */
	function register_term_meta() {
		register_meta( 'term', $this->term_meta_key, 'absint' );
	}

	/**
	 * WordPress action "admin_enqueue_scripts"
	 */
	function action_admin_enqueue_scripts(){
		// get the screen object to decide if we want to inject our scripts
		$screen = get_current_screen();

		// check if we are on any edit-{taxonomy} screen
		foreach( $this->taxonomies as $taxonomy ) {
			if ( $screen->id == 'edit-' . $taxonomy ){
				// WP core stuff we need
				wp_enqueue_media();
				wp_enqueue_style( 'thickbox' );
				$dependencies = array( 'jquery', 'thickbox', 'media-upload' );

				// register our custom script
				wp_register_script( 'taxonomy-term-image-js', $this->js_dir_url . '/taxonomy-term-image.js', $dependencies, $this->version, true );

				// Localize the modal window text so that we can translate it
				wp_localize_script( 'taxonomy-term-image-js', 'TaxonomyTermImageText', $this->labels );

				// enqueue the registered and localized script
				wp_enqueue_script( 'taxonomy-term-image-js' );
				break;
			}
		}
	}

	/**
	 * The HTML form for our taxonomy image field
	 *
	 * @param  int    $image_ID  the image ID
	 * @param  array  $image_src
	 * @return string the html output for the image form
	 */
	function taxonomy_term_image_field( $image_ID = null, $image_src = array() ) {
		wp_nonce_field('taxonomy-term-image-form-save', 'taxonomy-term-image-save-form-nonce');
		?>
		<input type="button" class="taxonomy-term-image-attach button" value="<?php echo esc_attr( $this->labels['imageButton'] ); ?>" />
		<input type="button" class="taxonomy-term-image-remove button" value="<?php echo esc_attr( $this->labels['removeButton'] ); ?>" />
		<input type="hidden" id="taxonomy-term-image-id" name="taxonomy_term_image" value="<?php echo esc_attr( $image_ID ); ?>" />
		<p class="description"><?php echo $this->labels['fieldDescription']; ?></p>

		<p id="taxonomy-term-image-container">
			<?php if ( isset( $image_src[0] ) ) : ?>
				<img class="taxonomy-term-image-attach" src="<?php print esc_attr( $image_src[0] ); ?>" />
			<?php endif; ?>
		</p>
		<?php
	}

	/**
	 * Add a new form field for the add taxonomy term form
	 */
	function taxonomy_add_form(){
		?>
		<div class="form-field term-image-wrap">
			<label><?php echo $this->labels['fieldTitle']; ?></label>
			<?php $this->taxonomy_term_image_field(); ?>
		</div>
		<?php
	}

	/**
	 * Add a new form field for the edit taxonomy term form
	 *
	 * @param $term | object | the term object
	 */
	function taxonomy_edit_form( $term ){
		// default values
		$image_ID = '';
		$image_src = array();

		$term_image_id = get_term_meta( $term->term_id, $this->term_meta_key, true );

		// look for existing data for this term
		if ( isset( $term_image_id ) ) {
			$image_ID  = $term_image_id;
			$image_src = wp_get_attachment_image_src( $image_ID, 'thumbnail' );
		}
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label><?php echo $this->labels['fieldTitle']; ?></label></th>
			<td class="taxonomy-term-image-row">
				<?php $this->taxonomy_term_image_field( $image_ID, $image_src ); ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Handle saving our custom taxonomy term meta
	 *
	 * @param $term_id
	 */
	function taxonomy_term_form_save( $term_id ) {

		// our requirements for saving:
		if (
			// nonce was submitted and is verified
			isset( $_POST['taxonomy-term-image-save-form-nonce'] ) &&
			wp_verify_nonce( $_POST['taxonomy-term-image-save-form-nonce'], 'taxonomy-term-image-form-save' ) &&

			// taxonomy data and taxonomy_term_image data was submitted
			isset( $_POST['taxonomy'] ) &&
			isset( $_POST['taxonomy_term_image'] ) &&

			// the taxonomy submitted is one of the taxonomies we are dealing with
			in_array( $_POST['taxonomy'], $this->taxonomies )
		)
		{
			// get the term_meta and assign it the old_image
			$old_image = get_term_meta( $term_id, $this->term_meta_key, true );
			// see if image data was submitted:
			// sanitize the data and save it as the new_image
			$new_image = isset( $_POST['taxonomy_term_image'] ) ? absint( $_POST['taxonomy_term_image'] ) : '';

			if ( $old_image && '' === $new_image ) {
				delete_term_meta( $term_id, $this->term_meta_key );
			}
			// if the new image is not the same as the old update the term_meta
			else if ( $old_image !== $new_image ) {
				// save the term image data
				update_term_meta( $term_id, $this->term_meta_key, $new_image );
			}
		}
	}

	/**
	 * Add the image data to any relevant get_term call
	 *
	 * @param $_term
	 * @param $taxonomy
	 *
	 * @return mixed
	 */
	function get_term( $_term, $taxonomy ) {
		// only modify term when dealing with this taxonomy
		if ( in_array( $taxonomy, $this->taxonomies ) ) {
			// (backwards compatibility) default to null if not found
			$image_id = get_term_meta( $_term->term_id, $this->term_meta_key, true );
			$_term->term_image = !empty( $image_id ) ? $image_id : null;
		}
		return $_term;
	}

	/**
	 * Add term_image data to objects when get_terms() is called
	 *
	 * @param $terms
	 */
	function get_terms( $terms ) {
		foreach( $terms as $i => $term ){
			if ( is_object( $term ) && isset( $term->taxonomy ) ) {
				$terms[ $i ] = $this->get_term( $term, $term->taxonomy );
			}
		}
		return $terms;
	}
}

endif;

add_action( 'init', function(){
	Taxonomy_Term_Image::instance();
});

