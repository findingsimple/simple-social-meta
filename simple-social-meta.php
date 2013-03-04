<?php
/*
Plugin Name: Simple Social Meta
Plugin URI: http://plugins.findingsimple.com
Description: Simple plugin that helps integrate meta tags required by social media sharing services such as facebook and twitter into WordPress powered site.
Version: 1.0
Author: Finding Simple
Author URI: http://findingsimple.com
License: GPL2
*/
/*  Copyright 2012  Jason Conroy  (email : plugins@findingsimple.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * @package SIMPLE-SOCIAL-META
 * @version 1.0
 * @author Jason Conroy <jason@findingsimple.com>
 * @copyright Copyright (c) 2012 Finding Simple
 * @link http://findingsimple.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

require_once dirname( __FILE__ ) . '/simple-social-meta-admin.php';
require_once dirname( __FILE__ ) . '/simple-social-meta-user-meta.php';

if ( ! class_exists( 'SIMPLE_SOCIAL_META' ) ) {

	/**
	 * So that themes and other plugins can customise the text domain, the SIMPLE_SOCIAL_META should
	 * not be initialized until after the plugins_loaded and after_setup_theme hooks.
	 * However, it also needs to run early on the init hook.
	 *
	 * @author Jason Conroy <jason@findingsimple.com>
	 * @package SIMPLE-SOCIAL-META
	 * @since 1.0
	 */
	function initialize_simple_social_meta() {
		SIMPLE_SOCIAL_META::init();
	}
	add_action( 'init', 'initialize_simple_social_meta', -1 );
	
	
	class SIMPLE_SOCIAL_META {
	
		static $text_domain, $defaults;
	
		/**
		 * Hook into WordPress where appropriate.
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */
		public static function init() {
	
			self::$text_domain = apply_filters( 'simple_social_meta_text_domain', 'SIMPLE_SOCIAL_META' );
			
			self::$defaults = array( 
				'element' => 'meta', 
				'echo' => true, 
				'show_default' => false,
				'before' => '', 
				'after' => "\n"
			); 
			
			/* Add minumum recommended thumbnail size for facebook */
			if ( function_exists( 'add_image_size' ) )
				add_image_size( 'Social Thumb', 300, 300 ); 
			
			/* Remove Social Thumb size from list of media sizes */
			add_filter( 'image_size_names_choose', __CLASS__ .'::remove_image_size_option', 99 );
			
			/* Facebook OpenGraph meta tags*/
			if ( get_option('simple_social_meta-fb-toggle') != 1 ) {
				
				/* Add Facebook XML Namespace to <html> tag */
				add_filter( 'language_attributes' , __CLASS__ .'::ssm_namespace' );
		
				 /* Default meta tags. */
				add_action( 'wp_head', __CLASS__ .'::ssm_title', 1 );
				add_action( 'wp_head', __CLASS__ .'::ssm_site_name', 1 ); 
				add_action( 'wp_head', __CLASS__ .'::ssm_description', 1 );
				add_action( 'wp_head', __CLASS__ .'::ssm_url', 1 );
				add_action( 'wp_head', __CLASS__ .'::ssm_image', 1 );
				add_action( 'wp_head', __CLASS__ .'::ssm_type', 1 );
				add_action( 'wp_head', __CLASS__ .'::ssm_locale', 1 );
				add_action( 'wp_head', __CLASS__ .'::ssm_app_id', 1 );
				
				/* Article specific meta tags */
				add_action( 'wp_head', __CLASS__ .'::ssm_article_published', 1 );
				add_action( 'wp_head', __CLASS__ .'::ssm_article_modified', 1 );
				//add_action( 'wp_head', __CLASS__ .'::ssm_article_author', 1 );
				//add_action( 'wp_head', __CLASS__ .'::ssm_article_section', 1 );
				//add_action( 'wp_head', __CLASS__ .'::ssm_article_tag', 1 );	
			
			}

			/* Twitter Card meta tags*/
			if ( get_option('simple_social_meta-tw-toggle') != 1 ) {
				
				add_action( 'wp_head', __CLASS__ .'::ssm_twitter_card', 1 );
				
				/* Use Facebook OpenGraph for these if it is enabled */
				if ( get_option('simple_social_meta-fb-toggle') == 1 ) {
					
					add_action( 'wp_head', __CLASS__ .'::ssm_twitter_url', 1 );
					add_action( 'wp_head', __CLASS__ .'::ssm_twitter_title', 1 );
					add_action( 'wp_head', __CLASS__ .'::ssm_twitter_description', 1 );
					add_action( 'wp_head', __CLASS__ .'::ssm_twitter_image', 1 );
						
				}		
				
				add_action( 'wp_head', __CLASS__ .'::ssm_twitter_site', 1 );
				add_action( 'wp_head', __CLASS__ .'::ssm_twitter_creator', 1 );
			
			}
			
			/* Google+ Author meta tag*/
			if ( get_option('simple_social_meta-gp-toggle') != 1 ) {
				add_action( 'wp_head', __CLASS__ .'::ssm_google_author', 1 );
				add_action( 'wp_head', __CLASS__ .'::ssm_google_publisher', 1 );
			}
	
		} 

		/**
		 * Remove social image size from the list of available sizes in the media uploader
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */	
		public static function remove_image_size_option( $sizes ){

			unset( $sizes['Social Thumb'] );
			
			return $sizes;
		 
		}
	
		/**
		 * Title
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */
		public static function ssm_title( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_title_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$title = SIMPLE_SOCIAL_META::ssm_get_title();
	
			if ( !empty($title) ) {
	
				$attributes = array(
					'property' => 'og:title',
					'content' => $title
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}
		
		/**
		 * Site Name
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */
		public static function ssm_site_name( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_site_name_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$name = get_bloginfo('name');
	
			if ( !empty($name) ) {
	
				$attributes = array(
					'property' => 'og:site_name',
					'content' => $name
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}
		
		/**
		 * Description
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */
		public static function ssm_description( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_description_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$description = SIMPLE_SOCIAL_META::ssm_get_description();
	
			if ( !empty($description) ) {
	
				$attributes = array(
					'property' => 'og:description',
					'content' => $description
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}
		
		/**
		 * URL
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */
		public static function ssm_url( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_url_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$url = SIMPLE_SOCIAL_META::ssm_get_url();
	
			if ( !empty($url) ) {
	
				$attributes = array(
					'property' => 'og:url',
					'content' => $url
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}
		
		/**
		 * Image
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */
		public static function ssm_image( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_image_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$image = SIMPLE_SOCIAL_META::ssm_get_image();
	
			if ( !empty($image) ) {
	
				$attributes = array(
					'property' => 'og:image',
					'content' => $image
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}
	
		/**
		 * Type
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */
		public static function ssm_type( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_type_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$type = SIMPLE_SOCIAL_META::ssm_get_type();
	
			if ( !empty($type) ) {
	
				$attributes = array(
					'property' => 'og:type',
					'content' => $type
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}
		
		/**
		 * Locale
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */
		public static function ssm_locale( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_locale_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
			
			//use WP language setting
			$locale = strtolower( str_replace( '-' , '_' , get_bloginfo('language') ) ) ;
	
			if ( !empty($locale) ) {
	
				$attributes = array(
					'property' => 'og:locale',
					'content' => $locale
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}
		
		/**
		 * App ID
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */
		public static function ssm_app_id( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_app_id_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$app_id = get_option('simple_social_meta-fb-appid');
	
			if ( !empty($app_id) ) {
	
				$attributes = array(
					'property' => 'fb:app_id',
					'content' => $app_id
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}
		
		/**
		 * Article Published 
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */		
		public static function ssm_article_published( $args = array() ) {
			
			if ( is_single() ) {
						
				$args = wp_parse_args( $args, self::$defaults );
				$args = apply_filters( 'ssm_article_published_args', $args );
				extract( $args, EXTR_SKIP );
		
				$attributes = array();
		
				$date = get_the_date( 'c' );
		
				if ( !empty($date) ) {
		
					$attributes = array(
						'property' => 'article:published_time',
						'content' => $date
					);
				
				}
		
				if ( !$echo && !empty($attributes) )
					return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
				
				if ( !empty($attributes) )
					echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
				
			}
			
		}
		
		/**
		 * Article Modified
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */		
		public static function ssm_article_modified( $args = array() ) {

			if ( is_single() && ( get_the_modified_time() != get_the_time() ) ) {
				
				$args = wp_parse_args( $args, self::$defaults );
				$args = apply_filters( 'ssm_article_modified_args', $args );
				extract( $args, EXTR_SKIP );
		
				$attributes = array();
		
				$modified_date = get_the_modified_time( 'c' );
		
				if ( !empty($modified_date) ) {
		
					$attributes = array(
						'property' => 'article:modified_time',
						'content' => $modified_date
					);
				
				}
		
				if ( !$echo && !empty($attributes) )
					return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
				
				if ( !empty($attributes) )
					echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
					
			}
			
		}	
		
		/**
		 * Author
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */		
		public static function ssm_article_author( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_article_author_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$author = '';
	
			if ( !empty($app_id) ) {
	
				$attributes = array(
					'property' => 'article:author',
					'content' => $author
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}
		
		/**
		 * Article Section
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */		
		public static function ssm_article_section( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_article_section_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$section = '';
	
			if ( !empty($app_id) ) {
	
				$attributes = array(
					'property' => 'article:section',
					'content' => $section
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}		

		/**
		 * Article Tag
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */		
		public static function ssm_article_tag( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_article_tag_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$app_id = $tag;
	
			if ( !empty($app_id) ) {
	
				$attributes = array(
					'property' => 'article:tag',
					'content' => $tag
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}

		/**
		 * Twitter:Card
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */		
		public static function ssm_twitter_card( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_twitter_card_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$card = 'summary';
	
			if ( !empty($card) ) {
	
				$attributes = array(
					'name' => 'twitter:card',
					'content' => $card
				);
				
			}
				
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}
		
		/**
		 * Twitter Title
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */
		public static function ssm_twitter_title( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_twitter_title_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$title = SIMPLE_SOCIAL_META::ssm_get_title();
	
			if ( !empty($title) ) {
	
				$attributes = array(
					'name' => 'twitter:title',
					'content' => $title
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}
				
		/**
		 * Twitter Description
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */
		public static function ssm_twitter_description( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_twiiter_description_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$description = SIMPLE_SOCIAL_META::ssm_get_description();
	
			if ( !empty($description) ) {
	
				$attributes = array(
					'name' => 'twitter:description',
					'content' => $description
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}
		
		/**
		 * Twitter URL
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */
		public static function ssm_twitter_url( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_twitter_url_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$url = SIMPLE_SOCIAL_META::ssm_get_url();
	
			if ( !empty($url) ) {
	
				$attributes = array(
					'name' => 'twitter:url',
					'content' => $url
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}
		
		/**
		 * Twitter Image
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */
		public static function ssm_twitter_image( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_twitter_image_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$image = SIMPLE_SOCIAL_META::ssm_get_image();
	
			if ( !empty($image) ) {
	
				$attributes = array(
					'name' => 'twitter:image',
					'content' => $image
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}
	
		/**
		 * Twitter:Site
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */		
		public static function ssm_twitter_site( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_twitter_site_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$site = get_option('simple_social_meta-tw-site-username');
	
			if ( !empty($site) ) {
	
				$attributes = array(
					'name' => 'twitter:site',
					'content' => '@' . $site
				);
				
			}
				
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
		}

		/**
		 * Twitter:Creator
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */		
		public static function ssm_twitter_creator( $args = array() ) {
			
			if ( is_singular() ) {
			
				$args = wp_parse_args( $args, self::$defaults );
				$args = apply_filters( 'ssm_twitter_creator_args', $args );
				extract( $args, EXTR_SKIP );
		
				$attributes = array();
				
				$creator =  get_user_meta( get_queried_object()->post_author , 'twitter', true );
							
				if ( !empty($creator) ) {
		
					$attributes = array(
						'name' => 'twitter:creator',
						'content' => '@' . $creator
					);
					
				}
					
				if ( !$echo && !empty($attributes) )
					return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
				
				if ( !empty($attributes) )
					echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
				
			}
			
		}	
		
		/**
		 * Google Plus Author Meta Tag
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */		
		public static function ssm_google_author( $args = array() ) {
			
			if ( is_singular() ) {
			
				$args = wp_parse_args( $args, self::$defaults );
				$args = apply_filters( 'ssm_google_author_args', $args );
				extract( $args, EXTR_SKIP );
		
				$attributes = array();
				
				$author =  get_user_meta( get_queried_object()->post_author , 'google', true );
							
				if ( !empty($author) ) {
		
					$attributes = array(
						'rel' => 'author',
						'href' => $author
					);
					
				}
				
				//use <link /> tag instead of <meta />
				$args['element'] = 'link';
					
				if ( !$echo && !empty($attributes) )
					return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
				
				if ( !empty($attributes) )
					echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
				
			}
			
		}	

		/**
		 * Google Plus Publisher Meta Tag
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */		
		public static function ssm_google_publisher( $args = array() ) {
						
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_google_publisher_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
			
			$publisher =  get_option('simple_social_meta-gp-publisher');
						
			if ( !empty($publisher) ) {
	
				$attributes = array(
					'rel' => 'publisher',
					'href' => $publisher
				);
				
			}
			
			//use <link /> tag instead of <meta />
			$args['element'] = 'link';
				
			if ( !$echo && !empty($attributes) )
				return SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_SOCIAL_META::ssm_output( $attributes , $args );
						
		}	
	
		/**
		 * Format output according to argument
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */	
		public static function ssm_output( $attributes = array() , $args = array() ) {
		
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'ssm_output_args', $args );
			extract( $args, EXTR_SKIP );
		
			if ($echo && !empty($attributes) ) {
			
				$tag = $args['before'] . '<' . $element . ' ';
				
				foreach ($attributes as $attribute => $value)
					$tag .= $attribute . '="' . $value . '" ';
				
				$tag .= '/>' . $args['after'];
				
				return $tag;
	
			} 
			
			return $attributes;
		
		}
	
		/**
		 * Get correct title
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */	
		public static function ssm_get_title() {
		
				global $post;
		
				if ((is_singular() && !is_front_page()) || is_admin()) {
					$title = get_the_title();
				} else if(is_search()) {
					$title = 'Search';
				} else if(is_category()) {
					$title =  'Archive for ' . single_cat_title("", false) ;
				} else if(is_tag()) {
					$title = 'Archive for ' . single_tag_title("", false) ;
				} else if(is_tax()) {
					$title = 'Archive for ' . single_term_title("", false) ;
				} else if(is_author()) {
					$id = get_query_var( 'author' );
					$title = 'Archive for ' .  get_the_author_meta( 'display_name', $id );
				} else if(is_date()) {
					$title = 'Archives by date' ;
				} else if(is_post_type_archive()) {
					$title = post_type_archive_title("", false);
				} else if(is_archive()) {
					$title = 'Archives';
				} else if(is_404()) {
					$title = 'Page Not Found';
				} else if(is_home() && !is_front_page()) {
					$title = get_the_title( get_option('page_for_posts', true) );
				} else if(is_front_page() || (is_home() && is_front_page()) ) {
					$title = get_bloginfo('name');
				} 

				/* Apply the wp_title filters so we're compatible with plugins. */
				$title = apply_filters( 'wp_title', $title );
		
				return $title;
		
		}
	
		/**
		 * Get correct description
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */		
		public static function ssm_get_description() {
		
			global $post;
			
			$description = '';
		
			if( !empty( $post->post_excerpt ) ){
				
				/* Use excerpt if it exists. */
				$description = $post->post_excerpt;
				
			} elseif (is_front_page() || is_home() || is_search()) {
				
				/* Use main blog description. */
				$description = get_bloginfo( 'description' );
				
			} elseif ( is_archive() ) {	

				/* If viewing a user/author archive. */
				if ( is_author() ) {
		
					/* Get the meta value for the 'Description' user meta key. */
					$description = get_user_meta( get_query_var( 'author' ), 'Description', true );
		
					/* If no description was found, get the user's description (biographical info). */
					if ( empty( $description ) )
						$description = get_the_author_meta( 'description', get_query_var( 'author' ) );

				/* If viewing a taxonomy term archive, get the term's description. */						
				} elseif ( is_category() || is_tag() || is_tax() ) {
					$description = term_description( '', get_query_var( 'taxonomy' ) );
		
				/* If viewing a custom post type archive. */
				} elseif ( is_post_type_archive() ) {
										
					/* Get the post type object. */
					$post_type = get_post_type_object( get_query_var( 'post_type' ) );
		
					/* If a description was set for the post type, use it. */
					if ( isset( $post_type->description ) )
						$description = $post_type->description;
				}

			} else {
				$description = $post->post_content;
			}
			
			$description = trim(strip_shortcodes(strip_tags( $description )));
			
			$pos0 = strpos($description, '.')+1;
			$pos0 = ($pos0 === false) ? strlen($description) : $pos0;
			$pos = strpos(substr($description,$pos0),'.');
			if ($pos < 0 || $pos === false) {
				$pos = strlen($description);
			} else {
				$pos = $pos + $pos0;
			}
			$description = str_replace("\n","",substr($description, 0 , $pos));
			$description = str_replace("\r","",$description);
			$description = str_replace("\"","'",$description);
			$description = nl2br($description);
		 
			return $description;
		}
	
		/**
		 * Get correct type
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */		
		public static function ssm_get_type() {
				
			if ( is_single() ) { 
				$type = "article";
			} else { 
				$type = "website";
			}
			
			return $type;
			
		}
	
		/**
		 * Get correct url
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */	
		public static function ssm_get_url() {
		
			global $post;
	
			if (is_singular() && !is_front_page()) {
				$url = get_permalink();
			} else if(is_search()) {
				$search = get_query_var('s');
				$url = get_search_link( $search );
			} else if(is_category()) {
				$url =  get_category_link( get_queried_object() );
			} else if(is_tag()) {
				$url = get_tag_link( get_queried_object() );
			} else if(is_tax()) {
				$url = get_term_link( get_queried_object() );
			} else if(is_author()) {
				$id = get_query_var( 'author' );
				$url = get_author_posts_url( $id );
			} else if(is_year()) {
				$url = get_year_link( get_query_var('year') );
			} else if(is_month()) {
				$url = get_month_link( get_query_var('year') , get_query_var('monthnum') );
			} else if(is_day()) {
				$url = get_day_link( get_query_var('year') , get_query_var('monthnum') , get_query_var('day') );
			} else if(is_post_type_archive()) {
				$url = get_post_type_archive_link( get_query_var('post_type') );
			} else if(is_home() && !is_front_page()) {
				$url = get_permalink( get_option('page_for_posts', true) );
			} else if(is_front_page() || (is_home() && is_front_page()) ) {
				$url = get_bloginfo('url');
				$url = preg_replace("~^https?://[^/]+$~", "$0/", $url); //trailing slash
			} else if( is_admin() ) {
				$url = get_permalink( $post->ID ); //displays the default value in the admin area
			}
	
			return $url;
	
		}
		
		/**
		 * Get correct image
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */			
		public static function ssm_get_image() {
		
			global $post;
			
			$image = '';
			
			if ( function_exists( 'get_the_image') ) {
			
				$image_array = get_the_image( array( 'format' => 'array', 'image_scan' => true , 'size' => 'Social Thumb', 'default_image' => get_option('simple_social_meta-default-image') ) ); 
				
				if ( !empty( $image_array['src'] ) )
					$image = $image_array['src'];
			
			} elseif ( has_post_thumbnail() ) {
						
				$image_array = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'Social Thumb' );

				if ( !empty( $image_array[0] ) )				
					$image = $image_array[0];
			
			} else {

				if ( get_option('simple_social_meta-default-image') )							
					$image = get_option('simple_social_meta-default-image');
			
			}
			
			return $image;	
		
		}

		/**
		 * Add facebook xml namespace
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-SOCIAL-META
		 * @since 1.0
		 */			
		public static function ssm_namespace( $attr ) {
		
			$attr .= " xmlns:fb=\"http://ogp.me/ns/fb#\" "; 

            return $attr;
		
		}

	}

}