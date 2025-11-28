<?php
/*
 * Optimize the Social Activity extension.
 *
 * @package um_ext\um_optimize\extensions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'um_activity_version' ) ) {
	return;
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
function um_optimize_activity__get_meta_sql( $sql, $queries, $type, $primary_table, $primary_id_column, $context = null ) {
	if ( UM()->options()->get( 'um_optimize_activity' )
		&& 'post' === $type
		&& isset( $context )
		&& 'um_activity' === $context->get( 'post_type' ) ) {

		$sql_um = UM()->Optimize()->query()->get_meta_sql( $sql, $queries, $type, $primary_table, $primary_id_column, $context );

		// TESTING.
//		error_log( 'Test activity. JOIN:     ' . $sql['join'] );
//		error_log( 'Test activity. JOIN_UM:  ' . $sql_um['join'] );
//		error_log( 'Test activity. WHERE:    ' . $sql['where'] );
//		error_log( 'Test activity. WHERE_UM: ' . $sql_um['where'] );

		return $sql_um;
	}
	return $sql;
}
add_filter( 'get_meta_sql', 'um_optimize_activity__get_meta_sql', 10, 6 );
