<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Endpoint preview: /portofolio/{slug}/live/
 */
function wssp_register_live_endpoint() {
    add_rewrite_endpoint( 'live', EP_PERMALINK );
}
add_action( 'init', 'wssp_register_live_endpoint' );

/**
 * Hooks untuk aktivasi/deaktivasi (fungsi saja; pendaftaran hook di file utama)
 */
function wssp_activate() {
    wssp_register_live_endpoint();
    flush_rewrite_rules();
}

function wssp_deactivate() {
    flush_rewrite_rules();
}