<!-- 
    2. CTA Buttons form UI 
-->
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="cta-buttons-form" style="display:none;" style="margin-top: 30px;">
    <h2 id="frame3-heading" aria-label="Button Design" aria-describedby="frame3-desc">Button Design</h2>
    <span id="frame3-desc" class="sr-only" style="position:absolute; left:-9999px;">In this interface, you must design the call-to-action buttons that users will interact with. There are three mandatory fields: first, the text that the button will contain; second, the URL it will direct the user to upon clicking; and third, a description explaining where the user is being redirected and for what purpose.  Additionally, by clicking the button below the title, you can create a second button. This can be useful if your audience consists of both individuals and organizations.</span>
    <!-- General error message -->
    <div id="general-form-error" class="error-message" role="alert" aria-live="assertive" style="display:none; color: red; margin: 10px 0;"></div>
    
    <!-- Selector for 1 or 2 buttons -->
    <div class="select-btn" style="margin-top: 20px;">
    <label style="font-weight: bold;" aria-label="Add another call to action button" aria-describedby="add-cta-desc">How many buttons will be visible on the landing page?</label>
    <span id="add-cta-desc" class="sr-only" style="position:absolute; left:-9999px;">If your project is multitarget, clicking here allows you to add a second button to your landing page. This way, you can direct individuals to one URL and organizations to another.</span>
    <div style="margin-top: 10px;">
        <div class="button-count-toggle">
            <input  type="radio" id="count-1" name="button_count" value="1" checked>
            <label for="count-1">1&nbsp;button</label>

            <input  type="radio" id="count-2" name="button_count" value="2">
            <label for="count-2">2&nbsp;buttons</label>

            <span class="toggle-slider"></span>
        </div>
        <div class="tooltip-info" style="margin: 10px 0;">
    <a href="#" id="why-two-buttons" aria-label="Why choose one or two buttons?" aria-describedby="why-two-desc">Why choose one or two buttons?</a>
    <span id="why-two-desc" class="sr-only" style="position:absolute; left:-9999px;">If your project is multitarget, having another button will allow you to redirect your end users to different URLs based on your criteria.</span>
</div>
    </div>
    <div id="tooltip-two-buttons" class="dataspace-tooltip" style="display:none; font-size: 12px; margin-top: 10px;">
        Selecting one button is good for single action focus. Two buttons can present primary and secondary actions.
    </div>
</div>
<div class="cta-blocks" style="display: flex; gap: 20px;">
    <div class="cta-block" id="cta-1" style="margin-bottom: 20px;">
        <h3 aria-label="First Button">First Button</h3>
        <label for="cta1-label" aria-label="Label of the button">Label:</label>
        <input type="text" id="cta1-label" maxlength="120" placeholder="Button text..." aria-required="true" />
        <div id="cta1-label-error" class="error-message" role="alert" aria-live="assertive" style="display: none; color: red;">The button label is required to proceed.</div>
        <div class="char-limit-msg" id="cta1-char-limit" style="display: none; color: red;">You have exceeded the maximum character limit for your button label. Remember that the maximum for this field is 120 characters.</div>

        <label for="cta1-url" aria-label="URL of the button">URL:</label>
        <input type="url" id="cta1-url" placeholder="https://example.com" aria-required="true" />
        <div id="cta1-url-error" class="error-message" role="alert" aria-live="assertive" style="display: none; color: red;">The URL field is required to proceed.</div>
        <div class="url-error" style="display: none; color: red;">This URL is invalid.</div>

        <label for="cta1-tooltip" aria-label="Accessibility description">Accessibility description:</label>
        <textarea id="cta1-tooltip" name="accessibility_description" maxlength="400" placeholder="Text visible on hover..." style="width: 100%; min-height: 50px;" aria-required="true"></textarea>
        <small>Describe where this button leads and why.</small>
        <div class="required-field-msg" id="cta1-tooltip-required" style="display: none; color: red;" role="alert" aria-live="assertive">Accessibility description is required.</div>
        <div class="char-limit-msg" id="cta1-tooltip-char-limit" style="display: none; color: red;">You have exceeded the maximum character limit for your accessibility description. Remember that the maximum for this field is 400 characters.</div>
    </div>

    <div class="cta-block" id="cta-2" style="opacity: 0.5; pointer-events: none;">
        <h3 aria-label="Second Button (Optional)">Second Button (Optional)</h3>
        <label for="cta2-label" aria-label="Label of the button">Label:</label>
        <input type="text" id="cta2-label" maxlength="120" placeholder="Button text..." aria-required="true" />
        <div id="cta2-label-error" class="error-message" role="alert" aria-live="assertive" style="display: none; color: red;">The button label is required to proceed.</div>
        <div class="char-limit-msg" id="cta2-char-limit" style="display: none; color: red;">You have exceeded the maximum character limit for your button label. Remember that the maximum for this field is 120 characters.</div>

        <label for="cta2-url" aria-label="URL of the button">URL:</label>
        <input type="url" id="cta2-url" placeholder="https://example.com" aria-required="true" />
        <div id="cta2-url-error" class="error-message" role="alert" aria-live="assertive" style="display: none; color: red;">The URL field is required to proceed.</div>
        <div class="url-error" style="display: none; color: red;">This URL is invalid.</div>

        <label for="cta2-tooltip" aria-label="Accessibility description">Accessibility description:</label>
        <textarea id="cta2-tooltip" name="accessibility_description" maxlength="400" placeholder="Text visible on hover..." style="width: 100%; min-height: 50px;" aria-required="true"></textarea>
        <small>Describe where this button leads and why.</small>
        <div class="required-field-msg" id="cta2-tooltip-required" style="display: none; color: red;" role="alert" aria-live="assertive">Accessibility description is required.</div>
        <div class="char-limit-msg" id="cta2-tooltip-char-limit" style="display: none; color: red;">You have exceeded the maximum character limit for your accessibility description. Remember that the maximum for this field is 400 characters.</div>
    </div>
</div>
<div class="buttons-container">
    <button id="step-back-btn-2" class="admin-button" type="button" onclick="showStep(1);" style="margin-top: 10px;">< Back</button>
    <button id="layout-next-btn" class="admin-button" type="button" aria-label="Generate the Landing Page" aria-describedby="generate-desc">Next ></button>
    <span id="generate-desc" class="sr-only" style="position:absolute; left:-9999px;">Clicking here will create the landing page for your project.</span>
</div>
</div>
