jQuery(function ($) {
	// Function to determine breakpoint
	function getBreakpoint() {
		const width = window.innerWidth;
		if (width >= 1024) return 'desktop';
		if (width >= 768) return 'tablet';
		return 'mobile';
	}

	// Layout-2 slider
	$('.layout-2__meta-area .partners-slider').each(function () {
		const $slider = $(this);
		const $track = $slider.find('.meta-partners');
		const $items = $track.children('.partner');
		const count = $items.length;
		const $prev = $slider.find('.slider-prev');
		const $next = $slider.find('.slider-next');

		function getSlideStep() {
			const breakpoint = getBreakpoint();

			if (breakpoint === 'mobile') {
				// Mobile: show 1 slide
				return $slider.width();
			} else {
				// Tablet/Desktop: width of one slide + gap
				const itemWidth = $items.first().outerWidth(true);
				return itemWidth || 200;
			}
		}

		function getSlidesToShow() {
			const breakpoint = getBreakpoint();

			if (breakpoint === 'mobile') return 1;
			if (breakpoint === 'tablet') return 2;
			return 3; // desktop
		}

		function getMaxIdx() {
			const slidesToShow = getSlidesToShow();
			return Math.max(0, count - slidesToShow);
		}

		function toggleSlider() {
			const breakpoint = getBreakpoint();
			let needSlider = false;

			// Determine if slider is needed based on breakpoint
			if (breakpoint === 'mobile') {
				// Mobile: slider if 2+ partners
				needSlider = count >= 2;
			} else if (breakpoint === 'tablet') {
				// Tablet: slider if 3+ partners
				needSlider = count >= 3;
			} else {
				// Desktop: slider if 4+ partners
				needSlider = count >= 4;
			}


			if (needSlider) {
				$prev.show();
				$next.show();
			} else {
				$prev.hide();
				$next.hide();
			}

			// Add/remove class for CSS
			$slider.toggleClass('has-slider', needSlider);

			return needSlider;
		}

		let idx = 0;

		function update() {
			if (!toggleSlider()) {
				// Slider not needed
				$track.css('transform', 'translateX(0)');
				return;
			}

			const slideStep = getSlideStep();
			const maxIdx = getMaxIdx();

			// Limit index
			idx = Math.max(0, Math.min(idx, maxIdx));

			// Calculate offset
			const translateX = idx * slideStep;

			$track.css({
				'transition': 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
				'transform': `translateX(-${translateX}px)`
			});

			// Update button states
			$prev.prop('disabled', idx === 0).toggleClass('disabled', idx === 0);
			$next.prop('disabled', idx >= maxIdx).toggleClass('disabled', idx >= maxIdx);
		}

		$next.on('click', function () {
			const maxIdx = getMaxIdx();
			if (idx < maxIdx) {
				idx += 1;
				update();
			}
		});

		$prev.on('click', function () {
			if (idx > 0) {
				idx -= 1;
				update();
			}
		});

		// Update slider on window resize
		let resizeTimeout;
		$(window).on('resize', function () {
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(function () {
				idx = Math.min(idx, getMaxIdx()); // Adjust position
				update();
			}, 150);
		});

		update();
	});

	// Layout-1 and other sliders
	$('.partners-slider').not('.layout-2__meta-area .partners-slider').each(function () {
		const $slider = $(this);
		const $track = $slider.find('.meta-partners');
		const $items = $track.children('.partner');
		const count = $items.length;
		const $prev = $slider.find('.slider-prev');
		const $next = $slider.find('.slider-next');

		function getSlidesToShow() {
			const breakpoint = getBreakpoint();

			if (breakpoint === 'mobile') return 1;
			if (breakpoint === 'tablet') return 2;
			return 3; // desktop
		}

		function getSlideWidth() {
			const slidesToShow = getSlidesToShow();
			return $slider.width() / slidesToShow;
		}

		// New logic: slider active based on breakpoint
		function toggleArrows() {
			const breakpoint = getBreakpoint();
			let needSlider = false;

			// Determine if slider is needed based on breakpoint
			if (breakpoint === 'mobile') {
				// Mobile: slider if 2+ partners
				needSlider = count >= 2;
			} else if (breakpoint === 'tablet') {
				// Tablet: slider if 3+ partners
				needSlider = count >= 3;
			} else {
				// Desktop: slider if 4+ partners
				needSlider = count >= 4;
			}

			if (needSlider) {
				$prev.show();
				$next.show();
			} else {
				$prev.hide();
				$next.hide();
			}

			// Add/remove class for CSS
			$slider.toggleClass('has-slider', needSlider);

			return needSlider;
		}

		let idx = 0;

		function update() {
			if (!toggleArrows()) {
				// Slider not needed
				$track.css('transform', 'translateX(0)');
				return;
			}

			const slidesToShow = getSlidesToShow();
			const slideWidth = getSlideWidth();

			// Boundary limits
			idx = Math.max(0, Math.min(idx, count - slidesToShow));

			// Smooth animation
			const gap = parseFloat($track.css('gap')) || 0;
			$track.css({
				'transition': 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
				'transform': `translateX(-${idx * (slideWidth + gap)}px)`
			});

			// Update button states
			$prev.prop('disabled', idx === 0).toggleClass('disabled', idx === 0);
			$next.prop('disabled', idx >= count - slidesToShow).toggleClass('disabled', idx >= count - slidesToShow);
		}

		$next.on('click', function () {
			const slidesToShow = getSlidesToShow();
			if (idx < count - slidesToShow) {
				idx += 1;
				update();
			}
		});

		$prev.on('click', function () {
			if (idx > 0) {
				idx -= 1;
				update();
			}
		});

		// Update on window resize
		let resizeTimeout;
		$(window).on('resize', function () {
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(function () {
				const slidesToShow = getSlidesToShow();
				idx = Math.min(idx, Math.max(0, count - slidesToShow));
				update();
			}, 150);
		});

		// Initial initialization
		update();
	});
});
