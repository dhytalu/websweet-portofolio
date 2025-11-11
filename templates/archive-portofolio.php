<?php
/**
 * Template: Archive Portofolio
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>

<main id="primary" class="site-main">
  <header class="page-header">
    <h1 class="page-title">Portofolio</h1>
  </header>

  <?php if ( have_posts() ) : ?>
    <div class="wssp-archive-grid">
      <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('wssp-archive-item'); ?>>
          <a class="wssp-item-link" href="<?php the_permalink(); ?>" aria-label="Buka detail portofolio">
            <div class="wssp-thumb">
              <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large' ); } ?>
            </div>
            <h2 class="entry-title"><?php the_title(); ?></h2>
          </a>
          <div class="entry-excerpt"><?php the_excerpt(); ?></div>
          <div class="wssp-actions">
            <a class="btn btn-outline-primary btn-sm" href="<?php the_permalink(); ?>">Detail</a>
            <?php echo do_shortcode('[wssp_live_preview class="btn-sm"]'); ?>
            <?php echo do_shortcode('[wssp_order_whatsapp class="btn-sm" text="Order via WhatsApp"]'); ?>
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