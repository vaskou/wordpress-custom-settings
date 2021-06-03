<?php

namespace WordpressCustomSettings;

abstract class SettingsSetup {

	protected $submenu_parent_slug;
	protected $page_title;
	protected $menu_title;
	protected $menu_slug;

	/**
	 * @var SettingSection[]
	 */
	private $sections = array();

	/**
	 * @var SettingField[]
	 */
	private $setting_fields = array();

	protected function __construct() {
		add_action( 'admin_menu', array( $this, 'settings_page' ) );
		add_filter( 'allowed_options', array( $this, 'allowed_options' ) );
		add_action( 'admin_init', array( $this, 'add_setting_sections' ) );
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'admin_init', array( $this, 'register_setting_field' ) );
	}

	/**
	 * @return string
	 */
	public function get_submenu_parent_slug(): string {
		return $this->submenu_parent_slug;
	}

	/**
	 * @param string $submenu_parent_slug
	 */
	public function set_submenu_parent_slug( string $submenu_parent_slug ): void {
		$this->submenu_parent_slug = $submenu_parent_slug;
	}

	/**
	 * @return string
	 */
	public function get_page_title(): string {
		return $this->page_title;
	}

	/**
	 * @param string $page_title
	 */
	public function set_page_title( string $page_title ): void {
		$this->page_title = $page_title;
	}

	/**
	 * @return string
	 */
	public function get_menu_title(): string {
		return $this->menu_title;
	}

	/**
	 * @param mixed $menu_title
	 */
	public function set_menu_title( $menu_title ): void {
		$this->menu_title = $menu_title;
	}

	/**
	 * @return string
	 */
	public function get_menu_slug(): string {
		return $this->menu_slug;
	}

	/**
	 * @param string $menu_slug
	 */
	public function set_menu_slug( string $menu_slug ): void {
		$this->menu_slug = $menu_slug;
	}

	/**
	 * @return SettingSection[]
	 */
	public function get_sections(): array {
		return $this->sections;
	}

	/**
	 * @param SettingSection $section
	 */
	public function add_section( SettingSection $section ): void {
		$this->sections[ $section->get_name() ] = $section;
	}

	/**
	 * @return SettingField[]
	 */
	public function get_setting_fields(): array {
		return $this->setting_fields;
	}

	/**
	 * @param SettingField $setting_field
	 */
	public function add_setting_field( $setting_field ): void {
		$this->setting_fields[ $setting_field->get_name() ] = $setting_field;
	}

	public function settings_page() {
//		add_menu_page(
		add_submenu_page(
			$this->submenu_parent_slug,
			$this->get_page_title(),
			$this->get_menu_title(),
			'manage_options',
			$this->get_menu_slug(),
			array( $this, 'add_menu_page_callback' )
		);
	}

	public function add_menu_page_callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page = $this->get_menu_slug();

		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error( $page, $page . '_message', __( 'Settings Saved' ), 'success' );
		}

		settings_errors( $page );
		?>
        <form action="options.php" method="post">
			<?php
			settings_fields( $page );
			do_settings_sections( $page );
			submit_button( 'Save Settings' );
			?>
        </form>
		<?php
	}

	public function allowed_options( $allowed_options ) {
		$options = array();

		foreach ( $this->setting_fields as $field_name => $field_args ) {
			array_push( $options, $field_name );
		}

		$allowed_options[ $this->get_menu_slug() ] = $options;

		return $allowed_options;
	}

	public function add_setting_sections() {

		foreach ( $this->sections as $section ) {
			add_settings_section(
				$section->get_name(),
				$section->get_title(),
				array( $this, 'settings_section_callback' ),
				$this->get_menu_slug()
			);
		}
	}

	public function register_setting() {
		$settings = ! is_array( $this->setting_fields ) ? array( $this->setting_fields ) : $this->setting_fields;

		foreach ( $settings as $setting ) {
			register_setting( $this->get_menu_slug(), $setting->get_name() );
		}
	}

	public function register_setting_field() {
		if ( ! empty( $this->setting_fields ) ) {

			foreach ( $this->setting_fields as $field ) {

				$field_name    = $field->get_name();
				$field_title   = $field->get_title();
				$field_section = $field->get_section();
				$args          = $field->get_args();

				$args['field_name'] = $field_name;

				add_settings_field(
					$field_name,
					$field_title,
					array( $this, 'settings_field_callback' ),
					$this->get_menu_slug(),
					$field_section,
					$args
				);
			}
		}
	}

	public function settings_section_callback( $args ) {
		$section_name = (string) $args['id'];
		$description  = ! empty( $this->sections[ $section_name ]->get_description() ) ? $this->sections[ $section_name ]->get_description() : '';

		if ( ! empty( $description ) ):
			?>
            <p>
				<?php echo $description; ?>
            </p>
		<?php
		endif;
	}


	public function settings_field_callback( $args ) {

		$field_name = $args['field_name'];

		$setting = get_option( $field_name );

		$type = ! empty( $args['type'] ) ? $args['type'] : 'text';

		$classes = ! empty( $args['classes'] ) ? $this->_get_classes( $args['classes'] ) : '';

		$options = ! empty( $args['options'] ) ? $args['options'] : array();

		switch ( $type ) {

			case 'text':
			case 'number':
			case 'password':
			case 'url':

				$style = '';//'width:100%;';
				if ( 'number' == $type ) {
					$style = '';
				}
				?>

                <input type="<?php echo $type; ?>"
                       name="<?php echo $field_name; ?>"
                       value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>"
                       class="<?php echo esc_attr( $classes ); ?>"
                       style="<?php echo $style; ?>">
				<?php
				break;

			case 'pages':
				echo wp_dropdown_pages(
					array(
						'name'              => $field_name,
						'echo'              => 0,
						'show_option_none'  => __( '&mdash; Select &mdash;' ),
						'option_none_value' => '0',
						'selected'          => $setting,
						'class'             => $classes,
					)
				);
				break;
			case 'checkbox':
				?>
                <input type="<?php echo $type; ?>"
                       class="<?php echo esc_attr( $classes ); ?>"
                       name="<?php echo $field_name; ?>"
                       value="Y" <?php echo 'Y' == $setting ? 'checked' : ''; ?>>
				<?php
				break;
			case 'select':
				echo $this->_get_select( $field_name, $setting, $options, $classes );
				break;
			case 'multiselect':
				echo $this->_get_select( $field_name, $setting, $options, $classes, true );
				break;
			case 'editor':
				wp_editor( $setting, $field_name, array(
					'textarea_rows' => 10,
				) );
				break;
			case 'user_roles':
				echo $this->_user_roles_field( $field_name, $setting );
				break;
		}
	}

	public function get_setting( $setting_name, $default = false ) {
		$setting = false;
		if ( array_key_exists( $setting_name, $this->setting_fields ) ) {
			$setting = get_option( $setting_name, $default );

			if ( ! empty( $this->setting_fields[ $setting_name ]->get_type() ) && 'editor' == $this->setting_fields[ $setting_name ]->get_type() ) {
				global $wp_embed;
				$content = $wp_embed->autoembed( $setting );
				$content = $wp_embed->run_shortcode( $content );
				$content = wpautop( $content );
				$setting = do_shortcode( $content );
			}
		}

		return $setting;
	}

	public function get_all_settings() {
		$settings      = array();
		$setting_names = array_keys( $this->setting_fields );

		foreach ( $setting_names as $setting_name ) {
			$settings[ $setting_name ] = $this->get_setting( $setting_name );
		}

		return $settings;
	}

	public function get_settings_page_url() {

		return add_query_arg(
			array(
				'page' => $this->get_menu_slug()
			),
			admin_url( $this->get_submenu_parent_slug() )
		);
	}

	/**
	 * @param string $field_name
	 * @param string $value
	 * @param string $classes
	 *
	 * @return false|string
	 */
	private function _user_roles_field( $field_name, $value, $classes = '' ) {
		ob_start();
		?>
        <select name="<?php echo esc_attr( $field_name ); ?>" class="<?php echo esc_attr( $classes ); ?>">
            <option value="-1"><?php esc_html_e( 'None' ); ?></option>
			<?php wp_dropdown_roles( $value ); ?>
        </select>
		<?php
		return ob_get_clean();
	}

	/**
	 * @param string $field_name
	 * @param string $value
	 * @param array $options
	 * @param string $classes
	 * @param bool $multiselect
	 *
	 * @return false|string
	 */
	private function _get_select( $field_name, $value, $options = array(), $classes = '', $multiple = false ) {
		if ( $multiple ) {
			$field_name .= '[]';
			$multiple   = 'multiple';
		}

		ob_start();
		?>
        <select name="<?php echo $field_name; ?>" class="<?php echo esc_attr( $classes ); ?>" <?php echo esc_attr( $multiple ); ?>>
			<?php foreach ( $options as $key => $option ):

				if ( is_array( $value ) ) {
					$selected = selected( in_array( $key, $value ), true, false );
				} else {
					$selected = selected( $value, $key, false );
				}
				?>

                <option value="<?php echo esc_attr( $key ); ?>" <?php echo $selected; ?>><?php echo $option; ?></option>
			<?php endforeach; ?>
        </select>
		<?php
		return ob_get_clean();
	}

	private function _get_classes( $classes ) {

		if ( empty( $classes ) ) {
			return '';
		}

		return is_array( $classes ) ? implode( ' ', $classes ) : $classes;
	}
}