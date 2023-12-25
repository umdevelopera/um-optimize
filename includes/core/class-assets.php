<?php
/**
 * Optimize assets.
 *
 * @package um_ext\um_optimize\core
 */

namespace um_ext\um_optimize\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Assets
 *
 * How to get an instance:
 *  UM()->classes['um_optimize_assets']
 *  UM()->Optimize()->assets()
 */
class Assets {

	/**
	 * Set true if footer scripts are printing.
	 *
	 * @var bool
	 */
	private $footer = false;

	private $has_um_form = false;

	private $has_um_shortcode = false;

	private $has_um_widget = false;

	/**
	 * Ultimate Member shortcodes
	 *
	 * @var array
	 */
	private $um_shortcodes = array(
		'ultimatemember',
		'ultimatemember_login',
		'ultimatemember_register',
		'ultimatemember_profile',
		'ultimatemember_directory',
		'um_loggedin',
		'um_loggedout',
		'um_show_content',
		'ultimatemember_searchform',
		'ultimatemember_followers',
		'ultimatemember_following',
		'ultimatemember_followers_bar',
		'ultimatemember_friends_online',
		'ultimatemember_friends',
		'ultimatemember_friend_reqs',
		'ultimatemember_friend_reqs_sent',
		'ultimatemember_friends_bar',
		'ultimatemember_friends_button',
		'ultimatemember_groups',
		'ultimatemember_groups_profile_list',
		'ultimatemember_group_discussion_activity',
		'ultimatemember_group_discussion_wall',
		'ultimatemember_group_invite_list',
		'ultimatemember_group_users_invite_list',
		'ultimatemember_group_members',
		'ultimatemember_group_new',
		'ultimatemember_group_single',
		'ultimatemember_my_groups',
		'ultimatemember_group_comments',
		'ultimatemember_mailchimp_subscribe',
		'ultimatemember_mailchimp_unsubscribe',
		'ultimatemember_messages',
		'ultimatemember_message_button',
		'ultimatemember_message_count',
		'ultimatemember_notice',
		'ultimatemember_notifications',
		'ultimatemember_notifications_button',
		'ultimatemember_unread_notifications_count',
		'ultimatemember_notification_count',
		'ultimatemember_online',
		'um_private_content',
		'ultimatemember_profile_completeness',
		'ultimatemember_profile_progress_bar',
		'um_profile_completeness_show_content',
		'um_profile_completeness_related_text',
		'ultimatemember_top_rated',
		'ultimatemember_most_rated',
		'ultimatemember_lowest_rated',
		'ultimatemember_wall',
		'ultimatemember_activity',
		'ultimatemember_activity_form',
		'ultimatemember_trending_hashtags',
		'ultimatemember_social_login',
		'um_user_bookmarks',
		'um_user_bookmarks_all',
		'um_bookmarks_button',
		'um_user_locations_map',
		'um_user_notes_add',
		'um_user_notes_view',
		'ultimatemember_gallery',
		'ultimatemember_gallery_photos',
		'ultimatemember_albums',
		'ultimatemember_tags',
	);


	/**
	 * Class constructor
	 */
	public function __construct() {
		add_filter( 'the_content', array( $this, 'check_post_content' ), 5 );
		add_action( 'dynamic_sidebar', array( $this, 'check_widgets' ), 5 );
		add_action( 'um_before_form_is_loaded', array( $this, 'check_um_form' ) );

		add_action( 'wp_print_footer_scripts', array( $this, 'set_footer' ), 7 );
		add_action( 'wp_print_footer_scripts', array( $this, 'optimize_assets' ), 8 );
		add_action( 'wp_print_scripts', array( $this, 'optimize_assets' ), 8 );
		add_action( 'wp_print_styles', array( $this, 'optimize_assets' ), 8 );

	}


	/**
	 * Check whether the page has Ultimate Member shortcode
	 *
	 * @param  string $post_content Content of the current post.
	 * @return string
	 */
	public function check_post_content( $post_content ) {
		foreach ( $this->get_um_shortcodes() as $shortcode ) {
			if ( false !== strpos( $post_content, '[' . $shortcode ) ) {
				$this->has_um_shortcode = true;
			}
		}
		return $post_content;
	}


	/**
	 * Check whether the page has Ultimate Member form
	 *
	 * @param array $args Form arguments.
	 */
	public function check_um_form( $args ) {
		$this->has_um_form = true;
	}


	/**
	 * Check whether the page has Ultimate Member widget
	 *
	 * @param array $widget
	 */
	public function check_widgets( $widget ) {
		if ( 0 === strpos( $widget['id'], 'um_' ) || 0 === strpos( $widget['id'], 'um-' ) ) {
			$this->has_um_widget = true;
		}
	}


	/**
	 * Combine dependencies to a one file.
	 *
	 * @param array  $dependencies A collection of _WP_Dependency.
	 * @param string $ext          Files extension. Accepts 'css', 'js'.
	 *
	 * @return array|boolean Information about combined field.
	 */
	public function get_combined_file( $dependencies, $ext = 'js' ){
		$allowed_ext = array(
			'css',
			'js'
		);
		if ( ! in_array( $ext, $allowed_ext, true ) ) {
			return false;
		}

		$deps  = array();
		$data  = array();
		$v_arr = array();
		foreach ( $dependencies as $dependency ) {
			$v_arr[] = $dependency->handle . $dependency->ver;
			$deps    = array_unique( array_merge( $deps, $dependency->deps ) );
			if ( ! empty( $dependency->extra['data'] ) ) {
				$data[] = $dependency->extra['data'];
			}
		}

		sort( $v_arr );
		$v_str   = implode( ',', $v_arr );
		$version = md5( $v_str );

		$handle = 'um-combined' . ('js'=== $ext ? '-sctipts' : '-styles') . ($this->footer ? '-footer' : '');
		$name   = $handle . '-' . $version . '.' . $ext;
		$path   = UM()->uploader()->get_upload_base_dir() . 'assets/' . $name;
		$src    = UM()->uploader()->get_upload_base_url() . 'assets/' . $name;

		if ( ! file_exists( $path ) ) {
			$baseurl  = trailingslashit( home_url() );
			$basepath = wp_normalize_path( ABSPATH );

			$content = array();
			foreach ( $dependencies as $dependency ) {
				$filename  = str_replace( $baseurl, $basepath, $dependency->src );
				$content[] = '/* File: ' . basename( $filename ) . ' */';
				$content[] = file_get_contents( $filename );
			}

			$dirname = dirname( $path );
			if ( ! is_dir( $dirname ) ) {
				wp_mkdir_p( $dirname );
			}
			if ( ! file_put_contents( $path, implode( PHP_EOL, array_filter( $content ) ) ) ) {
				return false;
			}
		}

		if ( $deps ) {
			$deps = array_diff( $deps, array_keys( $dependencies ) );
		}
		if ( $data ) {
			$data = implode( PHP_EOL, array_filter( $data ) );
		}

		return compact( 'deps', 'data', 'handle', 'path', 'src', 'version' );
	}


	/**
	 * Get an array of shortcodes added by Ultimate Member and its extensions	 *
	 *
	 * @return array
	 */
	public function get_um_shortcodes() {
		return apply_filters( 'um_optimize_shortcodes', $this->um_shortcodes );
	}


	/**
	 * Detect UM elements on the page
	 *
	 * @return boolean
	 */
	public function is_ultimatemember() {
		if ( $this->has_um_form || $this->has_um_shortcode || $this->has_um_widget ) {
			return true;
		}
		if ( is_ultimatemember() ) {
			return true;
		}

		$um_pages = UM()->config()->permalinks;
		$post     = get_post();

		if ( $post && is_a( $post, 'WP_Post' ) ) {
			if ( in_array( $post->ID, $um_pages ) ) {
				return true;
			}
			foreach ( $this->get_um_shortcodes() as $shortcode ) {
				if ( strpos( $post->post_content, '[' . $shortcode ) !== FALSE ) {
					$this->has_um_shortcode = true;
					return true;
				}
			}
		} elseif ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$request_uri = wp_unslash( $_SERVER['REQUEST_URI'] );
			foreach ( $um_pages as $page_id ) {
				$permalink = get_permalink( $page_id );
				if ( false !== strpos( $permalink, $request_uri ) ) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Detect UM files
	 *
	 * @param string $file An URL or dependency object.
	 *
	 * @return boolean
	 */
	public function is_ultimatemember_file( $file ) {
		$src = is_object( $file ) ? $file->src : $file;

		if ( false !== strpos( $src, '/plugins/ultimate-member/assets/' ) && false === strpos( $src, 'fonticons' ) ) {
			return true;
		}
		if ( isset( UM()->dependencies()->ext_required_version ) ) {
			foreach ( UM()->dependencies()->ext_required_version as $extension => $version ) {
				if ( false !== strpos( $src, "/plugins/um-$extension/" ) ) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Optimize Ultimate Member CSS and JS
	 */
	public function optimize_assets() {
		if ( $this->is_ultimatemember() ) {
			$this->combine_assets();
		} else {
			$this->remove_assets();
		}
	}


	/**
	 * Replace Ultimate Member CSS and JS
	 *
	 * @global WP_Scripts $wp_scripts
	 * @global WP_Styles $wp_styles
	 */
	public function combine_assets() {
		global $wp_scripts, $wp_styles;

		$um_scripts = array();
		if ( isset( $wp_scripts->queue ) && is_array( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $key => $script ) {
				if ( $this->is_ultimatemember_file( $wp_scripts->registered[ $script ]->src ) ) {
					$um_scripts[ $script ] = $wp_scripts->registered[ $script ];
				}
			}

			if ( $um_scripts ) {
				$data = $this->get_combined_file( $um_scripts, 'js' );
				if ( $data ) {
					$wp_scripts->add( $data['handle'], $data['src'], $data['deps'] );
					if ( ! empty( $data['data'] ) ) {
						$wp_scripts->add_data( $data['handle'], 'data', $data['data'] );
					}
					$wp_scripts->enqueue( $data['handle'] );
					$wp_scripts->dequeue( array_keys( $um_scripts ) );
				}
			}
		}

		$um_styles = array();
		if ( isset( $wp_styles->queue ) && is_array( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $key => $style ) {
				if ( $this->is_ultimatemember_file( $wp_styles->registered[ $style ]->src ) ) {
					$um_styles[ $style ] = $wp_styles->registered[ $style ];
				}
			}

			if ( $um_styles ) {
				$data = $this->get_combined_file( $um_styles, 'css' );
				if ( $data ) {
					$wp_styles->add( $data['handle'], $data['src'], $data['deps'] );
					if ( ! empty( $data['data'] ) ) {
						$wp_styles->add_data( $data['handle'], 'data', $data['data'] );
					}
					$wp_styles->enqueue( $data['handle'] );
					$wp_styles->dequeue( array_keys( $um_styles ) );
				}
			}
		}
	}


	/**
	 * Remove Ultimate Member CSS and JS
	 *
	 * @global WP_Scripts $wp_scripts
	 * @global WP_Styles $wp_styles
	 */
	public function remove_assets() {
		global $wp_scripts, $wp_styles;

		if ( isset( $wp_scripts->queue ) && is_array( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $key => $script ) {
				if ( $this->is_ultimatemember_file( $wp_scripts->registered[ $script ] ) ) {
					$wp_scripts->dequeue( $script );
				}
			}
		}

		if ( isset( $wp_styles->queue ) && is_array( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $key => $style ) {
				if ( $this->is_ultimatemember_file( $wp_styles->registered[ $style ] ) ) {
					$wp_styles->dequeue( $style );
				}
			}
		}
	}


	/**
	 * Sets true if footer scripts are printing.
	 */
	public function set_footer() {
		$this->footer = true;
	}

}
