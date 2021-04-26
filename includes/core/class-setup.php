<?php
namespace um_ext\um_optimize\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class for plugin activation
 *
 * @example UM()->classes['um_optimize_setup']
 * @example UM()->Optimize()->setup()
 */
class Setup {


	/**
	 * Settings
	 *
	 * @var array
	 */
	public $settings_defaults = [];


	/**
	 * Class constructor
	 */
	function __construct() {
		$this->settings_defaults = [];
	}


	/**
	 * Set Settings
	 */
	private function set_default_settings() {
		$options = get_option( 'um_options', array() );

		foreach ( $this->settings_defaults as $key => $value ) {
			//set new options to default
			if ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}
		}

		update_option( 'um_options', $options );
	}


	/**
	 * Run on plugin activation
	 */
	public function run_setup() {
		$this->set_default_settings();
	}

}