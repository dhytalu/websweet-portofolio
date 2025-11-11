<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WSSP_Importer {

    public function import_categories( $terms ) {
        $created = 0;
        $updated = 0;
        foreach ( $terms as $t ) {
            $slug = isset( $t['slug'] ) ? sanitize_title( $t['slug'] ) : '';
            // Ambil nama dari API; jika tidak ada, coba variasi lain, terakhir rapikan dari slug
            $name = '';
            if ( isset( $t['category'] ) ) {
                if ( is_array( $t['category'] ) ) {
                    // Antisipasi format mirip post title { rendered: "..." }
                    $name = isset( $t['category']['rendered'] ) ? wp_strip_all_tags( $t['category']['rendered'] ) : '';
                } else {
                    $name = sanitize_text_field( $t['category'] );
                }
            } elseif ( isset( $t['label'] ) ) {
                $name = sanitize_text_field( $t['label'] );
            }
            if ( empty( $name ) && ! empty( $slug ) ) {
                $name = $this->prettify_slug( $slug );
            }
            $desc = isset( $t['description'] ) ? wp_kses_post( $t['description'] ) : '';
            $remote_id = isset( $t['id'] ) ? intval( $t['id'] ) : 0;

            if ( empty( $slug ) ) {
                continue;
            }

            $existing = get_term_by( 'slug', $slug, 'jenis-web' );
            if ( $existing && isset( $existing->term_id ) ) {
                wp_update_term( $existing->term_id, 'jenis-web', array(
                    'category'        => ! empty( $name ) ? $name : $existing->name,
                    'description' => $desc,
                ) );
                if ( $remote_id ) {
                    update_term_meta( $existing->term_id, WSSP_REMOTE_META_KEY, $remote_id );
                }
                $updated++;
            } else {
                $res = wp_insert_term( $name ?: $slug, 'jenis-web', array(
                    'slug'        => $slug,
                    'description' => $desc,
                ) );
                if ( ! is_wp_error( $res ) && isset( $res['term_id'] ) ) {
                    if ( $remote_id ) {
                        update_term_meta( $res['term_id'], WSSP_REMOTE_META_KEY, $remote_id );
                    }
                    $created++;
                }
            }
        }
        return array( 'created' => $created, 'updated' => $updated );
    }

    private function prettify_slug( $slug ) {
        $slug = str_replace( array('-', '_'), ' ', $slug );
        $slug = preg_replace( '/\s+/', ' ', $slug );
        $slug = trim( $slug );
        return ucwords( $slug );
    }

    public function import_posts( $posts ) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $created = 0;
        $updated = 0;

        foreach ( $posts as $p ) {
            $remote_id = isset( $p['id'] ) ? intval( $p['id'] ) : 0;
            if ( ! $remote_id ) {
                continue;
            }

            $existing = $this->find_local_post_by_remote_id( $remote_id );
            $title = isset( $p['title']['rendered'] ) ? wp_strip_all_tags( $p['title']['rendered'] ) : ( isset( $p['slug'] ) ? $p['slug'] : 'Tanpa Judul' );
            $content = isset( $p['content']['rendered'] ) ? $p['content']['rendered'] : '';
            $excerpt = isset( $p['excerpt']['rendered'] ) ? $p['excerpt']['rendered'] : '';

            $postarr = array(
                'post_title'   => $title,
                'post_content' => $content,
                'post_excerpt' => $excerpt,
                'post_status'  => 'publish',
                'post_type'    => 'portofolio',
            );

            if ( $existing ) {
                $postarr['ID'] = $existing->ID;
                $result = wp_update_post( $postarr, true );
                if ( ! is_wp_error( $result ) ) {
                    $updated++;
                }
                $post_id = $existing->ID;
            } else {
                $result = wp_insert_post( $postarr, true );
                if ( ! is_wp_error( $result ) && $result ) {
                    add_post_meta( $result, WSSP_REMOTE_META_KEY, $remote_id, true );
                    $created++;
                    $post_id = $result;
                } else {
                    continue;
                }
            }

            // Set taxonomy terms (jenis-web)
            $slugs = WSSP_Client::extract_jenis_web_slugs( $p );
            if ( ! empty( $slugs ) ) {
                $term_ids = array();
                foreach ( $slugs as $slug ) {
                    $term = get_term_by( 'slug', sanitize_title( $slug ), 'jenis-web' );
                    if ( $term ) {
                        $term_ids[] = intval( $term->term_id );
                    }
                }
                if ( ! empty( $term_ids ) ) {
                    wp_set_object_terms( $post_id, $term_ids, 'jenis-web', false );
                }
            }

            // Download & set featured image
            $img_url = WSSP_Client::extract_featured_image_url( $p );
            if ( $img_url ) {
                $this->set_featured_image_from_url( $post_id, $img_url );
            }
        }

        return array( 'created' => $created, 'updated' => $updated );
    }

    private function find_local_post_by_remote_id( $remote_id ) {
        $q = new WP_Query( array(
            'post_type'      => 'portofolio',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'   => WSSP_REMOTE_META_KEY,
                    'value' => intval( $remote_id ),
                ),
            ),
            'fields'         => 'all',
            'no_found_rows'  => true,
        ) );
        if ( $q->have_posts() ) {
            return $q->posts[0];
        }
        return null;
    }

    private function set_featured_image_from_url( $post_id, $image_url ) {
        // Jika sudah ada thumbnail, jangan overwrite untuk sekarang.
        if ( has_post_thumbnail( $post_id ) ) {
            return;
        }
        $attachment_id = media_sideload_image( esc_url_raw( $image_url ), $post_id, null, 'id' );
        if ( is_wp_error( $attachment_id ) ) {
            return;
        }
        set_post_thumbnail( $post_id, intval( $attachment_id ) );
    }
}