<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Enqueue Bootstrap CSS pada frontend untuk halaman Portofolio
 */
function wssp_frontend_enqueue() {
    $is_archive = is_post_type_archive( 'portofolio' );
    $is_single  = is_singular( 'portofolio' );
    // Jangan muat Bootstrap di halaman preview embed (/live/)
    $is_live    = $is_single && ( get_query_var( 'live', false ) !== false );
    if ( ( $is_archive || $is_single ) && ! $is_live ) {
        wp_enqueue_style(
            'wssp-bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
            array(),
            '5.3.2'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'wssp_frontend_enqueue' );