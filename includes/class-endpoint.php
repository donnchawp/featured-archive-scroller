<?php
/**
 * Endpoint handler class.
 *
 * @package FeaturedArchiveScroller
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FAS_Endpoint
 *
 * Handles the gallery endpoint registration and detection.
 */
class FAS_Endpoint {

	/**
	 * Single instance of the class.
	 *
	 * @var FAS_Endpoint
	 */
	private static $instance = null;

	/**
	 * Endpoint slug.
	 *
	 * @var string
	 */
	private $endpoint_slug = 'gallery';

	/**
	 * Get single instance of the class.
	 *
	 * @return FAS_Endpoint
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
		// Register endpoint immediately during init.
		add_action( 'init', array( $this, 'register_endpoint' ), 0 );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_filter( 'request', array( $this, 'parse_pagination' ) );
	}

	/**
	 * Register the gallery endpoint.
	 */
	public function register_endpoint() {
		$slug = $this->get_endpoint_slug();

		// Add endpoint to all archive types.
		add_rewrite_endpoint( $slug, EP_ALL_ARCHIVES );
	}

	/**
	 * Add custom query vars.
	 *
	 * @param array $vars Existing query vars.
	 * @return array Modified query vars.
	 */
	public function add_query_vars( $vars ) {
		$vars[] = $this->endpoint_slug;
		return $vars;
	}

	/**
	 * Parse pagination from gallery endpoint.
	 *
	 * When URL is /archive/gallery/page/2/, the rewrite rule captures
	 * 'page/2' in the gallery query var. We need to extract the page
	 * number and set it properly in the paged query var.
	 *
	 * @param array $query_vars Query variables.
	 * @return array Modified query variables.
	 */
	public function parse_pagination( $query_vars ) {
		// Check if gallery query var is set.
		if ( ! isset( $query_vars[ $this->endpoint_slug ] ) ) {
			return $query_vars;
		}

		$gallery_value = $query_vars[ $this->endpoint_slug ];

		// Check if it contains pagination (e.g., 'page/2').
		if ( preg_match( '#^page/([0-9]+)/?$#', $gallery_value, $matches ) ) {
			// Set the paged query var.
			$query_vars['paged'] = absint( $matches[1] );

			// Set gallery to empty string (it should be empty, not 'page/2').
			$query_vars[ $this->endpoint_slug ] = '';
		}

		return $query_vars;
	}

	/**
	 * Get endpoint slug.
	 *
	 * @return string
	 */
	public function get_endpoint_slug() {
		// Allow customization via settings in the future.
		$slug = get_option( 'fas_endpoint_slug', $this->endpoint_slug );
		return sanitize_title( $slug );
	}

	/**
	 * Check if current request is a gallery endpoint.
	 *
	 * @return bool
	 */
	public function is_gallery_endpoint() {
		// Check if we're on an archive.
		if ( ! is_archive() ) {
			return false;
		}

		// Check if gallery query var is set.
		// Use a unique default value to distinguish between "not set" and "set to something".
		$gallery_var = get_query_var( $this->endpoint_slug, '__not_set__' );

		// The query var will be:
		// - '' (empty string) when URL is /archive/gallery/
		// - 'page/2' when URL is /archive/gallery/page/2/
		// - '__not_set__' when gallery is not in the URL at all
		// We want to return true for any value except '__not_set__'.
		return ( '__not_set__' !== $gallery_var );
	}

	/**
	 * Get the current archive type.
	 *
	 * @return string|false Archive type or false if not an archive.
	 */
	public function get_archive_type() {
		if ( is_category() ) {
			return 'category';
		} elseif ( is_tag() ) {
			return 'tag';
		} elseif ( is_tax() ) {
			return 'taxonomy';
		} elseif ( is_author() ) {
			return 'author';
		} elseif ( is_date() ) {
			return 'date';
		} elseif ( is_post_type_archive() ) {
			return 'post_type_archive';
		}

		return false;
	}

	/**
	 * Get the archive title.
	 *
	 * @return string
	 */
	public function get_archive_title() {
		// Remove prefix from archive title.
		return get_the_archive_title();
	}

	/**
	 * Get the archive description.
	 *
	 * @return string
	 */
	public function get_archive_description() {
		return get_the_archive_description();
	}
}
