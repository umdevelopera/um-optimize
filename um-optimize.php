<?php
/**
 * Plugin Name: Ultimate Member - Optimize and Color
 * Plugin URI:  https://github.com/umdevelopera/um-optimize
 * Description: Improves the performance of sites with Ultimate Member. Customize Ultimate Member colors.
 * Author:      umdevelopera
 * Author URI:  https://github.com/umdevelopera
 * Text Domain: um-optimize
 * Domain Path: /languages
 *
 * Requires Plugins: ultimate-member
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * UM version: 2.11.2
 * Version: 1.3.5
 *
 * @package um_ext\um_optimize
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';

$plugin_data = get_plugin_data( __FILE__, true, false );

define( 'um_optimize_url', plugin_dir_url( __FILE__ ) );
define( 'um_optimize_path', plugin_dir_path( __FILE__ ) );
define( 'um_optimize_plugin', plugin_basename( __FILE__ ) );
define( 'um_optimize_extension', $plugin_data['Name'] );
define( 'um_optimize_version', $plugin_data['Version'] );
define( 'um_optimize_textdomain', 'um-optimize' );


// Activation script.
if ( ! function_exists( 'um_optimize_activation_hook' ) ) {
	function um_optimize_activation_hook() {
		require_once 'includes/core/class-setup.php';
		if ( class_exists( 'um_ext\um_optimize\core\Setup' ) && function_exists( 'UM' ) ) {
			$setup = new um_ext\um_optimize\core\Setup();
			$setup->run();
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
			require_once 'includes/class-um-optimize.php';
			UM()->set_class( 'Optimize', true );
		}
	}
}
add_action( 'init', 'um_optimize_check_dependencies', 10 );
