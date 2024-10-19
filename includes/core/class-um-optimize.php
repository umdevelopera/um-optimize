<?php
/**
 * Init the extension.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UM_Optimize
 *
 * How to get an instance:
 *  UM()->classes['Optimize']
 *  UM()->Optimize()
 *
 * @package um_ext\um_optimize\core
 */
class UM_Optimize {


	/**
	 * An instance of the class.
	 *
	 * @var UM_Optimize
	 */
	private static $instance;


	/**
	 * Creates an instance of the class.
	 *
	 * @return UM_Optimize
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Class constructor
	 */
	public function __construct() {

		if( UM()->is_ajax() ) {
			$this->images();
			$this->member_directory();
			$this->query();
		} elseif ( UM()->is_request( 'admin' ) ) {
			$this->admin();
		} elseif ( UM()->is_request( 'frontend' ) ) {
			$this->assets();
			$this->images();
			$this->query();
		}

		// Extensions.
		if ( defined( 'um_activity_version' ) ) {
			require_once um_optimize_path . 'includes/extensions/activity.php';
		}
		if ( defined( 'um_groups_version' ) ) {
			require_once um_optimize_path . 'includes/extensions/groups.php';
		}
		if ( defined( 'um_user_notes_version' ) ) {
			require_once um_optimize_path . 'includes/extensions/user_notes.php';
		}
		if ( defined( 'um_user_photos_version' ) ) {
			require_once um_optimize_path . 'includes/extensions/user_photos.php';
		}

		// Scheduled_events.
		add_action( 'um_twicedaily_scheduled_events', array( $this, 'clear_files' ), 20 );
	}


	/**
	 * Admin features.
	 *
	 * @return um_ext\um_optimize\admin\Admin()
	 */
	public function admin() {
		if ( empty( UM()->classes['um_optimize_admin'] ) ) {
			require_once um_optimize_path . 'includes/admin/class-admin.php';
			UM()->classes['um_optimize_admin'] = new um_ext\um_optimize\admin\Admin();
		}
		return UM()->classes['um_optimize_admin'];
	}


	/**
	 * Optimize assets.
	 *
	 * @return um_ext\um_optimize\core\Assets()
	 */
	public function assets() {
		if ( empty( UM()->classes['um_optimize_assets'] ) ) {
			require_once um_optimize_path . 'includes/core/class-assets.php';
			UM()->classes['um_optimize_assets'] = new um_ext\um_optimize\core\Assets();
		}
		return UM()->classes['um_optimize_assets'];
	}


	/**
	 * Optimize images.
	 *
	 * @return um_ext\um_optimize\core\Images()
	 */
	public function images() {
		if ( empty( UM()->classes['um_optimize_images'] ) ) {
			require_once um_optimize_path . 'includes/core/class-images.php';
			UM()->classes['um_optimize_images'] = new um_ext\um_optimize\core\Images();
		}
		return UM()->classes['um_optimize_images'];
	}


	/**
	 * Optimize member directories.
	 *
	 * @return um_ext\um_optimize\core\Member_Directory()
	 */
	public function member_directory() {
		if ( empty( UM()->classes['um_optimize_member_directory'] ) ) {
			require_once um_optimize_path . 'includes/core/class-member-directory.php';
			UM()->classes['um_optimize_member_directory'] = new um_ext\um_optimize\core\Member_Directory();
		}
		return UM()->classes['um_optimize_member_directory'];
	}


	/**
	 * Optimize queries.
	 *
	 * @return um_ext\um_optimize\core\Query()
	 */
	public function query() {
		if ( empty( UM()->classes['um_optimize_query'] ) ) {
			require_once um_optimize_path . 'includes/core/class-query.php';
			UM()->classes['um_optimize_query'] = new um_ext\um_optimize\core\Query();
		}
		return UM()->classes['um_optimize_query'];
	}


	/**
	 * Remove outdated combined files.
	 *
	 * @return int Number of removed files.
	 */
	public function clear_files() {
		$i   = 0;
		$dir = wp_normalize_path( UM()->uploader()->get_upload_base_dir() . 'um_optimize/' );

		if ( is_dir( $dir ) ) {
			$files = scandir( $dir );
			foreach( $files as $file ) {
				if ( is_file( "$dir/$file" ) && FALSE !== filemtime( "$dir/$file" ) && ( time() - filemtime( "$dir/$file" ) > DAY_IN_SECONDS ) ) {
					unlink( "$dir/$file" );
					$i++;
				}
			}
		}
		return $i;
	}

}
