<?php
/**
 * Front-end features.
 *
 * @package um_ext\um_optimize\frontend
 */

namespace um_ext\um_optimize\frontend;

defined( 'ABSPATH' ) || exit;
if ( class_exists( 'um_ext\um_optimize\frontend\Init' ) ) {
	return;
}

/**
 * Front-end features.
 *
 * Get an instance this way: UM()->Optimize()->frontend()
 *
 * @package um_ext\um_optimize\frontend
 */
class Init {


	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->assets();
		$this->color();
		$this->profile();
	}


	/**
	 * Optimize assets.
	 *
	 * @return \um_ext\um_optimize\frontend\Assets
	 */
	public function assets() {
		if ( empty( UM()->classes['um_optimize_assets'] ) ) {
			require_once UM_OPTIMIZE_PATH . 'includes/frontend/class-assets.php';
			UM()->classes['um_optimize_assets'] = new Assets();
		}
		return UM()->classes['um_optimize_assets'];
	}


	/**
	 * Customize colors.
	 *
	 * @return \um_ext\um_optimize\frontend\Color
	 */
	public function color() {
		if ( empty( UM()->classes['um_optimize_frontend_color'] ) ) {
			require_once UM_OPTIMIZE_PATH . 'includes/frontend/class-color.php';
			UM()->classes['um_optimize_frontend_color'] = new Color();
		}
		return UM()->classes['um_optimize_frontend_color'];
	}


	/**
	 * Speed up profiles.
	 *
	 * @return \um_ext\um_optimize\frontend\Profile
	 */
	public function profile() {
		if ( empty( UM()->classes['um_optimize_frontend_profile'] ) ) {
			require_once UM_OPTIMIZE_PATH . 'includes/frontend/class-profile.php';
			UM()->classes['um_optimize_frontend_profile'] = new Profile();
		}
		return UM()->classes['um_optimize_frontend_profile'];
	}
}
