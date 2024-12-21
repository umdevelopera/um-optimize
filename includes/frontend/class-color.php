<?php
namespace um_ext\um_optimize\frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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

	const FILENAME = 'um-optimize-color-variables.css';

	const OPTIONS = array(
		'um_optimize_color_button_primary',
	);


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

		wp_register_style(
			'um-optimize-color-variables',
			$this->get_variables_src(),
			array(),
			um_optimize_version . '.' . $this->get_variables_time()
		);

		wp_register_style(
			'um-optimize-color',
			um_optimize_url . 'assets/css/um-optimize-color.css',
			array( 'um-optimize-color-variables' ),
			um_optimize_version
		);

		wp_enqueue_style( 'um-optimize-color' );
	}


	public function generate_variables_file() {
		$path = UM()->uploader()->get_upload_base_dir() . 'um_optimize/' . self::FILENAME;

		$content  = '.um {' . PHP_EOL;
		foreach( self::OPTIONS as $option ) {
			if ( UM()->options()->get( $option ) ) {
				$content .= '--' . $option . ':' . UM()->options()->get( $option ) . ';' . PHP_EOL;
			}
		}
		$content .= '}';

		$dirname = dirname( $path );
		if ( ! is_dir( $dirname ) ) {
			wp_mkdir_p( $dirname );
		}
		if ( ! file_put_contents( $path, $content ) ) {
			return false;
		}

		return $path;
	}


	public function get_variables_src() {
		$path = UM()->uploader()->get_upload_base_dir() . 'um_optimize/' . self::FILENAME;
		if ( ! file_exists( $path ) ) {
			$this->generate_variables_file();
		}
		return UM()->uploader()->get_upload_base_url() . 'um_optimize/' . self::FILENAME;
	}
	

	public function get_variables_time() {
		$path = UM()->uploader()->get_upload_base_dir() . 'um_optimize/' . self::FILENAME;
		if ( ! file_exists( $path ) ) {
			$this->generate_variables_file();
		}
		return filemtime( $path );
	}

}
