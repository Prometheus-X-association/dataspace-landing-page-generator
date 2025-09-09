<!-- 
    3. Template Selector UI 
-->
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="template-selector" style="display:none;" style="margin-top: 30px;">
    <h2 id="frame2-heading" tabindex="-1" aria-label="Select page layout" aria-describedby="frame2-desc">Select page layout</h2>
    <span id="frame2-desc" class="sr-only" style="position:absolute; left:-9999px;">On this page, you have two layout options for your landing page. We invite you to choose one of them and proceed to the next step by clicking the button at the bottom right to continue.</span>
    <div class="layout-options" style="display: flex; gap: 20px; flex-wrap: wrap;">
        <div class="layout-option" data-layout="layout-1" style="cursor: pointer;" aria-label="Layout 1">
            <img style="width: 350px;" src="<?php echo esc_url( plugin_dir_url(__FILE__) . '../public/images/Layout-1.jpg' ); ?>" alt="Layout 1" aria-label="Layout 1" aria-describedby="layout1-desc" />
            <span id="layout1-desc" class="sr-only" style="position:absolute; left:-9999px;">This is an image illustrating the first layout option for your landing page.</span>
            <p aria-label="Layout 1">Layout 1</p>
        </div>
        <div class="layout-option" data-layout="layout-2" style="cursor: pointer;" aria-label="Layout 2">
            <img style="width: 350px;" src="<?php echo esc_url( plugin_dir_url(__FILE__) . '../public/images/Layout-2.jpg' ); ?>" alt="Layout 2" aria-label="Layout 2" aria-describedby="layout2-desc" />
            <span id="layout2-desc" class="sr-only" style="position:absolute; left:-9999px;">This is an image illustrating the second layout option for your landing page.</span>
            <p aria-label="Layout 2">Layout 2</p>
        </div>
    </div>

    <div id="layout-error" class="dataspace-error" style="color: red; display: none; margin-top: 10px;">
        Please select a layout
    </div>
    <div class="buttons-container">
        <button id="step-back-btn-3" class="admin-button" type="button" onclick="showStep(2);" style="margin-top: 10px;">< Back</button>
        <button id="generate-landing-page" class="admin-button" type="button" aria-label="Next" aria-describedby="next-desc">Generate the Landing Page ></button>
        <span id="next-desc" class="sr-only" style="position:absolute; left:-9999px;">By clicking on this button, you will proceed to the next and final step to create your landing page.</span>
    </div>
</div>
