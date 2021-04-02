<?php

class Wordpress_Custom_Settings {

	const PAGE_TITLE = 'Bahgaty Registrations Settings';
	const MENU_SLUG = 'wordpress_custom_settings';


	const SETTING_NAME = 'bahgaty_registrations_settings';
	const SETTING_SECTION_TITLE = self::PAGE_TITLE;
	const SETTING_SECTION_DESCRIPTION = '';
	const SETTING_PAGE = self::MENU_SLUG;

	private $_submenu_parent_slug = 'options-general.php';
	private $setting_fields = array();

	protected $page_title;
	protected $menu_title;
	protected $menu_slug;
	protected $sections;

	private static $instance = null;

	public static function instance() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->page_title = 'Wordpress Custom Settings';
		$this->menu_title = 'Wordpress Custom Settings';
		$this->menu_slug  = 'wordpress_custom_settings';

		$this->sections = array(
			'section1' => array(
				'title'       => 'Section1-title',
				'description' => 'Section1-description',
			)
		);

		$this->setting_fields = array(
			'bahgaty_registrations_health_educator_role' => array(
				'title' => 'Health Educator Role',
				'args'  => array(
					'type' => 'user_roles'
				)
			),
		);

		add_action( 'admin_menu', array( $this, 'settings_page' ) );
		add_filter( 'allowed_options', array( $this, 'allowed_options' ) );
		add_action( 'admin_init', array( $this, 'add_setting_sections' ) );
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'admin_init', array( $this, 'register_setting_field' ) );
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
	 * @return string[][]
	 */
	public function get_sections(): array {
		return $this->sections;
	}

	/**
	 * @param string[][] $sections
	 */
	public function set_sections( array $sections ): void {
		$this->sections = $sections;
	}

	public function settings_page() {
//		add_menu_page(
		add_submenu_page(
			$this->_submenu_parent_slug,
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
			add_settings_error( $page, $page . '_message', __( 'Settings Saved', 'bahgaty-registrations' ), 'success' );
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

		foreach ( $this->sections as $section_name => $section ) {
			add_settings_section(
				$section_name,
				$section['title'],
				array( $this, 'settings_section_callback' ),
				$this->get_menu_slug()
			);
		}
	}

	public function register_setting() {
		$settings = ! is_array( $this->setting_fields ) ? array( $this->setting_fields ) : $this->setting_fields;

		foreach ( $settings as $setting_name => $setting ) {
			register_setting( $this->get_menu_slug(), $setting_name );
		}
	}

	public function register_setting_field() {
		$setting_fields = ! is_array( $this->setting_fields ) ? array( $this->setting_fields ) : $this->setting_fields;

		foreach ( $setting_fields as $field_name => $field ) {
			$args = ! empty( $field['args'] ) ? $field['args'] : array();

			$args['field_name'] = $field_name;

			add_settings_field(
				$field_name,
				$field['title'],
				array( $this, 'settings_field_callback' ),
				self::SETTING_PAGE,
				self::SETTING_NAME . '_section',
				$args
			);
		}
	}

	public function settings_section_callback( $args ) {
		$that         = $args['callback'][0];
		$section_name = $args['id'];
		$description  = ! empty( $that->sections[ $section_name ]['description'] ) ? $that->sections[ $section_name ]['description'] : '';
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
					)
				);
				break;
			case 'checkbox':
				?>
                <input type="<?php echo $type; ?>" name="<?php echo $field_name; ?>" value="Y" <?php echo 'Y' == $setting ? 'checked' : ''; ?>>
				<?php
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

			if ( ! empty( $this->setting_fields[ $setting_name ]['args']['type'] ) && 'editor' == $this->setting_fields[ $setting_name ]['args']['type'] ) {
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

	public function get_pages_ids() {
		$settings = $this->get_all_settings();
		unset( $settings['bahgaty_registrations_health_educator_role'] );

		return $settings;
	}

	private function _user_roles_field( $field_name, $value ) {
		ob_start();
		?>
        <select name="<?php echo esc_attr( $field_name ); ?>">
            <option value="-1"><?php esc_html_e( 'None', 'bahgaty-registrations' ); ?></option>
			<?php wp_dropdown_roles( $value ); ?>
        </select>
		<?php
		return ob_get_clean();
	}
}