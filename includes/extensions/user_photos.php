<?php
/*
 * Optimize the User Photos extension.
 *
 * @package um_ext\um_optimize\extensions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'um_user_photos_version' ) ) {
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
function um_optimize_photos__get_meta_sql( $sql, $queries, $type, $primary_table, $primary_id_column, $context ) {
	if ( UM()->options()->get( 'um_optimize_photos' )
		&& 'post' === $type
		&& 'um_user_photos' === $context->get( 'post_type' ) ) {

		$sql_um = UM()->Optimize()->query()->get_meta_sql( $sql, $queries, $type, $primary_table, $primary_id_column, $context );

		return $sql_um;
	}
	return $sql;
}
add_filter( 'get_meta_sql', 'um_optimize_photos__get_meta_sql', 10, 6 );
