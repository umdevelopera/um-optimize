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
		add_action( 'wp_ajax_um_optimize_color_reset', array( $this, 'reset_colors' ) );

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


	/**
	 * AJAX handler for the "Reset colors" button.
	 */
	public function reset_colors() {
		check_ajax_referer( 'um-admin-nonce', 'nonce' );

		UM()->Optimize()->setup()->set_default_colors();
		UM()->Optimize()->frontend()->color()->generate_variables_file();

		$response = array(
			'message' => __( 'Colors was reset successfully', 'um-optimize' )
		);

		wp_send_json_success( $response );
	}

}
