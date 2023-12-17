<?php
/**
 * Optimize queries.
 *
 * @package um_ext\um_optimize\core
 */

namespace um_ext\um_optimize\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'um_ext\um_optimize\core\Query' ) ) {


	/**
	 * Class Query
	 *
	 * How to get an instance:
	 *  UM()->classes['um_optimize_query']
	 *  UM()->Optimize()->query()
	 */
	class Query {

		/**
		 * Database table to query for the metadata.
		 *
		 * @var string
		 */
		public $meta_table;

		/**
		 * Column in meta_table that represents the ID of the object the metadata belongs to.
		 *
		 * @var string
		 */
		public $meta_id_column;

		/**
		 * Database table that where the metadata's objects are stored (eg $wpdb->users).
		 *
		 * @var string
		 */
		public $primary_table;

		/**
		 * Column in primary_table that represents the ID of the object.
		 *
		 * @var string
		 */
		public $primary_id_column;

		/**
		 * A flat list of table aliases used in JOIN clauses.
		 *
		 * @var array
		 */
		protected $table_aliases = array();


		/**
		 * Class constructor
		 */
		public function __construct() {

		}


		/**
		 * Filters SQL clauses to be appended to a main query.
		 *
		 * For more information on the accepted arguments, see the
		 * {@link https://developer.wordpress.org/reference/hooks/get_meta_sql/ get_meta_sql}
		 * documentation in the Developer Handbook.
		 *
		 * @since 1.1.0
		 *
		 * @see WP_Meta_Query::get_sql()
		 * @see WP_Meta_Query::get_sql_clauses()
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
		public function get_meta_sql( $sql, $queries, $type, $primary_table, $primary_id_column, $context = null ) {

			$this->table_aliases = array();

			$this->meta_table     = _get_meta_table( $type );
			$this->meta_id_column = sanitize_key( $type . '_id' );

			$this->primary_table     = $primary_table;
			$this->primary_id_column = $primary_id_column;

			$sql_um = $this->get_meta_sql_clauses( $queries );
			if ( $sql_um ) {
				$sql['join']  = ' ' . $sql_um['join'];
				$sql['where'] = empty( $sql_um['where'] ) ? '' : ' AND ( ' . $sql_um['where'] . ' )';
			}

			return $sql;
		}


		/**
		 * Generates SQL clauses for a single query array.
		 *
		 * If nested subqueries are found, this method recurses the tree to
		 * produce the properly nested SQL.
		 *
		 * For more information on the $queries argument, see the
		 * {@link https://developer.wordpress.org/reference/classes/wp_query/#custom-field-post-meta-parameters
		 * Custom Field (post meta) Parameters} documentation in the Developer Handbook.
		 *
		 * @since 1.1.0
		 *
		 * @see WP_Meta_Query::get_sql_for_query()
		 *
		 * @param array $queries Query to parse (passed by reference).
		 *
		 * @return array {
		 *     Array containing JOIN and WHERE SQL clauses to append to a single query array.
		 *
		 *     @type array  $join_i Array of INNER JOIN SQL fragments.
		 *     @type array  $join_l Array of LEFT JOIN SQL fragments.
		 *     @type string $join   SQL fragment to append to the main JOIN clause.
		 *     @type string $where  SQL fragment to append to the main WHERE clause.
		 * }
		 */
		public function get_meta_sql_clauses( $queries ) {

			$sql = array(
				'join'	 => '',
				'where'	 => '',
			);

			if ( is_array( $queries ) ) {
				// simple query.
				if ( array_key_exists( 'key', $queries ) ) {
					$sql_chunks = $this->get_meta_sql_clause( $queries );

					// complex query.
				} else {

					$sql_chunks = array(
						'join_i' => array(),
						'join_l' => array(),
						'where'	 => array(),
					);

					foreach ( $queries as $clause ) {
						if ( is_array( $clause ) ) {
							$clause_sql = $this->get_meta_sql_clauses( $clause );

							$sql_chunks['join_i']	 = array_merge( $sql_chunks['join_i'], $clause_sql['join_i'] );
							$sql_chunks['join_l']	 = array_merge( $sql_chunks['join_l'], $clause_sql['join_l'] );
							if ( is_array( $clause_sql['where'] ) ) {
								$sql_chunks['where'] = array_merge( $sql_chunks['where'], $clause_sql['where'] );
							} else {
								$sql_chunks['where'][] = $clause_sql['where'];
							}
						}
					}
				}
				$sql = $sql_chunks;

				$where = array_filter( $sql_chunks['where'] );
				if ( 1 < count( $where ) ) {
					$relation			 = empty( $queries['relation'] ) ? 'AND' : ('OR' === $queries['relation'] ? 'OR' : 'AND');
					$sql['where']	 = '( ' . implode( " ) $relation ( ", $where ) . ' )';
				} elseif ( 1 === count( $where ) ) {
					$sql['where'] = current( $where );
				} else {
					$sql['where'] = '';
				}

				$join = array_filter( array_merge( $sql_chunks['join_i'], $sql_chunks['join_l'] ) );
				if ( 1 < count( $join ) ) {
					$sql['join'] = implode( ' ', $join );
				} elseif ( 1 === count( $join ) ) {
					$sql['join'] = current( $join );
				} else {
					$sql['join'] = '';
				}
			}

			return $sql;
		}


		/**
		 * Generates SQL JOIN and WHERE clauses for a first-order query clause.
		 *
		 * @since 1.1.0
		 *
		 * @see WP_Meta_Query::get_sql_for_clause()
		 *
		 * @global \wpdb $wpdb
		 *
		 * @param array  $clause            Query clause (passed by reference).
		 *
		 * @return array {
		 *     Array containing JOIN and WHERE SQL clauses to append to a single query array.
		 *
		 *     @type array $join_i Array of INNER JOIN SQL fragments.
		 *     @type array $join_l Array of LEFT JOIN SQL fragments.
		 *     @type array $where  Array of SQL fragments for the WHERE clause.
		 * }
		 */
		public function get_meta_sql_clause( &$clause ) {
			global $wpdb;

			$sql = array(
				'join_i' => array(),
				'join_l' => array(),
				'where'	 => array(),
			);

			$meta_key					 = isset( $clause['key'] ) ? $clause['key'] : '';
			$meta_value				 = isset( $clause['value'] ) ? $clause['value'] : '';
			$meta_compare_key	 = isset( $clause['compare_key'] ) ? strtoupper( $clause['compare_key'] ) : ( is_array( $meta_key ) ? 'IN' : '=' );
			$meta_compare			 = isset( $clause['compare'] ) ? strtoupper( $clause['compare'] ) : ( is_array( $meta_value ) ? 'IN' : '=' );
			$meta_type				 = isset( $clause['type'] ) ? trim( $clause['type'] ) : 'CHAR';
			$alias						 = $wpdb->prepare( '%i', 'meta_' . $meta_key );

			// JOIN ON.
			if ( 'NOT EXISTS' === $meta_compare ) {
				// JOIN clauses for NOT EXISTS have their own syntax.
				$join = "LEFT JOIN $this->meta_table AS {$alias}";
			} else {
				// All other JOIN clauses.
				$join = "INNER JOIN $this->meta_table AS {$alias}";
			}
			if ( 'LIKE' === $meta_compare_key ) {
				$join .= $wpdb->prepare( " ON ( $this->primary_table.$this->primary_id_column = $alias.$this->meta_id_column AND $alias.meta_key LIKE %s )", '%' . $wpdb->esc_like( $meta_key ) . '%' );
			} elseif ( '=' === $meta_compare_key ) {
				$join .= $wpdb->prepare( " ON ( $this->primary_table.$this->primary_id_column = $alias.$this->meta_id_column AND $alias.meta_key = %s )", $meta_key );
			} else {
				$join .= " ON ( $this->primary_table.$this->primary_id_column = $alias.$this->meta_id_column )";
			}
			if ( 'NOT EXISTS' === $meta_compare ) {
				$sql['join_l'][$meta_key] = $join;
			} else {
				$sql['join_i'][$meta_key] = $join;
			}
			$this->table_aliases[] = $alias;

			// meta_key.
			if ( 'NOT EXISTS' === $meta_compare_key ) {
				$sql['where'][] = "$alias.$this->meta_id_column IS NULL";
			} elseif ( '' !== $meta_key && array_key_exists( 'compare_key', $clause ) ) {

				if ( in_array( $meta_compare_key, array( '!=', 'NOT IN', 'NOT LIKE', 'NOT EXISTS', 'NOT REGEXP' ), true ) ) {
					$subquery_alias             = $alias . '_sub';
					$meta_compare_string_start  = 'NOT EXISTS (';
					$meta_compare_string_start .= "SELECT 1 FROM $this->meta_table AS $subquery_alias ";
					$meta_compare_string_start .= "WHERE $subquery_alias.post_ID = $alias.post_ID ";
					$meta_compare_string_end    = 'LIMIT 1';
					$meta_compare_string_end	 .= ')';
				}

				switch ( $meta_compare_key ) {
					case '=':
					case 'EXISTS':
						$where							 = $wpdb->prepare( "$alias.meta_key = %s", $meta_key ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						break;
					case 'LIKE':
						$meta_compare_value	 = '%' . $wpdb->esc_like( $meta_key ) . '%';
						$where							 = $wpdb->prepare( "$alias.meta_key LIKE %s", $meta_compare_value ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						break;
					case 'IN':
						$meta_compare_string = "$alias.meta_key IN (" . substr( str_repeat( ',%s', count( $meta_key ) ), 1 ) . ')';
						$where							 = $wpdb->prepare( $meta_compare_string, $meta_key ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						break;
					case 'RLIKE':
					case 'REGEXP':
						$operator						 = $meta_compare;
						if ( 'BINARY' === strtoupper( $meta_type ) ) {
							$cast	 = 'BINARY';
							$almk	 = "CAST($alias.meta_key AS BINARY)";
						} else {
							$cast	 = '';
							$almk	 = "$alias.meta_key";
						}
						$where = $wpdb->prepare( "$almk $operator $cast %s", $meta_key ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						break;

					case '!=':
					case 'NOT EXISTS':
						$meta_compare_string = $meta_compare_string_start . "AND $subquery_alias.meta_key = %s " . $meta_compare_string_end;
						$where							 = $wpdb->prepare( $meta_compare_string, $meta_key ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						break;
					case 'NOT LIKE':
						$meta_compare_string = $meta_compare_string_start . "AND $subquery_alias.meta_key LIKE %s " . $meta_compare_string_end;
						$meta_compare_value	 = '%' . $wpdb->esc_like( $meta_key ) . '%';
						$where							 = $wpdb->prepare( $meta_compare_string, $meta_compare_value ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						break;
					case 'NOT IN':
						$array_subclause		 = '(' . substr( str_repeat( ',%s', count( $meta_key ) ), 1 ) . ') ';
						$meta_compare_string = $meta_compare_string_start . "AND $subquery_alias.meta_key IN " . $array_subclause . $meta_compare_string_end;
						$where							 = $wpdb->prepare( $meta_compare_string, $meta_key ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						break;
					case 'NOT REGEXP':
						$operator						 = $meta_compare;
						if ( 'BINARY' === strtoupper( $meta_type ) ) {
							$cast	 = 'BINARY';
							$almk	 = "CAST($subquery_alias.meta_key AS BINARY)";
						} else {
							$cast	 = '';
							$almk	 = "$subquery_alias.meta_key";
						}

						$meta_compare_string = $meta_compare_string_start . "AND $almk REGEXP $cast %s " . $meta_compare_string_end;
						$where							 = $wpdb->prepare( $meta_compare_string, $meta_key ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						break;
				}

				$sql['where'][] = $where;
			}

			// meta_value.
			if ( 'NOT EXISTS' === $meta_compare ) {
				$sql['where'][] = "$alias.$this->meta_id_column IS NULL";
			} elseif ( '' !== $meta_value && array_key_exists( 'compare', $clause ) ) {

				if ( in_array( $meta_compare, array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ), true ) ) {
					if ( ! is_array( $meta_value ) ) {
						$meta_value = preg_split( '/[,\s]+/', $meta_value );
					}
				} elseif ( is_string( $meta_value ) ) {
					$meta_value = trim( $meta_value );
				}

				switch ( $meta_compare ) {
					case 'IN':
					case 'NOT IN':
						$meta_compare_string = '(' . substr( str_repeat( ',%s', count( $meta_value ) ), 1 ) . ')';
						$where							 = $wpdb->prepare( $meta_compare_string, $meta_value );
						break;

					case 'BETWEEN':
					case 'NOT BETWEEN':
						$where = $wpdb->prepare( '%s AND %s', $meta_value[0], $meta_value[1] );
						break;

					case 'LIKE':
					case 'NOT LIKE':
						$meta_value	 = '%' . $wpdb->esc_like( $meta_value ) . '%';
						$where			 = $wpdb->prepare( '%s', $meta_value );
						break;

					// EXISTS with a value is interpreted as '='.
					case 'EXISTS':
						$meta_compare	 = '=';
						$where				 = $wpdb->prepare( '%s', $meta_value );
						break;

					// 'value' is ignored for NOT EXISTS.
					case 'NOT EXISTS':
						$where = '';
						break;

					default:
						$where = $wpdb->prepare( '%s', $meta_value );
						break;
				}

				if ( 'CHAR' === $meta_type ) {
					$sql['where'][] = "$alias.meta_value {$meta_compare} {$where}";
				} else {
					$sql['where'][] = "CAST($alias.meta_value AS {$meta_type}) {$meta_compare} {$where}";
				}
			}

			return $sql;
		}


		/**
		 * Retrieves an array of posts matching the given criteria.
		 *
		 * For more information on the accepted arguments, see the
		 * {@link https://developer.wordpress.org/reference/classes/wp_query/ WP_Query}
		 * documentation in the Developer Handbook.
		 *
		 * @since 1.1.0
		 *
		 * @see WP_Query::parse_query()
		 *
		 * @param array  $args   Array or string of Query parameters.
		 * @param string $return A format of return. Accepts: 'posts', 'WP_Query'. Default 'posts',
		 *
		 * @return WP_Query|WP_Post[]|int[] Array of post objects or post IDs.
		 *                                  WP_Query object if parameter $return is 'WP_Query'.
		 */
		public function get_posts( $args, $return = 'posts' ) {
			add_filter( 'get_meta_sql', array( $this, 'get_meta_sql' ), 10, 6 );

			if ( 'WP_Query' === $return ) {
				$posts = new WP_Query( $args );
				wp_reset_postdata();
			} else {
				$posts = get_posts( $args );
			}

			remove_filter( 'get_meta_sql', array( $this, 'get_meta_sql' ), 10 );

			return $posts;
		}

	}

}