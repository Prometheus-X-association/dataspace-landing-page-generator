=== Prometheus-X Dataspace Landing Page Generator ===
Contributors: inokufu
Tags: dataspace, page, generator
Requires at least: 6.8
Tested up to: 6.8
Requires PHP: 8.2
Stable tag: 1.0.0
License: MIT
License URI: https://mit-license.org/

Create dynamic project landing pages using data from the Prometheus-X Dataspace Catalog API, with customizable call-to-action buttons and templates.

== Description ==

The Prometheus-X Dataspace Landing Page Generator plugin allows you to create professional project landing pages by fetching data from the Prometheus-X Dataspace Catalog API.  
Perfect for organizations participating in data ecosystems who need to showcase their projects with dynamic content.

= Key Features =

* **API Integration**: Seamlessly connects to the Prometheus-X Dataspace Catalog API,
* **Multiple Templates**: Choose from 2 professionally designed layout options,
* **Dynamic Content**: Automatically populate pages with project data including titles, descriptions, logos, and partners information,
* **Customizable CTA Buttons**: Add up to 2 call-to-action buttons with full customization,
* **Partner Management**: Display partner logos with responsive slider functionality,
* **Progressive Image Loading**: Background image processing to ensure smooth user experience.

= How It Works =

1. Create a new **page** in WordPress,
2. Click the **Dataspace landing page** button,
3. Enter your 24-character Prometheus-X Dataspace Project ID,
4. Customize CTA buttons,
5. Select your preferred template layout,
6. Customize partners information, project data and background image,
7. Publish your dynamic landing page.

= Requirements =

* PHP 8.2 or higher,
* WordPress 6.8 or higher,
* Active internet connection for API integration,
* Valid Prometheus-X Dataspace Project ID.

== Installation ==
1. Install the plugin through the WordPress plugins screen directly, or upload the plugin files to the `/wp-content/plugins/prometheus-x-dataspace-landing-page-generator` directory,
2. Activate the plugin through the **Plugins** screen in WordPress,
3. When creating a new page, you will see the **Dataspace landing page** button to start generating your landing page.

== Frequently Asked Questions ==
= Where do I get a Prometheus-X Dataspace Project ID? =

You need to be part of a project registered in the Prometheus-X Dataspace.
The project ID is a 24-character string that identifies your project in the ecosystem.
Contact your project administrator or visit the Prometheus-X Catalog platform for more information.

= Can I customize the templates? =

The plugin comes with 2 pre-designed templates (Layout 1 and Layout 2).
While you can customize colors and content through the WordPress customizer, template structure modifications would require theme or plugin customization.
However, you could create a new template by duplicating and modifying the existing ones in the plugin files.

= Does this work with my theme? =

In theory, yes! The plugin is not theme-dependent and works with any WordPress theme.
However, some themes may override certain style configurations, which could affect the visual appearance of your landing pages.

= Can I add more than 2 CTA buttons? =

Currently, the plugin supports up to 2 CTA buttons per landing page.
This limitation ensures optimal design and user experience across different devices.

= Is the plugin GDPR compliant? =

The plugin fetches publicly available project data from the Prometheus-X Dataspace Catalog API.
No personal data is stored locally by the plugin.
However, you should review your site's overall GDPR compliance, especially regarding any forms or tracking you may add to the generated pages.

== External services ==

This plugin connects to the Visions Trust API to retrieve project information from the Prometheus-X Dataspace Catalog. This service is required for the plugin's core functionality of generating dynamic landing pages.

**What the service is and what it is used for:**
The Visions Trust API (api.visionstrust.com) provides access to the Prometheus-X Dataspace Catalog, which contains project information including titles, descriptions, logos, participant details, and orchestrator information. This data is used to populate the dynamic landing pages created by the plugin.

**What data is sent and when:**
The plugin sends only the 24-character Project ID that you provide when creating a landing page. This ID is sent via HTTPS GET request to `https://api.visionstrust.com/v1/ecosystems/[PROJECT_ID]` each time a user loads a page that uses the plugin or when refreshing project data in the WordPress admin.

**Service provider information:**
This service is provided by Visions Trust. 
- Terms of service: https://visionstrust.com/assets/2024_09_18_VisionsTrust_Marketplace_General_terms_and_conditions_of_use_VF.docx-93d6edd9.pdf
- Privacy policy: https://visionstrust.com/assets/20240822_VisionsTrust_Privacy_Policy_website.docx-b46cf6c4.pdf

No personal user data is collected or transmitted by this plugin. Only the project ID you specify, which is public, is sent to retrieve publicly available project information.

== Screenshots ==
1. Page editor interface in WordPress, with "Dataspace landing page" button on the right-hand side
2. Project ID entry modal showing how to find your 24-character project ID from the Visions Trust catalog
3. Button configuration interface for customizing CTA buttons
4. Template selection screen showing Layout 1 and Layout 2 options with preview thumbnails
5. Generated landing page in WordPress editor, showing project details and customization options
6. Final Layout 1 landing page displaying project information, partner logos, and CTA buttons
7. Final Layout 2 landing page displaying project information, partner logos, and CTA buttons
8. Mobile-responsive view of the landing page with optimized layout

== Changelog ==

== Upgrade Notice ==
