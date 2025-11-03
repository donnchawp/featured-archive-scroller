/**
 * Featured Archive Scroller - Gallery JavaScript
 *
 * Handles scroll behavior, keyboard navigation, lazy loading, and prefetching.
 *
 * @package FeaturedArchiveScroller
 */

(function() {
	'use strict';

	/**
	 * Initialize gallery functionality.
	 */
	function initGallery() {
		const galleryContainer = document.querySelector('.fas-gallery-scroll');
		const prevButton = document.querySelector('.fas-nav-prev');
		const nextButton = document.querySelector('.fas-nav-next');
		const galleryItems = document.querySelectorAll('.fas-gallery-item');

		if (!galleryContainer || !prevButton || !nextButton) {
			return;
		}

		// Scroll gallery into view on page load
		const galleryWrapper = document.querySelector('.fas-gallery-wrapper');
		if (galleryWrapper) {
			// Small delay to ensure page is fully loaded
			setTimeout(() => {
				galleryWrapper.scrollIntoView({
					behavior: 'smooth',
					block: 'start'
				});
			}, 100);
		}

		// Navigation state
		let currentIndex = 0;
		let isScrolling = false;

		/**
		 * Easing function for smooth animation.
		 */
		function easeInOutCubic(t) {
			return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
		}

		/**
		 * Smooth scroll to specific item with custom animation.
		 */
		function scrollToItem(index) {
			if (index < 0 || index >= galleryItems.length || isScrolling) {
				return;
			}

			const item = galleryItems[index];
			if (!item) {
				return;
			}

			isScrolling = true;
			currentIndex = index;

			// Temporarily disable scroll snap during animation
			const originalSnapType = galleryContainer.style.scrollSnapType;
			galleryContainer.style.scrollSnapType = 'none';

			// Calculate target scroll position
			const containerRect = galleryContainer.getBoundingClientRect();
			const itemRect = item.getBoundingClientRect();
			const targetScroll = galleryContainer.scrollLeft + (itemRect.left - containerRect.left) - (containerRect.width - itemRect.width) / 2;

			// Animation settings
			const startScroll = galleryContainer.scrollLeft;
			const distance = targetScroll - startScroll;
			const duration = 500; // 500ms for smoother, more visible scrolling
			const startTime = performance.now();

			// Animate scroll
			function animate(currentTime) {
				const elapsed = currentTime - startTime;
				const progress = Math.min(elapsed / duration, 1);
				const easedProgress = easeInOutCubic(progress);

				galleryContainer.scrollLeft = startScroll + (distance * easedProgress);

				if (progress < 1) {
					requestAnimationFrame(animate);
				} else {
					// Re-enable scroll snap after animation
					galleryContainer.style.scrollSnapType = originalSnapType;
					isScrolling = false;
					updateNavigationState();
					prefetchNextImage(index);
				}
			}

			requestAnimationFrame(animate);
		}

		/**
		 * Update navigation button states.
		 */
		function updateNavigationState() {
			// Disable prev button at start
			if (currentIndex <= 0) {
				prevButton.disabled = true;
				prevButton.setAttribute('aria-disabled', 'true');
			} else {
				prevButton.disabled = false;
				prevButton.setAttribute('aria-disabled', 'false');
			}

			// Disable next button at end
			if (currentIndex >= galleryItems.length - 1) {
				nextButton.disabled = true;
				nextButton.setAttribute('aria-disabled', 'true');
			} else {
				nextButton.disabled = false;
				nextButton.setAttribute('aria-disabled', 'false');
			}
		}

		/**
		 * Get current visible item index.
		 */
		function getCurrentIndex() {
			const containerRect = galleryContainer.getBoundingClientRect();
			const containerCenter = containerRect.left + (containerRect.width / 2);

			let closestIndex = 0;
			let closestDistance = Infinity;

			galleryItems.forEach((item, index) => {
				const itemRect = item.getBoundingClientRect();
				const itemCenter = itemRect.left + (itemRect.width / 2);
				const distance = Math.abs(containerCenter - itemCenter);

				if (distance < closestDistance) {
					closestDistance = distance;
					closestIndex = index;
				}
			});

			return closestIndex;
		}

		/**
		 * Prefetch next image.
		 */
		function prefetchNextImage(index) {
			const nextIndex = index + 1;
			if (nextIndex < galleryItems.length) {
				const nextItem = galleryItems[nextIndex];
				const nextImage = nextItem.querySelector('img');
				if (nextImage && nextImage.loading === 'lazy') {
					// Create a link element to prefetch
					const link = document.createElement('link');
					link.rel = 'prefetch';
					link.as = 'image';
					link.href = nextImage.src;
					document.head.appendChild(link);
				}
			}
		}

		/**
		 * Handle previous button click.
		 */
		function handlePrevClick() {
			const newIndex = Math.max(0, currentIndex - 1);
			scrollToItem(newIndex);
		}

		/**
		 * Handle next button click.
		 */
		function handleNextClick() {
			const newIndex = Math.min(galleryItems.length - 1, currentIndex + 1);
			scrollToItem(newIndex);
		}

		/**
		 * Handle keyboard navigation.
		 */
		function handleKeyboard(event) {
			// Only handle keyboard when gallery is in view
			const galleryRect = galleryContainer.getBoundingClientRect();
			const isInView = galleryRect.top < window.innerHeight && galleryRect.bottom > 0;

			if (!isInView) {
				return;
			}

			if (event.key === 'ArrowLeft') {
				event.preventDefault();
				handlePrevClick();
			} else if (event.key === 'ArrowRight') {
				event.preventDefault();
				handleNextClick();
			}
		}

		/**
		 * Handle scroll events.
		 */
		function handleScroll() {
			// Don't update index during programmatic scrolling
			if (isScrolling) {
				return;
			}

			// Update current index based on scroll position
			currentIndex = getCurrentIndex();
			updateNavigationState();
		}

		/**
		 * Handle touch swipe.
		 */
		let touchStartX = 0;
		let touchEndX = 0;

		function handleTouchStart(event) {
			touchStartX = event.changedTouches[0].screenX;
		}

		function handleTouchEnd(event) {
			touchEndX = event.changedTouches[0].screenX;
			handleSwipe();
		}

		function handleSwipe() {
			const swipeThreshold = 50;
			const diff = touchStartX - touchEndX;

			if (Math.abs(diff) > swipeThreshold) {
				if (diff > 0) {
					// Swipe left (next)
					handleNextClick();
				} else {
					// Swipe right (prev)
					handlePrevClick();
				}
			}
		}

		/**
		 * Lazy load images with Intersection Observer.
		 */
		function initLazyLoading() {
			if ('IntersectionObserver' in window) {
				const imageObserver = new IntersectionObserver((entries, observer) => {
					entries.forEach(entry => {
						if (entry.isIntersecting) {
							const img = entry.target;
							if (img.dataset.src) {
								img.src = img.dataset.src;
								img.removeAttribute('data-src');
							}
							observer.unobserve(img);
						}
					});
				}, {
					rootMargin: '200px' // Load images 200px before they come into view
				});

				const lazyImages = galleryContainer.querySelectorAll('img[loading="lazy"]');
				lazyImages.forEach(img => imageObserver.observe(img));
			}
		}

		/**
		 * Initialize click handlers on gallery items.
		 */
		function initItemClicks() {
			galleryItems.forEach((item, index) => {
				item.addEventListener('click', (event) => {
					// Update current index when clicking on items
					if (!event.target.closest('.fas-gallery-link')) {
						currentIndex = index;
						updateNavigationState();
					}
				});
			});
		}

		// Event listeners
		prevButton.addEventListener('click', handlePrevClick);
		nextButton.addEventListener('click', handleNextClick);
		document.addEventListener('keydown', handleKeyboard);
		galleryContainer.addEventListener('scroll', handleScroll);
		galleryContainer.addEventListener('touchstart', handleTouchStart);
		galleryContainer.addEventListener('touchend', handleTouchEnd);

		// Initialize
		// Start at index 0 (first image)
		currentIndex = 0;
		updateNavigationState();
		initLazyLoading();
		initItemClicks();

		// Prefetch first image
		if (galleryItems.length > 0) {
			prefetchNextImage(0);
		}
	}

	/**
	 * Initialize when DOM is ready.
	 */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initGallery);
	} else {
		initGallery();
	}

})();
