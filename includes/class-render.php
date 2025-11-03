<?php
/**
 * Render class.
 *
 * @package FeaturedArchiveScroller
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FAS_Render
 *
 * Handles template loading and rendering.
 */
class FAS_Render {

	/**
	 * Single instance of the class.
	 *
	 * @var FAS_Render
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class.
	 *
	 * @return FAS_Render
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
		add_filter( 'template_include', array( $this, 'template_loader' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_head', array( $this, 'add_seo_tags' ) );
	}

	/**
	 * Load custom template for gallery endpoint.
	 *
	 * @param string $template Template path.
	 * @return string Modified template path.
	 */
	public function template_loader( $template ) {
		$endpoint = FAS_Endpoint::get_instance();
		$settings = FAS_Settings::get_instance();

		// Check if gallery is enabled and we're on a gallery endpoint.
		if ( ! $settings->is_gallery_enabled() || ! $endpoint->is_gallery_endpoint() ) {
			return $template;
		}

		// Look for template in theme first, then plugin.
		$template_name = 'archive-gallery.php';
		$theme_template = locate_template( array( $template_name ) );

		if ( $theme_template ) {
			return $theme_template;
		}

		// Load plugin template.
		$plugin_template = FAS_PLUGIN_DIR . 'templates/' . $template_name;
		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}

		return $template;
	}

	/**
	 * Enqueue CSS and JavaScript assets.
	 */
	public function enqueue_assets() {
		$endpoint = FAS_Endpoint::get_instance();
		$settings = FAS_Settings::get_instance();

		// Only enqueue on gallery pages.
		if ( ! $settings->is_gallery_enabled() || ! $endpoint->is_gallery_endpoint() ) {
			return;
		}

		// Enqueue CSS.
		wp_enqueue_style(
			'fas-gallery-styles',
			FAS_PLUGIN_URL . 'assets/css/gallery.css',
			array(),
			FAS_VERSION
		);

		// Enqueue JavaScript.
		wp_enqueue_script(
			'fas-gallery-script',
			FAS_PLUGIN_URL . 'assets/js/gallery.js',
			array(),
			FAS_VERSION,
			true
		);

		// Pass data to JavaScript.
		wp_localize_script(
			'fas-gallery-script',
			'fasData',
			array(
				'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
				'showTitleOverlay' => $settings->show_title_overlay(),
			)
		);
	}

	/**
	 * Add SEO meta tags to gallery pages.
	 */
	public function add_seo_tags() {
		$endpoint = FAS_Endpoint::get_instance();
		$settings = FAS_Settings::get_instance();

		if ( ! $endpoint->is_gallery_endpoint() ) {
			return;
		}

		// Add noindex if enabled.
		if ( $settings->is_noindex_enabled() ) {
			echo '<meta name="robots" content="noindex, follow" />' . "\n";
		}

		// Add canonical tag.
		$canonical_url = $this->get_canonical_url();
		if ( $canonical_url ) {
			echo '<link rel="canonical" href="' . esc_url( $canonical_url ) . '" />' . "\n";
		}
	}

	/**
	 * Get canonical URL for gallery page.
	 *
	 * @return string|false
	 */
	private function get_canonical_url() {
		// Use current URL as canonical.
		global $wp;
		$current_url = home_url( add_query_arg( array(), $wp->request ) );
		return $current_url;
	}

	/**
	 * Get posts with featured images for current archive.
	 *
	 * @param array $args Additional query args.
	 * @return WP_Query
	 */
	public function get_gallery_posts( $args = array() ) {
		global $wp_query;

		// Get current query vars.
		$query_vars = $wp_query->query_vars;

		// Merge with default args.
		$default_args = array(
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'paged'          => get_query_var( 'paged', 1 ),
			'meta_query'     => array(
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
			),
		);

		// Get settings.
		$settings = FAS_Settings::get_instance();

		// If placeholders are enabled, remove the featured image requirement.
		if ( $settings->show_placeholder() ) {
			unset( $default_args['meta_query'] );
		}

		// Preserve archive context.
		$archive_args = array();

		if ( is_category() ) {
			$archive_args['cat'] = get_queried_object_id();
		} elseif ( is_tag() ) {
			$archive_args['tag_id'] = get_queried_object_id();
		} elseif ( is_tax() ) {
			$term = get_queried_object();
			$archive_args['tax_query'] = array(
				array(
					'taxonomy' => $term->taxonomy,
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				),
			);
		} elseif ( is_author() ) {
			$archive_args['author'] = get_queried_object_id();
		} elseif ( is_date() ) {
			if ( is_year() ) {
				$archive_args['year'] = get_query_var( 'year' );
			}
			if ( is_month() ) {
				$archive_args['monthnum'] = get_query_var( 'monthnum' );
			}
			if ( is_day() ) {
				$archive_args['day'] = get_query_var( 'day' );
			}
		} elseif ( is_post_type_archive() ) {
			$archive_args['post_type'] = get_query_var( 'post_type' );
		}

		// Merge all args.
		$final_args = array_merge( $default_args, $archive_args, $args );

		return new WP_Query( $final_args );
	}

	/**
	 * Get featured image data for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array|false Image data or false if no featured image.
	 */
	public function get_featured_image_data( $post_id ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );

		if ( ! $thumbnail_id ) {
			return false;
		}

		$image_url = wp_get_attachment_image_url( $thumbnail_id, 'full' );
		$image_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'full' );
		$image_sizes = wp_get_attachment_image_sizes( $thumbnail_id, 'full' );
		$image_alt = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );

		return array(
			'url'    => $image_url,
			'srcset' => $image_srcset,
			'sizes'  => $image_sizes,
			'alt'    => $image_alt ? $image_alt : get_the_title( $post_id ),
		);
	}
}
