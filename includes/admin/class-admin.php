<?php
/**
 * Admin features.
 *
 * @package um_ext\um_optimize\admin
 */

namespace um_ext\um_optimize\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'um_ext\um_optimize\admin\Admin' ) ) {

	/**
	 * Class Admin.
	 *
	 * @package um_ext\um_optimize\admin
	 */
	class Admin {


		/**
		 * Admin constructor.
		 */
		public function __construct() {
			add_filter( 'um_settings_structure', array( &$this, 'extend_settings' ) );
		}


		/**
		 * Add fields to the page
		 *
		 * @param  array $settings
		 * @return array
		 */
		public function extend_settings( $settings ) {

			$fields = array(
				array(
					'id'      => 'um_optimize_assets_info',
					'type'    => 'info_text',
					'label'   => __( 'CSS and JS', 'um-optimize' ),
					'value'   => __( 'Optimize CSS and JS files loading', 'um-optimize' ),
				),
				array(
					'id'      => 'um_optimize_css_dequeue',
					'type'    => 'checkbox',
					'label'   => __( 'Dequeue unused CSS files', 'um-optimize' ),
					'tooltip' => __( 'Dequeue CSS files queued by the Ultimate Member plugin from pages where there are no Ultimate Member elements.', 'um-optimize' ),
				),
				array(
					'id'      => 'um_optimize_js_dequeue',
					'type'    => 'checkbox',
					'label'   => __( 'Dequeue unused JS files', 'um-optimize' ),
					'tooltip' => __( 'Dequeue JS files queued by the Ultimate Member plugin from pages where there are no Ultimate Member elements.', 'um-optimize' ),
				),
				array(
					'id'      => 'um_optimize_css_combine',
					'type'    => 'checkbox',
					'label'   => __( 'Combine CSS files', 'um-optimize' ),
					'tooltip' => __( 'Combine CSS files queued by the Ultimate Member plugin and its extensions.', 'um-optimize' ),
				),
				array(
					'id'      => 'um_optimize_js_combine',
					'type'    => 'checkbox',
					'label'   => __( 'Combine JS files', 'um-optimize' ),
					'tooltip' => __( 'Combine JS files queued by the Ultimate Member plugin and its extensions.', 'um-optimize' ),
				),
			);

			if ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ( defined( 'UM_SCRIPT_DEBUG' ) && UM_SCRIPT_DEBUG ) ) {
				$fields[] = array(
					'id'          => 'um_optimize_css_minify',
					'type'        => 'checkbox',
					'label'       => __( 'Minify CSS files', 'um-optimize' ),
					'tooltip'     => __( 'Minify combined CSS files.', 'um-optimize' ),
					'conditional' => array( 'um_optimize_css_combine', '=', '1' ),
				);
				$fields[] = array(
					'id'          => 'um_optimize_js_minify',
					'type'        => 'checkbox',
					'label'       => __( 'Minify JS files', 'um-optimize' ),
					'tooltip'     => __( 'Minify combined JS files.', 'um-optimize' ),
					'conditional' => array( 'um_optimize_js_combine', '=', '1' ),
				);
			}

			$section = array(
				'title'  => __( 'Optimize', 'um-optimize' ),
				'fields' => $fields,
			);

			$settings['']['sections']['optimize'] = $section;

			return $settings;
		}

	}
}
