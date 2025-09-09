// CTA Button Accessibility Enhancement
// Focus CTA buttons on mouseover to trigger screen-reader announcements
(function() {
    if (typeof window !== 'undefined') {
        document.addEventListener('mouseover', function(ev) {
            var target = ev.target;
            if (target && target.classList && target.classList.contains('cta-btn')) {
                target.focus();
            }
        }, true); // use capture to fire early
    }
})();