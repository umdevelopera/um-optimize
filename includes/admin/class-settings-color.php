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
		// Settings.
		add_filter( 'um_settings_structure', array( $this, 'extend_settings' ) );

		// Custom fields in setttings.
		add_filter( 'um_render_field_type_color_actions', array( $this, 'render_color_actions' ), 10, 4 );

		// Actions.
		add_action( 'admin_action_um_optimize_color_export', array( $this, 'action_export' ) );
		add_action( 'admin_action_um_optimize_color_reset', array( $this, 'action_reset' ) );
		add_filter( 'um_adm_action_custom_update_notice', array( $this, 'admin_notices' ), 10, 2 );

		// Save handlers.
		add_action( 'admin_init', array( $this, 'action_import' ), 9 );
		add_filter( 'um_settings_save', array( $this, 'action_save' ) );
	}


	/**
	 * Handle the "Export colors" action.
	 */
	public function action_export() {
		check_admin_referer( 'um_optimize_color_export', 'nonce' );

		$colors = UM()->Optimize()->setup()->get_default_colors();
		foreach( $colors as $option => &$value ) {
			if ( UM()->options()->get( $option ) ) {
				$value = sanitize_hex_color( UM()->options()->get( $option ) );
			}
		}

		$time = wp_date( 'Y-m-d H-i' );
		$site = untrailingslashit( strtr(
			site_url(),
			array(
				'http://'  => '',
				'https://' => '',
			)
		) );
		$data = array(
			'name'    => 'Custom colors for Ultimate Member',
			'site'    => $site,
			'time'    => $time,
			'options' => $colors,
		);

		$json = wp_json_encode( $data );

		$size = strlen( $json );
		$name = "Custom colors for Ultimate Member ($site) $time.json";
		$type = 'application/json';

		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: inline; filename="' . $name . '"' );
		header( 'Content-Length: ' . $size );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Type: ' . $type );
		header( 'Cache-Control: must-revalidate, no-cache, no-store' );

		$levels = ob_get_level();
		for ( $i = 0; $i < $levels; $i++ ) {
			@ob_end_clean();
		}

		exit( $json );
	}


	/**
	 * Handle the "Import colors" action.
	 */
	public function action_import() {
		if ( empty( $_POST['um_optimize_color_import'] ) || empty( $_FILES['colorsjson'] ) ) {
			return;
		}

		check_admin_referer( 'um-settings-nonce', '__umnonce' );

		if ( "application/json" !== $_FILES['colorsjson']['type'] ) {
			$redirect_url = admin_url( 'admin.php?page=um_options&tab=appearance&section=color&update=um_optimize_color_import_err' );
		} else {
			$assoc = array( 'associative' => true );
			$json  = wp_json_file_decode( $_FILES['colorsjson']['tmp_name'], $assoc );
			if ( ! is_array( $json ) || ! array_key_exists( 'options', $json ) ) {
				$redirect_url = admin_url( 'admin.php?page=um_options&tab=appearance&section=color&update=um_optimize_color_import_err' );
			} else {
				$colors  = UM()->Optimize()->setup()->get_default_colors();
				$options = get_option( 'um_options', array() );
				foreach( $colors as $option => $value ) {
					if ( array_key_exists( $option, $json['options'] ) ) {
						$options[ $option ] = sanitize_hex_color( $json['options'][ $option ] );
					}
				}
				update_option( 'um_options', $options );
				$redirect_url = admin_url( 'admin.php?page=um_options&tab=appearance&section=color&update=um_optimize_color_import' );
			}
		}

		exit( wp_safe_redirect( $redirect_url ) );
	}


	/**
	 * Handle the "Reset colors" action.
	 */
	public function action_reset() {
		check_admin_referer( 'um_optimize_color_reset', 'nonce' );

		UM()->Optimize()->setup()->set_default_colors();

		$redirect_url = admin_url( 'admin.php?page=um_options&tab=appearance&section=color&update=um_optimize_color_reset' );
		exit( wp_safe_redirect( $redirect_url ) );
	}


	/**
	 * Execute on settings save.
	 */
	public function action_save( $settings ) {
		if ( isset( $_REQUEST['section'] ) && 'color' === sanitize_key( wp_unslash( $_REQUEST['section'] ) ) ) {
			UM()->Optimize()->setup()->generate_variables_file();
		}
	}


	/**
	 * Add custom admin notice after the "Reset colors" action.
	 *
	 * @param array  $messages Admin notice messages.
	 * @param string $update   Update action key.
	 *
	 * @return array Admin notice messages.
	 */
	public function admin_notices( $messages, $update ) {
		if ( 'um_optimize_color_reset' === $update ) {
			UM()->Optimize()->setup()->generate_variables_file();
			$messages[0]['content'] = __( 'Colors have been reset to default values.', 'um-optimize' );
		}
		if ( 'um_optimize_color_import' === $update ) {
			UM()->Optimize()->setup()->generate_variables_file();
			$messages[0]['content'] = __( 'Colors have been imported.', 'um-optimize' );
		}
		if ( 'um_optimize_color_import_err' === $update ) {
			$messages[0]['err_content'] = __( 'Colors have not been imported.', 'um-optimize' );
		}
		return $messages;
	}


	/**
	 * Add fields to the page
	 *
	 * @param  array $settings UM Settings.
	 * @return array  UM Settings.
	 */
	public function extend_settings( $settings ) {

		$sections = array(
			$this->settings_section(),
			$this->settings_section_common(),
			$this->settings_section_button(),
			$this->settings_section_field(),
			$this->settings_section_menu(),
		);

		$settings['appearance']['sections']['color'] = array(
			'title'         => __( 'Colors', 'um-optimize' ),
			'form_sections' => $sections,
		);

		return $settings;
	}


	/**
	 * Render field type "mailchimp_api_key"
	 *
	 * @hook   um_render_field_type_mailchimp_api_key
	 *
	 * @param  string $html        A field output html.
	 * @param  array  $data        A field data.
	 * @param  array  $form_data   A form data.
	 * @param  object $admin_form  A form object.
	 * @return string
	 */
	public function render_color_actions( $html, $data, $form_data, $admin_form ) {
		if ( empty( $data['id'] ) ) {
			return false;
		}

		$reset_link = add_query_arg(
			array(
				'action' => 'um_optimize_color_reset',
				'nonce'  => wp_create_nonce( 'um_optimize_color_reset' ),
			),
			admin_url()
		);

		$export_link = add_query_arg(
			array(
				'action' => 'um_optimize_color_export',
				'nonce'  => wp_create_nonce( 'um_optimize_color_export' ),
			),
			admin_url()
		);

		ob_start();
		?>
<div style="background: #f6f7f7; border: 1px solid #c3c4c7; padding: 15px;">
	<a class="button" id="um_optimize_color_reset" href="<?php echo esc_attr( $reset_link ); ?>"
		 title="<?php esc_attr_e( 'Restore default colors', 'um-optimize' ); ?>">
		<?php esc_html_e( 'Reset colors', 'um-optimize' ); ?>
	</a>
	<a class="button" id="um_optimize_color_export" href="<?php echo esc_attr( $export_link ); ?>" download
		 title="<?php esc_attr_e( 'Save colors to the file', 'um-optimize' ); ?>">
		<?php esc_html_e( 'Export colors', 'um-optimize' ); ?>
	</a>
	<button class="button" id="um_optimize_color_import" type="submit" formenctype="multipart/form-data" name="um_optimize_color_import" value="1" disabled
					title="<?php esc_attr_e( 'Load colors from the file', 'um-optimize' ); ?>">
		<?php esc_html_e( 'Import colors', 'um-optimize' ); ?>
	</button>
	<input id="colorsjson" name="colorsjson" type="file" accept="application/json" style="line-height: 30px; padding: 0px;">
</div>
<script type="text/javascript">
	jQuery( function() {
		jQuery( '#um_optimize_color_reset' ).on( 'click', function(e) {
			var message = '<?php esc_html_e( 'Confirm colors reset.', 'um-optimize' ); ?>' + '\n'
					+ '<?php esc_html_e( 'Current colors will be overridden.', 'um-optimize' ); ?>';
			return confirm( message );
		} );

		jQuery( '#um_optimize_color_import' ).on( 'click', function(e) {
			var message = '<?php esc_html_e( 'Confirm colors import.', 'um-optimize' ); ?>' + '\n'
					+ '<?php esc_html_e( 'Current colors will be overridden.', 'um-optimize' ); ?>';
			return confirm( message );
		} );

		jQuery( '#colorsjson' ).on( 'change', function(e) {
			jQuery( e.currentTarget ).siblings( '#um_optimize_color_import' ).prop( 'disabled', !e.target.value );
		} );
	});
</script>
		<?php
		return ob_get_clean();
	}


	/**
	 * Section "Colors".
	 *
	 * @return array
	 */
	public function settings_section() {

		$fields = array(
			array(
				'id'    => 'um_optimize_color_actions',
				'type'  => 'color_actions',
				'label' => __( 'Tools', 'um-optimize' ),
			),
			array(
				'id'          => 'um_optimize_color',
				'type'        => 'checkbox',
				'label'       => __( 'Enable custom colors', 'um-optimize' ),
				'description' => __( 'I wish to customize Ultimate Member colors.', 'um-optimize' ),
			),
		);

		return array(
			'title'       => __( 'Colors', 'um-optimize' ),
			'description' => __( 'You can use tools to export/import the color set or restore the default color set. Turn on "Enable custom colors" and use settings below to customize Ultimate Member colors.', 'um-optimize' ),
			'fields'      => $fields,
		);
	}


	/**
	 * Section "Links and buttons".
	 *
	 * @return array
	 */
	public function settings_section_button() {

		$fields = array(
			array(
				'id'    => 'um_optimize_color_link',
				'type'  => 'color',
				'label' => __( 'Link', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_link_hover',
				'type'  => 'color',
				'label' => __( 'Link hover', 'um-optimize' ),
			),
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
			'title'  => __( 'Links and buttons', 'um-optimize' ),
			'fields' => $fields,
		);
	}


	/**
	 * Section "Common".
	 *
	 * @return array
	 */
	public function settings_section_common() {

		$fields = array(
			array(
				'id'    => 'um_optimize_color_common_active',
				'type'  => 'color',
				'label' => __( 'Active element', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_common_active_text',
				'type'  => 'color',
				'label' => __( 'Active element text', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_common_background',
				'type'  => 'color',
				'label' => __( 'Background', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_common_light_line',
				'type'  => 'color',
				'label' => __( 'Light line', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_common_line',
				'type'  => 'color',
				'label' => __( 'Line', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_common_light_text',
				'type'  => 'color',
				'label' => __( 'Light text', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_common_text',
				'type'  => 'color',
				'label' => __( 'Text', 'um-optimize' ),
			),
		);

		return array(
			'title'  => __( 'Common', 'um-optimize' ),
			'fields' => $fields,
		);
	}


	/**
	 * Section "Fields and filters".
	 *
	 * @return array
	 */
	public function settings_section_field() {

		$fields = array(
			array(
				'id'    => 'um_optimize_color_field_active',
				'type'  => 'color',
				'label' => __( 'Active element', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_field_background',
				'type'  => 'color',
				'label' => __( 'Background', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_field_background_item',
				'type'  => 'color',
				'label' => __( 'Background for item', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_field_border',
				'type'  => 'color',
				'label' => __( 'Border', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_field_placeholder',
				'type'  => 'color',
				'label' => __( 'Placeholder', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_field_text',
				'type'  => 'color',
				'label' => __( 'Text', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_field_label',
				'type'  => 'color',
				'label' => __( 'Label', 'um-optimize' ),
			),
		);

		return array(
			'title'  => __( 'Fields and filters', 'um-optimize' ),
			'fields' => $fields,
		);
	}


	/**
	 * Section "Profile menu".
	 *
	 * @return array
	 */
	public function settings_section_menu() {

		$fields = array(
			array(
				'id'    => 'um_optimize_color_menu_active',
				'type'  => 'color',
				'label' => __( 'Active tab', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_menu_background',
				'type'  => 'color',
				'label' => __( 'Background', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_menu_hover',
				'type'  => 'color',
				'label' => __( 'Hover', 'um-optimize' ),
			),
			array(
				'id'    => 'um_optimize_color_menu_text',
				'type'  => 'color',
				'label' => __( 'Text', 'um-optimize' ),
			),
		);

		return array(
			'title'  => __( 'Profile menu', 'um-optimize' ),
			'fields' => $fields,
		);
	}

}
