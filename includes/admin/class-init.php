<?php
namespace um_ext\um_optimize\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( class_exists( 'um_ext\um_optimize\admin\Init' ) ) {
	return;
}

/**
 * Admin features.
 *
 * Get an instance this way: UM()->Optimize()->admin()
 *
 * @package um_ext\um_optimize\admin
 */
class Init {


	/**
	 * Admin constructor.
	 */
	public function __construct() {
		$this->settings_color();
		$this->settings_optimize();
	}


	/**
	 * Extends settings.
	 * Adds the "Color" tab.
	 *
	 * @return um_ext\um_optimize\admin\Settings_Color
	 */
	public function settings_color() {
		if ( empty( UM()->classes['um_optimize_settings_color'] ) ) {
			require_once um_optimize_path . 'includes/admin/class-settings-color.php';
			UM()->classes['um_optimize_settings_color'] = new Settings_Color();
		}
		return UM()->classes['um_optimize_settings_color'];
	}


	/**
	 * Extends settings.
	 * Adds the "Optimize" tab.
	 *
	 * @return um_ext\um_optimize\admin\Settings_Optimize
	 */
	public function settings_optimize() {
		if ( empty( UM()->classes['um_optimize_settings_optimize'] ) ) {
			require_once um_optimize_path . 'includes/admin/class-settings-optimize.php';
			UM()->classes['um_optimize_settings_optimize'] = new Settings_Optimize();
		}
		return UM()->classes['um_optimize_settings_optimize'];
	}

}
