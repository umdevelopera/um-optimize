<?php
/**
 * Optimize queries.
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
	 *
	 * @package um_ext\um_optimize\core
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
		 * @see \WP_Meta_Query::get_sql()
		 * @see \WP_Meta_Query::get_sql_clauses()
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
		 * @see \WP_Meta_Query::get_sql_for_query()
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
			if ( ! is_array( $queries ) ) {
				return array(
					'join'	 => '',
					'where'	 => '',
				);
			}

			if ( array_key_exists( 'key', $queries ) || array_key_exists( 'value', $queries ) ) {
				// simple query.
				$sql_chunks = $this->get_meta_sql_clause( $queries );

			} else {
				// complex query.

				$sql_chunks = array(
					'join_i' => array(),
					'join_l' => array(),
					'where'	 => array(),
				);

				foreach ( $queries as $clause ) {
					if ( is_array( $clause ) ) {
						$clause_sql = $this->get_meta_sql_clauses( $clause );

						$sql_chunks['join_i'] = array_merge( $sql_chunks['join_i'], (array) $clause_sql['join_i'] );
						$sql_chunks['join_l'] = array_merge( $sql_chunks['join_l'], (array) $clause_sql['join_l'] );
						$sql_chunks['where']  = array_merge( $sql_chunks['where'], (array) $clause_sql['where'] );
					}
				}
			}
			$sql = $sql_chunks;

			$where = array_filter( $sql_chunks['where'] );
			if ( 1 < count( $where ) ) {
				if ( empty( $queries['relation'] ) || 'AND' === strtoupper( $queries['relation'] ) ) {
					$sql['where'] = '( ' . implode( " ) AND ( ", $where ) . ' )';
				} else {
					$sql['where'] = implode( ' OR ', $where );
				}
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

			return $sql;
		}


		/**
		 * Generates SQL JOIN and WHERE clauses for a first-order query clause.
		 *
		 * @since 1.1.0
		 * @version 1.3.2 - fix for array meta key.
		 *
		 * @see \WP_Meta_Query::get_sql_for_clause()
		 *
		 * @global \wpdb $wpdb
		 *
		 * @param array $clause Query clause (passed by reference).
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

			$m_key      = isset( $clause['key'] ) ? $clause['key'] : '';
			$m_key_com  = isset( $clause['compare_key'] ) ? strtoupper( $clause['compare_key'] ) : ( is_array( $m_key ) ? 'IN' : '=' );
			$m_key_type = isset( $clause['type_key'] ) ? strtoupper( $clause['type_key'] ) : 'CHAR';
			$m_val      = isset( $clause['value'] ) ? $clause['value'] : '';
			$m_val_com  = isset( $clause['compare'] ) ? strtoupper( $clause['compare'] ) : ( is_array( $m_val ) ? 'IN' : '=' );
			$m_val_type = isset( $clause['type'] ) ? strtoupper( $clause['type'] ) : 'CHAR';

			$join_id = is_array( $m_key ) ? implode( '|', $m_key ) : $m_key;
			$alias   = $wpdb->prepare( '%i', 'meta_' . $join_id );


			// JOIN ON.
			if ( 'LIKE' === $m_key_com && $m_key ) {
				$join_on = $wpdb->prepare(
					"( $this->primary_table.$this->primary_id_column = $alias.$this->meta_id_column AND $alias.`meta_key` LIKE %s )",
					'%' . $wpdb->esc_like( $m_key ) . '%'
				);
			} elseif ( 'IN' === $m_key_com && $m_key ) {
				$in_string = substr( str_repeat( ',%s', count( (array) $m_key ) ), 1 );
				$join_on   = $wpdb->prepare(
					"( $this->primary_table.$this->primary_id_column = $alias.$this->meta_id_column AND $alias.`meta_key` IN ($in_string) )",
					(array) $m_key
				);
			} elseif ( '=' === $m_key_com && $m_key ) {
				$join_on = $wpdb->prepare(
					"( $this->primary_table.$this->primary_id_column = $alias.$this->meta_id_column AND $alias.`meta_key` = %s )",
					(string) $m_key
				);
			} else {
				$join_on = "( $this->primary_table.$this->primary_id_column = $alias.$this->meta_id_column )";
			}

			// JOIN.
			if ( 'NOT EXISTS' === $m_val_com ) {
				// JOIN clauses for NOT EXISTS have their own syntax.
				$sql['join_l'][ $join_id ] = "LEFT JOIN $this->meta_table AS {$alias} ON {$join_on}";
			} else {
				// All other JOIN clauses.
				$sql['join_i'][ $join_id ] = "INNER JOIN $this->meta_table AS {$alias} ON {$join_on}";
			}
			$this->table_aliases[] = $alias;


			// WHERE for meta_key.
			if ( 'NOT EXISTS' === $m_key_com ) {
				$sql['where'][] = "$alias.$this->meta_id_column IS NULL";
			} elseif ( '' !== $m_key && '=' !== $m_key_com ) {

				if ( in_array( $m_key_com, array( '!=', 'NOT IN', 'NOT LIKE', 'NOT EXISTS', 'NOT REGEXP' ), true ) ) {
					$subquery_alias = $alias . '_sub';
					$compare_start  = 'NOT EXISTS (';
					$compare_start .= "SELECT 1 FROM $this->meta_table AS $subquery_alias ";
					$compare_start .= "WHERE $subquery_alias.post_ID = $alias.post_ID ";
					$compare_end    = 'LIMIT 1';
					$compare_end	 .= ')';
				}

				switch ( $m_key_com ) {
					case '=':
					case 'EXISTS':
						$where = $wpdb->prepare( "$alias.`meta_key` = %s", $m_key ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						break;

					case 'LIKE':
						$compare_value = '%' . $wpdb->esc_like( $m_key ) . '%';
						$where         = $wpdb->prepare( "$alias.`meta_key` LIKE %s", $compare_value ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						break;

					case 'IN':
						$compare_string = "$alias.`meta_key` IN (" . substr( str_repeat( ',%s', count( $m_key ) ), 1 ) . ')';
						$where          = $wpdb->prepare( $compare_string, $m_key ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						break;

					case 'RLIKE':
					case 'REGEXP':
						if ( 'BINARY' === strtoupper( $m_key_type ) ) {
							$cast	 = 'BINARY';
							$almk	 = "CAST($alias.`meta_key` AS BINARY)";
						} else {
							$cast	 = '';
							$almk	 = "$alias.`meta_key`";
						}
						$where = $wpdb->prepare( "$almk $m_key_com $cast %s", $m_key ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						break;

					case '!=':
					case 'NOT EXISTS':
						$compare_string = $compare_start . "AND $subquery_alias.`meta_key` = %s " . $compare_end;
						$where          = $wpdb->prepare( $compare_string, $m_key ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						break;

					case 'NOT LIKE':
						$compare_string = $compare_start . "AND $subquery_alias.`meta_key` LIKE %s " . $compare_end;
						$compare_value  = '%' . $wpdb->esc_like( $m_key ) . '%';
						$where          = $wpdb->prepare( $compare_string, $compare_value ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						break;

					case 'NOT IN':
						$array_subclause = '(' . substr( str_repeat( ',%s', count( $m_key ) ), 1 ) . ') ';
						$compare_string  = $compare_start . "AND $subquery_alias.`meta_key` IN " . $array_subclause . $compare_end;
						$where           = $wpdb->prepare( $compare_string, $m_key ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						break;

					case 'NOT REGEXP':
						if ( 'BINARY' === strtoupper( $m_key_type ) ) {
							$cast = 'BINARY';
							$almk = "CAST($subquery_alias.`meta_key` AS BINARY)";
						} else {
							$cast = '';
							$almk = "$subquery_alias.`meta_key`";
						}
						$compare_string = $compare_start . "AND $almk REGEXP $cast %s " . $compare_end;
						$where          = $wpdb->prepare( $compare_string, $m_key ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						break;
				}

				$sql['where'][] = $where;
			}


			// WHERE for meta_value.
			if ( 'NOT EXISTS' === $m_val_com ) {
				$sql['where'][] = "$alias.$this->meta_id_column IS NULL";
			} elseif ( '' !== $m_val && $m_val_com ) {

				if ( in_array( $m_val_com, array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ), true ) ) {
					if ( ! is_array( $m_val ) ) {
						$m_val = preg_split( '/[,\s]+/', $m_val );
					}
				} elseif ( is_string( $m_val ) ) {
					$m_val = trim( $m_val );
				}

				switch ( $m_val_com ) {
					case 'IN':
					case 'NOT IN':
						$compare_string = '(' . substr( str_repeat( ',%s', count( $m_val ) ), 1 ) . ')';
						$where_val      = $wpdb->prepare( $compare_string, $m_val );
						break;

					case 'BETWEEN':
					case 'NOT BETWEEN':
						$where_val = $wpdb->prepare( '%s AND %s', $m_val[0], $m_val[1] );
						break;

					case 'LIKE':
					case 'NOT LIKE':
						$m_val     = '%' . $wpdb->esc_like( $m_val ) . '%';
						$where_val = $wpdb->prepare( '%s', $m_val );
						break;

					// EXISTS with a value is interpreted as '='.
					case 'EXISTS':
						$m_val_com = '=';
						$where_val = $wpdb->prepare( '%s', $m_val );
						break;

					// 'value' is ignored for NOT EXISTS.
					case 'NOT EXISTS':
						$where_val = '';
						break;

					default:
						$where_val = $wpdb->prepare( '%s', $m_val );
						break;
				}

				if ( 'CHAR' === $m_val_type ) {
					$sql['where'][] = "$alias.`meta_value` {$m_val_com} {$where_val}";
				} elseif ( 'NUMERIC' === $m_val_type ) {
					$sql['where'][] = "CAST($alias.`meta_value` AS UNSIGNED) {$m_val_com} {$where_val}";
				} else  {
					$sql['where'][] = "CAST($alias.`meta_value` AS {$m_val_type}) {$m_val_com} {$where_val}";
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
		 * @see \WP_Query::parse_query()
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