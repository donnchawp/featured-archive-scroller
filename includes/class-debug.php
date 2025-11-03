<?php
/**
 * Debug helper class.
 *
 * @package FeaturedArchiveScroller
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FAS_Debug
 *
 * Provides debugging tools for the plugin.
 */
class FAS_Debug {

	/**
	 * Single instance of the class.
	 *
	 * @var FAS_Debug
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class.
	 *
	 * @return FAS_Debug
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
		add_action( 'admin_menu', array( $this, 'add_debug_page' ) );
		add_action( 'admin_post_fas_flush_rewrite', array( $this, 'handle_flush_rewrite' ) );
	}

	/**
	 * Add debug page to admin menu.
	 */
	public function add_debug_page() {
		add_submenu_page(
			'options-general.php',
			__( 'Archive Scroller Debug', 'featured-archive-scroller' ),
			__( 'Archive Scroller Debug', 'featured-archive-scroller' ),
			'manage_options',
			'fas-debug',
			array( $this, 'render_debug_page' )
		);
	}

	/**
	 * Handle flush rewrite action.
	 */
	public function handle_flush_rewrite() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_admin_referer( 'fas_flush_rewrite' );

		// Re-register endpoint.
		$endpoint = FAS_Endpoint::get_instance();
		$endpoint->register_endpoint();

		// Flush rewrite rules.
		flush_rewrite_rules();

		wp_redirect( admin_url( 'options-general.php?page=fas-debug&flushed=1' ) );
		exit;
	}

	/**
	 * Render debug page.
	 */
	public function render_debug_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Just checking for success message.
		if ( isset( $_GET['flushed'] ) ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Rewrite rules flushed successfully!', 'featured-archive-scroller' ) . '</p></div>';
		}
		?>
		<div class="wrap">
			<h1>Archive Scroller Debug</h1>

			<h2>Flush Rewrite Rules</h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="fas_flush_rewrite" />
				<?php wp_nonce_field( 'fas_flush_rewrite' ); ?>
				<p>Click this button to manually flush rewrite rules and re-register the gallery endpoint.</p>
				<?php submit_button( 'Flush Rewrite Rules' ); ?>
			</form>

			<hr />

			<h2>Query Vars</h2>
			<p><strong>Gallery query var registered:</strong>
				<?php
				global $wp;
				if ( in_array( 'gallery', $wp->public_query_vars ) ) {
					echo '<span style="color:green;">✓ YES</span>';
				} else {
					echo '<span style="color:red;">✗ NO</span>';
				}
				?>
			</p>

			<hr />

			<h2>Rewrite Rules</h2>
			<p>Looking for "gallery" in rewrite rules:</p>
			<pre style="background: #f5f5f5; padding: 10px; overflow: auto; max-height: 400px;"><?php
				$rules = get_option( 'rewrite_rules' );
				$found = false;

				if ( is_array( $rules ) ) {
					foreach ( $rules as $pattern => $replacement ) {
						if ( strpos( $pattern, 'gallery' ) !== false || strpos( $replacement, 'gallery' ) !== false ) {
							echo '<strong style="color:green;">FOUND:</strong> ' . esc_html( $pattern ) . ' => ' . esc_html( $replacement ) . "\n";
							$found = true;
						}
					}
				}

				if ( ! $found ) {
					echo '<strong style="color:red;">No "gallery" rules found!</strong>';
				}
			?></pre>

			<hr />

			<h2>Test URLs</h2>
			<p>Try these URLs to test the gallery endpoint:</p>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/2025/' ) ); ?>" target="_blank"><?php echo esc_url( home_url( '/2025/' ) ); ?></a> (base archive)</li>
				<li><a href="<?php echo esc_url( home_url( '/2025/gallery/' ) ); ?>" target="_blank"><?php echo esc_url( home_url( '/2025/gallery/' ) ); ?></a> (gallery endpoint)</li>
			</ul>

			<hr />

			<h2>Current Request Info</h2>
			<?php
			if ( is_admin() ) {
				echo '<p><em>Visit a frontend page to see request details.</em></p>';
			}
			?>
		</div>
		<?php
	}
}
