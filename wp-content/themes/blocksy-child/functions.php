<?php
/**
 * NabhaWorks Child Theme
 */

add_action('wp_enqueue_scripts', function () {
    // Stile base del child (style.css)
    wp_enqueue_style(
        'blocksy-child',
        get_stylesheet_uri(),
        ['ct-main-styles'],
        wp_get_theme()->get('Version')
    );

    // CSS editoriali — spostati dal Customizer al child theme
    wp_enqueue_style(
        'nabhaworks-globale',
        get_stylesheet_directory_uri() . '/assets/css/nabhaworks-globale.css',
        ['blocksy-child'],
        '1.0.0'
    );

    wp_enqueue_style(
        'nabhaworks-observatorivm',
        get_stylesheet_directory_uri() . '/assets/css/nabhaworks-observatorivm.css',
        ['blocksy-child'],
        '1.0.0'
    );
}, 20);