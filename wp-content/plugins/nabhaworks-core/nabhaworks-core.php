<?php
/**
 * Plugin Name: NabhaWorks Core
 * Plugin URI:  https://nabhaworks.altervista.org/
 * Description: Categorie editoriali del blog, portfolio (CPT Progetti) e sezione "Fonti e approfondimenti".
 * Version:     1.1.0
 * Author:      Mario Ansaldi
 * License:     GPL-2.0-or-later
 * Text Domain: nabhaworks-core
 */

if (!defined('ABSPATH')) {
    exit;
}

/* -------------------------------------------------------------
 * 1. CATEGORIE EDITORIALI DEL BLOG (create automaticamente)
 * ------------------------------------------------------------- */

function nabhaworks_default_categories(): array {
    return [
        'manoscritti-e-testi'     => 'Manoscritti e testi',
        'archeologia-e-storia'    => 'Archeologia e storia',
        'studi-e-interpretazione' => 'Studi e interpretazione',
        'ricerca-e-strumenti'     => 'Ricerca e strumenti',
    ];
}

function nabhaworks_ensure_categories(): void {
    foreach (nabhaworks_default_categories() as $slug => $name) {
        if (term_exists($slug, 'category')) {
            continue;
        }
        wp_insert_term($name, 'category', ['slug' => $slug]);
    }
}

register_activation_hook(__FILE__, 'nabhaworks_ensure_categories');

// Rete di sicurezza: ricrea la categoria se qualcuno la cancella per errore
add_action('admin_init', 'nabhaworks_ensure_categories');

/* -------------------------------------------------------------
 * 2. CUSTOM POST TYPE: PROGETTI (portfolio)
 * ------------------------------------------------------------- */
add_action('init', function () {

    register_post_type('nabha_progetto', [
        'labels' => [
            'name'               => 'Progetti',
            'singular_name'      => 'Progetto',
            'add_new'            => 'Aggiungi progetto',
            'add_new_item'       => 'Aggiungi nuovo progetto',
            'edit_item'          => 'Modifica progetto',
            'view_item'          => 'Vedi progetto',
            'search_items'       => 'Cerca progetti',
            'not_found'          => 'Nessun progetto trovato',
            'not_found_in_trash' => 'Nessun progetto nel cestino',
        ],
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => ['slug' => 'progetti'],
        'show_in_rest'  => true, // indispensabile per Gutenberg
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
        'menu_icon'     => 'dashicons-book-alt',
        'menu_position' => 5,
    ]);

    register_taxonomy('nabha_argomento', 'nabha_progetto', [
        'labels' => [
            'name'          => 'Argomenti',
            'singular_name' => 'Argomento',
        ],
        'hierarchical'      => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'argomento'],
    ]);
});

/* -------------------------------------------------------------
 * 3. FONTI E APPROFONDIMENTI
 *    Meta box nell'editor + sezione a fine articolo.
 *    Formato: una fonte per riga —  Descrizione | URL
 * ------------------------------------------------------------- */

const NABHA_FONTI_META = '_nabha_fonti';

add_action('add_meta_boxes', function () {
    add_meta_box(
        'nabha_fonti',
        'Fonti e approfondimenti',
        'nabhaworks_fonti_metabox',
        ['post', 'nabha_progetto'],
        'normal',
        'default'
    );
});

function nabhaworks_fonti_metabox($post): void {
    wp_nonce_field('nabha_fonti_save', 'nabha_fonti_nonce');
    $value = get_post_meta($post->ID, NABHA_FONTI_META, true);
    ?>
    <p style="color:#666; margin-top:0;">
        Una fonte per riga. Per renderla cliccabile, aggiungi l'URL dopo un pipe:<br>
        <code>Autore, Titolo (anno) | https://esempio.org/scheda</code>
    </p>
    <textarea name="nabha_fonti" rows="6" style="width:100%;"><?php
        echo esc_textarea($value);
    ?></textarea>
    <?php
}

add_action('save_post', function ($post_id) {
    if (!isset($_POST['nabha_fonti_nonce']) ||
        !wp_verify_nonce($_POST['nabha_fonti_nonce'], 'nabha_fonti_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (!isset($_POST['nabha_fonti'])) {
        return;
    }
    update_post_meta(
        $post_id,
        NABHA_FONTI_META,
        sanitize_textarea_field(wp_unslash($_POST['nabha_fonti']))
    );
});

function nabhaworks_render_fonti($post_id): string {
    $raw = get_post_meta($post_id, NABHA_FONTI_META, true);
    if (!$raw) {
        return '';
    }

    $items = '';
    foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts = array_map('trim', explode('|', $line, 2));
        $item  = esc_html($parts[0]);
        $url   = $parts[1] ?? '';

        if ($url !== '') {
            $item .= ' — <a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">approfondisci ↗</a>';
        }
        $items .= '<li>' . $item . '</li>';
    }

    if ($items === '') {
        return '';
    }

    return '<section class="nabha-fonti"><h2>Fonti e approfondimenti</h2><ul>' . $items . '</ul></section>';
}

add_filter('the_content', function ($content) {
    if (!is_singular(['post', 'nabha_progetto']) || !in_the_loop() || !is_main_query()) {
        return $content;
    }
    return $content . nabhaworks_render_fonti(get_the_ID());
});

/* -------------------------------------------------------------
 * 4. STILI
 * ------------------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {
    wp_register_style(
        'nabha-fonti',
        plugins_url('assets/fonti.css', __FILE__),
        [],
        '1.1.0'
    );
    if (is_singular(['post', 'nabha_progetto'])) {
        wp_enqueue_style('nabha-fonti');
    }
});