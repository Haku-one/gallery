<?php
/**
 * Elementor Custom Queries for taxonomy `tip-rabot`.
 *
 * How to use in Elementor:
 * - In Loop Grid: set Query ID to `tip_rabot_grid`
 * - In Loop Carousel: set Query ID to `tip_rabot_carousel`
 *
 * Optional URL params:
 * - tip-rabot: term slug of taxonomy `tip-rabot` (e.g., ?tip-rabot=remont)
 * - carousel_count: integer; grid will offset by this to avoid duplicates (e.g., ?carousel_count=5)
 * - grid_offset: integer; overrides computed offset for grid if provided
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper: build tax_query for `tip-rabot` taxonomy from GET param.
 */
function er_build_tip_rabot_tax_query_from_request(): array {
    $term_slug = isset($_GET['tip-rabot']) ? sanitize_text_field(wp_unslash($_GET['tip-rabot'])) : '';
    if ($term_slug === '' || $term_slug === 'all') {
        return [];
    }

    return [
        [
            'taxonomy' => 'tip-rabot',
            'field'    => 'slug',
            'terms'    => [$term_slug],
            'operator' => 'IN',
            'include_children' => true,
        ],
    ];
}

/**
 * Loop Carousel query: `tip_rabot_carousel`
 * Use URL: ?tip-rabot=slug to filter by taxonomy term.
 */
add_action('elementor/query/tip_rabot_carousel', function (WP_Query $query): void {
    // Filter by taxonomy if provided
    $tax_query = er_build_tip_rabot_tax_query_from_request();
    if (!empty($tax_query)) {
        $query->set('tax_query', $tax_query);
    }

    // Stable ordering for predictable offsets/duplicates handling
    if (!$query->get('orderby')) {
        $query->set('orderby', 'date');
    }
    if (!$query->get('order')) {
        $query->set('order', 'DESC');
    }

    // Avoid performance overhead for carousels
    $query->set('no_found_rows', true);
    $query->set('ignore_sticky_posts', true);
});

/**
 * Loop Grid query: `tip_rabot_grid`
 * Will auto-apply `offset` based on `carousel_count` URL param to avoid duplicates
 * with the carousel when both use the same ordering and filters.
 */
add_action('elementor/query/tip_rabot_grid', function (WP_Query $query): void {
    // Filter by taxonomy if provided
    $tax_query = er_build_tip_rabot_tax_query_from_request();
    if (!empty($tax_query)) {
        $query->set('tax_query', $tax_query);
    }

    // Stable ordering
    if (!$query->get('orderby')) {
        $query->set('orderby', 'date');
    }
    if (!$query->get('order')) {
        $query->set('order', 'DESC');
    }

    // Respect explicit grid_offset if provided
    $explicit_offset = isset($_GET['grid_offset']) ? intval($_GET['grid_offset']) : null;
    if ($explicit_offset !== null && $explicit_offset > 0) {
        $query->set('offset', $explicit_offset);
    } else {
        // Compute offset from carousel_count if present
        $carousel_count = isset($_GET['carousel_count']) ? max(0, intval($_GET['carousel_count'])) : 0;
        if ($carousel_count > 0) {
            $query->set('offset', $carousel_count);
        }
    }

    $query->set('ignore_sticky_posts', true);
});