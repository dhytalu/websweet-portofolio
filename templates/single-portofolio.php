<?php
/**
 * Template: Single Portofolio
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>

<main id="primary" class="site-main container py-5">
  <?php while ( have_posts() ) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class('wssp-single-portofolio'); ?>>
      <div class="row">
        <div class="col-md-4">
          <?php if ( has_post_thumbnail() ) : ?>
            <div class="mb-3">
              <?php the_post_thumbnail( 'full', array( 'class' => 'img-fluid rounded' ) ); ?>
            </div>
          <?php endif; ?>
          
        </div>
        <div class="col-md-8">
          <div class="entry-header">
            <h1 class="entry-title"><?php the_title(); ?></h1>
            <div class="d-flex justify-content-start align-items-center">
              <p class="my-2 mx-1">
                <?php echo do_shortcode('[wssp_live_preview class="btn-sm"]'); ?>
              </p>
              <p class="my-2 mx-1">
                <?php echo do_shortcode('[wssp_order_whatsapp class="btn-sm" text="Order via WhatsApp"]'); ?>
              </p>
            </div>
          </div>
          <?php
            $terms = get_the_terms( get_the_ID(), 'jenis-web' );
            if ( $terms && ! is_wp_error( $terms ) ) {
              echo '<div class="wssp-terms mb-2"><strong>Jenis:</strong> ';
              $names = wp_list_pluck( $terms, 'name' );
              echo '<a href="'.esc_url( get_term_link( $terms[0] ) ).'">'.esc_html( implode( ', ', $names ) ).'</a>';
              echo '</div>';
            }
          ?>
          <div class="entry-content">
            <?php the_content(); ?>
          </div>
        </div>
      </div>
    </article>
  <?php endwhile; ?>
</main>

<?php get_footer(); ?>