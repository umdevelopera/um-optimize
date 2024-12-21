<?php
namespace um_ext\um_optimize\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( class_exists( 'um_ext\um_optimize\admin\Settings_Optimize' ) ) {
	return;
}

/**
 * Extends settings.
 * Adds the "Optimize" tab to wp-admin > Ultimate Member > Settings > General.
 *
 * Get an instance this way: UM()->Optimize()->admin()->settings_optimize()
 *
 * @package um_ext\um_optimize\admin
 */
class Settings_Optimize {


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
			$this->settings_section_assets(),
			$this->settings_section_images(),
			$this->settings_section_queries(),
		);

		$settings['']['sections']['optimize'] = array(
			'title'         => __( 'Optimize', 'um-optimize' ),
			'form_sections' => $sections,
		);

		return $settings;
	}


	/**
	 * Section "CSS and JS".
	 *
	 * @return array
	 */
	public function settings_section_assets() {

		$fields = array(
			array(
				'id'          => 'um_optimize_css_dequeue',
				'type'        => 'checkbox',
				'label'       => __( 'Dequeue unused styles', 'um-optimize' ),
				'description' => __( 'Dequeue CSS files on pages that do not have Ultimate Member components.', 'um-optimize' ),
			),
			array(
				'id'          => 'um_optimize_js_dequeue',
				'type'        => 'checkbox',
				'label'       => __( 'Dequeue unused scripts', 'um-optimize' ),
				'description' => __( 'Dequeue JS files on pages that do not have Ultimate Member components.', 'um-optimize' ),
			),
			array(
				'id'          => 'um_optimize_css_combine',
				'type'        => 'checkbox',
				'label'       => __( 'Combine styles', 'um-optimize' ),
				'description' => __( 'Combine CSS files queued by the Ultimate Member plugin and its extensions.', 'um-optimize' ),
			),
			array(
				'id'          => 'um_optimize_js_combine',
				'type'        => 'checkbox',
				'label'       => __( 'Combine scripts', 'um-optimize' ),
				'description' => __( 'Combine JS files queued by the Ultimate Member plugin and its extensions.', 'um-optimize' ),
			),
		);

		return array(
			'title'       => __( 'CSS and JS', 'um-optimize' ),
			'description' => __( 'Ultimate Member loads various styles and scripts that are necessary for its components to work. Extensions can also load their own styles and scripts. Loading many styles and scripts can slow down page rendering. It is recommended to disable loading of Ultimate Member styles and scripts on pages that do not have its components. Loading one large style or script file has less impact on page rendering delay than loading multiple files. It is recommended to combine multiple Ultimate Member styles and scripts into one style file and one script file.', 'um-optimize' ),
			'fields'      => $fields,
		);
	}


	/**
	 * Section "Images".
	 *
	 * @return array
	 */
	public function settings_section_images(){
		$sizes     = UM()->files()->get_profile_photo_size( 'cover_thumb_sizes' );
		$sizes[''] = __( 'Default', 'um-optimize' );

		$fields = array(
			array(
				'id'          => 'um_optimize_profile_photo',
				'type'        => 'checkbox',
				'label'       => __( 'Profile Photo caching', 'um-optimize' ),
				'description' => __( 'Allow using Profile Photo images from the browser cache.', 'um-optimize' ),
			),
			array(
				'id'          => 'um_optimize_cover_photo',
				'type'        => 'checkbox',
				'label'       => __( 'Cover Photo caching', 'um-optimize' ),
				'description' => __( 'Allow using Cover Photo images from the browser cache.', 'um-optimize' ),
			),
			array(
				'id'          => 'um_optimize_cover_photo_size',
				'type'        => 'select',
				'size'        => 'small',
				'label'       => __( 'Cover Photo size in directory', 'um-optimize' ),
				'default'     => UM()->options()->get( 'profile_coversize' ),
				'options'     => $sizes,
				'description' => __( 'Select the size of the Cover Photo thumbnail for the member directory.', 'um-optimize' ),
			),
		);

		return array(
			'title'       => __( 'Images', 'um-optimize' ),
			'description' => __( 'Ultimate Member does not allow using Cover Photo and Profile Photo images from the browser cache. This approach is safe and secure, but it slows down rendering pages with Ultimate Member components and loading the member directory. It is recommended to allow using images from the browser cache if your site is public. Ultimate Member uses the largest Cover Photo thumbnail in the member directory on the desktop. Such large images are usually not necessary. It is recommended to use an image that is 500px wide or slightly larger.', 'um-optimize' ),
			'fields'      => $fields,
		);
	}


	/**
	 * Section "SQL queries".
	 *
	 * @return array
	 */
	public function settings_section_queries(){

		$fields = array();

		if ( UM()->options()->get( 'members_page' ) ) {
			$fields[] = array(
				'id'          => 'um_optimize_members',
				'type'        => 'checkbox',
				'label'       => __( 'Speed up member directories', 'um-optimize' ),
				'description' => __( 'Optimize the SQL query that retrieves users for the member directory.', 'um-optimize' ),
			);
		}
		if ( defined( 'um_activity_version' ) ) {
			$fields[] = array(
				'id'          => 'um_optimize_activity',
				'type'        => 'checkbox',
				'label'       => __( 'Speed up Activity', 'um-optimize' ),
				'description' => __( 'Optimize the SQL query that retrieves posts for the Social Activity extension.', 'um-optimize' ),
			);
		}
		if ( defined( 'um_groups_version' ) ) {
			$fields[] = array(
				'id'          => 'um_optimize_groups',
				'type'        => 'checkbox',
				'label'       => __( 'Speed up Groups', 'um-optimize' ),
				'description' => __( 'Optimize the SQL query that retrieves posts for the Groups extension.', 'um-optimize' ),
			);
		}
		if ( defined( 'um_user_notes_version' ) ) {
			$fields[] = array(
				'id'          => 'um_optimize_notes',
				'type'        => 'checkbox',
				'label'       => __( 'Speed up Notes', 'um-optimize' ),
				'description' => __( 'Optimize the SQL query that retrieves notes for the User Notes extension.', 'um-optimize' ),
			);
		}
		if ( defined( 'um_user_photos_version' ) ) {
			$fields[] = array(
				'id'          => 'um_optimize_photos',
				'type'        => 'checkbox',
				'label'       => __( 'Speed up Photos', 'um-optimize' ),
				'description' => __( 'Optimize the SQL query that retrieves albums for the User Photos extension.', 'um-optimize' ),
			);
		}
		if ( defined( 'um_reviews_version' ) ) {
			$fields[] = array(
				'id'          => 'um_optimize_reviews',
				'type'        => 'checkbox',
				'label'       => __( 'Speed up Reviews', 'um-optimize' ),
				'description' => __( 'Optimize the SQL query that retrieves reviews for the User Reviews extension.', 'um-optimize' ),
			);
		}

		return empty( $fields ) ? null : array(
			'title'       => __( 'SQL queries', 'um-optimize' ),
			'description' => __( 'Ultimate Member uses the standard WP_Query and WP_User_Query classes to build database queries. Queries built this way are reliable and stable, but not optimized. This slows down retrieving users in the user directory and posts in extensions that use custom post type, which slows down page rendering. It is recommended to enable SQL queries optimization to get posts and users faster.', 'um-optimize' ),
			'fields'      => $fields,
		);
	}
}
