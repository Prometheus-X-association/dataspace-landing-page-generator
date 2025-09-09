jQuery(document).ready(function ($) {
    const $closePopupButton = $('#close-popup-button');

    $closePopupButton.on('click', function () {
        $popup.hide();
    });

    // --- Initial render of saved logo previews on page load ---
    $('#trusted-logos-wrapper .trusted-entry').each(function () {
        const $entry = $(this);
        const $wrapper = $entry.find('.dataspace-media-wrapper');
        const apiUrl = $wrapper.find('input[type="hidden"]').data('api-url');
        if (apiUrl) {
            if ($wrapper.find('img.media-preview').length === 0) {
                $wrapper.prepend(
                    `<img src="${apiUrl}" class="media-preview" style="max-width:80px; display:block; margin-bottom:5px;" />`
                );
            }
        }
    });

    // Cached selectors
    const $popup               = $('#dataspace-popup');
    const $btn                 = $('#dataspace-button');
    const $projectIdInput      = $('#dataspace-project-id');
    const $fetchBtn            = $('#fetch-project');
    const $idError             = $('#dataspace-id-error');
    const $popupContent        = $('.dataspace-popup-content');
    const $cta1Label           = $('#cta1-label');
    const $cta1Url             = $('#cta1-url');
    const $cta2Label           = $('#cta2-label');
    const $cta2Url             = $('#cta2-url');
    const $cta1Tooltip         = $('#cta1-tooltip');
    const $cta2Tooltip         = $('#cta2-tooltip');
    const $layoutNext          = $('#layout-next-btn');
    const $templateSelector    = $('.template-selector');
    const $layoutOptions       = $('.layout-option');
    const $generateBtn         = $('#generate-landing-page');
    // Initial state: require layout selection
    $generateBtn.prop('disabled', true).attr('aria-label', 'Please select a layout first');
    const $tooltipLink         = $('#dataspace-tip-link');
    const $tooltipContent      = $('#dataspace-tip-content');
    const $whyTwo              = $('#why-two-buttons');
    const $tooltipTwo          = $('#tooltip-two-buttons');

    let selectedLayout = null;

    // Toggle popup open/close
    $btn.on('click', function () {
        $popup.slideToggle();
        $idError.hide();
        if ($popup.is(':visible')) {
            window.showStep(1);
        }
    });

    /* -------------------------------------------
    *  Close tooltips when clicking outside
    * ------------------------------------------- */
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#dataspace-tip-link, #dataspace-tip-content').length) {
            $tooltipContent.slideUp(400);
        }
        if (!$(e.target).closest('#why-two-buttons, #tooltip-two-buttons').length) {
            $tooltipTwo.slideUp(400);
        }
    });

    // Tooltip link toggle
    $tooltipLink.on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $tooltipContent.slideToggle(400);

        // --- SR announcement for "Where to find project ID?" ---
        const srMsg = 'The project ID can be found in the URL corresponding to the project\'s webpage in the Visions Trust catalog. Log in, go to "Projects", click "See Project", and copy the string after the last slash.';
        $('#sr-project-id-message').text(srMsg);
    });

    // Hide ID error on input
    $projectIdInput.on('input', function () {
        $idError.hide();
    });

    // Fetch project data and show CTA Buttons form
    $fetchBtn.on('click', function (e) {
        e.preventDefault();
        $idError.hide();
        const projectID = $projectIdInput.val().trim();
        if (projectID.length !== 24) {
            $idError.text('The project ID must be exactly 24 characters.').show();
            // Edit the element to include ARIA attributes
            const errorElement = document.getElementById('dataspace-id-error');
            if (errorElement) {
                errorElement.setAttribute('aria-live', 'assertive');
                errorElement.setAttribute('role', 'alert');
            }
            return;
        }
        $.ajax({
            type: 'POST',
            url: dataspace_data.ajaxurl,
            dataType: 'json',
            data: { 
                action: 'ptxdala_get_project', 
                project_id: projectID,
                nonce: dataspace_data.nonces.get_project
            },
            success: function (response) {
                if (response.success) {
                    window.dataspaceProjectData = response.data;
                    $popupContent.hide();
                    $('.cta-buttons-form').show(300);
                } else {
                    $idError.text(response.data.message).show();
                }
            },
            error: function () {
                $idError.text('API request error.').show();
            }
        });
    });

    // Validate URL format
    function validateUrl(url) {
        // Check if this is a URL at all
        if (!url) return false;

        try {
            const urlObj = new URL(url);
            // Check protocol
            return urlObj.protocol === 'http:' || urlObj.protocol === 'https:';
        } catch (e) {
            return false;
        }
    }


    // Attach input listeners for CTA fields to hide error messages on input
    $cta1Label.on('input', function () {
        $('#cta1-char-limit').hide();
        $('#general-form-error').hide();
    });
    $cta1Url.on('input', function () {
        $('#cta1-url-error').hide();
        $('#general-form-error').hide();
    });
    $cta1Tooltip.on('input', function () {
        $('#cta1-tooltip-required').hide();
        $('#cta1-tooltip-char-limit').hide();
        $('#general-form-error').hide();
    });
    $cta2Label.on('input', function () {
        $('#cta2-char-limit').hide();
        $('#general-form-error').hide();
    });
    $cta2Url.on('input', function () {
        $('#cta2-url-error').hide();
        $('#general-form-error').hide();
    });
    $cta2Tooltip.on('input', function () {
        $('#cta2-tooltip-required').hide();
        $('#cta2-tooltip-char-limit').hide();
        $('#general-form-error').hide();
    });

    // Add message container if it doesn't exist yet
    function ensureMsg($btn) {
        if ($btn.next('.dataspace-msg').length === 0) {
            $btn.after('<div class="dataspace-msg" style="color:#d830ff; margin-top:8px; font-weight:600; display:none;"></div>');
        }
    }


    // --- Next Button (layout-next-btn) ---
    ensureMsg($layoutNext);
    $layoutNext.on('click', function (e) {
        e.preventDefault();
        let valid = true;
        let invalidCount = 0;

        // For first button
        const label1N = $cta1Label.val().trim();
        const url1N = $cta1Url.val().trim();
        const tooltip1N = $cta1Tooltip.val().trim();

        // Validate label
        if (!label1N) {
            $('#cta1-char-limit').text('The button label is required to proceed.').show();
            valid = false;
            invalidCount++;
        } else if (label1N.length > 120) {
            $('#cta1-char-limit').text('The maximum character limit is 120').show();
            valid = false;
        } else {
            $('#cta1-char-limit').hide();
        }

        // Validate URL
        if (!url1N) {
            $('#cta1-url-error').text('The URL field is required to proceed.').show();
            valid = false;
            invalidCount++;
        } else if (!validateUrl(url1N)) {
            $('#cta1-url-error').text('Please enter a valid URL starting with http:// or https://').show();
            valid = false;
        } else {
            $('#cta1-url-error').hide();
        }

        // Validate tooltip
        if (!tooltip1N) {
            $('#cta1-tooltip-required').show();
            valid = false;
            invalidCount++;
        } else if (tooltip1N.length > 400) {
            $('#cta1-tooltip-char-limit').text('The maximum character limit is 400').show();
            valid = false;
        } else {
            $('#cta1-tooltip-char-limit').hide();
            $('#cta1-tooltip-required').hide();
        }

        // For second button
        const label2N = $cta2Label.val().trim();
        const url2N = $cta2Url.val().trim();
        const tooltip2N = $cta2Tooltip.val().trim();
        const twoButtonsSelected = $('#count-2').is(':checked');

        if (twoButtonsSelected) {
            // Validate label
            if (!label2N) {
                $('#cta2-char-limit').text('The button label is required to proceed.').show();
                valid = false;
                invalidCount++;
            } else if (label2N.length > 120) {
                $('#cta2-char-limit').text('The maximum character limit is 120').show();
                valid = false;
            } else {
                $('#cta2-char-limit').hide();
            }

            // Validate URL
            if (!url2N) {
                $('#cta2-url-error').text('The URL field is required to proceed.').show();
                valid = false;
                invalidCount++;
            } else if (!validateUrl(url2N)) {
                $('#cta2-url-error').text('Please enter a valid URL starting with http:// or https://').show();
                valid = false;
            } else {
                $('#cta2-url-error').hide();
            }

            // Validate tooltip
            if (!tooltip2N) {
                $('#cta2-tooltip-required').show();
                valid = false;
                invalidCount++;
            } else if (tooltip2N.length > 400) {
                $('#cta2-tooltip-char-limit').text('The maximum character limit is 400').show();
                valid = false;
            } else {
                $('#cta2-tooltip-char-limit').hide();
                $('#cta2-tooltip-required').hide();
            }
        } else {
            $('#cta2-tooltip-required').hide();
        }

        // Show general form error if needed
        if (invalidCount >= 2) {
            $('#general-form-error').text('There are mandatory fields that are empty or not properly completed.').show();
        } else {
            $('#general-form-error').hide();
        }

        // Show specific error messages
        if (!valid) {
            $layoutNext.attr('aria-label', 'One or more fields are empty or invalid. Please review the form.');
            return;
        }
        // All good – restore default aria label
        $layoutNext.attr('aria-label', 'Generate the Landing Page');

        // --- Collect data from API step (window.dataspaceProjectData) ---
        const projectData = window.dataspaceProjectData || {};

        // --- Collect data from CTA fields ---
        const ctaButtons = [];
        const text1 = $cta1Label.val().trim();
        const url1 = $cta1Url.val().trim();
        const tooltip1 = $cta1Tooltip.val().trim();
        if (text1 && url1 && tooltip1) {
            ctaButtons.push({ text: text1, url: url1, tooltip: tooltip1 });
        }
        const text2 = $cta2Label.val().trim();
        const url2 = $cta2Url.val().trim();
        const tooltip2 = $cta2Tooltip.val().trim();
        if (text2 && url2 && tooltip2) {
            ctaButtons.push({ text: text2, url: url2, tooltip: tooltip2 });
        }

        // --- Form object that we'll use for preview ---
        const previewData = {
            projectData: projectData,
            ctaButtons: ctaButtons
        };

        // (1) conveniently get data
        const pd = previewData.projectData;
        const btns = previewData.ctaButtons;

        // Trim short description to 40 words for preview readability
        const shortDesc = pd.short_description
            ? pd.short_description.split(/\s+/).slice(0, 40).join(' ') + (pd.short_description.split(/\s+/).length > 40 ? '…' : '')
            : '';

        const partners = (pd.participants || []).slice(1);

        // Should we show arrows?
        const showArrows = partners.length > 3;

        const partnersHtml = (showArrows ? partners.slice(0, 3) : partners).map(p => `
            <div class="partner">
                <img src="${p.logo}" alt="${p.name}" />
                <div class="partner-name">${p.name}</div>
            </div>
        `).join('');

        // ==== LAYOUT 1 ====
        const previewHtml = `
            <div class="dataspace-landing layout-1" style="pointer-events:none; user-select:none;">
                <div class="layout-1__header">
                    <div class="layout-1__image">
                        ${pd.image ? `<img src="${pd.image}" alt="Cover Image" />` : ''}
                    </div>
                    <div class="layout-1__title-block">
                        <h1>${pd.title || ''}</h1>
                        <div class="layout-1__short-description">
                            ${shortDesc}
                        </div>
                        <div class="cta-buttons">
                            ${btns.map(b => {
                                const tooltip = b.tooltip || '';
                                const ariaLabel = tooltip ? `${b.text}. ${tooltip}` : b.text;
                                return `<a href="${b.url}" class="cta-btn" target="_blank" role="button" title="${tooltip}" aria-label="${ariaLabel}">${b.text}</a>`;
                            }).join('')}
                        </div>
                    </div>
                </div>
                <div class="layout-1__meta">
                    <div class="meta-block">
                        <span class="meta-title">Proposed by</span>
                        <div class="meta-org">
                            ${ pd.orchestrator_logo
                                ? `<img src="${pd.orchestrator_logo}" alt="Proposed by Logo" />`
                                : ''
                            }
                            <div class="meta-org-name">
                                ${pd.orchestrator_name || ''}
                            </div>
                        </div>
                    </div>
                    <div class="meta-block meta-block-partners">
                        <span class="meta-title">Trusted actors involved in the project</span>
                        <div class="partners-slider">
                            ${ showArrows
                                ? `<button class="slider-prev" aria-label="Previous partner logo">&laquo;</button>`
                                : ''
                            }
                            <div class="meta-partners">
                                ${partnersHtml}
                            </div>
                            ${ showArrows
                                ? `<button class="slider-next" aria-label="Next partner logo">&raquo;</button>`
                                : ''
                            }
                        </div>
                    </div>
                </div>
                <div class="layout-1__content">
                    ${pd.description || ''}
                </div>
                <div class="layout-1__footer">
                    <img src="${dataspace_data.plugin_url}public/images/Prometeus.png" alt="Prometheus X" />
                </div>
            </div>
        `;

        // Add preview for layout-1
        const $opt1 = $('.layout-option[data-layout="layout-1"]');
        $opt1.find('img').remove();

        const $wrapper1 = $('<div class="preview-box">').css({
            width: '354px',
            height: '470px',
            overflow: 'hidden',
            margin: '0 auto',
            pointerEvents: 'none',
            userSelect: 'none',
            background: '#ffffff'
        });

        const $content1 = $(previewHtml).css({
            width: '1400px',
            transform: 'scale(0.252857)',
            transformOrigin: 'top left',
            display: 'block'
        });

        $wrapper1.append($content1);
        $opt1.prepend($wrapper1);

        // --- Initialize slider (layout-1) ---
        const $sliderBlock1 = $content1.find('.meta-block-partners');
        const $track1 = $sliderBlock1.find('.meta-partners');
        const $items1 = $track1.children('.partner');

        if ($items1.length > 3) {
            if (!$sliderBlock1.find('.slider-prev').length) {
                $sliderBlock1.append('<button class="slider-prev" disabled>&laquo;</button>');
                $sliderBlock1.append('<button class="slider-next">&raquo;</button>');
            }
            const $prev1 = $sliderBlock1.find('.slider-prev');
            const $next1 = $sliderBlock1.find('.slider-next');
            let idx1 = 0;
            const perView1 = 3;
            const itemW1 = $items1.first().outerWidth(true);

            function update1() {
                idx1 = Math.max(0, Math.min(idx1, $items1.length - perView1));
                $track1.css('transform', `translateX(-${idx1 * itemW1}px)`);
                $prev1.prop('disabled', idx1 === 0);
                $next1.prop('disabled', idx1 >= $items1.length - perView1);
            }

            $prev1.off('click').on('click', () => { idx1--; update1(); });
            $next1.off('click').on('click', () => { idx1++; update1(); });
            update1();
        }

        // ==== LAYOUT 2 ====
        const previewHtml2 = `
            <div class="dataspace-landing layout-2" style="pointer-events:none; user-select:none;">
                <div class="layout-2__header">
                    <div class="layout-2__content">
                        <h1>${pd.title || ''}</h1>
                        <div class="layout-2__short-description">
                            ${shortDesc}
                        </div>
                        <div class="cta-buttons">
                            ${btns.map(b => {
                                const tooltip = b.tooltip || '';
                                const ariaLabel = tooltip ? `${b.text}. ${tooltip}` : b.text;
                                return `<a href="${b.url}" class="cta-btn" target="_blank" role="button" title="${tooltip}" aria-label="${ariaLabel}">${b.text}</a>`;
                            }).join('')}
                        </div>
                    </div>
                    <div class="layout-2__image">
                        ${pd.image ? `<img src="${pd.image}" alt="Cover Image" />` : ''}
                    </div>
                </div>
                <div class="layout-1__meta">
                    <div class="meta-block">
                        <span class="meta-title">Proposed by</span>
                        <div class="meta-org">
                            ${pd.orchestrator_logo
                                ? `<img src="${pd.orchestrator_logo}" alt="Proposed by Logo" />`
                                : ''
                            }
                            <div class="meta-org-name">${pd.orchestrator_name || ''}</div>
                        </div>
                    </div>
                    <div class="meta-block meta-block-partners">
                        <span class="meta-title">Trusted actors involved in the project</span>
                        <div class="partners-slider">
                            ${ showArrows
                                ? `<button class="slider-prev" aria-label="Previous partner logo">&laquo;</button>`
                                : ''
                            }
                            <div class="meta-partners">
                                ${partnersHtml}
                            </div>
                            ${ showArrows
                                ? `<button class="slider-next" aria-label="Next partner logo">&raquo;</button>`
                                : ''
                            }
                        </div>
                    </div>
                </div>
                <div class="layout-2__description">${pd.description || ''}</div>
                <div class="layout-1__footer">
                    <img src="${dataspace_data.plugin_url}public/images/Prometeus.png" alt="Prometheus X" />
                </div>
            </div>
        `;

        // Add preview for layout-2
        const $opt2 = $('.layout-option[data-layout="layout-2"]');
        $opt2.find('img').remove();

        const $wrapper2 = $('<div class="preview-box">').css({
            width: '354px',
            height: '470px',
            overflow: 'hidden',
            margin: '0 auto',
            pointerEvents: 'none',
            userSelect: 'none',
            background: '#ffffff'
        });

        const $content2 = $(previewHtml2).css({
            width: '1400px',
            transform: 'scale(0.252857)',
            transformOrigin: 'top left',
            display: 'block'
        });

        $wrapper2.append($content2);
        $opt2.prepend($wrapper2);

        // --- Initialize slider (layout-2) ---
        const $sliderBlock2 = $content2.find('.meta-block-partners'); // partners block
        const $track2 = $sliderBlock2.find('.meta-partners');
        const $items2 = $track2.children('.partner');

        if ($items2.length > 3) {
            if (!$sliderBlock2.find('.slider-prev').length) {
                $sliderBlock2.append('<button class="slider-prev" disabled>&laquo;</button>');
                $sliderBlock2.append('<button class="slider-next">&raquo;</button>');
            }
            const $prev2 = $sliderBlock2.find('.slider-prev');
            const $next2 = $sliderBlock2.find('.slider-next');
            let idx2 = 0;
            const perView2 = 3;
            const itemW2 = $items2.first().outerWidth(true);

            function update2() {
                idx2 = Math.max(0, Math.min(idx2, $items2.length - perView2));
                $track2.css('transform', `translateX(-${idx2 * itemW2}px)`);
                $prev2.prop('disabled', idx2 === 0);
                $next2.prop('disabled', idx2 >= $items2.length - perView2);
            }

            $prev2.off('click').on('click', () => { idx2--; update2(); });
            $next2.off('click').on('click', () => { idx2++; update2(); });
            update2();
        }

        // --- Show template selector ---
        $('.cta-buttons-form').hide(300);
        $templateSelector.show(300, function () {
            // Enable Next button so user can click and get message about layout selection requirement
            $generateBtn.prop('disabled', false)
                .attr({
                    'aria-label': 'Please select a layout first',
                    'aria-describedby': 'next-desc'
                });
            // After step 3 appears, set focus on heading so screen readers announce it
            const $heading = $('#frame2-heading');
            if ($heading.length) {
                $heading.focus();
            }
        });
    });

    // Tooltip for why two buttons
    $whyTwo.on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $tooltipTwo.slideToggle(400);
    });

    // Template selection
    $layoutOptions.on('click', function () {
        $layoutOptions.removeClass('selected').css('opacity', 0.4);
        $(this).addClass('selected').css('opacity', 1);
        selectedLayout = $(this).data('layout');
        // Since a layout is selected, ensure button aria-label is reset
        $generateBtn.attr('aria-label', 'Next');
        $generateBtn.prop('disabled', false);
        // Restore original description for screen-readers
        $generateBtn.attr('aria-describedby', 'next-desc');
        $('#layout-error').hide();
    });

    // Generate landing page
    $generateBtn.on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if (!selectedLayout) {
            const errorMsg = 'Please select a layout first';
            // Show and announce the error message
            const $err = $('#layout-error');
            $err.text(errorMsg)
                .attr({
                    'role': 'alert',
                    'aria-live': 'assertive'
                })
                .show();

            // Update button for assistive technologies
            $generateBtn.attr({
                'aria-label': errorMsg,
                'aria-describedby': 'layout-error'
            });
            return;
        }
        $('#layout-error').hide();

        const postId = $('#post_ID').val();
        const btn = $(this);
        btn.prop('disabled', true).text('Generating...');

        // Collect CTA buttons data
        const btns = [];
        if ($cta1Label.val().trim() && $cta1Url.val().trim() && $cta1Tooltip.val().trim()) {
            btns.push({ text: $cta1Label.val().trim(), url: $cta1Url.val().trim(), tooltip: $cta1Tooltip.val().trim() });
        }
        if ($cta2Label.val().trim() && $cta2Url.val().trim() && $cta2Tooltip.val().trim()) {
            btns.push({ text: $cta2Label.val().trim(), url: $cta2Url.val().trim(), tooltip: $cta2Tooltip.val().trim() });
        }

        // Show overlay immediately
        ensureOverlay();

        $.ajax({
            url: dataspace_data.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'ptxdala_generate_landing_page',
                post_id: postId,
                template: selectedLayout,
                cta_buttons: JSON.stringify(btns),
                project_data: JSON.stringify(window.dataspaceProjectData),
                nonce: dataspace_data.nonces.generate_landing_page
            },
            success: function (response) {
                /* AJAX response received */
                if (response.success) {
                    if (window.wp && wp.data && wp.blocks) {
                        // Gutenberg
                        // Gutenberg path

                        // Set title
                        if (response.data && response.data.title) {
                            wp.data.dispatch('core/editor').editPost({ title: response.data.title });
                        }

                        // Get HTML from content
                        const html = response.data?.content || response.data?.description || '';

                        if (!html) {
                            return;
                        }

                        try {
                            // Parse HTML into blocks
                            const blocks = wp.blocks.rawHandler({
                                HTML: html,
                                mode: 'BLOCKS'
                            });

                            if (blocks && blocks.length) {
                                // Clear existing blocks
                                wp.data.dispatch('core/block-editor').resetBlocks([]);

                                // Insert new blocks
                                wp.data.dispatch('core/block-editor').insertBlocks(blocks);
                            } else {
                                // fallback: insert as paragraph
                                const fallback = wp.blocks.createBlock('core/paragraph', {
                                    content: html
                                });
                                wp.data.dispatch('core/block-editor').insertBlocks([fallback]);
                            }
                        } catch (error) {
                            // If error occurred, insert as plain text
                            const fallback = wp.blocks.createBlock('core/paragraph', {
                                content: html.replace(/<[^>]*>/g, '')
                            });
                            wp.data.dispatch('core/block-editor').insertBlocks([fallback]);
                        }
                        $('#dataspace-popup').hide();
                    } else {
                        // Classic editor
                        $('#title').val(response.data.title).trigger('change');
                        $('#excerpt').val(response.data.excerpt);

                        // Safely insert content into classic editor
                        const content = response.data.description || response.data.content || '';
                        if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                            // Clean content from potentially dangerous shortcodes
                            const cleanContent = content.replace(/\[.*?\]/g, '');
                            tinymce.activeEditor.setContent(cleanContent);
                        } else {
                            $('#content').val(content);
                        }
                    }

                    // Common actions for both editors
                    setTimeout(function () {
                        $('#title-prompt-text, .editor-post-title__placeholder').hide();
                        $('#title').removeClass('screen-reader-text');
                    }, 100);

                    $('#ptxdala_landing_meta_box').slideDown();
                    $('textarea[name="short_description"]').val(response.data.short_description);
                    if (response.data.cover_image_url) {
                        const w = $('input[name="cover_image_id"]').closest('.dataspace-media-wrapper');
                        if (response.data.cover_image_id) {
                            w.find('input[type="hidden"]').val(response.data.cover_image_id);
                        }
                        w.find('img').remove();
                        w.prepend(`<img src="${response.data.cover_image_url}" class="media-preview" style="max-width:150px; display:block;" />`);
                    }
                    if (response.data.proposed_logo_url) {
                        const w2 = $('input[name="proposed_by_logo_id"]').closest('.dataspace-media-wrapper');
                        if (response.data.proposed_by_logo_id) {
                            w2.find('input[type="hidden"]').val(response.data.proposed_by_logo_id);
                        }
                        w2.find('img').remove();
                        w2.prepend(`<img src="${response.data.proposed_logo_url}" class="media-preview" style="max-width:150px; display:block;" />`);
                    }
                    // Proposed by Name
                    if (response.data.proposed_by_name) {
                        $('input[name="proposed_by_name"]').val(response.data.proposed_by_name);
                    }
                    // Trusted entries
                    const $wrap = $('#trusted-logos-wrapper').empty();
                    response.data.trusted_logos.forEach((item, idx) => {
                        const img = item.url
                            ? `<img src="${item.url}" class="media-preview" style="max-width:80px; display:block; margin-bottom:5px;" />`
                            : '';
                        const html = `
                            <div class="trusted-entry" style="margin-bottom:10px; padding:10px; border:1px dashed #ccc;">
                                <div class="dataspace-media-wrapper" data-field="trusted_logo_${idx}">
                                    ${img}<input type="hidden" name="trusted_actors_logo_ids[${idx}]" value="${item.id}" data-api-url="${item.url}" />
                                    <button class="button dataspace-upload">Select Image</button>
                                    <button class="button dataspace-remove">Remove</button>
                                </div>
                                <p style="margin-top:5px;">
                                    Name: <input type="text" name="trusted_actors_names[${idx}]" value="${item.name}" style="width:300px;" />
                                </p>
                                <button type="button" class="button remove-trusted-entry" style="margin-top:5px;">
                                    Delete partner
                                </button>
                            </div>`;
                        $wrap.append(html);
                    });
                    response.data.cta_buttons.forEach((b, i) => {
                        $(`input[name="cta_button_text_${i}"]`).val(b.text);
                        $(`input[name="cta_button_url_${i}"]`).val(b.url);
                        $(`textarea[name="cta_button_tooltip_${i}"]`).val(b.tooltip);
                    });
                    $('#page_template').val(selectedLayout);
                    $popup.slideUp();
                    $btn.slideUp();
                    btn.text('Done');

                    // Start progressive loading if there are remaining images
                    const pending = response.data.pending_images || [];
                    if (pending.length) {
                        showImageProgress(pending, postId);
                    }
                } else {
                    alert(response.data.message || 'Error generating page');
                    btn.prop('disabled', false);
                }
            },
            error: function () {
                alert('AJAX error');
                btn.prop('disabled', false).text('Generate the landing page >');
            }
        });

        return false;
    });

    // --- Button count change: hide errors for second button if switching to 1 button ---
    $('input[name="button_count"]').on('change', function () {
        const twoButtonsSelected = $('#count-2').is(':checked');
        if (twoButtonsSelected) {
            $('#cta-2').css({
                'opacity': '1',
                'pointer-events': 'auto'
            }).find('input, textarea').prop('disabled', false);
        } else {
            $('#cta-2').css({
                'opacity': '0.5',
                'pointer-events': 'none'
            }).find('input, textarea').prop('disabled', true);
            // Hide all messages for the second button
            $('#cta2-char-limit').hide();
            $('#cta2-url-error').hide();
            $('#cta2-tooltip-required').hide();
            $('#cta2-tooltip-char-limit').hide();
            // Clear all fields for the second button
            $('#cta2-label').val('');
            $('#cta2-url').val('');
            $('#cta2-tooltip').val('');
        }
    });

    // Media upload
    $(document).on('click', '.dataspace-upload', function (e) {
        e.preventDefault();
        const $wr = $(this).closest('.dataspace-media-wrapper');
        const $in = $wr.find('input[type="hidden"]');
        const frame = wp.media({
            title: 'Select or Upload Image',
            button: { text: 'Use this image' },
            multiple: false
        });
        frame.on('select', function () {
            const a = frame.state().get('selection').first().toJSON();
            $in.val(a.id);
            const u = a.sizes.medium ? a.sizes.medium.url : a.url;
            $wr.find('img').remove();
            $wr.prepend(`<img src="${u}" class="media-preview" style="max-width:80px; display:block; margin-bottom:5px;" />`);
        });
        frame.open();
    });

    // Media remove
    $(document).on('click', '.dataspace-remove', function (e) {
        e.preventDefault();
        const $wr = $(this).closest('.dataspace-media-wrapper');
        $wr.find('input[type="hidden"]').val('');
        $wr.find('img').remove();
    });

    // Add trusted entry
    $(document).on('click', '#add-trusted-entry', function (e) {
        e.preventDefault();
        const idx = $('#trusted-logos-wrapper .trusted-entry').length;
        const html = `
            <div class="trusted-entry" style="margin-bottom:10px;padding:10px;border:1px dashed #ccc;">
                <div class="dataspace-media-wrapper" data-field="trusted_logo_${idx}">
                    <input type="hidden" name="trusted_actors_logo_ids[${idx}]" value="" data-api-url="" />
                    <button class="button dataspace-upload">Select Image</button>
                    <button class="button dataspace-remove">Remove</button>
                </div>
                <p style="margin-top:5px;">
                    Name: <input type="text" name="trusted_actors_names[${idx}]" value="" style="width:300px;" />
                </p>
                <button type="button" class="button remove-trusted-entry" style="margin-top:5px;">
                    Delete partner
                </button>
            </div>`;
        $('#trusted-logos-wrapper').append(html);
    });

    // Remove trusted entry
    $(document).on('click', '.remove-trusted-entry', function (e) {
        e.preventDefault();
        $(this).closest('.trusted-entry').remove();
    });

    // Add Back buttons to each relevant step container after document ready
    if ($('.cta-buttons-form').length && !$('.cta-buttons-form #step-back-btn-2').length) {
        const backBtn2 = $('<button>', { id: 'step-back-btn-2', text: 'Back', class: 'button' }).on('click', function () { showStep(1); });
        $('.cta-buttons-form').append(backBtn2);
    }
    if ($('.template-selector').length && !$('.template-selector #step-back-btn-3').length) {
        const backBtn3 = $('<button>', { id: 'step-back-btn-3', text: 'Back', class: 'button' }).on('click', function () { showStep(2); });
        $('.template-selector').append(backBtn3);
    }
    // Update showStep to include clearing logic for previous steps
    window.showStep = function (step) {
        const $ = jQuery;  // Ensure jQuery is used

        // First, hide all steps
        $('.dataspace-popup-content, .cta-buttons-form, .template-selector').hide();

        // Clear data based on the step we're going to
        switch (step) {
            case 1:
                // Clear step 2 and 3 data if coming back
                $('.cta-buttons-form input, .cta-buttons-form textarea').val('');  // Clear CTA fields
                $('.template-selector .preview-box').remove();  // Remove previews from step 3
                $('.template-selector').find('.selected').removeClass('selected').css('opacity', 0.4);  // Reset layout selection
                selectedLayout = null;  // Reset global selectedLayout
                break;
            case 2:
                // Clear step 3 data
                $('.template-selector .preview-box').remove();  // Remove previews
                $('.template-selector').find('.selected').removeClass('selected').css('opacity', 0.4);  // Reset layout selection
                selectedLayout = null;  // Reset global
                break;
            case 3:
                // No need to clear previous, but ensure step 2 is reset if needed (already handled in case 2)
                break;
        }

        // Now show the target step
        switch (step) {
            case 0:
                // Possibly handle or do nothing
                break;
            case 1:
                $('.dataspace-popup-content').show();
                break;
            case 2:
                $('.cta-buttons-form').show();
                break;
            case 3:
                $('.template-selector').show();
                // Enable button and set initial hints
                $generateBtn.prop('disabled', false)
                    .attr({
                        'aria-label': 'Please select a layout first',
                        'aria-describedby': 'next-desc'
                    });
                // Focus heading for step 3 (Template Selector)
                const $heading3 = $('#frame2-heading');
                if ($heading3.length) {
                    $heading3.focus();
                }
                break;
        }
        window.currentStep = step;
        // step navigation debug removed
    };

    // Add hidden live-region for screen reader announcements
    if ($('#sr-project-id-message').length === 0) {
        $('body').append('<div id="sr-project-id-message" style="position:absolute;left:-9999px;" aria-live="assertive" role="alert"></div>');
    }
});

/* ---------------------------------------------------
   PROGRESSIVE IMAGE DOWNLOAD
---------------------------------------------------*/
function ensureOverlay() {
    let $ov = jQuery('#dataspace-progress-overlay');
    if (!$ov.length) {
        $ov = jQuery(
            '<div id="dataspace-progress-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:#ffffff;z-index:100000;display:flex;align-items:center;justify-content:center;flex-direction:column;">' +
            '<div style="width:300px;height:20px;border:1px solid #999;margin-bottom:10px;position:relative;">' +
            '<div class="bar" style="background:#0073aa;height:100%;width:0%;"></div>' +
            '</div>' +
            '<div class="percent" style="font-weight:bold;">0%</div>' +
            '<p class="status-msg" style="margin-top:8px;">Generating landing page…</p>' +
            '</div>'
        ).appendTo('body');
    }
    return $ov;
}

function showImageProgress(list, postId) {
    const $ = jQuery;
    // overlay may already exist
    let $overlay = ensureOverlay();
    // status text already indicates generation; keep unchanged
    const $bar = $overlay.find('.bar');
    const $percent = $overlay.find('.percent');

    let total = list.length;
    let completed = 0;
    let active = 0;
    const concurrency = 3;

    // Block save/publish buttons
    const $saveButtons = $('#publish, #save-post, #post-preview, .editor-post-save-draft, .editor-post-publish-button, .editor-post-publish-panel__toggle');
    $saveButtons.prop('disabled', true);
    window.onbeforeunload = function () { return 'Images are still downloading. Are you sure?'; };

    function updateProgress() {
        const pct = Math.round((completed / total) * 100);
        $bar.css('width', pct + '%');
        $percent.text(pct + '%');
        if (completed >= total) {
            setTimeout(() => $overlay.fadeOut(300, () => $overlay.remove()), 400);
            $saveButtons.prop('disabled', false);
            window.onbeforeunload = null;
        }
    }

    function launch() {
        if (!list.length) return;
        while (active < concurrency && list.length) {
            const item = list.shift();
            active++;
            $.post(dataspace_data.ajaxurl, {
                action: 'ptxdala_fetch_image',
                post_id: postId,
                key: item.key,
                url: item.url,
                nonce: dataspace_data.nonces.fetch_image
            }, function (r) {
                if (r.success) {
                    applyImageResult(item.key, r.data);
                }
            }, 'json').always(function () {
                active--; completed++; updateProgress(); launch();
            });
        }
    }

    function applyImageResult(key, data) {
        if (key === 'cover') {
            const w = $('input[name="cover_image_id"]').closest('.dataspace-media-wrapper');
            w.find('input[type="hidden"]').val(data.id);
            w.find('img').remove();
            w.prepend(`<img src="${data.local_url}" class="media-preview" style="max-width:150px; display:block;" />`);
        } else if (key === 'proposed') {
            const w = $('input[name="proposed_by_logo_id"]').closest('.dataspace-media-wrapper');
            w.find('input[type="hidden"]').val(data.id);
            w.find('img').remove();
            w.prepend(`<img src="${data.local_url}" class="media-preview" style="max-width:150px; display:block;" />`);
        } else if (key.startsWith('trusted_')) {
            const idx = parseInt(key.split('_')[1], 10);
            const entry = $('#trusted-logos-wrapper .trusted-entry').eq(idx);
            entry.find('input[name^="trusted_actors_logo_ids"]').val(data.id);
            entry.find('img.media-preview').attr('src', data.local_url);
        }
    }

    // Block form submit in classic editor
    let queuedSubmit = false;
    const $form = $('#post');
    $form.on('submit.dataspaceBlock', function (e) {
        if (window.dataspaceImagesLoading) {
            e.preventDefault();
            queuedSubmit = true;
            alert('Images are still downloading. The page will be saved automatically once finished.');
        }
    });

    window.dataspaceImagesLoading = true;

    function finishLoading() {
        $saveButtons.prop('disabled', false);
        window.onbeforeunload = null;
        window.dataspaceImagesLoading = false;
        $form.off('submit.dataspaceBlock');
        if (queuedSubmit) {
            // trigger autosave
            setTimeout(() => {
                if ($('#publish').length) { $('#save-post').click(); }
            }, 300);
        }
    }

    function updateProgress2() {
        const pct = Math.round((completed / total) * 100);
        $bar.css('width', pct + '%');
        $percent.text(pct + '%');
        if (completed >= total) {
            setTimeout(() => $overlay.fadeOut(300, () => $overlay.remove()), 400);
            finishLoading();
        }
    }

    updateProgress2();
    launch();
}