<?php
namespace um_ext\um_optimize\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( class_exists( 'um_ext\um_optimize\admin\Settings_Color' ) ) {
	return;
}

/**
 * Extends settings.
 * Adds the "Color" tab to wp-admin > Ultimate Member > Settings > Appearance.
 *
 * Get an instance this way: UM()->Optimize()->admin()->settings_color()
 *
 * @package um_ext\um_optimize\admin
 */
class Settings_Color {


	/**
	 * Admin constructor.
	 */
	public function __construct() {
		add_filter( 'um_settings_structure', array( $this, 'extend_settings' ) );
	}


	/**
	 * Add fields to the page
	 *
	 * @param  array $settings
	 * @return array
	 */
	public function extend_settings( $settings ) {

		$sections = array(
			$this->settings_section(),
			$this->settings_section_button(),
		);

		$settings['appearance']['sections']['color'] = array(
			'title'         => __( 'Colors', 'um-optimize' ),
			'form_sections' => $sections,
		);

		return $settings;
	}


	/**
	 * Section "Colors".
	 *
	 * @return array
	 */
	public function settings_section() {

		$fields = array(
			array(
				'id'          => 'um_optimize_color',
				'type'        => 'checkbox',
				'label'       => __( 'Customize colors', 'um-optimize' ),
				'description' => __( 'I wish to customize Ultimate Member colors.', 'um-optimize' ),
			),
		);

		return array(
			'title'       => __( 'Colors', 'um-optimize' ),
			'description' => __( 'You can use settings below to override default Ultimate Member colors.', 'um-optimize' ),
			'fields'      => $fields,
		);
	}


	/**
	 * Section "Buttons".
	 *
	 * @return array
	 */
	public function settings_section_button() {

		$fields = array(
			array(
				'id'    => 'um_optimize_color_button_primary',
				'type'  => 'color',
				'label' => __( 'Primary button', 'um-optimize' ),
			),
		);

		return array(
			'title'  => __( 'Buttons', 'um-optimize' ),
			'fields' => $fields,
		);
	}

}
