<?php
namespace um_ext\um_optimize\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( class_exists( 'um_ext\um_optimize\core\AJAX' ) ) {
	return;
}

/**
 * AJAX handlers.
 *
 * Get an instance this way: UM()->Optimize()->ajax()
 *
 * @package um_ext\um_optimize\core
 */
class AJAX {


	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->member_directory();
	}


	/**
	 * Optimize member directories.
	 *
	 * @return \um_ext\um_optimize\core\Member_Directory
	 */
	public function member_directory() {
		if ( empty( UM()->classes['um_optimize_member_directory'] ) ) {
			require_once um_optimize_path . 'includes/core/class-member-directory.php';
			UM()->classes['um_optimize_member_directory'] = new Member_Directory();
		}
		return UM()->classes['um_optimize_member_directory'];
	}

}
