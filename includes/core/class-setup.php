<?php
namespace um_ext\um_optimize\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Actions on installation.
 *
 * Get an instance this way: UM()->Optimize()->setup()
 *
 * @package um_ext\um_optimize\core
 */
class Setup {

	const COLORS = array(
		'um_optimize_color_common_active'          => '#3ba1da',
		'um_optimize_color_common_active_text'     => '#ffffff',
		'um_optimize_color_common_background'      => '#ffffff',
		'um_optimize_color_common_line'            => '#ddd',
		'um_optimize_color_common_light_line'      => '#eee',
		'um_optimize_color_common_text'            => '#444',
		'um_optimize_color_common_light_text'      => '#999',

		'um_optimize_color_link'                   => '#3ba1da',
		'um_optimize_color_link_hover'             => '#44b0ec',
		'um_optimize_color_button_primary'         => '#3ba1da',
		'um_optimize_color_button_primary_hover'   => '#44b0ec',
		'um_optimize_color_button_primary_text'    => '#ffffff',
		'um_optimize_color_button_secondary'       => '#eeeeee',
		'um_optimize_color_button_secondary_hover' => '#e5e5e5',
		'um_optimize_color_button_secondary_text'  => '#666666',

		'um_optimize_color_field_active'           => '#3ba1da',
		'um_optimize_color_field_background'       => '#ffffff',
		'um_optimize_color_field_border'           => '#d0d0d0',
		'um_optimize_color_field_label'            => '#555555',
		'um_optimize_color_field_placeholder'      => '#909090',
		'um_optimize_color_field_text'             => '#666666',
	);

	/**
	 * Default settings.
	 * @var array
	 */
	public $settings_defaults;


	/**
	 * Get default colors.
	 */
	public function get_default_colors() {
		return self::COLORS;
	}


	/**
	 * Set default colors.
	 */
	public function set_default_colors() {
		$options_old = get_option( 'um_options', array() );
		$options_new = array_replace( $options_old, self::COLORS );
		update_option( 'um_options', $options_new );
		update_option( 'um_optimize_color', wp_date( 'Y-m-d' ) );
	}


	/**
	 * Set default settings.
	 */
	public function set_default_settings() {
		$options = get_option( 'um_options', array() );

		foreach ( $this->settings_defaults as $key => $value ) {
			if ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}
		}

		update_option( 'um_options', $options );
	}


	/**
	 * Run on activation.
	 */
	public function run() {
		if ( ! get_option( 'um_optimize_color' ) ) {
			$this->set_default_colors();
		}
	}

}
