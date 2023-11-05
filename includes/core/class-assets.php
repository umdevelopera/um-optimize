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

if ( ! class_exists( 'um_ext\um_optimize\core\Assets' ) ) {

	/**
	 * Class Assets
	 *
	 * How to get an instance:
	 *  UM()->classes['um_optimize_assets']
	 *  UM()->Optimize()->assets()
	 */
	class Assets {

		/**
		 * Combined styles.
		 *
		 * @var array
		 */
		protected $css = array();

		/**
		 * Combined scripts.
		 *
		 * @var type
		 */
		protected $js = array();

		/**
		 * Set true if footer scripts are printing.
		 *
		 * @var bool
		 */
		protected $footer = false;

		/**
		 * Set true if the page has Ultimate Member form.
		 *
		 * @var bool
		 */
		private $has_um_form = false;

		/**
		 * Set true if the page has Ultimate Member shortcode.
		 *
		 * @var bool
		 */
		private $has_um_shortcode = false;

		/**
		 * Set true if the page has Ultimate Member widget.
		 *
		 * @var bool
		 */
		private $has_um_widget = false;


		/**
		 * Class constructor
		 */
		public function __construct() {
			// Check for UM elememts.
			add_filter( 'the_content', array( $this, 'check_post_content' ), 5 );
			add_action( 'dynamic_sidebar', array( $this, 'check_widgets' ), 5 );
			add_action( 'um_before_form_is_loaded', array( $this, 'check_um_form' ) );

			// Priority must be less than 10. Function _wp_footer_scripts() is called on 10.
			add_action( 'wp_print_footer_scripts', array( $this, 'set_footer' ), 8 );
			add_action( 'wp_print_footer_scripts', array( $this, 'optimize_assets' ), 9 );

			add_action( 'wp_print_scripts', array( $this, 'optimize_assets' ), 10 );
			add_action( 'wp_print_styles', array( $this, 'optimize_assets' ), 10 );

			add_filter( 'um_optimize_filecontent', array( $this, 'optimize_filecontent' ), 10, 3 );
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
					break;
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
		 * Check whether the page has Ultimate Member widget.
		 *
		 * @param array $widget
		 */
		public function check_widgets( $widget ) {
			if ( 0 === strpos( $widget['id'], 'um_' ) || 0 === strpos( $widget['id'], 'um-' ) ) {
				$this->has_um_widget = true;
			}
		}


		/**
		 * Combine Ultimate Member CSS and JS files
		 *
		 * @param string $ext Files extension. Accepts 'css', 'js'.
		 */
		public function combine_assets( $ext ) {

			if ( 'css' === $ext ) {
				$assets = wp_styles();
			} elseif ( 'js' === $ext ) {
				$assets = wp_scripts();
			} else {
				return false;
			}

			$um_assets = $this->get_um_assets( $assets );
			if ( $um_assets ) {
				$combined = $this->get_combined_file( $um_assets, $ext );
				if ( $combined && is_array( $combined ) ) {
					$assets->add( $combined['handle'], $combined['src'], $combined['deps'] );
					$assets->add_data( $combined['handle'], 'data', $combined['data'] );
					$assets->enqueue( $combined['handle'] );
					$assets->dequeue( array_keys( $um_assets ) );
					$this->$ext = array_merge( $this->$ext, $um_assets );
				}
			}
		}


		/**
		 * Dequeue Ultimate Member CSS and JS files
		 *
		 * @param string $ext Files extension. Accepts 'css', 'js'.
		 */
		public function dequeue_assets( $ext ) {

			if ( 'css' === $ext ) {
				$assets = wp_styles();
			} elseif ( 'js' === $ext ) {
				$assets = wp_scripts();
			} else {
				return false;
			}

			if ( isset( $assets->queue ) && is_array( $assets->queue ) ) {
				foreach ( $assets->queue as $handle ) {
					if ( $this->is_ultimatemember_file( $assets->registered[ $handle ] ) ) {
						$assets->dequeue( $handle );
					}
				}
			}
		}


		/**
		 * Get or create combined file.
		 *
		 * @param array  $um_assets A collection of _WP_Dependency.
		 * @param string $ext       Files extension. Accepts 'css', 'js'.
		 *
		 * @return array|boolean Information about combined field.
		 */
		public function get_combined_file( $um_assets, $ext ){
			if ( empty( $um_assets ) ) {
				return false;
			}

			if ( 'css' === $ext ) {
				$handle = 'um-combined-styles';
			} elseif ( 'js' === $ext ) {
				$handle = 'um-combined-scripts';
			} else {
				return false;
			}
			if ( $this->footer ) {
				$handle .= '-footer';
			}

			// Exclude files that have already been combined.
			if ( ! empty( $this->$ext ) ) {
				$um_assets = array_diff_key( (array) $um_assets, (array) $this->$ext );
			}

			$deps = array();
			$data = array();
			$ver_ = array();

			foreach ( $um_assets as $dependency ) {
				$ver_[] = $dependency->handle . $dependency->ver;
				$deps  += $dependency->deps;
				if ( ! empty( $dependency->extra['data'] ) ) {
					$data[] = $dependency->extra['data'];
				}
			}

			sort( $ver_ );
			$version = md5( implode( ',', $ver_ ) );
			$name    = $handle . '-' . $version . '.' . $ext;

			$path = UM()->uploader()->get_upload_base_dir() . 'um_optimize/' . $name;
			$src  = UM()->uploader()->get_upload_base_url() . 'um_optimize/' . $name;

			if ( ! file_exists( $path ) ) {
				$baseurl  = trailingslashit( home_url() );
				$basepath = wp_normalize_path( ABSPATH );

				$content = array();
				foreach ( $um_assets as $dependency ) {
					$filename    = str_replace( $baseurl, $basepath, $dependency->src );
					$filecontent = file_get_contents( $filename );
					if ( FALSE !== $filecontent ) {
						$content[] = '/* File: ' . basename( $filename ) . ' */';
						$content[] = apply_filters( 'um_optimize_filecontent', $filecontent, $dependency, $ext );
					}
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
				// Exclude files that have already been combined.
				$deps = array_unique( array_diff( $deps, array_keys( $um_assets ), array_keys( $this->$ext ) ) );
			}
			if ( $data ) {
				$data = implode( PHP_EOL, array_filter( $data ) );
			}

			return compact( 'deps', 'data', 'handle', 'path', 'src', 'version' );
		}


		/**
		 * Get an array of Ultimate Member assets.
		 *
		 * @param  WP_Dependencies $assets  WP_Styles or WP_Scripts.
		 * @param  array           $handles An array of handles of queued dependencies.
		 *
		 * @return array A collection of _WP_Dependency.
		 */
		public function get_um_assets( $assets, $handles = array() ) {
			$um_assets = array();
			if ( empty( $handles ) && isset( $assets->queue ) && is_array( $assets->queue ) ) {
				$handles = $assets->queue;
			}
			foreach ( $handles as $handle ) {
				$dependency = $assets->registered[ $handle ];
				if ( $this->is_ultimatemember_file( $dependency->src ) ) {
					if ( ! empty( $dependency->deps ) && is_array( $dependency->deps ) ) {
						$um_assets += $this->get_um_assets( $assets, $dependency->deps );
					}
					$um_assets[ $handle ] = $dependency;
				}
			}
			return $um_assets;
		}


		/**
		 * Get an array of shortcodes added by Ultimate Member and its extensions
		 *
		 * @return array A collection of Ultimate Member shortcodes.
		 */
		public function get_um_shortcodes() {
			$um_shortcodes = array(
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
			return apply_filters( 'um_optimize_shortcodes', $um_shortcodes );
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
				if ( in_array( $post->ID, $um_pages, true ) ) {
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

			$exclude = apply_filters( 'um_optimize_assets_exclude', array() );
			if ( ! empty( $exclude ) && preg_match( '/' . implode( '|', $exclude ) . '/i', $src ) ) {
				return false;
			}
			if ( false !== strpos( $src, '/plugins/ultimate-member/assets/' ) ) {
				return true;
			}
			if ( isset( UM()->dependencies()->ext_required_version ) ) {
				foreach ( UM()->dependencies()->ext_required_version as $extension => $version ) {
					if ( false !== strpos( $src, "/plugins/um-$extension/assets/" ) ) {
						return true;
					}
				}
			}

			return false;
		}


		/**
		 * Optimize loading of Ultimate Member CSS and JS files
		 */
		public function optimize_assets() {
			if ( ! $this->is_ultimatemember() && UM()->options()->get( 'um_optimize_css_dequeue' ) ) {
				$this->dequeue_assets( 'css' );
			} elseif ( UM()->options()->get( 'um_optimize_css_combine' ) ) {
				$this->combine_assets( 'css' );
			}

			if ( ! $this->is_ultimatemember() && UM()->options()->get( 'um_optimize_css_dequeue' ) ) {
				$this->dequeue_assets( 'js' );
			} elseif ( UM()->options()->get( 'um_optimize_css_combine' ) ) {
				$this->combine_assets( 'js' );
			}
		}


		/**
		 * Modify file content before combining
		 *
		 * @param string         $filecontent File content.
		 * @param _WP_Dependency $dependency
		 * @param string         $ext         File extension. Accepts 'css', 'js'.
		 *
		 * @return string File content.
		 */
		public function optimize_filecontent( $filecontent, $dependency, $ext ) {

			if ( 'css' === $ext ) {
				$dir        = dirname( $dependency->src );
				$parent_dir = dirname( $dir );

				$replace_pairs = array(
					'url("./'     => 'url("' . $dir . '/',
					'url("../'    => 'url("' . $parent_dir . '/',
					'url("font'   => 'url("' . $dir . '/font',
					'url("images' => 'url("' . $dir . '/images',
				);
				$filecontent = strtr( $filecontent, $replace_pairs );

				/* For testing */
//				if ( false !== stripos( $filecontent, 'url("./' ) ) {
//					str_replace( 'url("./', 'url("' . $dir . '/', $filecontent );
//				}
//				if ( false !== stripos( $filecontent, 'url("../' ) ) {
//					str_replace( 'url("../', 'url("' . $parent_dir . '/', $filecontent );
//				}
//				if ( false !== stripos( $filecontent, 'url("font' ) ) {
//					str_replace( 'url("font', 'url("' . $dir . '/font', $filecontent );
//				}
//				if ( false !== stripos( $filecontent, 'url("images' ) ) {
//					str_replace( 'url("images', 'url("' . $dir . '/images', $filecontent );
//				}

				if ( UM()->options()->get( 'um_optimize_css_combine' ) ) {
					if ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ( defined( 'UM_SCRIPT_DEBUG' ) && UM_SCRIPT_DEBUG ) ) {
						$pattern = array(
							'/^\s+|\s+$/m',
							'/\/\*.*?\*\//',
							'/\/\*\*.*?\*\//s',
						);
						$filecontent = preg_replace( $pattern, '', $filecontent );
					}
				}
			}

			if ( 'js' === $ext ) {
				if ( UM()->options()->get( 'um_optimize_js_minify' ) ) {
					if ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ( defined( 'UM_SCRIPT_DEBUG' ) && UM_SCRIPT_DEBUG ) ) {
						$pattern = array(
							'/^\s+|\s+$/m',
							'/^\/\/.*$/m',
							'/\/\*.*?\*\//',
							'/\/\*\*.*?\*\//s',
						);
						$filecontent = preg_replace( $pattern, '', $filecontent );
					}
				}
			}

			return $filecontent;
		}


		/**
		 * Sets true if footer scripts are printing.
		 */
		public function set_footer() {
			$this->footer = true;
		}

	}
}