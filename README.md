# Dataspace Landing Page Generator BB

A WordPress plugin that creates dynamic project landing pages using data from the Vision Trust Catalog API. This plugin enables automatic generation of professional landing pages with customizable templates, CTA buttons, and partner logos.

## Overview

The Prometheus-X Dataspace Landing Page Generator transforms project data from the Vision Trust ecosystem into beautifully designed WordPress pages. It fetches project information, orchestrator details, and trusted actor data to create comprehensive landing pages that showcase dataspace projects effectively.

## Installation

### Prerequisites
- WordPress 6.8 or higher
- PHP 8.2 or higher
- Active internet connection for API calls

### Manual Installation

1. **Download the Plugin**
   ```bash
   git clone <<PROJECT_URL>>
   ```

2. **Upload to WordPress**
   - Upload the plugin folder to `/wp-content/plugins/prometheus-x-dataspace-landing-page-generator/`
   - Or use the WordPress admin interface: `Plugins → Add New → Upload Plugin`

3. **Activate the Plugin**
   - Go to WordPress `Admin → Plugins`
   - Find `Prometheus-X Dataspace Landing Page Generator`
   - Click `Activate`

## Usage

### Creating a Dataspace Landing Page

1. **Start a New Page**
   - Go to `Pages → Add New` in your WordPress admin

2. **Launch the Wizard**
   - Click the `Dataspace landing page` button
   - The 3-step wizard will appear

3. **Pull Project Data**
   - Enter your Vision Trust project ID
   - Click `Create My Landing Page` to retrieve information from the Dataspace and navigate to the next step

4. **Customize Call-To-Actions buttons**
   - Configure up to 2 CTA buttons with custom text and URLs

5. **Select Template**
   - Preview both templates with your project data
   - Select your preferred layout

6. **Generate & Publish**
   - Click `Generate the Landing Page` to create your page
   - Review the generated content
   - Publish when ready

### Project ID Format
Project IDs must be exactly 24 characters and follow the Vision Trust Catalog format:
```
Example: 65aa638d4dbbec41d0217cc3
```

## Customization

You can customize the overlay color for the second Layout, by adding custom CSS to your theme. The plugin uses a CSS custom property that you can override:

```css
/* Add this to your theme's style.css or custom CSS */
:root {
  --dataspace-extension-overlay-color: #f0f0f0;
}
```

## External Services

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

## Troubleshooting

### Common Issues

#### "Project not found" Error
- Verify project ID is exactly 24 characters
- Check internet connection
- Confirm Vision Trust API is accessible

#### Images Not Loading
- Check WordPress media upload permissions
- Ensure sufficient disk space

#### Template Not Applying
- Verify page template is set correctly
- Check for theme conflicts
- Clear any caching plugins

#### JavaScript Errors
- Check for jQuery conflicts
- Verify all assets are loading correctly
- Check browser console for specific errors

### Debug Mode
Enable WordPress debug mode for detailed error information:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```
