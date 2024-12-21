<?php
namespace um_ext\um_optimize\frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( class_exists( 'um_ext\um_optimize\frontend\Assets' ) ) {
	return;
}

/**
 * Optimize assets.
 *
 * Get an instance this way: UM()->Optimize()->frontend()->assets()
 *
 * @package um_ext\um_optimize\frontend
 */
class Assets {

	/**
	 * Files extension. Accepts 'css', 'js'.
	 *
	 * @var string
	 */
	protected $ext;

	/**
	 * Set true if footer scripts are printing.
	 *
	 * @var bool
	 */
	protected $footer = false;

	/**
	 * An array of handles of dependencies already queued.
	 *
	 * @var string[]
	 */
	public $done = array();

	/**
	 * An array of handles of queued dependencies.
	 *
	 * @var string[]
	 */
	public $queue = array();

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
	 * An array of handles of queued UM dependencies.
	 *
	 * @var array
	 */
	private $um_dependencies = array();

	/**
	 * Dependencies API. Accepts WP_Scripts, WP_Styles.
	 *
	 * @var \WP_Dependencies
	 */
	private $wp_dependencies;


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
		if ( $ext && in_array( $ext, array( 'css', 'js' ), true ) ) {
			$this->ext = $ext;
		}

		if ( 'css' === $this->ext ) {
			$this->wp_dependencies = wp_styles();
		} elseif ( 'js' === $this->ext ) {
			$this->wp_dependencies = wp_scripts();
		} else {
			return false;
		}

		$um_dependencies = $this->get_um_dependencies( $this->wp_dependencies->queue, $ext, (int) $this->footer );

		if ( empty( $this->done[$this->ext] ) ) {
			$this->done[$this->ext] = array();
		}
		if ( empty( $this->queue[$this->ext] ) ) {
			$this->queue[$this->ext] = array();
		}
		$this->queue[$this->ext] = array_merge( $this->queue[$this->ext], array_keys( $um_dependencies ) );

		// Exclude files that have already been combined.
		$um_dependencies_new = array_diff_key( $um_dependencies, array_flip( $this->done[$this->ext] ) );

		$combined = $this->get_combined_file( $um_dependencies_new, $ext );
		if ( $combined && is_array( $combined ) ) {
			$this->wp_dependencies->add( $combined['handle'], $combined['src'], $combined['deps'] );
			$this->wp_dependencies->add_data( $combined['handle'], 'data', $combined['data'] );
			$this->wp_dependencies->add_data( $combined['handle'], 'group', $this->footer );
			$this->wp_dependencies->enqueue( $combined['handle'] );
			$this->wp_dependencies->dequeue( array_keys( $um_dependencies_new ) );
			$this->done[$this->ext] = array_merge( $this->done[$this->ext], array_keys( $um_dependencies_new ) );
		}
	}


	/**
	 * Dequeue Ultimate Member CSS and JS files
	 *
	 * @param string $ext Files extension. Accepts 'css', 'js'.
	 */
	public function dequeue_assets( $ext ) {
		if ( $ext && in_array( $ext, array( 'css', 'js' ), true ) ) {
			$this->ext = $ext;
		}

		if ( 'css' === $this->ext ) {
			$this->wp_dependencies = wp_styles();
		} elseif ( 'js' === $this->ext ) {
			$this->wp_dependencies = wp_scripts();
		} else {
			return false;
		}
		if ( empty( $this->wp_dependencies->queue ) ) {
			return false;
		}

		$not_dequeue_def = array(
			'um_notifications',
		);

		/**
		 * Hook: um_optimize_not_dequeue
		 *
		 * Type: filter
		 *
		 * Description: Extends an array of assets that should not be dequeued.
		 *
		 * @since 1.1.3
		 *
		 * @param {array}  $not_dequeue_def An array of assets that should not be dequeued.
		 * @param {string} $ext             A type of assets: css or js.
		 */
		$not_dequeue = apply_filters( 'um_optimize_not_dequeue', $not_dequeue_def, $ext );

		foreach ( $this->wp_dependencies->queue as $handle ) {
			if ( in_array( $handle, $not_dequeue, true ) ) {
				continue;
			}
			if ( $this->is_ultimatemember_file( $this->wp_dependencies->registered[$handle] ) ) {
				$this->wp_dependencies->dequeue( $handle );
			}
		}
	}


	/**
	 * Get or create combined file.
	 *
	 * @param array  $um_dependencies A collection of _WP_Dependency.
	 * @param string $ext       Files extension. Accepts 'css', 'js'.
	 *
	 * @return array|boolean Information about combined field.
	 */
	public function get_combined_file( $um_dependencies, $ext = '' ) {
		if ( $ext && in_array( $ext, array( 'css', 'js' ), true ) ) {
			$this->ext = $ext;
		}

		// Exclude files that have already been combined.
		if ( ! empty( $this->done[$this->ext] ) ) {
			$um_dependencies = array_diff_key( (array) $um_dependencies, (array) $this->done[$this->ext] );
		}
		if ( empty( $um_dependencies ) ) {
			return false;
		}

		if ( 'css' === $this->ext ) {
			$handle = 'um-combined-styles';
		} elseif ( 'js' === $this->ext ) {
			$handle = 'um-combined-scripts';
		} else {
			return false;
		}
		if ( $this->footer ) {
			$handle .= '-footer';
		}

		$data	 = array();
		$deps	 = array();
		$ver_	 = array();

		foreach ( $um_dependencies as $dependency ) {
			if ( ! empty( $dependency->extra['data'] ) ) {
				$data[] = $dependency->extra['data'];
			}
			if ( ! empty( $dependency->deps ) ) {
				$deps = array_merge( $deps, (array) $dependency->deps );
			}
			$ver_[] = $dependency->handle . $dependency->ver;
		}

		sort( $ver_ );
		$version = md5( implode( ',', $ver_ ) );
		$name		 = $handle . '-' . $version . '.' . $this->ext;

		$path	 = UM()->uploader()->get_upload_base_dir() . 'um_optimize/' . $name;
		$src	 = UM()->uploader()->get_upload_base_url() . 'um_optimize/' . $name;

		if ( ! file_exists( $path ) ) {
			$baseurl	 = trailingslashit( site_url() );
			$basepath	 = wp_normalize_path( ABSPATH );

			$content = array();
			foreach ( $um_dependencies as $dependency ) {
				$filename		 = str_replace( $baseurl, $basepath, $dependency->src );
				$filecontent = file_get_contents( $filename );
				if ( FALSE !== $filecontent ) {
					$content[] = '/* File: ' . basename( $filename ) . ' */';
					$content[] = $this->optimize_file_content( $filecontent, $dependency );
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
			$deps = array_unique( array_diff( $deps, array_keys( $um_dependencies ), array_keys( $this->um_dependencies ) ) );
		}
		if ( $data ) {
			$data = implode( PHP_EOL, array_filter( $data ) );
		}

		return compact( 'deps', 'data', 'handle', 'path', 'src', 'version' );
	}


	/**
	 * Get UM dependencies by queued dependencies.
	 *
	 * @since 1.1.1
	 *
	 * @param array  $deps_list An array of handles of queued dependencies.
	 * @param string $ext       Files extension. Accepts 'css', 'js'.
	 * @param int|false $group  Optional. Group level: level (int), no groups (false). Default false.
	 *
	 * @return array            An array of handles of queued UM dependencies.
	 */
	public function get_um_dependencies( $deps_list, $ext = '', $group = false ) {
		if ( $ext && in_array( $ext, array( 'css', 'js' ), true ) ) {
			$this->ext = $ext;
		}

		if ( empty( $this->um_dependencies[$this->ext] ) ) {
			$this->um_dependencies[$this->ext] = array();
		}

		if ( is_array( $deps_list ) ) {
			foreach ( $deps_list as $handle ) {
				$dependency = $this->wp_dependencies->registered[$handle];
				if ( empty( $dependency->src ) ) {
					continue;
				}
				if ( $this->is_ultimatemember_file( $dependency->src ) ) {
					if ( ! empty( $dependency->deps ) && is_array( $dependency->deps ) ) {
						$this->get_um_dependencies( $dependency->deps, $ext );
					}
					if ( ! array_key_exists( $handle, $this->um_dependencies[$this->ext] ) ) {
						$this->um_dependencies[$this->ext][$handle] = $dependency;
					}
				}
			}
		}

		if ( false === $group ) {
			return $this->um_dependencies[$this->ext];
		} elseif ( empty( $group ) ) {
			$um_dependencies = array();
			foreach ( $this->um_dependencies[$this->ext] as $handle => $dependency ) {
				if ( empty( $this->wp_dependencies->get_data( $handle, 'group' ) ) ) {
					$um_dependencies[$handle] = $dependency;
				}
			}
			return $um_dependencies;
		} else {
			$um_dependencies = array();
			foreach ( $this->um_dependencies[$this->ext] as $handle => $dependency ) {
				if ( $group === $this->wp_dependencies->get_data( $handle, 'group' ) ) {
					$um_dependencies[$handle] = $dependency;
				}
			}
			return $um_dependencies;
		}
	}


	/**
	 * Get an array of post types added by Ultimate Member and its extensions
	 *
	 * @since 1.1.0
	 *
	 * @return array A collection of Ultimate Member post types.
	 */
	public function get_um_post_types() {
		$um_post_types = array(
			'um_directory',
			'um_form',
			'um_role',
			'um_account_tabs',
			'um_activity',
			'um_groups',
			'um_groups_discussion',
			'um_mailchimp',
			'um_notice',
			'um_private_content',
			'um_profile_tabs',
			'um_review',
			'um_social_login',
			'um_notes',
			'um_user_photos',
		);
		return apply_filters( 'um_optimize_post_types', $um_post_types );
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

		$um_pages			 = UM()->config()->permalinks;
		$um_post_types = $this->get_um_post_types();

		if ( is_singular() ) {
			$post = get_post();
			if ( in_array( $post->ID, $um_pages, true ) ) {
				return true;
			}
			if ( in_array( $post->post_type, $um_post_types, true ) ) {
				return true;
			}
			foreach ( $this->get_um_shortcodes() as $shortcode ) {
				if ( strpos( $post->post_content, '[' . $shortcode ) !== FALSE ) {
					$this->has_um_shortcode = true;
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

		$extensions = array(
			'account-tabs' => '1.1.4',
			'debug'        => '1.5.1',
			'optimize'     => '1.3.0',
		);
		if ( isset( UM()->dependencies()->ext_required_version ) ) {
			$extensions = array_merge( $extensions, (array) UM()->dependencies()->ext_required_version );
		}
		foreach ( $extensions as $extension => $version ) {
			if ( false !== strpos( $src, "/plugins/um-$extension/assets/" ) ) {
				return true;
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

		if ( ! $this->is_ultimatemember() && UM()->options()->get( 'um_optimize_js_dequeue' ) ) {
			$this->dequeue_assets( 'js' );
		} elseif ( UM()->options()->get( 'um_optimize_js_combine' ) ) {
			$this->combine_assets( 'js' );
		}
	}


	/**
	 * Modify file content before combining
	 *
	 * @param string         $filecontent File content.
	 * @param _WP_Dependency $dependency
	 *
	 * @return string File content.
	 */
	public function optimize_file_content( $filecontent, $dependency ) {

		if ( 'css' === $this->ext ) {
			$dir				 = dirname( $dependency->src );
			$parent_dir	 = dirname( $dir );

			$replace_pairs = array(
				'url("./'			 => 'url("' . $dir . '/',
				'url("../'		 => 'url("' . $parent_dir . '/',
				'url("font'		 => 'url("' . $dir . '/font',
				'url("images'	 => 'url("' . $dir . '/images',
				'url(./'			 => 'url(' . $dir . '/',
				'url(../'			 => 'url(' . $parent_dir . '/',
				'url(font'		 => 'url(' . $dir . '/font',
				'url(images'	 => 'url(' . $dir . '/images',
			);
			$filecontent	 = strtr( $filecontent, $replace_pairs );

			if ( apply_filters( 'um_optimize_minify_css', true ) ) {
				if ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ( defined( 'UM_SCRIPT_DEBUG' ) && UM_SCRIPT_DEBUG ) ) {
					$pattern		 = array(
						'/^\s+|\s+$/m',
						'/\/\*.*?\*\//',
						'/\/\*\*.*?\*\//s',
					);
					$filecontent = preg_replace( $pattern, '', $filecontent );
				}
			}
		}

		if ( 'js' === $this->ext ) {
			if ( apply_filters( 'um_optimize_minify_js', true ) ) {
				if ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ( defined( 'UM_SCRIPT_DEBUG' ) && UM_SCRIPT_DEBUG ) ) {
					$pattern		 = array(
						'/^\s+|\s+$/m',
						'/^\/\/.*$/m',
						'/\/\*\*.*?\*\//s',
					);
					$filecontent = preg_replace( $pattern, '', $filecontent );
				}
			}
		}

		return apply_filters( 'um_optimize_file_content', $filecontent, $dependency );
	}


	/**
	 * Sets true if footer scripts are printing.
	 */
	public function set_footer() {
		$this->footer = true;
	}

}
