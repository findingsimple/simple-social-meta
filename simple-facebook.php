<?php
/*
Plugin Name: Simple Facebook Open Graph
Plugin URI: http://plugins.findingsimple.com
Description: Simple plugin that helps integrate basic facebook open graph meta into your WordPress powered site.
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
 * @package SIMPLE-FACEBOOK
 * @version 1.0
 * @author Jason Conroy <jason@findingsimple.com>
 * @copyright Copyright (c) 2012 Finding Simple
 * @link http://findingsimple.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

require_once dirname( __FILE__ ) . '/simple-facebook-admin.php';

if ( ! class_exists( 'SIMPLE_FACEBOOK' ) ) {

	/**
	 * So that themes and other plugins can customise the text domain, the SIMPLE_FACEBOOK should
	 * not be initialized until after the plugins_loaded and after_setup_theme hooks.
	 * However, it also needs to run early on the init hook.
	 *
	 * @author Jason Conroy <jason@findingsimple.com>
	 * @package SIMPLE-BOOK
	 * @since 1.0
	 */
	function initialize_simple_facebook() {
		SIMPLE_FACEBOOK::init();
	}
	add_action( 'init', 'initialize_simple_facebook', -1 );
	
	
	class SIMPLE_FACEBOOK {
	
		static $text_domain, $defaults;
	
		/**
		 * Hook into WordPress where appropriate.
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */
		public static function init() {
	
			self::$text_domain = apply_filters( 'simple_facebook_text_domain', 'SIMPLE_AGLS' );
			
			self::$defaults = array( 
				'element' => 'meta', 
				'echo' => true, 
				'show_default' => false,
				'before' => '', 
				'after' => "\n",
				'start' => "<!-- SIMPLE-FACEBOOK START -->",
				'end' => "<!-- SIMPLE-FACEBOOK END -->"
			); 
			
			/* Add minumum recommended thumbnail size for facebook */
			if ( function_exists( 'add_image_size' ) )
				add_image_size( 'Facebook Thumb', 200, 200 ); 
				
			/* Add Facebook XML Namespace to <html> tag */
			add_filter( 'language_attributes' , __CLASS__ .'::sfb_namespace' );
							
			/* Top */
			add_action( 'wp_head', __CLASS__ .'::sfb_comment_start', 1 ); 
	
			 /* Default meta tags. */
			add_action( 'wp_head', __CLASS__ .'::sfb_title', 1 );
			add_action( 'wp_head', __CLASS__ .'::sfb_site_name', 1 ); 
			add_action( 'wp_head', __CLASS__ .'::sfb_description', 1 );
			add_action( 'wp_head', __CLASS__ .'::sfb_url', 1 );
			add_action( 'wp_head', __CLASS__ .'::sfb_image', 1 );
			add_action( 'wp_head', __CLASS__ .'::sfb_type', 1 );
			add_action( 'wp_head', __CLASS__ .'::sfb_locale', 1 );
			add_action( 'wp_head', __CLASS__ .'::sfb_app_id', 1 );
			
			/* Article specific meta tags */
			add_action( 'wp_head', __CLASS__ .'::sfb_article_published', 1 );
			add_action( 'wp_head', __CLASS__ .'::sfb_article_modified', 1 );
			//add_action( 'wp_head', __CLASS__ .'::sfb_article_author', 1 );
			//add_action( 'wp_head', __CLASS__ .'::sfb_article_section', 1 );
			//add_action( 'wp_head', __CLASS__ .'::sfb_article_tag', 1 );			
			
			/* Tail */
			add_action( 'wp_head', __CLASS__ .'::sfb_comment_end', 1 ); 
	
		} 
	
	
		/**
		 * Meta start comment
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */
		public static function sfb_comment_start( $args = array() ) {
		
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'sfb_comment_start_args', $args );
			extract( $args, EXTR_SKIP );
			
			if ( !$echo )
				return $before . $start . $after;
			
			echo $before . $start . $after;
	
		}
	
		/**
		 * Meta end comment
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */
		public static function sfb_comment_end( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'sfb_comment_end_args', $args );
			extract( $args, EXTR_SKIP );
	
			if ( !$echo )
				return $before . $end . $after;
			
			echo $before . $end . $after;
			
		}
	
		/**
		 * Title
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */
		public static function sfb_title( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'sfb_title_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$title = SIMPLE_FACEBOOK::sfb_get_title();
	
			if ( !empty($title) ) {
	
				$attributes = array(
					'property' => 'og:title',
					'content' => $title
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
		}
		
		/**
		 * Site Name
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */
		public static function sfb_site_name( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'sfb_site_name_args', $args );
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
				return SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
		}
		
		/**
		 * Description
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */
		public static function sfb_description( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'sfb_description_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$description = SIMPLE_FACEBOOK::sfb_get_description();
	
			if ( !empty($description) ) {
	
				$attributes = array(
					'property' => 'og:description',
					'content' => $description
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
		}
		
		/**
		 * URL
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */
		public static function sfb_url( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'sfb_url_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$url = SIMPLE_FACEBOOK::sfb_get_url();
	
			if ( !empty($url) ) {
	
				$attributes = array(
					'property' => 'og:url',
					'content' => $url
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
		}
		
		/**
		 * Image
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */
		public static function sfb_image( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'sfb_image_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$image = SIMPLE_FACEBOOK::sfb_get_image();
	
			if ( !empty($image) ) {
	
				$attributes = array(
					'property' => 'og:image',
					'content' => $image
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
		}
	
		/**
		 * Type
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */
		public static function sfb_type( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'sfb_type_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$type = SIMPLE_FACEBOOK::sfb_get_type();
	
			if ( !empty($type) ) {
	
				$attributes = array(
					'property' => 'og:type',
					'content' => $type
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
		}
		
		/**
		 * Locale
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */
		public static function sfb_locale( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'sfb_locale_args', $args );
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
				return SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
		}
		
		/**
		 * App ID
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */
		public static function sfb_app_id( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'sfb_app_id_args', $args );
			extract( $args, EXTR_SKIP );
	
			$attributes = array();
	
			$app_id = get_option('simple_facebook-appid');
	
			if ( !empty($app_id) ) {
	
				$attributes = array(
					'property' => 'og:app_id',
					'content' => $app_id
				);
			
			}
	
			if ( !$echo && !empty($attributes) )
				return SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
		}
		
		/**
		 * Article Published 
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */		
		public static function sfb_article_published( $args = array() ) {
			
			if ( is_single() ) {
						
				$args = wp_parse_args( $args, self::$defaults );
				$args = apply_filters( 'sfb_article_published_args', $args );
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
					return SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
				
				if ( !empty($attributes) )
					echo SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
				
			}
			
		}
		
		/**
		 * Article Modified
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */		
		public static function sfb_article_modified( $args = array() ) {

			if ( is_single() && ( get_the_modified_time() != get_the_time() ) ) {
				
				$args = wp_parse_args( $args, self::$defaults );
				$args = apply_filters( 'sfb_article_modified_args', $args );
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
					return SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
				
				if ( !empty($attributes) )
					echo SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
					
			}
			
		}	
		
		/**
		 * Author
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */		
		public static function sfb_article_author( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'sfb_article_author_args', $args );
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
				return SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
		}
		
		/**
		 * Article Section
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */		
		public static function sfb_article_section( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'sfb_article_section_args', $args );
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
				return SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
		}		

		/**
		 * Article Tag
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */		
		public static function sfb_article_tag( $args = array() ) {
	
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'sfb_article_tag_args', $args );
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
				return SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
			if ( !empty($attributes) )
				echo SIMPLE_FACEBOOK::sfb_output( $attributes , $args );
			
		}	
	
		/**
		 * Format output according to argument
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */	
		public static function sfb_output( $attributes = array() , $args = array() ) {
		
			$args = wp_parse_args( $args, self::$defaults );
			$args = apply_filters( 'sfb_output_args', $args );
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
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */	
		public static function sfb_get_title() {
		
				global $post;
		
				if (is_singular()) {
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
				} else if(is_home()) {
					$title = get_bloginfo('name');
				} else if(is_admin()) {
					$title = get_the_title( $post->ID ); //displays the default value in the admin area
				}
		
				return $title;
		
		}
	
		/**
		 * Get correct description
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */		
		public static function sfb_get_description() {
		
			global $post;
		
			if(!empty($post->post_excerpt)){
				$description = $post->post_excerpt;
			} if (is_front_page() || is_archive() || is_home() || is_search()) {	
				$description = trim(strip_shortcodes(strip_tags( get_bloginfo( 'description' ))));
			} else {
				$description = trim(strip_shortcodes(strip_tags($post->post_content)));
			}
		
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
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */		
		public static function sfb_get_type() {
				
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
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */	
		public static function sfb_get_url() {
		
			global $post;
	
			if (is_singular()) {
				$url = get_permalink();
			} else if(is_search()) {
				$search = get_query_var('s');
				$url = get_bloginfo('url') . "/search/". $search;
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
			} else if(is_home()) {
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
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */			
		public static function sfb_get_image() {
		
			global $post;
			
			if ( function_exists( 'get_the_image') ) {
			
				$image_array = get_the_image( array( 'format' => 'array', 'image_scan' => true , 'size' => 'Facebook Thumb', 'default_image' => get_option('simple_facebook-default-image') ) ); 
				
				$image = $image_array['src'];
			
			} elseif ( has_post_thumbnail() ) {
						
				$image_array = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'Facebook Thumb' );
				
				$image = $image_array[0];
			
			} else {
			
				$image = get_option('simple_facebook-default-image');
			
			}
			
			return $image;	
		
		}

		/**
		 * Add facebook xml namespace
		 *
		 * @author Jason Conroy <jason@findingsimple.com>
		 * @package SIMPLE-FACEBOOK
		 * @since 1.0
		 */			
		public static function sfb_namespace( $attr ) {
		
			$attr .= " xmlns:fb=\"http://ogp.me/ns/fb#\" "; 

            return $attr;
		
		}

	}

}