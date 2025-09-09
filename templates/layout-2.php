<?php
/*
 * Template Name: Dataspace Layout 2
 * Layout 2 template for project landing page
 * Variables expected:
 * $short_description — short description
 * $cover_url — image
 * $proposed_logo_url — logo
 * $trusted_logos — array with logo URLs
 * $cta_buttons — array of buttons ['text' => '', 'url' => '']
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header();

$cover_id = get_post_meta(get_the_ID(), 'cover_image_id', true);
if ($cover_id) {
    $cover_url = wp_get_attachment_image_url($cover_id, 'full');
} else {
    $raw = get_post_meta(get_the_ID(), '_dataspace_raw_urls', true);
    $cover_url = is_array($raw) && !empty($raw['cover']) ? esc_url($raw['cover']) : '';
}

$short_description = get_post_meta(get_the_ID(), 'short_description', true);

$proposed_id = get_post_meta(get_the_ID(), 'proposed_by_logo_id', true);
if ($proposed_id) {
    $proposed_logo_url = wp_get_attachment_image_url($proposed_id, 'full');
} else {
    $raw = get_post_meta(get_the_ID(), '_dataspace_raw_urls', true);
    $proposed_logo_url = is_array($raw) && !empty($raw['proposed']) ? esc_url($raw['proposed']) : '';
}

$trusted_entries = json_decode( get_post_meta( get_the_ID(), 'trusted_actors_entries', true ), true ) ?: [];

$cta_buttons = json_decode(get_post_meta(get_the_ID(), 'cta_buttons', true), true) ?? [];


?>

<div class="dataspace-landing layout-2">
    <div class="layout-2__header">
        <div class="layout-2__content">
            <h1 aria-label="<?php echo esc_attr(get_the_title()); ?>"><?php echo esc_html(get_the_title()); ?></h1>

            <?php $short_desc_trim2 = wp_trim_words($short_description, 30, '...'); ?>
            <div class="layout-2__short-description" aria-label="<?php echo esc_attr(esc_html($short_desc_trim2)); ?>">
                <?php echo esc_html($short_desc_trim2); ?>
            </div>

            <div class="cta-buttons">
                <?php foreach ($cta_buttons as $i => $button): ?>
                <?php if (!empty($button['text']) && !empty($button['url'])): 
                    $tooltip_text = isset($button['tooltip']) ? $button['tooltip'] : '';
                    $aria_label_text = $button['text'];
                    $desc_id = 'cta-desc-' . $i;
                ?>
                <a href="<?php echo esc_url($button['url']); ?>" 
                   class="cta-btn" 
                   target="_blank"
                   role="button"
                   title="<?php echo esc_attr($tooltip_text); ?>" 
                   aria-label="<?php echo esc_attr($aria_label_text); ?>"
                   <?php if ($tooltip_text) echo 'aria-describedby="' . esc_attr( $desc_id ) . '"'; ?>
                   tabindex="0" >
                    <?php echo esc_html($button['text']); ?>
                </a>
                <?php if ($tooltip_text): ?>
                    <span id="<?php echo esc_attr($desc_id); ?>" class="sr-only" style="position:absolute; left:-9999px;"> <?php echo esc_html($tooltip_text); ?> </span>
                <?php endif; ?>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="layout-2__image">
            <?php if ($cover_url): ?>
            <img src="<?php echo esc_url($cover_url); ?>" alt="Cover Image" aria-label="Cover Image" />
            <?php endif; ?>
        </div>
    </div>

    <div class="layout-1__meta">
        <div class="meta-block">
            <span class="meta-title" aria-label="Proposed by">Proposed by</span>
            <div class="meta-org">
                <?php if ($proposed_logo_url): ?>
                <img src="<?php echo esc_url($proposed_logo_url); ?>" alt="Proposed by Logo" aria-label="Proposed by Logo" />
                <?php endif; ?>
                <div class="meta-org-name">
                    <?php echo esc_html( get_post_meta( get_the_ID(), 'proposed_by_name', true ) ); ?>
                </div>
            </div>
        </div>
        <?php if ( ! empty( $trusted_entries ) ) : ?>
        <div class="meta-block meta-block-partners">
            <span class="meta-title" aria-label="Trusted actors involved in the project">Trusted actors involved in the project</span>
            <div class="partners-slider">
            <button class="slider-prev" aria-label="Previous partner logo" style="display: none;">&laquo;</button>
            <div class="meta-partners">
                <?php foreach ( $trusted_entries as $partner ): ?>
                    <div class="partner">
                        <div class="partner-logo">
                            <?php
                            // render the logo itself (fallback to external URL)
                            if ( ! empty( $partner['id'] ) ) {
                                echo wp_get_attachment_image(
                                    $partner['id'],
                                    'full',
                                    false,
                                    [ 'alt' => esc_attr( $partner['name'] ), 'aria-label' => esc_attr( $partner['name'] ) ]
                                );
                            } elseif ( ! empty( $partner['url'] ) ) {
                                echo '<img src="' . esc_url( $partner['url'] ) . '" alt="' . esc_attr( $partner['name'] ) . '" aria-label="' . esc_attr( $partner['name'] ) . '" />';
                            }
                            ?>
                        </div>
                        <div class="partner-name">
                            <?php 
                            // render the name
                            echo esc_html( $partner['name'] ); 
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="slider-next" aria-label="Next partner logo" style="display: none;">&raquo;</button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="layout-2__description">
        <?php the_content(); ?>
    </div>

    <div class="layout-1__footer">
        <img src="<?php echo esc_url( plugin_dir_url(__FILE__) . '../public/images/Prometeus.png' ); ?>" alt="Prometheus X" aria-label="Prometheus X" />
    </div>
</div>


<?php get_footer(); ?>