<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Bangun URL order WhatsApp untuk post tertentu
 */
function wssp_get_whatsapp_order_url( $post_id ) {
    $number = get_option( WSSP_WHATSAPP_NUMBER_OPTION, '' );
    if ( empty( $number ) ) { return ''; }
    $title = get_the_title( $post_id );
    $permalink = get_permalink( $post_id );
    $live = get_post_meta( $post_id, '_wssp_url_live_preview', true );
    $template = get_option( WSSP_WHATSAPP_TEMPLATE_OPTION, '' );
    if ( empty( $template ) ) {
        $template = "Halo, saya tertarik dengan portofolio {title}.\nLink: {permalink}\nPreview: {live_preview}";
    }
    $message = str_replace(
        array('{title}', '{permalink}', '{live_preview}'),
        array( $title, $permalink, $live ?: '' ),
        $template
    );
    return 'https://wa.me/' . rawurlencode( $number ) . '?text=' . rawurlencode( $message );
}