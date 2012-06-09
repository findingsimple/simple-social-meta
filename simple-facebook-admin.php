<?php

if ( ! class_exists( 'SIMPLE_FACEBOOK_Admin' ) ) {

/**
 * So that themes and other plugins can customise the text domain, the SIMPLE_AGLS_Admin should
 * not be initialized until after the plugins_loaded and after_setup_theme hooks.
 * However, it also needs to run early on the init hook.
 *
 * @author Jason Conroy <jason@findingsimple.com>
 * @package WP AGLS
 * @since 1.0
 */
function simple_initialize_facebook_admin() {
	SIMPLE_FACEBOOK_Admin::init();
}
add_action( 'init', 'simple_initialize_facebook_admin', -1 );


class SIMPLE_FACEBOOK_Admin {

	public static function init() {  

		/* create custom plugin settings menu */
		add_action( 'admin_menu',  __CLASS__ . '::simple_facebook_create_menu' );

		/* Add the post facebook meta box on the 'add_meta_boxes' hook. */
		add_action( 'add_meta_boxes', __CLASS__ . '::add_simple_facebook_meta_box' );

		/* Save the post facebook meta box data on the 'save_post' hook. */
		add_action( 'save_post', __CLASS__ . '::save_simple_facebook_meta_box', 10, 2 );

	}

	public static function simple_facebook_create_menu() {

		//create new top-level menu
		add_options_page( 'Simple Facebook Settings', 'Simple Facebook', 'administrator', 'simple_facebook', __CLASS__ . '::simple_facebook_settings_page' );

		//call register settings function
		add_action( 'admin_init',  __CLASS__ . '::register_mysettings' );

	}


	public static function register_mysettings() {
	
		$page = 'simple_facebook-settings'; 

		//general settings
		add_settings_section( 
			'simple_facebook-general', 
			'General Settings',
			__CLASS__ . '::simple_facebook_general_callback',
			$page
		);

		add_settings_field(
			'simple_facebook-appid',
			'App ID',
			__CLASS__ . '::simple_facebook_appid_callback',
			$page,
			'simple_facebook-general'
		);

		add_settings_field(
			'simple_facebook-default-image',
			'Default Image',
			__CLASS__ . '::simple_facebook_default_image_callback',
			$page,
			'simple_facebook-general'
		);
		
		//register our settings	
		register_setting( $page, 'simple_facebook-appid' );	
		register_setting( $page, 'simple_facebook-default-image' );

	}

	public static function simple_facebook_settings_page() {
	
		$page = 'simple_facebook-settings'; 
	
	?>
	<div class="wrap">
	
		<div id="icon-options-general" class="icon32"><br /></div><h2>Simple Facebook Settings</h2>
		
		<?php settings_errors(); ?>
	
		<form method="post" action="options.php">
			
			<?php settings_fields( $page ); ?>
			
			<?php do_settings_sections( $page ); ?>
		
			<p class="submit">
				<input type="submit" class="button-primary" value="Save Changes" />
			</p>
		
		</form>
		
	</div>
	
	<?php 
	} 

	public static function simple_facebook_general_callback() {
		
		//do nothing
		
	}
	
	public static function simple_facebook_appid_callback() {
	
		echo '<input name="simple_facebook-appid" type="text" id="simple_facebook-appid" class="regular-text" value="'. esc_attr( get_option('simple_facebook-appid') ) . '"  />';
		
	}
	
	public static function simple_facebook_default_image_callback() {
	
		echo '<input name="simple_facebook-default-image" type="text" id="simple_facebook-default-image" class="regular-text" value="'. esc_attr( get_option('simple_facebook-default-image') ) . '"  />';
		
	}

	/**
	 * Adds the Simple Facebook meta box for all public post types.
	 *
	 * @since 1.2.0
	 */
	public static function add_simple_facebook_meta_box() {

		/* Get all available public post types. */
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		/* Loop through each post type, adding the meta box for each type's post editor screen. */
		foreach ( $post_types as $type )
			add_meta_box( 'simple-facebook-meta-data', sprintf( __( 'Facebook Open Graph Meta', SIMPLE_FACEBOOK::$text_domain ), $type->labels->singular_name ), __CLASS__ . '::simple_facebook_meta_box_display', $type->name, 'normal', 'high' );
	}

	/**
	 * Displays the Simple Facebook meta box.
	 *
	 * @since 1.2.0
	 */
	public static function simple_facebook_meta_box_display( $object, $box ) { ?>
	
		<?php $args = array (
			'echo' => false
		); ?>

		<input type="hidden" name="simple-facebook-meta-box-nonce" value="<?php echo wp_create_nonce( basename( __FILE__ ) ); ?>" />

		<div class="post-settings">

		<p>
			<label for="sfb-title"><?php _e( 'Title:', SIMPLE_FACEBOOK::$text_domain ); ?></label>
			<br />
			<input type="text" name="sfb-title" id="sfb-title" value="<?php echo esc_attr( get_post_meta( $object->ID, 'sfb-title', true ) ); ?>" size="30" tabindex="30" style="width: 99%;" />
			<br />
			<span style="color:#aaa;">Default: <?php $default = SIMPLE_FACEBOOK::sfb_title( $args ); echo $default['content']; ?></span>
		</p>
		
		<p>
			<label for="sfb-site-name"><?php _e( 'Site Name:', SIMPLE_FACEBOOK::$text_domain ); ?></label>
			<br />
			<input type="text" name="sfb-site-name" id="sfb-site-name" value="<?php echo esc_attr( get_post_meta( $object->ID, 'sfb-site-name', true ) ); ?>" size="30" tabindex="30" style="width: 99%;" />
			<br />
			<span style="color:#aaa;">Default: <?php $default = SIMPLE_FACEBOOK::sfb_site_name( $args ); echo $default['content']; ?></span>
		</p>

		<p>
			<label for="sfb-description"><?php _e( 'Description:', SIMPLE_FACEBOOK::$text_domain ); ?></label>
			<br />
			<textarea name="sfb-description" id="sfb-description" cols="60" rows="2" tabindex="30" style="width: 99%;"><?php echo esc_textarea( get_post_meta( $object->ID, 'sfb-description', true ) ); ?></textarea>
			<br />
			<span style="color:#aaa;">Default: <?php $default = SIMPLE_FACEBOOK::sfb_description( $args ); echo $default['content']; ?></span>
		</p>

		<p>
			<label for="sfb-url"><?php _e( 'URL:', SIMPLE_FACEBOOK::$text_domain ); ?></label>
			<br />
			<input type="text" name="sfb-url" id="sfb-url" value="<?php echo esc_attr( get_post_meta( $object->ID, 'sfb-url', true ) ); ?>" size="30" tabindex="30" style="width: 99%;" />
			<br />
			<span style="color:#aaa;">Default: <?php $default = SIMPLE_FACEBOOK::sfb_url( $args ); echo $default['content']; ?></span>
		</p>
		
		<p>
			<label for="sfb-image"><?php _e( 'Image:', SIMPLE_FACEBOOK::$text_domain ); ?></label>
			<br />
			<input type="text" name="sfb-image" id="sfb-image" value="<?php echo esc_attr( get_post_meta( $object->ID, 'sfb-image', true ) ); ?>" size="30" tabindex="30" style="width: 99%;" />
			<br />
			<span style="color:#aaa;">Default: <?php $default = SIMPLE_FACEBOOK::sfb_image( $args ); echo $default['content']; ?></span>
		</p>
		
		<p>
			<label for="sfb-type"><?php _e( 'Type:', SIMPLE_FACEBOOK::$text_domain ); ?></label>
			<br />
			<input type="text" name="sfb-type" id="sfb-type" value="<?php echo esc_attr( get_post_meta( $object->ID, 'sfb-type', true ) ); ?>" size="30" tabindex="30" style="width: 99%;" />
			<br />
			<span style="color:#aaa;">Default: <?php $default = SIMPLE_FACEBOOK::sfb_type( $args ); echo $default['content']; ?></span>
		</p>
		
		<p>
			<label for="sfb-locale"><?php _e( 'Locale:', SIMPLE_FACEBOOK::$text_domain ); ?></label>
			<br />
			<input type="text" name="sfb-locale" id="sfb-locale" value="<?php echo esc_attr( get_post_meta( $object->ID, 'sfb-locale', true ) ); ?>" size="30" tabindex="30" style="width: 99%;" />
			<br />
			<span style="color:#aaa;">Default: <?php $default = SIMPLE_FACEBOOK::sfb_locale( $args ); echo $default['content']; ?></span>
		</p>

		</div><!-- .form-table --><?php
	}

	/**
	 * Saves the post AGLS meta box settings as post metadata.
	 *
	 */
	public static function save_agls_meta_box( $post_id, $post ) {

		/* Verify the nonce before proceeding. */
		if ( !isset( $_POST['simple-facebook-meta-box-nonce'] ) || !wp_verify_nonce( $_POST['simple-facebook-meta-box-nonce'], basename( __FILE__ ) ) )
			return $post_id;

		/* Get the post type object. */
		$post_type = get_post_type_object( $post->post_type );

		/* Check if the current user has permission to edit the post. */
		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
			return $post_id;

		$meta = array(
			'sfb-title' => strip_tags( $_POST['sfb-title'] ),
			'sfb-site-name' => strip_tags( $_POST['sfb-site-name'] ),
			'sfb-description' => strip_tags( $_POST['sfb-description'] ),
			'sfb-url' => strip_tags( $_POST['sfb-url'] ),
			'sfb-image' => strip_tags( $_POST['sfb-image'] ),
			'sfb-type' => strip_tags( $_POST['sfb-type'] ),
			'sfb-locale' => strip_tags( $_POST['sfb-locale'] )
		);

		foreach ( $meta as $meta_key => $new_meta_value ) {

			/* Get the meta value of the custom field key. */
			$meta_value = get_post_meta( $post_id, $meta_key, true );

			/* If a new meta value was added and there was no previous value, add it. */
			if ( $new_meta_value && '' == $meta_value )
				add_post_meta( $post_id, $meta_key, $new_meta_value, true );

			/* If the new meta value does not match the old value, update it. */
			elseif ( $new_meta_value && $new_meta_value != $meta_value )
				update_post_meta( $post_id, $meta_key, $new_meta_value );

			/* If there is no new meta value but an old value exists, delete it. */
			elseif ( '' == $new_meta_value && $meta_value )
				delete_post_meta( $post_id, $meta_key, $meta_value );
		}
	}

}

}


