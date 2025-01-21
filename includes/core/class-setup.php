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
		'um_optimize_color_common_light_line'      => '#eeeeee',
		'um_optimize_color_common_line'            => '#dddddd',
		'um_optimize_color_common_light_text'      => '#999999',
		'um_optimize_color_common_text'            => '#444444',
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
		'um_optimize_color_field_background_item'  => '#eeeeee',
		'um_optimize_color_field_border'           => '#dddddd',
		'um_optimize_color_field_label'            => '#555555',
		'um_optimize_color_field_placeholder'      => '#999999',
		'um_optimize_color_field_text'             => '#666666',
		'um_optimize_color_menu_active'            => '#3ba1da',
		'um_optimize_color_menu_background'        => '#444444',
		'um_optimize_color_menu_hover'             => '#555555',
		'um_optimize_color_menu_text'              => '#ffffff',
	);

	const FILENAME = 'um-optimize-color-variables.css';

	/**
	 * CSS selectors used as a scope for the color variables.
	 *
	 * @var array
	 */
	protected $scopes = array(
		'.um',
		'.um-modal',
		'.um-popup',
		'.um-is-loaded',
		'.um-new-dropdown',
		'.um-activity-confirm',
		'.um-groups-widget',
		'.um-notes-holder',
		'.um-notification-shortcode',
		'.um-reviews-widget',
		'.um-search-form',
		'.um-user-bookmarks-modal',
		'.um-user-photos-add',
		'.um-user-photos-albums',
		'.um-user-photos-modal',
		'.um-user-photos-widget',
		'.um-user-tags-wdgt',
	);


	/**
	 * Default settings.
	 *
	 * @var array
	 */
	protected $settings_defaults = array(
		'um_optimize_css_dequeue'      => 1,
		'um_optimize_js_dequeue'       => 1,
		'um_optimize_css_combine'      => 0,
		'um_optimize_js_combine'       => 0,
		'um_optimize_profile_photo'    => 1,
		'um_optimize_cover_photo'      => 1,
		'um_optimize_cover_photo_size' => '',
		'um_optimize_members'          => 0,
		'um_optimize_activity'         => 0,
		'um_optimize_groups'           => 0,
		'um_optimize_notes'            => 0,
		'um_optimize_photos'           => 0,
		'um_optimize_reviews'          => 0,
		'um_optimize_color'            => 0,
	);


	/**
	 * Generate CSS file with variables.
	 *
	 * @return boolean This function returns the path to the file, or FALSE on failure.
	 */
	public function generate_variables_file() {
		$path   = UM()->uploader()->get_upload_base_dir() . 'um_optimize/' . self::FILENAME;
		$colors = $this->get_default_colors();
		$scopes = apply_filters( 'um_optimize_color_scopes', $this->scopes );

		$content =  implode( ',', $scopes ) . '{' . PHP_EOL;
		foreach( $colors as $option => $value ) {
			if ( UM()->options()->get( $option ) ) {
				$val = sanitize_hex_color( UM()->options()->get( $option ) );
				$var = strtr(
					$option,
					array(
						'um_optimize_color' => 'color',
						'common_'           => '',
						'button'            => 'btn',
						'primary'           => 'pr',
						'secondary'         => 'sc',
						'_'                 => '-',
					)
				);
				$content .= '--' . $var . ':' . $val . ';' . PHP_EOL;
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


	/**
	 * Get an URL to the CSS file with variables.
	 *
	 * @return string This function returns an URL to the file.
	 */
	public function get_variables_file_url() {
		$this->get_variables_file_path();
		return UM()->uploader()->get_upload_base_url() . 'um_optimize/' . self::FILENAME;
	}


	/**
	 * Get a path to the CSS file with variables.
	 *
	 * @return string This function returns a path to the file.
	 */
	public function get_variables_file_path() {
		$path = UM()->uploader()->get_upload_base_dir() . 'um_optimize/' . self::FILENAME;
		if ( ! file_exists( $path ) ) {
			UM()->Optimize()->setup()->generate_variables_file();
		}
		return $path;
	}


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
		$this->set_default_settings();
		if ( ! get_option( 'um_optimize_color' ) ) {
			$this->set_default_colors();
		}
	}

}
