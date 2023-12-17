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

			$fields   = array();

			// CSS and JS.
			$fields[] = array(
				'id'      => 'um_optimize_assets_info',
				'type'    => 'info_text',
				'label'   => __( 'CSS and JS', 'um-optimize' ),
				'value'   => __( 'Optimize CSS and JS files loading', 'um-optimize' ),
			);
			$fields[] = array(
				'id'      => 'um_optimize_css_dequeue',
				'type'    => 'checkbox',
				'label'   => __( 'Dequeue unused CSS files', 'um-optimize' ),
				'tooltip' => __( 'Dequeue CSS files queued by the Ultimate Member plugin from pages where there are no Ultimate Member elements.', 'um-optimize' ),
			);
			$fields[] = array(
				'id'      => 'um_optimize_js_dequeue',
				'type'    => 'checkbox',
				'label'   => __( 'Dequeue unused JS files', 'um-optimize' ),
				'tooltip' => __( 'Dequeue JS files queued by the Ultimate Member plugin from pages where there are no Ultimate Member elements.', 'um-optimize' ),
			);
			$fields[] = array(
				'id'      => 'um_optimize_css_combine',
				'type'    => 'checkbox',
				'label'   => __( 'Combine CSS files', 'um-optimize' ),
				'tooltip' => __( 'Combine CSS files queued by the Ultimate Member plugin and its extensions.', 'um-optimize' ),
			);
			$fields[] = array(
				'id'      => 'um_optimize_js_combine',
				'type'    => 'checkbox',
				'label'   => __( 'Combine JS files', 'um-optimize' ),
				'tooltip' => __( 'Combine JS files queued by the Ultimate Member plugin and its extensions.', 'um-optimize' ),
			);

			// SQL queries.
			$fields[] = array(
				'id'      => 'um_optimize_assets_info',
				'type'    => 'info_text',
				'label'   => __( 'SQL queries', 'um-optimize' ),
				'value'   => __( 'Optimize SQL queries to get posts and users faster', 'um-optimize' ),
			);
			if ( defined( 'um_activity_version' ) ) {
				$fields[] = array(
					'id'      => 'um_optimize_activity',
					'type'    => 'checkbox',
					'label'   => __( 'Speed up Activity', 'um-optimize' ),
					'tooltip' => __( 'Optimize the SQL query that retrieves posts for the Social Activity extension.', 'um-optimize' ),
				);
			}
			if ( defined( 'um_groups_version' ) ) {
				$fields[] = array(
					'id'      => 'um_optimize_groups',
					'type'    => 'checkbox',
					'label'   => __( 'Speed up Groups', 'um-optimize' ),
					'tooltip' => __( 'Optimize the SQL query that retrieves posts for the Groups extension.', 'um-optimize' ),
				);
			}
			if ( defined( 'um_user_notes_version' ) ) {
				$fields[] = array(
					'id'      => 'um_optimize_notes',
					'type'    => 'checkbox',
					'label'   => __( 'Speed up Notes', 'um-optimize' ),
					'tooltip' => __( 'Optimize the SQL query that retrieves notes for the User Notes extension.', 'um-optimize' ),
				);
			}
			if ( defined( 'um_user_photos_version' ) ) {
				$fields[] = array(
					'id'      => 'um_optimize_photos',
					'type'    => 'checkbox',
					'label'   => __( 'Speed up Photos', 'um-optimize' ),
					'tooltip' => __( 'Optimize the SQL query that retrieves albums for the User Photos extension.', 'um-optimize' ),
				);
			}
			if ( UM()->options()->get( 'members_page' ) ) {
				$fields[] = array(
					'id'      => 'um_optimize_members',
					'type'    => 'checkbox',
					'label'   => __( 'Speed up member directories', 'um-optimize' ),
					'tooltip' => __( 'Optimize the SQL query that retrieves users for the member directory.', 'um-optimize' ),
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
