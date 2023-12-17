<?php
/**
 * Optimize member directories.
 *
 * @package um_ext\um_optimize\core
 */

namespace um_ext\um_optimize\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'um_ext\um_optimize\core\Member_Directory' ) ) {

	/**
	 * Class Assets
	 *
	 * How to get an instance:
	 *  UM()->classes['um_optimize_member_directory']
	 *  UM()->Optimize()->member_directory()
	 */
	class Member_Directory {


		/**
		 * Class constructor
		 */
		public function __construct() {
			// Member directory with the default usermeta table.
			add_filter( 'get_meta_sql', array( $this, 'get_meta_sql' ), 10, 6 );

			// Member directory with the custom table for usermeta.
			add_filter( 'um_query_args_filter_global_meta', array( $this, 'handle_filter_meta_sql' ), 10, 6 );
		}


		/**
		 * Filters SQL clauses to be appended to a main query.
		 *
		 * @since 1.1.0
		 *
		 * @see \um_ext\um_optimize\core\Query::get_meta_sql()
		 *
		 * @param string[] $sql               Array containing the query's JOIN and WHERE clauses.
		 * @param array    $queries           Array of meta queries.
		 * @param string   $type              Type of meta.
		 * @param string   $primary_table     Primary table.
		 * @param string   $primary_id_column Primary column ID.
		 * @param object   $context           Optional. The main query object that corresponds to the type, for
		 *                                    example a `WP_Query`, `WP_User_Query`, or `WP_Site_Query`.
		 *                                    Default null.
		 *
		 * @return string[] {
		 *     Array containing JOIN and WHERE SQL clauses to append to the main query,
		 *     or false if no table exists for the requested meta type.
		 *
		 *     @type string $join  SQL fragment to append to the main JOIN clause.
		 *     @type string $where SQL fragment to append to the main WHERE clause.
		 * }
		 */
		public function get_meta_sql ( $sql, $queries, $type, $primary_table, $primary_id_column, $context ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX
				&& UM()->options()->get( 'members_page' )
				&& UM()->options()->get( 'um_optimize_members' )
				&& 'user' === $type
				&& isset( $_POST['action'] ) && 'um_get_members' === $_POST['action'] ) {

				$sql_um = UM()->Optimize()->query()->get_meta_sql( $sql, $queries, $type, $primary_table, $primary_id_column, $context );

				// TESTING.
//				error_log( 'Test member directory. JOIN:     ' . $sql['join'] );
//				error_log( 'Test member directory. JOIN_UM:  ' . $sql_um['join'] );
//				error_log( 'Test member directory. WHERE:    ' . $sql['where'] );
//				error_log( 'Test member directory. WHERE_UM: ' . $sql_um['where'] );

				return $sql_um;
			}
			return $sql;
		}


		/**
		 * Changes SQL clauses for filtering members in the Member Directory.
		 *
		 * @since 1.1.1
		 *
		 * @see \um\core\Member_Directory_Meta::handle_filter_query()
		 *
		 * @param bool   $skip_default Skip default JOIN for this field if TRUE.
		 * @param object $um_mdm       The \um\core\Member_Directory_Meta class.
		 * @param string $field        Meta Key of the field.
		 * @param mixed  $value        Value(s) of the field.
		 * @param string $filter_type  Filter type.
		 * @param bool   $is_default
		 *
		 * @return bool
		 */
		public function handle_filter_meta_sql ( $skip_default, $um_mdm, $field, $value, $filter_type, $is_default ) {
			global $wpdb;

			if (  defined( 'DOING_AJAX' ) && DOING_AJAX
				&& UM()->options()->get( 'um_optimize_members' )
				&& false === $skip_default ) {

				$alias        = $wpdb->prepare( '%i', 'umm_' . $field );
				$skip_default = true;

				switch ( $filter_type ) {

					default:
						$skip_default = false;
						break;

					case 'text':
						$compare = apply_filters( 'um_members_directory_filter_text', '=', $field );
						$value   = apply_filters( 'um_members_directory_filter_text_meta_value', trim( stripslashes( $value ) ), $field );

						$um_mdm->joins[] = $wpdb->prepare(
							"INNER JOIN {$wpdb->prefix}um_metadata AS {$alias} ON ( {$alias}.user_id = u.ID AND {$alias}.um_key = %s )",
							$field
						);

						$um_mdm->where_clauses[] = $wpdb->prepare(
							"{$alias}.um_key = %s AND {$alias}.um_value {$compare} %s",
							$field,
							$value
						);

						if ( ! $is_default ) {
							$um_mdm->custom_filters_in_query[ $field ] = $value;
						}
						break;

					case 'select':
						if ( ! is_array( $value ) ) {
							$value = array( $value );
						}

						$values_array = array();
						foreach ( $value as $single_val ) {
							$single_val = trim( stripslashes( $single_val ) );

							$values_array[] = $wpdb->prepare( "{$alias}.um_value = %s", $single_val );
							$values_array[] = $wpdb->prepare( "{$alias}.um_value LIKE %s", '%"' . $single_val . '"%' );
							$values_array[] = $wpdb->prepare( "{$alias}.um_value LIKE %s", '%' . serialize( (string) $single_val ) . '%' );

							if ( is_numeric( $single_val ) ) {
								$values_array[] = $wpdb->prepare( "{$alias}.um_value LIKE %s", '%' . serialize( (int) $single_val ) . '%' );
							}
						}
						$values = implode( ' OR ', $values_array );

						$um_mdm->joins[] = $wpdb->prepare(
							"INNER JOIN {$wpdb->prefix}um_metadata AS {$alias} ON ( {$alias}.user_id = u.ID AND {$alias}.um_key = %s )",
							$field
						);

						$um_mdm->where_clauses[] = $wpdb->prepare(
							"( {$alias}.um_key = %s AND ( {$values} ) )",
							$field
						);

						if ( ! $is_default ) {
							$um_mdm->custom_filters_in_query[ $field ] = $value;
						}
						break;

					case 'slider':
						$min = min( $value );
						$max = max( $value );

						$um_mdm->joins[] = $wpdb->prepare(
							"INNER JOIN {$wpdb->prefix}um_metadata AS {$alias} ON ( {$alias}.user_id = u.ID AND {$alias}.um_key = %s )",
							$field
						);

						$um_mdm->where_clauses[] = $wpdb->prepare(
							"( {$alias}.um_key = %s AND {$alias}.um_value BETWEEN %d AND %d )",
							$field,
							$min,
							$max
						);

						if ( ! $is_default ) {
							$um_mdm->custom_filters_in_query[ $field ] = $value;
						}
						break;

					case 'datepicker':
						$offset = 0;
						if ( ! $is_default ) {
							if ( isset( $_POST['gmt_offset'] ) && is_numeric( $_POST['gmt_offset'] ) ) {
								$offset = (int) $_POST['gmt_offset'];
							}
						} else {
							$gmt_offset = get_post_meta( $directory_data['form_id'], '_um_search_filters_gmt', true );
							if ( is_numeric( $gmt_offset ) ) {
								$offset = (int) $gmt_offset;
							}
						}

						$from_time = (int) min( $value ) + ( $offset * HOUR_IN_SECONDS ); // client time zone offset
						$to_time   = (int) max( $value ) + ( $offset * HOUR_IN_SECONDS ) + DAY_IN_SECONDS - 1; // time 23:59
						$from_date = date( 'Y/m/d', $from_time );
						$to_date   = date( 'Y/m/d', $to_time );

						$um_mdm->joins[] = $wpdb->prepare(
							"INNER JOIN {$wpdb->prefix}um_metadata AS {$alias} ON ( {$alias}.user_id = u.ID AND {$alias}.um_key = %s )",
							$field
						);

						$um_mdm->where_clauses[] = $wpdb->prepare(
							"( {$alias}.um_key = %s AND {$alias}.um_value BETWEEN %s AND %s )",
							$field,
							$from_date,
							$to_date
						);

						if ( ! $is_default ) {
							$um_mdm->custom_filters_in_query[ $field ] = array( $from_date, $to_date );
						}
						break;

					case 'timepicker':

						$um_mdm->joins[] = $wpdb->prepare(
							"INNER JOIN {$wpdb->prefix}um_metadata AS {$alias} ON ( {$alias}.user_id = u.ID AND {$alias}.um_key = %s )",
							$field
						);

						if ( $value[0] === $value[1] ) {
							$um_mdm->where_clauses[] = $wpdb->prepare(
								"( {$alias}.um_key = %s AND {$alias}.um_value = %s )",
								$field,
								$value[0]
							);
						} else {
							$um_mdm->where_clauses[] = $wpdb->prepare(
								"( {$alias}.um_key = %s AND CAST( {$alias}.um_value AS TIME ) BETWEEN %s AND %s )",
								$field,
								$value[0],
								$value[1]
							);
						}

						if ( ! $is_default ) {
							$um_mdm->custom_filters_in_query[ $field ] = $value;
						}
						break;
				}
			}

			return $skip_default;
		}

	}
}