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
 * UM version: 2.12.1
 * Version: 1.3.6
 *
 * @package um_ext\um_optimize
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/plugin.php';

$plugin_data = get_plugin_data( __FILE__, true, false );

define( 'UM_OPTIMIZE_URL', plugin_dir_url( __FILE__ ) );
define( 'UM_OPTIMIZE_PATH', plugin_dir_path( __FILE__ ) );
define( 'UM_OPTIMIZE_PLUGIN', plugin_basename( __FILE__ ) );
define( 'UM_OPTIMIZE_EXTENSION', $plugin_data['Name'] );
define( 'UM_OPTIMIZE_VERSION', $plugin_data['Version'] );


// Activation script.
if ( ! function_exists( 'um_optimize_activation_hook' ) ) {
	function um_optimize_activation_hook() {
		require_once 'includes/common/class-setup.php';
		if ( class_exists( 'um_ext\um_optimize\common\Setup' ) && function_exists( 'UM' ) ) {
			$setup = new um_ext\um_optimize\common\Setup();
			$setup->run();
		}
	}
}
register_activation_hook( UM_OPTIMIZE_PLUGIN, 'um_optimize_activation_hook' );


// Check dependencies.
if ( ! function_exists( 'um_optimize_check_dependencies' ) ) {
	function um_optimize_check_dependencies() {
		if ( ! function_exists( 'UM' ) || ! UM()->dependencies()->ultimatemember_active_check() ) {
			// UM is not active.
			add_action(
				'admin_notices',
				function () {
					// translators: %s - plugin name.
					echo '<div class="error"><p>' . wp_kses_post( sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-optimize' ), UM_OPTIMIZE_EXTENSION ) ) . '</p></div>';
				}
			);
		} else {
			require_once 'includes/class-um-optimize.php';
			UM()->set_class( 'Optimize', true );
		}
	}
}
add_action( 'init', 'um_optimize_check_dependencies', 10 );
