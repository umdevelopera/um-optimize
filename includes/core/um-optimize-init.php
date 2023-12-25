<?php if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main class of the "Ultimate Member - Optimize" plugin
 *
 * @example UM()->classes['Optimize']
 * @example UM()->Optimize()
 */
class UM_Optimize {


	/**
	 * Class object
	 * @var UM_Optimize
	 */
	private static $instance;


	/**
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
		//add_filter( 'um_call_object_Optimize', array( &$this, 'get_this' ) );

		add_action( 'plugins_loaded', array( &$this, 'init' ), 1 );

		if ( UM()->is_request( 'admin' ) ) {

		} elseif( UM()->is_ajax() ) {
			
		} else{
			$this->assets();
		}
	}


	/**
	 * Optimize assets
	 * @return um_ext\um_optimize\core\Assets()
	 */
	public function assets() {
		if ( empty( UM()->classes['um_optimize_assets'] ) ) {
			UM()->classes['um_optimize_assets'] = new um_ext\um_optimize\core\Assets();
		}
		return UM()->classes['um_optimize_assets'];
	}


	/**
	 * Get class object
	 * @return $this
	 */
	public function get_this() {
		return $this;
	}


	/**
	 * Init
	 */
	public function init() {

		/* locale */
		$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
		load_textdomain( um_optimize_textdomain, WP_LANG_DIR . '/plugins/' . um_optimize_textdomain . '-' . $locale . '.mo' );
		load_plugin_textdomain( um_optimize_textdomain, false, dirname( plugin_basename(  __FILE__ ) ) . '/languages/' );

		// actions
		//require_once um_optimize_path . 'includes/core/actions/um-mailchimp-account.php';

		// filters
		//require_once um_optimize_path . 'includes/core/filters/um-mailchimp-account.php';

	}


	/**
	 * Setup
	 * @return um_ext\um_optimize\core\Setup()
	 */
	public function setup() {
		if ( empty( UM()->classes['um_optimize_setup'] ) ) {
			UM()->classes['um_optimize_setup'] = new um_ext\um_optimize\core\Setup();
		}
		return UM()->classes['um_optimize_setup'];
	}

}

//create class var
add_action( 'plugins_loaded', 'um_init_optimize', -10, 1 );
function um_init_optimize() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'Optimize', true );
	}
}