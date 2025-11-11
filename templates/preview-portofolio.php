<?php
/**
 * Template: Preview Portofolio (embed live URL)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>

<main id="primary" class="site-main">
  <div class="container my-4">
    <div class="row">
      <div class="col-12">
        <h1 class="h3 mb-3">Live Preview: <?php the_title(); ?></h1>
        <div class="mb-3 d-flex gap-2">
          <a class="btn btn-outline-secondary btn-sm" href="<?php echo esc_url( get_permalink() ); ?>">Kembali ke Detail</a>
          <?php
            $live = get_post_meta( get_the_ID(), '_wssp_url_live_preview', true );
            if ( $live ) : ?>
              <a class="btn btn-primary btn-sm" href="<?php echo esc_url( $live ); ?>" target="_blank" rel="noopener">Buka di Tab Baru</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php
      $live_url = get_post_meta( get_the_ID(), '_wssp_url_live_preview', true );
      if ( empty( $live_url ) ) : ?>
        <div class="alert alert-warning" role="alert">
          Live Preview belum tersedia untuk item ini.
        </div>
      <?php else : ?>
        <div class="ratio ratio-16x9">
          <iframe
            src="<?php echo esc_url( $live_url ); ?>"
            title="Live Preview"
            loading="lazy"
            allow="fullscreen"
            style="border: 1px solid #e5e7eb; border-radius: .5rem;">
          </iframe>
        </div>
      <?php endif; ?>
  </div>
</main>

<?php get_footer(); ?>