<?php

/*
  Plugin Name: Ultimate Member - Optimize
  Description: Optimize loading of the Ultimate Member pages and resources
  Version: 1.1.0
  Author: Ultimate Member
  Author URI: http://ultimatemember.com/
  Text Domain: um-optimize
  Domain Path: /languages
 */

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_optimize_url', plugin_dir_url( __FILE__ ) );
define( 'um_optimize_path', plugin_dir_path( __FILE__ ) );
define( 'um_optimize_plugin', plugin_basename( __FILE__ ) );
define( 'um_optimize_extension', $plugin_data['Name'] );
define( 'um_optimize_version', $plugin_data['Version'] );
define( 'um_optimize_textdomain', 'um-optimize' );
define( 'um_optimize_requires', '2.1.19' );

/* Check dependencies and run */

add_action( 'plugins_loaded', 'um_optimize_check_dependencies', -20 );

if ( !function_exists( 'um_optimize_check_dependencies' ) ) {

	function um_optimize_check_dependencies() {

		if ( !defined( 'um_path' ) || !file_exists( um_path . 'includes/class-dependencies.php' ) ) { //UM is not installed
			$error_message = sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-optimize' ), um_optimize_extension );
		} else {

			require_once um_path . 'includes/class-dependencies.php';
			$is_um_active = um\is_um_active();

			if ( !$is_um_active ) { //UM is not active
				$error_message = sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-optimize' ), um_optimize_extension );
			} else {

				$compare_versions = UM()->dependencies()->compare_versions( um_optimize_requires, um_optimize_version, 'optimize', um_optimize_extension );


				// TEST
				$compare_versions = true;
				// TEST
				

				if ( true !== $compare_versions ) { //UM old version is active
					$error_message = $compare_versions;
				}
			}
		}

		if ( !empty( $error_message ) ) {
			add_action( 'admin_notices', function () use ( $error_message ) {
				echo '<div class="error"><p>' . $error_message . '</p></div>';
			} );
		} else {
			require_once um_optimize_path . 'includes/core/um-optimize-init.php';
		}
	}

}

/* Activation */

register_activation_hook( um_optimize_plugin, 'um_optimize_activation_hook' );

if ( !function_exists( 'um_optimize_activation_hook' ) ) {

	function um_optimize_activation_hook() {
		//first install
		$version = get_option( 'um_optimize_version' );
		if ( !$version ) {
			update_option( 'um_optimize_last_version_upgrade', um_optimize_version );
		}

		if ( $version != um_optimize_version ) {
			update_option( 'um_optimize_version', um_optimize_version );
		}

		//run setup
		if ( !class_exists( 'um_ext\um_optimize\core\Setup' ) ) {
			require_once um_optimize_path . 'includes/core/class-setup.php';
		}

		$optimize_setup = new um_ext\um_optimize\core\Setup();
		$optimize_setup->run_setup();
	}

}