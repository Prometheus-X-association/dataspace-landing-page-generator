// WordPress Media Library Integration for Trusted Logos
jQuery(document).ready(function($) {
    var frame;
    $('#edit-trusted-logos').on('click', function(e) {
        e.preventDefault();
        var current = $('#trusted-actors-logo-ids').val();
        var ids = current ? JSON.parse(current) : [];

        // Disable previous instance
        if (frame) {
            frame.off();
            frame = null;
        }

        frame = wp.media({
            title: 'Select Trusted Logos',
            button: { text: 'Done' },
            library: { type: 'image' },
            multiple: true
        });

        frame.on('open', function() {
            var selection = frame.state().get('selection');
            selection.reset();
            ids.forEach(function(id) {
                var attachment = wp.media.attachment(id);
                attachment.fetch();
                selection.add(attachment);
            });
        });

        frame.on('select', function() {
            var selection = frame.state().get('selection').toJSON();
            var new_ids = selection.map(function(att) { return att.id; });
            $('#trusted-actors-logo-ids').val(JSON.stringify(new_ids));

            var html = '';
            selection.forEach(function(att) {
                var thumb = att.sizes && att.sizes.thumbnail
                          ? att.sizes.thumbnail.url
                          : att.url;
                html += "<div style='display:inline-block;margin:2px;'><img src='" + thumb + "' style='max-width:60px;'/></div>";
            });
            $('#trusted-logos-gallery').html(html);
        });

        frame.open();
    });
});