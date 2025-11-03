<?php
/**
 * Settings class.
 *
 * @package FeaturedArchiveScroller
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FAS_Settings
 *
 * Handles plugin settings and admin page.
 */
class FAS_Settings {

	/**
	 * Single instance of the class.
	 *
	 * @var FAS_Settings
	 */
	private static $instance = null;

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	private $page_slug = 'featured-archive-scroller';

	/**
	 * Option group name.
	 *
	 * @var string
	 */
	private $option_group = 'fas_settings';

	/**
	 * Get single instance of the class.
	 *
	 * @return FAS_Settings
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add settings page to admin menu.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Featured Archive Scroller Settings', 'featured-archive-scroller' ),
			__( 'Archive Scroller', 'featured-archive-scroller' ),
			'manage_options',
			$this->page_slug,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		// Register settings.
		register_setting(
			$this->option_group,
			'fas_enable_gallery',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'default'           => true,
			)
		);

		register_setting(
			$this->option_group,
			'fas_noindex_gallery',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'default'           => true,
			)
		);

		register_setting(
			$this->option_group,
			'fas_show_placeholder',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'default'           => false,
			)
		);

		register_setting(
			$this->option_group,
			'fas_endpoint_slug',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_title',
				'default'           => 'gallery',
			)
		);

		register_setting(
			$this->option_group,
			'fas_show_title_overlay',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'default'           => true,
			)
		);

		// Add settings section.
		add_settings_section(
			'fas_general_section',
			__( 'General Settings', 'featured-archive-scroller' ),
			array( $this, 'render_general_section' ),
			$this->page_slug
		);

		// Add settings fields.
		add_settings_field(
			'fas_enable_gallery',
			__( 'Enable Gallery Endpoint', 'featured-archive-scroller' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug,
			'fas_general_section',
			array(
				'label_for'   => 'fas_enable_gallery',
				'description' => __( 'Enable the /gallery endpoint on archive pages.', 'featured-archive-scroller' ),
				'default'     => true,
			)
		);

		add_settings_field(
			'fas_endpoint_slug',
			__( 'Endpoint Slug', 'featured-archive-scroller' ),
			array( $this, 'render_text_field' ),
			$this->page_slug,
			'fas_general_section',
			array(
				'label_for'   => 'fas_endpoint_slug',
				'description' => __( 'The URL slug for the gallery endpoint (default: gallery).', 'featured-archive-scroller' ),
			)
		);

		add_settings_field(
			'fas_noindex_gallery',
			__( 'Noindex Gallery Pages', 'featured-archive-scroller' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug,
			'fas_general_section',
			array(
				'label_for'   => 'fas_noindex_gallery',
				'description' => __( 'Add noindex meta tag to gallery pages to prevent search engine indexing.', 'featured-archive-scroller' ),
				'default'     => true,
			)
		);

		add_settings_field(
			'fas_show_placeholder',
			__( 'Show Placeholder Images', 'featured-archive-scroller' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug,
			'fas_general_section',
			array(
				'label_for'   => 'fas_show_placeholder',
				'description' => __( 'Show placeholder for posts without featured images.', 'featured-archive-scroller' ),
				'default'     => false,
			)
		);

		add_settings_field(
			'fas_show_title_overlay',
			__( 'Show Title Overlay on Hover', 'featured-archive-scroller' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug,
			'fas_general_section',
			array(
				'label_for'   => 'fas_show_title_overlay',
				'description' => __( 'Display post title and excerpt overlay when hovering over images.', 'featured-archive-scroller' ),
				'default'     => true,
			)
		);
	}

	/**
	 * Sanitize checkbox value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @return bool
	 */
	public function sanitize_checkbox( $value ) {
		// Checkboxes return '1' when checked, nothing when unchecked.
		return ! empty( $value ) ? true : false;
	}

	/**
	 * Render general settings section.
	 */
	public function render_general_section() {
		echo '<p>' . esc_html__( 'Configure the gallery endpoint behavior and appearance.', 'featured-archive-scroller' ) . '</p>';
	}

	/**
	 * Render checkbox field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_checkbox_field( $args ) {
		$default = isset( $args['default'] ) ? $args['default'] : false;
		$option  = get_option( $args['label_for'] );

		// If option doesn't exist, use default.
		if ( false === $option ) {
			$option = $default;
		}
		?>
		<label>
			<input
				type="checkbox"
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				name="<?php echo esc_attr( $args['label_for'] ); ?>"
				value="1"
				<?php checked( $option, true ); ?>
			/>
			<?php if ( ! empty( $args['description'] ) ) : ?>
				<span class="description"><?php echo esc_html( $args['description'] ); ?></span>
			<?php endif; ?>
		</label>
		<?php
	}

	/**
	 * Render text field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_text_field( $args ) {
		$option = get_option( $args['label_for'], 'gallery' );
		?>
		<input
			type="text"
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="<?php echo esc_attr( $args['label_for'] ); ?>"
			value="<?php echo esc_attr( $option ); ?>"
			class="regular-text"
		/>
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if settings were saved.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking WordPress core settings-updated parameter.
		if ( isset( $_GET['settings-updated'] ) ) {
			// Flush rewrite rules if endpoint slug changed.
			flush_rewrite_rules();

			add_settings_error(
				'fas_messages',
				'fas_message',
				__( 'Settings saved successfully.', 'featured-archive-scroller' ),
				'success'
			);
		}

		// Show error/update messages.
		settings_errors( 'fas_messages' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( $this->option_group );
				do_settings_sections( $this->page_slug );
				submit_button( __( 'Save Settings', 'featured-archive-scroller' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Check if gallery endpoint is enabled.
	 *
	 * @return bool
	 */
	public function is_gallery_enabled() {
		return (bool) get_option( 'fas_enable_gallery', true );
	}

	/**
	 * Check if noindex is enabled for gallery pages.
	 *
	 * @return bool
	 */
	public function is_noindex_enabled() {
		return (bool) get_option( 'fas_noindex_gallery', true );
	}

	/**
	 * Check if placeholder images should be shown.
	 *
	 * @return bool
	 */
	public function show_placeholder() {
		return (bool) get_option( 'fas_show_placeholder', false );
	}

	/**
	 * Check if title overlay should be shown.
	 *
	 * @return bool
	 */
	public function show_title_overlay() {
		return (bool) get_option( 'fas_show_title_overlay', true );
	}
}
