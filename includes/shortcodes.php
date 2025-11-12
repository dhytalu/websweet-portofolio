<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcodes: Live Preview & Order WhatsApp
 */
function wssp_register_shortcodes() {
    add_shortcode( 'wssp_live_preview', 'wssp_shortcode_live_preview' );
    add_shortcode( 'wssp_order_whatsapp', 'wssp_shortcode_order_whatsapp' );
}
add_action( 'init', 'wssp_register_shortcodes' );

function wssp_shortcode_live_preview( $atts = array() ) {
    $atts = shortcode_atts( array(
        'post_id' => get_the_ID(),
        'text'    => 'Live Preview',
        'class'   => '',
        'target'  => '_blank',
        'icon'    => '1',
        'icon_class' => 'fa fa-eye me-2',
        'embed'   => '1',
    ), $atts );
    $post_id = intval( $atts['post_id'] );
    if ( ! $post_id ) { return ''; }
    $url = get_post_meta( $post_id, '_wssp_url_live_preview', true );
    if ( empty( $url ) ) { return ''; }
    $classes = trim( 'btn btn-primary ' . ( $atts['class'] ? $atts['class'] : '' ) );
    $icon_html = ( $atts['icon'] && $atts['icon'] !== '0' ) ? '<i class="' . esc_attr( $atts['icon_class'] ) . '"></i>' : '';
    $href = ($atts['embed'] && $atts['embed'] !== '0')
      ? trailingslashit( get_permalink( $post_id ) ) . 'live/'
      : $url;
    return sprintf(
        '<a class="%s" href="%s" target="%s" rel="noopener">%s%s</a>',
        esc_attr( $classes ),
        esc_url( $href ),
        esc_attr( $atts['target'] ),
        $icon_html,
        esc_html( $atts['text'] )
    );
}

function wssp_shortcode_order_whatsapp( $atts = array() ) {
    $atts = shortcode_atts( array(
        'post_id' => get_the_ID(),
        'text'    => 'Order',
        'class'   => '',
        'target'  => '_blank',
        'icon'    => '1',
        'icon_class' => 'fa fa-whatsapp me-2',
    ), $atts );
    $post_id = intval( $atts['post_id'] );
    if ( ! $post_id ) { return ''; }
    if ( ! function_exists( 'wssp_get_whatsapp_order_url' ) ) { return ''; }
    $url = wssp_get_whatsapp_order_url( $post_id );
    if ( empty( $url ) ) { return ''; }
    $classes = trim( 'btn btn-success ' . ( $atts['class'] ? $atts['class'] : '' ) );
    $icon_html = ( $atts['icon'] && $atts['icon'] !== '0' ) ? '<i class="' . esc_attr( $atts['icon_class'] ) . '"></i>' : '';
    return sprintf(
        '<a class="%s" href="%s" target="%s" rel="noopener">%s%s</a>',
        esc_attr( $classes ),
        esc_url( $url ),
        esc_attr( $atts['target'] ),
        $icon_html,
        esc_html( $atts['text'] )
    );
}