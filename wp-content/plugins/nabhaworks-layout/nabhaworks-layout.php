<?php
/**
 * Plugin Name: Nabhaworks Layout
 * Description: Layout scuro per l'articolo singolo (testata, immagine in evidenza, colonna correlati, colori per categoria).
 * Version: 1.0.0
 * Author: Nabhaworks
 */
if (!defined('ABSPATH')) exit;

// Usa il nostro template per gli articoli (post) singoli.
add_filter('template_include', function ($template) {
    if (is_singular('post')) {
        $mine = plugin_dir_path(__FILE__) . 'templates/single-articolo.php';
        if (file_exists($mine)) return $mine;
    }
    return $template;
}, 99);

// Carica il CSS solo sugli articoli.
add_action('wp_enqueue_scripts', function () {
    if (is_singular('post')) {
        wp_enqueue_style('nabhaworks-articolo', plugins_url('assets/articolo.css', __FILE__), [], '1.0.0');
    }
});

// Classe sul body per poter colorare tutto per categoria.
add_filter('body_class', function ($classes) {
    if (is_singular('post')) {
        $classes[] = 'nw-single';
    }
    return $classes;
});
