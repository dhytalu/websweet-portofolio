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
              <?php
                $live = get_post_meta( get_the_ID(), '_wssp_url_live_preview', true );
                $wa = function_exists('wssp_get_whatsapp_order_url') ? wssp_get_whatsapp_order_url( get_the_ID() ) : '';
                if ( $live ) :
              ?>
                <p class="my-2 mx-1">
                  <a class="btn btn-primary" href="<?php echo esc_url( $live ); ?>" target="_blank" rel="noopener"><i class="fa fa-eye pe-2"></i>Live Preview</a>
                </p>
              <?php endif; ?>
              <?php if ( ! empty( $wa ) ) : ?>
                <p class="my-2 mx-1">
                  <a class="btn btn-success" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener"><i class="fa fa-whatsapp pe-2"></i>Order</a>
                </p>
              <?php endif; ?>
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