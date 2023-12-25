<?php
/**
  Plugin Name: Ultimate Member - Optimize
	Plugin URI: https://github.com/umdevelopera/um-optimize
  Description: Optimize loading of the Ultimate Member pages and resources
  Version: 1.0.0
	Author: umdevelopera
	Author URI:  https://github.com/umdevelopera
  Text Domain: um-optimize
  Domain Path: /languages
	UM version:  2.6.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_optimize_url', plugin_dir_url( __FILE__ ) );
define( 'um_optimize_path', plugin_dir_path( __FILE__ ) );
define( 'um_optimize_plugin', plugin_basename( __FILE__ ) );
define( 'um_optimize_extension', $plugin_data['Name'] );
define( 'um_optimize_version', $plugin_data['Version'] );
define( 'um_optimize_textdomain', 'um-optimize' );
define( 'um_optimize_requires', '2.6.11' );

// Activation script.
if ( ! function_exists( 'um_optimize_activation_hook' ) ) {
	function um_optimize_activation_hook() {
		$version = get_option( 'um_optimize_version' );
		if ( ! $version ) {
			update_option( 'um_optimize_last_version_upgrade', um_optimize_version );
		}
		if ( $version != um_optimize_version ) {
			update_option( 'um_optimize_version', um_optimize_version );
		}
	}
}
register_activation_hook( um_optimize_plugin, 'um_optimize_activation_hook' );

// Check dependencies.
if ( ! function_exists( 'um_optimize_check_dependencies' ) ) {
	function um_optimize_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! function_exists( 'UM' ) || ! UM()->dependencies()->ultimatemember_active_check() ) {
			// UM is not active.
			add_action(
				'admin_notices',
				function () {
					// translators: %s - plugin name.
					echo '<div class="error"><p>' . wp_kses_post( sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-optimize' ), um_optimize_extension ) ) . '</p></div>';
				}
			);
		} else {
			require_once 'includes/core/class-um-optimize.php';

			function um_optimize_init() {
				if ( function_exists( 'UM' ) ) {
					UM()->set_class( 'Optimize', true );
				}
			}
			add_action( 'plugins_loaded', 'um_optimize_init', 4, 1 );
		}
	}
}
add_action( 'plugins_loaded', 'um_optimize_check_dependencies', 2 );
