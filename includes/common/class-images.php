<?php
namespace um_ext\um_optimize\common;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( class_exists( 'um_ext\um_optimize\common\Images' ) ) {
	return;
}

/**
 * Optimize images.
 *
 * Get an instance this way: UM()->Optimize()->images()
 *
 * @package um_ext\um_optimize\common
 */
class Images {


	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Profile Photo caching.
		$profile_photo_caching = UM()->options()->get( 'um_optimize_profile_photo' );
		if ( $profile_photo_caching ) {
			add_filter( 'um_filter_avatar_cache_time', array( $this, 'avatar_cache_time' ), 10, 2 );
		}

		// Cover Photo caching.
		$cover_photo_caching = UM()->options()->get( 'um_optimize_cover_photo' );
		if ( $cover_photo_caching ) {
			add_filter( 'um_user_cover_photo_uri__filter', array( $this, 'cover_photo_uri' ), 10, 3 );
		}

		// Cover Photo size in directory.
		$cover_photo_size = UM()->options()->get( 'um_optimize_cover_photo_size' );
		if ( $cover_photo_size && is_numeric( $cover_photo_size ) ) {
			if ( UM()->options()->get( 'member_directory_own_table' ) ) {
				add_action( 'um_members_after_user_name', array( $this, 'cover_photo_size_meta' ), 10, 2 );
			} else {
				add_filter( 'um_member_directory_cover_image_size', array( $this, 'cover_photo_size' ), 10, 2 );
			}
		}
	}


	/**
	 * Change Profile Photo cache time.
	 *
	 * Added to the filter hook `um_filter_avatar_cache_time`.
	 *
	 * @see um_get_avatar_uri()
	 *
	 * @since 1.2.0
	 *
	 * @param int $cache_time Avatar cache time.
	 * @param int $user_id    User ID.
	 * @return int
	 */
	public function avatar_cache_time( $cache_time, $user_id ) {
		$image = um_profile( 'profile_photo' );
		if ( $image ) {
			$dir = wp_normalize_path( UM()->uploader()->get_upload_base_dir() );
			if ( is_multisite() ) {
				$blog_id = get_current_blog_id();
				$dir     = str_replace( wp_normalize_path( "/sites/$blog_id/" ), '/', $dir );
			}
			$filename = wp_normalize_path( "$dir/$user_id/$image" );
			$filetime = filemtime( $filename );
			if ( $filetime ) {
				$cache_time = $filetime;
			}
		}
		return $cache_time;
	}


	/**
	 * Change Cover Photo cache time.
	 *
	 * Added to the filter hook `um_user_cover_photo_uri__filter`.
	 *
	 * @see um_user()
	 *
	 * @since 1.2.0
	 *
	 * @param string $cover_uri  Cover photo URL.
	 * @param bool   $is_default Default or not.
	 * @param array  $attrs      Attributes.
	 * @return string
	 */
	public function cover_photo_uri( $cover_uri, $is_default, $attrs ) {
		if ( ! $is_default ) {
			$image   = um_profile( 'cover_photo' );
			$user_id = um_profile( 'ID' );
			if ( $image && $user_id ) {
				$dir = wp_normalize_path( UM()->uploader()->get_upload_base_dir() );
				if ( is_multisite() ) {
					$blog_id = get_current_blog_id();
					$dir     = str_replace( wp_normalize_path( "/sites/$blog_id/" ), '/', $dir );
				}
				$filename = wp_normalize_path( "$dir/$user_id/$image" );
				$filetime = filemtime( $filename );
				if ( $filetime ) {
					$cover_url = current( explode( '?', $cover_uri ) );
					$cover_uri = "$cover_url?$filetime";
				}
			}
		}
		return $cover_uri;
	}


	/**
	 * Change Cover Photo size for the member directory.
	 *
	 * Added to the filter hook `um_member_directory_cover_image_size`.
	 *
	 * @see \um\core\Member_Directory::ajax_get_members()
	 *
	 * @since 1.4.0
	 *
	 * @param int   $cover_size     Cover photo size.
	 * @param array $directory_data Directory settings.
	 * @return int Cover photo size.
	 */
	public function cover_photo_size( $cover_size, $directory_data ) {
		if ( ! wp_is_mobile() ) {
			$size = UM()->options()->get( 'um_optimize_cover_photo_size' );
			if ( $size && is_numeric( $size ) ) {
				$cover_size = (int) $size;
			}
		}
		return $cover_size;
	}


	/**
	 * Change Cover Photo size for the member directory.
	 *
	 * Added to the action hook `um_members_after_user_name`.
	 *
	 * @see \um\core\Member_Directory::build_user_card_data()
	 *
	 * @since 1.2.0
	 * @version 1.3.1 Fix for the UM v2.9.2 - used function wp_is_mobile()
	 *
	 * @param int   $user_id        User ID.
	 * @param array $directory_data Directory settings.
	 */
	public function cover_photo_size_meta( $user_id, $directory_data ) {
		if ( ! wp_is_mobile() ) {
			UM()->member_directory()->cover_size = (int) UM()->options()->get( 'um_optimize_cover_photo_size' );
		}
	}
}
