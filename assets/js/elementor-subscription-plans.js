/**
 * DESCRIPTION: Elementor subscription plans widget JavaScript
 *
 * Handles the expandable features list functionality for the
 * subscription plans widget.
 *
 * @package WPUF\Elementor
 */

(function() {
	'use strict';

	// Handle feature toggle buttons
	document.addEventListener('click', function(e) {
		if (e.target.classList.contains('wpuf-sub-features-toggle')) {
			e.preventDefault();
			var button = e.target;
			var packId = button.getAttribute('data-pack-id');
			var isExpanded = button.getAttribute('data-expanded') === 'true';
			var featuresList = document.getElementById('wpuf-sub-features-list-' + packId);
			var seeMoreBtn = featuresList.parentElement.querySelector('.wpuf-sub-features-see-more');
			var seeLessBtn = featuresList.parentElement.querySelector('.wpuf-sub-features-see-less');
			var hiddenItems = featuresList.querySelectorAll('.wpuf-sub-feature-hidden');

			if (isExpanded) {
				// Collapse
				hiddenItems.forEach(function(item) {
					item.style.display = 'none';
				});
				seeMoreBtn.style.display = '';
				seeLessBtn.style.display = 'none';
				button.setAttribute('data-expanded', 'false');
			} else {
				// Expand
				hiddenItems.forEach(function(item) {
					item.style.display = 'flex';
				});
				seeMoreBtn.style.display = 'none';
				seeLessBtn.style.display = '';
				seeLessBtn.setAttribute('data-expanded', 'true');
			}
		}
	});
})();
