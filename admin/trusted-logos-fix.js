jQuery(function ($) {
    const $entriesField = $('#trusted-actors-entries'); // hidden JSON (stringified array)
    const $wrapper = $('#trusted-logos-wrapper');       // container for all groups


    function saveEntries() {
        const entries = [];
        $wrapper.find('.trusted-entry').each(function () {
            const $entry = $(this);
            const id = parseInt($entry.find('input[type="hidden"]').val(), 10) || 0;
            const name = $entry.find('input[type="text"]').val() || '';
            entries.push({ id, name });
        });
        $entriesField.val(JSON.stringify(entries));
    }

    function renderEntry(id = 0, name = '', imageUrl = '') {
        const index = $wrapper.find('.trusted-entry').length;

        let imageTag = '';
        if (id && !isNaN(id)) {
            const att = wp.media.attachment(id).toJSON();
            const thumb = att?.sizes?.thumbnail?.url || att?.url || '';
            imageTag = thumb ? `<img src="${thumb}" class="media-preview" style="max-width:80px; display:block; margin-bottom:5px;" />` : '';
        } else if (imageUrl) {
            imageTag = `<img src="${imageUrl}" class="media-preview" style="max-width:80px; display:block; margin-bottom:5px;" />`;
        }

        const html = `
        <div class="trusted-entry" style="margin-bottom: 10px; padding: 10px; border: 1px dashed #ccc;">
            <div class="dataspace-media-wrapper" data-field="trusted_logo_${index}">
                ${imageTag}
                <input type="hidden" name="trusted_actors_logo_ids[${index}]" value="${id}" data-api-url="${imageUrl}" />
                <button class="button dataspace-upload">Select Image</button>
                <button class="button dataspace-remove">Remove</button>
            </div>
            <p style="margin-top:5px;">Name: <input type="text" name="trusted_actors_names[${index}]" value="${name}" style="width: 300px;" maxlength="120" /></p>
            <button type="button" class="button remove-trusted-entry" style="margin-top:5px;">Delete partner</button>
        </div>`;

        $wrapper.append(html);
    }

    (function renderOnLoad() {
        let entries = [];
        try { entries = JSON.parse($entriesField.val() || '[]'); } catch (e) { }
        entries.forEach(entry => {
            renderEntry(entry.id, entry.name, entry.url || '');
        });
    })();

    $wrapper.on('click', '.remove-trusted-entry', function () {
        $(this).closest('.trusted-entry').remove();
        saveEntries();
    });

    $wrapper.on('input', 'input[type="text"]', function () {
        saveEntries();
    });



    $wrapper.on('click', '.dataspace-remove', function (e) {
        e.preventDefault();
        const $entry = $(this).closest('.trusted-entry');
        $entry.find('input[type="hidden"]').val('');
        $entry.find('img').remove();
        saveEntries();
    });
});
