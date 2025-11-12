<?php
/**
 * Template: Archive Portofolio
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>

<main id="primary" class="site-main container py-5">
  <header class="page-header">
    <h1 class="page-title">Portofolio</h1>
  </header>

  <?php if ( have_posts() ) : ?>
    <div class="wssp-archive-grid row">
      <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('wssp-archive-item col-md-3 mb-4'); ?>>
          <a class="wssp-item-link" href="<?php the_permalink(); ?>" aria-label="Buka detail portofolio">
            <div class="wssp-thumb">
              <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'full' ); } ?>
            </div>
            <h2 class="entry-title fs-5 fw-bold text-dark my-2"><?php the_title(); ?></h2>
          </a>
          <div class="wssp-actions d-flex justify-content-between">
            <?php echo do_shortcode('[wssp_live_preview class="btn-sm w-100 m-1"]'); ?>
            <?php echo do_shortcode('[wssp_order_whatsapp class="btn-sm w-100 m-1" text="Order"]'); ?>
          </div>
        </article>
      <?php endwhile; ?>
    </div>

    <nav class="navigation pagination" role="navigation">
      <?php the_posts_pagination( array( 'mid_size' => 2 ) ); ?>
    </nav>
  <?php else : ?>
    <p>Tidak ada portofolio saat ini.</p>
  <?php endif; ?>
</main>

<?php get_footer(); ?>