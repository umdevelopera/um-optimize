<?php
/**
 * Init the extension.
 *
 * @package um_ext\um_optimize\core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UM_Optimize
 *
 * How to get an instance:
 *  UM()->classes['Optimize']
 *  UM()->Optimize()
 *
 * @package um_ext\um_optimize\core
 */
class UM_Optimize {


	/**
	 * An instance of the class.
	 *
	 * @var UM_Optimize
	 */
	private static $instance;


	/**
	 * Creates an instance of the class.
	 *
	 * @return UM_Optimize
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Class constructor
	 */
	public function __construct() {
		if ( UM()->is_request( 'admin' ) ) {

		} elseif( UM()->is_ajax() ) {

		} elseif ( UM()->is_request( 'frontend' ) ) {
			$this->assets();
		}
	}


	/**
	 * Optimize assets
	 *
	 * @return um_ext\um_optimize\core\Assets()
	 */
	public function assets() {
		if ( empty( UM()->classes['um_optimize_assets'] ) ) {
			UM()->classes['um_optimize_assets'] = new um_ext\um_optimize\core\Assets();
		}
		return UM()->classes['um_optimize_assets'];
	}

}
