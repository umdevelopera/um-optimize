<?php
namespace um_ext\um_optimize\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( class_exists( 'um_ext\um_optimize\admin\Settings_Color' ) ) {
	return;
}

/**
 * Extends settings.
 * Adds the "Color" tab to wp-admin > Ultimate Member > Settings > Appearance.
 *
 * Get an instance this way: UM()->Optimize()->admin()->settings_color()
 *
 * @package um_ext\um_optimize\admin
 */
class Settings_Color {


	/**
	 * Admin constructor.
	 */
	public function __construct() {
		add_filter( 'um_settings_structure', array( $this, 'extend_settings' ) );
		add_action( 'ultimate-member_page_um_options', array( $this, 'print_script' ) );

		add_filter( 'um_settings_save', array( $this, 'on_save' ) );
	}


	/**
	 * Add fields to the page
	 *
	 * @param  array $settings
	 * @return array
	 */
	public function extend_settings( $settings ) {

		$sections = array(
			$this->settings_section(),
			$this->settings_section_button(),
		);

		$settings['appearance']['sections']['color'] = array(
			'title'         => __( 'Colors', 'um-optimize' ),
			'form_sections' => $sections,
		);

		return $settings;
	}


	/**
	 * Execute on settings save.
	 */
	public function on_save( $settings ) {
		if ( isset( $_REQUEST['section'] ) && 'color' === sanitize_key( wp_unslash( $_REQUEST['section'] ) ) ) {
			UM()->Optimize()->frontend()->color()->generate_variables_file();
		}
	}


	/**
	 * Script for the "Reset colors" button.
	 */
	public function print_script() {
		?>
<script type="text/javascript">
	jQuery( function() {
		jQuery( '#um_options_um_optimize_color_reset' ).on( 'click', function(e) {
			e.preventDefault();

			var $btn = jQuery( e.currentTarget ).prop( 'disabled', true );

			confirm( '<?php esc_html_e( 'Confirm colors reset.', 'um-optimize' ); ?>' ) && wp.ajax.send( 'um_optimize_color_reset', {
				data: {
					nonce: um_admin_scripts.nonce
				},
				success: function( data ) {
					$btn.prop( 'disabled', false ).siblings( '.um_setting_ajax_button_response' ).addClass( 'description complete' ).html( data.message );
					setTimeout( function() {
						$btn.siblings( '.um_setting_ajax_button_response' ).removeClass( 'description complete' ).html( '' );
						window.location.reload();
					}, 1500 );
				},
				error: function( data ) {
					console.log( data );
				}
			});
		});
	});
</script>
		<?php
	}


	/**
	 * Section "Colors".
	 *
	 * @return array
	 */
	public function settings_section() {

		$fields = array(
			array(
				'id'          => 'um_optimize_color',
				'type'        => 'checkbox',
				'label'       => __( 'Customize colors', 'um-optimize' ),
				'description' => __( 'I wish to customize Ultimate Member colors.', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_reset',
				'type'  => 'ajax_button',
				'label' => __( 'Restore default colors', 'um-optimize' ),
				'value' => __( 'Reset colors', 'um-optimize' ),
				'size'  => 'small',
			),
		);

		return array(
			'title'       => __( 'Colors', 'um-optimize' ),
			'description' => __( 'You can use settings below to override default Ultimate Member colors.', 'um-optimize' ),
			'fields'      => $fields,
		);
	}


	/**
	 * Section "Buttons".
	 *
	 * @return array
	 */
	public function settings_section_button() {

		$fields = array(
			array(
				'id'    => 'um_optimize_color_button_primary',
				'type'  => 'color',
				'label' => __( 'Primary button', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_button_primary_hover',
				'type'  => 'color',
				'label' => __( 'Primary button hover', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_button_primary_text',
				'type'  => 'color',
				'label' => __( 'Primary button text', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_button_secondary',
				'type'  => 'color',
				'label' => __( 'Secondary button', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_button_secondary_hover',
				'type'  => 'color',
				'label' => __( 'Secondary button hover', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_button_secondary_text',
				'type'  => 'color',
				'label' => __( 'Secondary button text', 'um-optimize' ),
			),
		);

		return array(
			'title'  => __( 'Buttons', 'um-optimize' ),
			'fields' => $fields,
		);
	}

}
