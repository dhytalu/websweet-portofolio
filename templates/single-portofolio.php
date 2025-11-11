<?php
/**
 * Template: Single Portofolio
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>

<main id="primary" class="site-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class('wssp-single-portofolio'); ?>>
      <header class="entry-header">
        <h1 class="entry-title"><?php the_title(); ?></h1>
        <?php
          $live = get_post_meta( get_the_ID(), '_wssp_url_live_preview', true );
          if ( $live ) :
        ?>
          <p class="wssp-live-preview">
            <a class="button button-primary" href="<?php echo esc_url( $live ); ?>" target="_blank" rel="noopener">Live Preview</a>
          </p>
        <?php endif; ?>
      </header>

      <?php if ( has_post_thumbnail() ) : ?>
        <div class="entry-thumbnail">
          <?php the_post_thumbnail( 'large' ); ?>
        </div>
      <?php endif; ?>

      <div class="entry-content">
        <?php the_content(); ?>
      </div>

      <footer class="entry-footer">
        <?php
          $terms = get_the_terms( get_the_ID(), 'jenis-web' );
          if ( $terms && ! is_wp_error( $terms ) ) {
            echo '<div class="wssp-terms"><strong>Jenis:</strong> ';
            $names = wp_list_pluck( $terms, 'name' );
            echo esc_html( implode( ', ', $names ) );
            echo '</div>';
          }
        ?>
      </footer>
    </article>
  <?php endwhile; ?>
</main>

<?php get_footer(); ?>