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
			add_filter( 'get_meta_sql', array( $this, 'get_meta_sql' ), 10, 6 );
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
		function get_meta_sql( $sql, $queries, $type, $primary_table, $primary_id_column, $context ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX
				&& defined( 'WP_DEBUG' ) && WP_DEBUG
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

	}
}