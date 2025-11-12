<?php
/**
 * Template: Preview Portofolio (full page, native CSS)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$live_url = get_post_meta( get_the_ID(), '_wssp_url_live_preview', true );
?><!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Live Preview: <?php echo esc_html( get_the_title() ); ?></title>
    <style>
      :root { color-scheme: light dark; }
      * { box-sizing: border-box; }
      html, body { height: 100%; }
      body {
        margin: 0;
        font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, "Helvetica Neue", Arial, "Apple Color Emoji", "Segoe UI Emoji";
        background: #0b1220;
        color: #e5e7eb;
      }
      .wssp-page {
        min-height: 100vh;
        display: grid;
        grid-template-rows: auto 1fr;
      }
      .wssp-toolbar {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        background: #111827;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        position: sticky;
        top: 0;
        z-index: 10;
      }
      .wssp-title { font-size: 14px; opacity: .8; margin-left: auto; }
      .wssp-btn {
        appearance: none;
        border: 1px solid rgba(255,255,255,0.2);
        background: transparent;
        color: #e5e7eb;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 14px;
        text-decoration: none;
        line-height: 1;
        transition: background .15s ease, border-color .15s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
      }
      .wssp-btn:hover { background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.3); }
      .wssp-btn-primary { background: #2563eb; border-color: #2563eb; }
      .wssp-btn-primary:hover { background: #1e40af; border-color: #1e40af; }
      .wssp-embed {
        height: 100%;
        width: 100%;
        display: block;
        border: none;
        background: #fff;
      }
      .wssp-embed-wrap { height: 100%; }
      .wssp-alert {
        margin: 24px;
        padding: 12px 16px;
        border-radius: 8px;
        background: #f59e0b; /* amber */
        color: #1f2937;
        font-weight: 600;
      }
      @media (prefers-color-scheme: dark) {
        .wssp-alert { background: #f59e0b; color: #111827; }
      }
      @media only screen and (max-width: 768px){
        .hiddenMobile {display: none;}
      }
    </style>
  </head>
  <body>
    <div class="wssp-page">
      <div class="wssp-toolbar">
        <a class="wssp-btn" href="<?php echo esc_url( get_permalink() ); ?>" aria-label="Kembali ke detail">⟵ <span class="hiddenMobile">Kembali</span></a>
        <?php echo do_shortcode('[wssp_order_whatsapp class="wssp-btn wssp-btn-primary" target="_blank" text="Order Web"]'); ?>
        <span class="wssp-title">Live Preview: <?php echo esc_html( get_the_title() ); ?></span>
      </div>
      <div class="wssp-embed-wrap">
        <?php if ( empty( $live_url ) ) : ?>
          <div class="wssp-alert">Live Preview belum tersedia untuk item ini.</div>
        <?php else : ?>
          <iframe class="wssp-embed" src="<?php echo esc_url( $live_url ); ?>" title="Live Preview" loading="lazy" allow="fullscreen"></iframe>
        <?php endif; ?>
      </div>
    </div>
  </body>
</html>