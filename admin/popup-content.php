<!-- 
    1. Pop-up UI for Project ID input 
-->
<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="dataspace-popup-content">
    <h2 id="frame1-heading" aria-label="Enter your project ID" aria-describedby="frame1-desc">Enter your project ID</h2>
    <span id="frame1-desc" class="sr-only" style="position:absolute; left:-9999px;">In this popup, you will find an input where you can enter your project or use case ID.  The project ID can be found in the URL corresponding to the project's webpage in the Visions Trust catalog. To locate it, log in to your organization's account, navigate to the "Projects" section,  click on "See Project," and the project ID will appear after the "/" slash in the URL.</span>
    <div class="popup-tip">
        <a href="#" id="dataspace-tip-link" aria-label="Where to find my Project ID." aria-describedby="tip-desc">Where to find my project ID?</a>
        <div id="dataspace-tip-content" style="display: none;">
            <p>
                The project ID can be found in the URL corresponding to the project's webpage in the Visions Trust catalog. 
                Log in, go to "Projects", click "See Project" and copy the string after the last slash.
            </p>
            <span id="tip-desc" class="sr-only" style="position:absolute; left:-9999px;">The project ID can be found in the URL corresponding to the project's webpage in the Visions Trust catalog. To locate it, log in to your organization's account, navigate to the "Projects" section,  click on "See Project," and the project ID will appear after the "/" slash in the URL.</span>
            <img src="<?php echo esc_url( plugin_dir_url(__FILE__) . '../public/images/project_ID.png' ); ?>" alt="ID example" style="width: 100%;"> 
        </div>
    </div>
    <label for="dataspace-project-id" class="screen-reader-text">Project ID</label>
    <input type="text" id="dataspace-project-id" maxlength="24" placeholder="Paste your 24-character Project ID" aria-required="true" aria-describedby="dataspace-id-error" aria-label="Input to insert the project ID" />

    <div id="dataspace-id-error" class="dataspace-error" style="display: none;">You have entered an invalid project ID. Please check for extra spaces in the entered ID</div>
    <div class="buttons-container">
        <button id="fetch-project" class="admin-button" aria-label="Create My Landing Page" aria-describedby="fetch-desc">Create My Landing Page ></button>
        <span id="fetch-desc" class="sr-only" style="position:absolute; left:-9999px;">Click this button to proceed to the next step, where you can choose a layout for your landing page.</span>
    </div>
</div>
