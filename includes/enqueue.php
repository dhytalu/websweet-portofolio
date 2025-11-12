<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Enqueue Bootstrap CSS pada frontend untuk halaman Portofolio
 */
function wssp_frontend_enqueue() {
    if ( is_singular( 'portofolio' ) || is_post_type_archive( 'portofolio' ) ) {
        wp_enqueue_style(
            'wssp-bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
            array(),
            '5.3.2'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'wssp_frontend_enqueue' );