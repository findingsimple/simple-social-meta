<?php

if ( ! class_exists( 'SIMPLE_SOCIAL_META_User_Meta' ) ) {

/**
 * So that themes and other plugins can customise the text domain, the SIMPLE_SOCIAL_META_Admin should
 * not be initialized until after the plugins_loaded and after_setup_theme hooks.
 * However, it also needs to run early on the init hook.
 *
 * @author Jason Conroy <jason@findingsimple.com>
 * @package SIMPLE_SOCIAL_META
 * @since 1.0
 */
function simple_initialize_social_meta_user_meta() {
	SIMPLE_SOCIAL_META_User_Meta::init();
}
add_action( 'init', 'simple_initialize_social_meta_user_meta', -1 );


class SIMPLE_SOCIAL_META_User_Meta {

	public static function init() {  

		/* create custom plugin settings menu */
		add_filter( 'user_contactmethods', __CLASS__ . '::ssm_contact_methods', 10, 1 );

	}
	
	/**
	 * Filter the contactmethods array to add fields for Google+ and Twitter for each user
	 *
	 * @author Jason Conroy <jason@findingsimple.com>
	 * @package SIMPLE-SOCIAL-META
	 * @since 1.0
	 */	
	public static function ssm_contact_methods( $contactmethods ) {
		
		$contactmethods['google'] = 'Google+';
		
		$contactmethods['twitter'] = __( 'Twitter username (without @)', SIMPLE_SOCIAL_META::$text_domain );

		return $contactmethods;
		
	}

}

}