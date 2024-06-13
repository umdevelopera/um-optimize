<?php
/**
 * Plugin Name: Ultimate Member - Optimize
 * Plugin URI:  https://github.com/umdevelopera/um-optimize
 * Description: Optimize loading for sites with the Ultimate Member plugin.
 * Author:      umdevelopera
 * Author URI:  https://github.com/umdevelopera
 * Text Domain: um-optimize
 * Domain Path: /languages
 *
 * Version: 1.1.2
 * UM version: 2.8.6
 * Requires at least: 5.5
 * Requires PHP: 5.6
 *
 * @package UM Tools
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
define( 'um_optimize_requires', '2.7.0' );


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
			UM()->set_class( 'Optimize', true );
		}
	}
}
add_action( 'plugins_loaded', 'um_optimize_check_dependencies', 2 );
