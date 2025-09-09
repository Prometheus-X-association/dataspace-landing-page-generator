jQuery(document).ready(function($) {
    // Wait for sidebar featured image panel
    var checkEditor = setInterval(function() {
        var $featuredImage = $('.editor-post-featured-image');
        if ($featuredImage.length) {
            clearInterval(checkEditor);

            // Create button with icon from localized data
            var iconUrl = dataspaceData.pluginUrl + 'public/images/icone-PTX.png';
            var $button = $('<div id="dataspace-button-in-sidebar" class="dataspace-btn components-button editor-post-featured-image__toggle" style="display: flex; align-items: center; justify-content: center; margin-bottom: 16px; width: 100%; cursor: pointer;"><img src="' + iconUrl + '" style="height: 24px; margin-right: 8px;" alt="Logo"/> Dataspace landing page</div>');

            $featuredImage.before($button);

            $button.on('click', function() {
                $('#dataspace-popup').slideToggle();
            });

            // Close popup when clicking outside
            $(document).on('click', function(e) {
                var $popup = $('#dataspace-popup');
                if (!$popup.is(e.target) && $popup.has(e.target).length === 0 && !$button.is(e.target)) {
                    $popup.hide();
                }
            });
        }
    }, 100);
});