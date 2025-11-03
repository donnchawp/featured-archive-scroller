<?php
/**
 * Archive Gallery Template
 *
 * Displays a horizontal scrolling gallery of featured images.
 *
 * @package FeaturedArchiveScroller
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$render   = FAS_Render::get_instance();
$endpoint = FAS_Endpoint::get_instance();
$settings = FAS_Settings::get_instance();

// Get posts for gallery.
$gallery_query = $render->get_gallery_posts();
?>

<div class="fas-gallery-wrapper">
	<header class="fas-gallery-header">
		<h1 class="fas-gallery-title"><?php echo wp_kses_post( $endpoint->get_archive_title() ); ?></h1>
		<?php
		$description = $endpoint->get_archive_description();
		if ( $description ) :
			?>
			<div class="fas-gallery-description"><?php echo wp_kses_post( $description ); ?></div>
		<?php endif; ?>

		<div class="fas-gallery-meta">
			<span class="fas-gallery-count">
				<?php
				printf(
					/* translators: %d: Number of images */
					esc_html( _n( '%d image', '%d images', $gallery_query->found_posts, 'featured-archive-scroller' ) ),
					absint( $gallery_query->found_posts )
				);
				?>
			</span>
		</div>
	</header>

	<?php if ( $gallery_query->have_posts() ) : ?>
		<div class="fas-gallery-container">
			<button class="fas-nav-arrow fas-nav-prev" aria-label="<?php esc_attr_e( 'Previous image', 'featured-archive-scroller' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>

			<div class="fas-gallery-scroll" role="region" aria-label="<?php esc_attr_e( 'Image gallery', 'featured-archive-scroller' ); ?>">
				<?php
				while ( $gallery_query->have_posts() ) :
					$gallery_query->the_post();
					$image_data = $render->get_featured_image_data( get_the_ID() );
					?>
					<article class="fas-gallery-item" data-post-id="<?php echo esc_attr( get_the_ID() ); ?>">
						<a href="<?php the_permalink(); ?>" class="fas-gallery-link">
							<?php if ( $image_data ) : ?>
								<img
									src="<?php echo esc_url( $image_data['url'] ); ?>"
									<?php if ( $image_data['srcset'] ) : ?>
										srcset="<?php echo esc_attr( $image_data['srcset'] ); ?>"
										sizes="<?php echo esc_attr( $image_data['sizes'] ); ?>"
									<?php endif; ?>
									alt="<?php echo esc_attr( $image_data['alt'] ); ?>"
									class="fas-gallery-image"
									loading="lazy"
								/>
							<?php elseif ( $settings->show_placeholder() ) : ?>
								<div class="fas-gallery-placeholder">
									<svg width="100" height="100" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
										<rect width="100" height="100" fill="#f0f0f0"/>
										<path d="M35 40L50 60L65 45L75 60" stroke="#ccc" stroke-width="2" fill="none"/>
										<circle cx="40" cy="35" r="5" fill="#ccc"/>
									</svg>
								</div>
							<?php endif; ?>

							<?php if ( $settings->show_title_overlay() ) : ?>
								<div class="fas-gallery-overlay">
									<h2 class="fas-gallery-item-title"><?php the_title(); ?></h2>
									<?php if ( has_excerpt() ) : ?>
										<div class="fas-gallery-item-excerpt"><?php echo wp_kses_post( get_the_excerpt() ); ?></div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</a>
					</article>
				<?php endwhile; ?>
			</div>

			<button class="fas-nav-arrow fas-nav-next" aria-label="<?php esc_attr_e( 'Next image', 'featured-archive-scroller' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		</div>

		<?php
		// Pagination.
		if ( $gallery_query->max_num_pages > 1 ) :
			// Build the base URL with /gallery endpoint.
			global $wp;
			$current_url = home_url( add_query_arg( array(), $wp->request ) );
			$base_url    = trailingslashit( $current_url );

			// Remove any existing /page/X/ from the base URL.
			$base_url = preg_replace( '#/page/[0-9]+/?#', '/', $base_url );

			// Ensure /gallery is in the base URL.
			if ( false === strpos( $base_url, '/gallery' ) ) {
				$base_url = trailingslashit( $base_url ) . 'gallery/';
			}
			?>
			<nav class="fas-gallery-pagination" aria-label="<?php esc_attr_e( 'Gallery pagination', 'featured-archive-scroller' ); ?>">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links() output is already escaped.
				echo paginate_links(
					array(
						'base'      => $base_url . 'page/%#%/',
						'format'    => '',
						'total'     => $gallery_query->max_num_pages,
						'current'   => max( 1, get_query_var( 'paged' ) ),
						'prev_text' => __( '&laquo; Previous', 'featured-archive-scroller' ),
						'next_text' => __( 'Next &raquo;', 'featured-archive-scroller' ),
					)
				);
				?>
			</nav>
		<?php endif; ?>

	<?php else : ?>
		<div class="fas-gallery-empty">
			<p><?php esc_html_e( 'No images found in this archive.', 'featured-archive-scroller' ); ?></p>
			<p>
				<a href="<?php echo esc_url( remove_query_arg( 'gallery' ) ); ?>" class="fas-back-link">
					<?php esc_html_e( 'View standard archive', 'featured-archive-scroller' ); ?>
				</a>
			</p>
		</div>
	<?php endif; ?>

	<?php wp_reset_postdata(); ?>
</div>

<?php
get_footer();
