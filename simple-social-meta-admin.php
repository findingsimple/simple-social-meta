<?php

if ( ! class_exists( 'SIMPLE_SOCIAL_META_Admin' ) ) {

/**
 * So that themes and other plugins can customise the text domain, the SIMPLE_SOCIAL_META_Admin should
 * not be initialized until after the plugins_loaded and after_setup_theme hooks.
 * However, it also needs to run early on the init hook.
 *
 * @author Jason Conroy <jason@findingsimple.com>
 * @package SIMPLE_SOCIAL_META
 * @since 1.0
 */
function simple_initialize_social_meta_admin() {
	SIMPLE_SOCIAL_META_Admin::init();
}
add_action( 'init', 'simple_initialize_social_meta_admin', -1 );


class SIMPLE_SOCIAL_META_Admin {

	public static function init() {  

		/* create custom plugin settings menu */
		add_action( 'admin_menu',  __CLASS__ . '::simple_social_meta_create_menu' );

		/* Add the post meta box on the 'add_meta_boxes' hook. */
		add_action( 'add_meta_boxes', __CLASS__ . '::add_simple_social_meta_meta_box' );

		/* Save the post meta box data on the 'save_post' hook. */
		add_action( 'save_post', __CLASS__ . '::save_simple_social_meta_meta_box', 10, 2 );

	}

	public static function simple_social_meta_create_menu() {

		//create new top-level menu
		add_options_page( 'Simple Social Meta Settings', 'Simple Social Meta', 'administrator', 'simple_social_meta', __CLASS__ . '::simple_social_meta_settings_page' );

		//call register settings function
		add_action( 'admin_init',  __CLASS__ . '::register_mysettings' );

	}


	public static function register_mysettings() {
	
		$page = 'simple_social_meta-settings'; 

		//general settings
		add_settings_section( 
			'simple_social_meta-general', 
			'General Settings',
			__CLASS__ . '::simple_social_meta_general_callback',
			$page
		);

		add_settings_field(
			'simple_social_meta-default-image',
			'Default Image',
			__CLASS__ . '::simple_social_meta_default_image_callback',
			$page,
			'simple_social_meta-general'
		);
		
		//facebook settings
		add_settings_section( 
			'simple_social_meta-facebook', 
			'Facebook Settings',
			__CLASS__ . '::simple_social_meta_facebook_callback',
			$page
		);

		add_settings_field(
			'simple_social_meta-fb-toggle',
			'Toggle Facebook Opengraph Meta',
			__CLASS__ . '::simple_social_meta_fb_toggle_callback',
			$page,
			'simple_social_meta-facebook'
		);

		add_settings_field(
			'simple_social_meta-fb-appid',
			'Facebook App ID',
			__CLASS__ . '::simple_social_meta_fb_appid_callback',
			$page,
			'simple_social_meta-facebook'
		);
		
		//twitter settings
		add_settings_section( 
			'simple_social_meta-twitter', 
			'Twitter Settings',
			__CLASS__ . '::simple_social_meta_twitter_callback',
			$page
		);

		add_settings_field(
			'simple_social_meta-tw-toggle',
			'Toggle Twitter Card Meta',
			__CLASS__ . '::simple_social_meta_tw_toggle_callback',
			$page,
			'simple_social_meta-twitter'
		);

		add_settings_field(
			'simple_social_meta-tw-site-username',
			'Twitter Username/Account for the Site',
			__CLASS__ . '::simple_social_meta_tw_site_username_callback',
			$page,
			'simple_social_meta-twitter'
		);
		
		//google+ settings
		add_settings_section( 
			'simple_social_meta-google', 
			'Google+ Settings',
			__CLASS__ . '::simple_social_meta_google_callback',
			$page
		);

		add_settings_field(
			'simple_social_meta-gp-toggle',
			'Toggle Google+ Author Meta',
			__CLASS__ . '::simple_social_meta_gp_toggle_callback',
			$page,
			'simple_social_meta-google'
		);

		add_settings_field(
			'simple_social_meta-gp-publisher',
			'Google+ Publisher Profile URL',
			__CLASS__ . '::simple_social_meta_gp_publisher_callback',
			$page,
			'simple_social_meta-google'
		);
		
		//register our settings	
		register_setting( $page, 'simple_social_meta-default-image' );
		
		register_setting( $page, 'simple_social_meta-fb-toggle' );
		register_setting( $page, 'simple_social_meta-fb-appid' );
		
		register_setting( $page, 'simple_social_meta-tw-toggle' );
		register_setting( $page, 'simple_social_meta-tw-site-username' );
		
		register_setting( $page, 'simple_social_meta-gp-toggle' );
		register_setting( $page, 'simple_social_meta-gp-publisher' );		

	}

	public static function simple_social_meta_settings_page() {
	
		$page = 'simple_social_meta-settings'; 
	
	?>
	<div class="wrap">
	
		<div id="icon-options-general" class="icon32"><br /></div><h2>Simple Social Meta Settings</h2>
		
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

	public static function simple_social_meta_general_callback() {
		
		//do nothing
		
	}
	
	public static function simple_social_meta_default_image_callback() {
	
		echo '<input name="simple_social_meta-default-image" type="text" id="simple_social_meta-default-image" class="regular-text" value="'. esc_attr( get_option('simple_social_meta-default-image') ) . '"  />';
		
	}
	
	public static function simple_social_meta_facebook_callback() {
		
		//do nothing
		
	}

	public static function simple_social_meta_fb_toggle_callback() {
	
		echo '<input name="simple_social_meta-fb-toggle" id="simple_social_meta-fb-toggle" type="checkbox" value="1" class="code" ' . checked( 1, get_option('simple_social_meta-fb-toggle'), false ) . ' /> Check to DISABLE Facebook OpenGraph Meta';
		
	}
	
	public static function simple_social_meta_fb_appid_callback() {
	
		echo '<input name="simple_social_meta-fb-appid" type="text" id="simple_social_meta-fb-appid" class="regular-text" value="'. esc_attr( get_option('simple_social_meta-fb-appid') ) . '"  />';
		
	}
	
	public static function simple_social_meta_twitter_callback() {
		
		//do nothing
		
	}

	public static function simple_social_meta_tw_toggle_callback() {
	
		echo '<input name="simple_social_meta-tw-toggle" id="simple_social_meta-tw-toggle" type="checkbox" value="1" class="code" ' . checked( 1, get_option('simple_social_meta-tw-toggle'), false ) . ' /> Check to DISABLE Twitter Card Meta';
		
	}
	
	public static function simple_social_meta_tw_site_username_callback() {
	
		echo '<input name="simple_social_meta-tw-site-username" type="text" id="simple_social_meta-tw-site-username" class="regular-text" value="'. esc_attr( get_option('simple_social_meta-tw-site-username') ) . '"  />';
		
	}
	
	public static function simple_social_meta_google_callback() {
		
		//do nothing
		
	}

	public static function simple_social_meta_gp_toggle_callback() {
	
		echo '<input name="simple_social_meta-gp-toggle" id="simple_social_meta-gp-toggle" type="checkbox" value="1" class="code" ' . checked( 1, get_option('simple_social_meta-gp-toggle'), false ) . ' /> Check to DISABLE Google+ Author Meta';
	
	}
	
	public static function simple_social_meta_gp_publisher_callback() {
	
		echo '<input name="simple_social_meta-gp-publisher" type="text" id="simple_social_meta-gp-publisher" class="regular-text" value="'. esc_attr( get_option('simple_social_meta-gp-publisher') ) . '"  />';
		
	}

	/**
	 * Adds the Simple Social meta box for all public post types.
	 *
	 * @since 1.2.0
	 */
	public static function add_simple_social_meta_meta_box() {

		/* Get all available public post types. */
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		/* Loop through each post type, adding the meta box for each type's post editor screen. */
		foreach ( $post_types as $type )
			add_meta_box( 'simple-social-meta-data', sprintf( __( 'Social Meta', SIMPLE_SOCIAL_META::$text_domain ), $type->labels->singular_name ), __CLASS__ . '::simple_social_meta_meta_box_display', $type->name, 'normal', 'high' );
	}

	/**
	 * Displays the Simple Facebook meta box.
	 *
	 * @since 1.2.0
	 */
	public static function simple_social_meta_meta_box_display( $object, $box ) { ?>
	
		<?php $args = array (
			'echo' => false
		); ?>

		<input type="hidden" name="simple-social-meta-meta-box-nonce" value="<?php echo wp_create_nonce( basename( __FILE__ ) ); ?>" />

		<div class="post-settings">

		<p>
			<label for="ssm-title"><?php _e( 'Title:', SIMPLE_SOCIAL_META::$text_domain ); ?></label>
			<br />
			<input type="text" name="ssm-title" id="ssm-title" value="<?php echo esc_attr( get_post_meta( $object->ID, '_ssm-title', true ) ); ?>" size="30" tabindex="30" style="width: 99%;" />
			<br />
			<span style="color:#aaa;">Default: <?php $default = SIMPLE_SOCIAL_META::ssm_title( $args ); echo $default['content']; ?></span>
		</p>
		
		<p>
			<label for="ssm-site-name"><?php _e( 'Site Name:', SIMPLE_SOCIAL_META::$text_domain ); ?></label>
			<br />
			<input type="text" name="ssm-site-name" id="ssm-site-name" value="<?php echo esc_attr( get_post_meta( $object->ID, '_ssm-site-name', true ) ); ?>" size="30" tabindex="30" style="width: 99%;" />
			<br />
			<span style="color:#aaa;">Default: <?php $default = SIMPLE_SOCIAL_META::ssm_site_name( $args ); echo $default['content']; ?></span>
		</p>

		<p>
			<label for="ssm-description"><?php _e( 'Description:', SIMPLE_SOCIAL_META::$text_domain ); ?></label>
			<br />
			<textarea name="ssm-description" id="ssm-description" cols="60" rows="2" tabindex="30" style="width: 99%;"><?php echo esc_textarea( get_post_meta( $object->ID, '_ssm-description', true ) ); ?></textarea>
			<br />
			<span style="color:#aaa;">Default: <?php $default = SIMPLE_SOCIAL_META::ssm_description( $args ); echo $default['content']; ?></span>
		</p>

		<p>
			<label for="ssm-url"><?php _e( 'URL:', SIMPLE_SOCIAL_META::$text_domain ); ?></label>
			<br />
			<input type="text" name="ssm-url" id="ssm-url" value="<?php echo esc_attr( get_post_meta( $object->ID, '_ssm-url', true ) ); ?>" size="30" tabindex="30" style="width: 99%;" />
			<br />
			<span style="color:#aaa;">Default: <?php $default = SIMPLE_SOCIAL_META::ssm_url( $args ); echo $default['content']; ?></span>
		</p>
		
		<p>
			<label for="ssm-image"><?php _e( 'Image:', SIMPLE_SOCIAL_META::$text_domain ); ?></label>
			<br />
			<input type="text" name="ssm-image" id="ssm-image" value="<?php echo esc_attr( get_post_meta( $object->ID, '_ssm-image', true ) ); ?>" size="30" tabindex="30" style="width: 99%;" />
			<br />
			<span style="color:#aaa;">Default: <?php $default = SIMPLE_SOCIAL_META::ssm_image( $args ); echo $default['content']; ?></span>
		</p>
		
		<p>
			<label for="ssm-type"><?php _e( 'Type:', SIMPLE_SOCIAL_META::$text_domain ); ?></label>
			<br />
			<input type="text" name="ssm-type" id="ssm-type" value="<?php echo esc_attr( get_post_meta( $object->ID, '_ssm-type', true ) ); ?>" size="30" tabindex="30" style="width: 99%;" />
			<br />
			<span style="color:#aaa;">Default: <?php $default = SIMPLE_SOCIAL_META::ssm_type( $args ); echo $default['content']; ?></span>
		</p>
		
		<p>
			<label for="ssm-locale"><?php _e( 'Locale:', SIMPLE_SOCIAL_META::$text_domain ); ?></label>
			<br />
			<input type="text" name="ssm-locale" id="ssm-locale" value="<?php echo esc_attr( get_post_meta( $object->ID, '_ssm-locale', true ) ); ?>" size="30" tabindex="30" style="width: 99%;" />
			<br />
			<span style="color:#aaa;">Default: <?php $default = SIMPLE_SOCIAL_META::ssm_locale( $args ); echo $default['content']; ?></span>
		</p>

		</div><!-- .form-table --><?php
	}

	/**
	 * Saves the post meta box settings as post metadata.
	 *
	 */
	public static function save_simple_social_meta_meta_box( $post_id, $post ) {

		/* Verify the nonce before proceeding. */
		if ( !isset( $_POST['simple-social-meta-meta-box-nonce'] ) || !wp_verify_nonce( $_POST['simple-social-meta-meta-box-nonce'], basename( __FILE__ ) ) )
			return $post_id;

		/* Get the post type object. */
		$post_type = get_post_type_object( $post->post_type );

		/* Check if the current user has permission to edit the post. */
		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
			return $post_id;

		$meta = array(
			'ssm-title' => strip_tags( $_POST['ssm-title'] ),
			'ssm-site-name' => strip_tags( $_POST['ssm-site-name'] ),
			'ssm-description' => strip_tags( $_POST['ssm-description'] ),
			'ssm-url' => strip_tags( $_POST['ssm-url'] ),
			'ssm-image' => strip_tags( $_POST['ssm-image'] ),
			'ssm-type' => strip_tags( $_POST['ssm-type'] ),
			'ssm-locale' => strip_tags( $_POST['ssm-locale'] )
		);

		foreach ( $meta as $meta_key => $new_meta_value ) {

			/* Get the meta value of the custom field key. */
			$meta_value = get_post_meta( $post_id, '_' . $meta_key , true );

			/* If there is no new meta value but an old value exists, delete it. */
			if ( '' == $new_meta_value && $meta_value )
				delete_post_meta( $post_id, '_' . $meta_key , $meta_value );

			/* If a new meta value was added and there was no previous value, add it. */
			elseif ( $new_meta_value && '' == $meta_value )
				add_post_meta( $post_id, '_' . $meta_key , $new_meta_value, true );

			/* If the new meta value does not match the old value, update it. */
			elseif ( $new_meta_value && $new_meta_value != $meta_value )
				update_post_meta( $post_id, '_' . $meta_key , $new_meta_value );
		}
	}

}

}


