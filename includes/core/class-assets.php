<?php

namespace um_ext\um_optimize\core;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main API functionality
 *
 * @example UM()->classes['um_optimize_assets']
 * @example UM()->Optimize()->assets()
 */
class Assets {

	private $has_um_shortcode = false;
	private $has_um_widget = false;

	/**
	 * Set here IDs of the pages, that use Ultimate Member scripts and styles
	 * @var array
	 */
	public $um_posts = [];

	/**
	 * Set here URLs of the pages, that use Ultimate Member scripts and styles
	 * @var array
	 */
	public $um_urls = [
			'/account/',
			'/activity/',
			'/groups/',
			'/login/',
			'/logout/',
			'/members/',
			'/my-groups/',
			'/password-reset/',
			'/register/',
			'/user/',
	];

	/**
	 * Class constructor
	 */
	public function __construct() {

		add_action( 'wp_print_footer_scripts', [ $this, 'remove_assets' ], 9 );
		add_action( 'wp_print_scripts', [ $this, 'remove_assets' ], 9 );
		add_action( 'wp_print_styles', [ $this, 'remove_assets' ], 9 );
		add_action( 'dynamic_sidebar', [ $this, 'check_widgets' ], 9 );

		add_filter( 'the_content', [ $this, 'check_post_content' ], 9 );
	}

	/**
	 * Check whether the page has Ultimate Member shortcode
	 * @param  string $post_content
	 * @return string
	 */
	public function check_post_content( $post_content ) {

		if ( strpos( $post_content, '[ultimatemember_' ) !== FALSE ) {
			$this->has_um_shortcode = true;
		}
		if ( strpos( $post_content, '[ultimatemember form_id' ) !== FALSE ) {
			$this->has_um_shortcode = true;
		}

		return $post_content;
	}

	/**
	 * Check whether the page has Ultimate Member widget
	 * @param array $widget
	 */
	public function check_widgets( $widget ) {
		if ( strpos( $widget['id'], 'um_' ) === 0 || strpos( $widget['id'], 'um-' ) === 0 ) {
			$this->has_um_widget = true;
		}
	}

	/**
	 * Detect UM elements on the page
	 * @global \um_ext\um_optimize\core\WP_Post $post
	 * @global bool                             $um_load_assets
	 * @return boolean
	 */
	public function is_ultimatemember() {
		global $post;

		if ( is_ultimatemember() || $this->has_um_widget || $this->has_um_shortcode ) {
			return true;
		}

		$REQUEST_URI = $_SERVER['REQUEST_URI'];
		if ( in_array( $REQUEST_URI, $this->um_urls ) ) {
			return true;
		}
		foreach ( $this->um_urls as $um_url ) {
			if ( strpos( $REQUEST_URI, $um_url ) !== FALSE ) {
				return true;
			}
		}

		if ( isset( $post ) && is_a( $post, 'WP_Post' ) ) {
			if ( in_array( $post->ID, $this->um_posts ) ) {
				return true;
			}
			if ( strpos( $post->post_content, '[ultimatemember_' ) !== FALSE ) {
				return true;
			}
			if ( strpos( $post->post_content, '[ultimatemember form_id' ) !== FALSE ) {
				return true;
			}
		}
	}

	/**
	 * Detect UM files
	 * @param  string  $handle
	 * @param  string  $src
	 * @return boolean
	 */
	public function is_ultimatemember_file( $src ) {

		if ( strpos( $src, '/plugins/ultimate-member/assets/' ) !== FALSE ) {
			return true;
		}
		if ( isset( UM()->dependencies()->ext_required_version ) ) {
			foreach ( UM()->dependencies()->ext_required_version as $extension => $version ) {
				if ( strpos( $src, "/plugins/um-$extension/" ) !== FALSE ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Remove Ultimate Member CSS and JS
	 * @global WP_Scripts $wp_scripts
	 * @global WP_Styles $wp_styles
	 * @return NULL
	 */
	public function remove_assets() {
		global $wp_scripts, $wp_styles;

		if ( empty( $wp_scripts->queue ) || empty( $wp_styles->queue ) ) {
			return;
		}

		if ( $this->is_ultimatemember() ) {
			return;
		}

		foreach ( $wp_scripts->queue as $key => $script ) {
			if ( $this->is_ultimatemember_file( $wp_scripts->registered[$script]->src ) ) {
				unset( $wp_scripts->queue[$key] );
			}
		}

		foreach ( $wp_styles->queue as $key => $style ) {
			if ( $this->is_ultimatemember_file( $wp_styles->registered[$style]->src ) ) {
				unset( $wp_styles->queue[$key] );
			}
		}
	}

}
