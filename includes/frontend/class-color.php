<?php
/**
 * Customize Ultimate Member colors.
 *
 * @package um_ext\um_optimize\frontend
 */

namespace um_ext\um_optimize\frontend;

defined( 'ABSPATH' ) || exit;
if ( class_exists( 'um_ext\um_optimize\frontend\Color' ) ) {
	return;
}

/**
 * Front-end features.
 *
 * Get an instance this way: UM()->Optimize()->frontend()->color()
 *
 * @package um_ext\um_optimize\frontend
 */
class Color {


	/**
	 * Class constructor.
	 */
	public function __construct() {

		// scripts & styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 5 );
	}


	/**
	 * Enqueue styles.
	 */
	public function enqueue() {
		if ( ! UM()->options()->get( 'um_optimize_color' ) ) {
			return;
		}

		// TESTING.
//		if ( defined( 'UM_SCRIPT_DEBUG') && UM_SCRIPT_DEBUG && WP_DEBUG && 29 === get_current_user_id() ) {
//			return;
//		}

		$enqueue = UM()->frontend()->enqueue();
		$suffix  = $enqueue::get_suffix();

		$path = UM()->Optimize()->setup()->get_variables_file_path();
		$src  = UM()->Optimize()->setup()->get_variables_file_url();

		wp_register_style(
			'um-optimize-color-variables',
			$src,
			array(),
			UM_OPTIMIZE_VERSION . '.' . filemtime( $path )
		);

		wp_register_style(
			'um-optimize-color',
			UM_OPTIMIZE_URL . 'assets/css/um-optimize-color' . $suffix . '.css',
			array( 'um-optimize-color-variables' ),
			UM_OPTIMIZE_VERSION
		);

		wp_enqueue_style( 'um-optimize-color' );
	}
}
