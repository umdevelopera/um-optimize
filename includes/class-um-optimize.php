<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UM_Optimize inits the extension.
 *
 * Get an instance this way: UM()->Optimize()
 *
 * @package um_ext\um_optimize
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

		if ( UM()->is_ajax() ) {
			$this->ajax();
			$this->images();
			$this->query();
		} elseif ( UM()->is_request( 'admin' ) ) {
			$this->admin();
		} elseif ( ! is_admin() && ! is_login() ) {
			$this->frontend();
			$this->images();
			$this->query();
		}

		// integrations.
		$this->integrations();

		// Scheduled_events.
		add_action( 'um_twicedaily_scheduled_events', array( $this, 'clear_files' ), 20 );
	}


	/**
	 * Admin features.
	 *
	 * @return um_ext\um_optimize\admin\Init
	 */
	public function admin() {
		if ( empty( UM()->classes['um_optimize_admin'] ) ) {
			require_once um_optimize_path . 'includes/admin/class-init.php';
			UM()->classes['um_optimize_admin'] = new um_ext\um_optimize\admin\Init();
		}
		return UM()->classes['um_optimize_admin'];
	}


	/**
	 * AJAX handlers.
	 *
	 * @return um_ext\um_optimize\common\AJAX
	 */
	public function ajax() {
		if ( empty( UM()->classes['um_optimize_ajax'] ) ) {
			require_once um_optimize_path . 'includes/common/class-ajax.php';
			UM()->classes['um_optimize_ajax'] = new um_ext\um_optimize\common\AJAX();
		}
		return UM()->classes['um_optimize_ajax'];
	}


	/**
	 * Front-end features.
	 *
	 * @return um_ext\um_optimize\frontend\Init
	 */
	public function frontend() {
		if ( empty( UM()->classes['um_optimize_frontend'] ) ) {
			require_once um_optimize_path . 'includes/frontend/class-init.php';
			UM()->classes['um_optimize_frontend'] = new um_ext\um_optimize\frontend\Init();
		}
		return UM()->classes['um_optimize_frontend'];
	}


	/**
	 * Optimize images.
	 *
	 * @return um_ext\um_optimize\common\Images
	 */
	public function images() {
		if ( empty( UM()->classes['um_optimize_images'] ) ) {
			require_once um_optimize_path . 'includes/common/class-images.php';
			UM()->classes['um_optimize_images'] = new um_ext\um_optimize\common\Images();
		}
		return UM()->classes['um_optimize_images'];
	}


	/**
	 * Integration with other extensions.
	 *
	 * @return um_ext\um_optimize\integrations\Init
	 */
	public function integrations() {
		if ( empty( UM()->classes['um_optimize_integrations'] ) ) {
			require_once um_optimize_path . 'includes/integrations/class-init.php';
			UM()->classes['um_optimize_integrations'] = new um_ext\um_optimize\integrations\Init();
		}
		return UM()->classes['um_optimize_integrations'];
	}


	/**
	 * Optimize queries.
	 *
	 * @return um_ext\um_optimize\common\Query
	 */
	public function query() {
		if ( empty( UM()->classes['um_optimize_query'] ) ) {
			require_once um_optimize_path . 'includes/common/class-query.php';
			UM()->classes['um_optimize_query'] = new um_ext\um_optimize\common\Query();
		}
		return UM()->classes['um_optimize_query'];
	}


	/**
	 * Actions on installation.
	 *
	 * @return um_ext\um_optimize\common\Setup
	 */
	public function setup() {
		if ( empty( UM()->classes['um_optimize_setup'] ) ) {
			require_once um_optimize_path . 'includes/common/class-setup.php';
			UM()->classes['um_optimize_setup'] = new um_ext\um_optimize\common\Setup();
		}
		return UM()->classes['um_optimize_setup'];
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
