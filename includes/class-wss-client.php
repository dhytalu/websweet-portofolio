<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WSSP_Client {
    private $base;
    private $access_key;
    private $last_error = null;

    public function __construct() {
        $this->base = WSSP_API_BASE;
        $this->access_key = get_option( WSSP_OPTION_KEY, '' );
    }

    private function request( $endpoint, $query = array() ) {
        if ( empty( $this->access_key ) ) {
            $this->last_error = 'Access key kosong';
            return array( 'items' => array(), 'headers' => array(), 'error' => 'Access key kosong' );
        }

        $query['access_key'] = $this->access_key;

        $url = add_query_arg( $query, trailingslashit( $this->base ) . ltrim( $endpoint, '/' ) );
        $ttl = intval( get_option( WSSP_CACHE_TTL_OPTION, 900 ) );
        if ( $ttl > 0 ) {
            $cache_key = 'wssp_cache_' . md5( $url );
            $cached = get_transient( $cache_key );
            if ( false !== $cached ) {
                return array( 'items' => $cached, 'headers' => array(), 'error' => null );
            }
        }
        $args = array(
            'timeout' => 15,
            'headers' => array(
                'Accept' => 'application/json',
            ),
        );
        $res = wp_remote_get( $url, $args );
        if ( is_wp_error( $res ) ) {
            $this->last_error = $res->get_error_message();
            return array( 'items' => array(), 'headers' => array(), 'error' => $res->get_error_message() );
        }
        $code = wp_remote_retrieve_response_code( $res );
        $body = wp_remote_retrieve_body( $res );
        $headers = wp_remote_retrieve_headers( $res );

        if ( $code !== 200 ) {
            $this->last_error = 'HTTP ' . $code;
            return array( 'items' => array(), 'headers' => $headers, 'error' => 'HTTP ' . $code );
        }
        $items = json_decode( $body, true );
        if ( ! is_array( $items ) ) {
            $items = array();
        }
        $this->last_error = null;
        if ( $ttl > 0 ) {
            set_transient( $cache_key, $items, $ttl );
            // Simpan index cache key untuk memudahkan clear cache
            $index = get_option( WSSP_CACHE_INDEX_OPTION, array() );
            if ( ! is_array( $index ) ) {
                $index = array();
            }
            if ( ! in_array( $cache_key, $index, true ) ) {
                $index[] = $cache_key;
                // Batasi ukuran index agar tidak terlalu besar
                if ( count( $index ) > 500 ) {
                    $index = array_slice( $index, -500 );
                }
                update_option( WSSP_CACHE_INDEX_OPTION, $index );
            }
        }
        return array( 'items' => $items, 'headers' => $headers, 'error' => null );
    }

    /**
     * Fetch all taxonomy terms: jenis-web (max per_page 100)
     */
    public function fetch_all_terms() {
        $page = 1;
        $all = array();
        do {
            $res = $this->request( 'jenis-web', array(
                'per_page' => 100,
                'page'     => $page,
                '_fields'  => 'id,name,slug,description',
                'orderby'  => 'name',
                'order'    => 'asc',
            ) );
            if ( $res['error'] ) {
                break;
            }
            $items = $res['items'];
            $all = array_merge( $all, $items );
            $page++;
        } while ( ! empty( $items ) && count( $items ) === 100 );
        return $all;
    }

    /**
     * Fetch all portofolio posts. If modified_after provided, incremental fetch.
     */
    public function fetch_all_portofolio( $modified_after = null ) {
        $page = 1;
        $all = array();
        do {
            $per_page = intval( get_option( WSSP_PER_PAGE_OPTION, 12 ) );
            if ( $per_page < 1 ) { $per_page = 1; }
            if ( $per_page > 100 ) { $per_page = 100; }
            $query = array(
                'per_page' => $per_page,
                'page'     => $page,
                '_embed'   => 1,
                '_fields'  => 'id,slug,title,excerpt,content,featured_media,_embedded',
                'orderby'  => 'date',
                'order'    => 'desc',
                'status'   => 'publish',
            );
            if ( ! empty( $modified_after ) ) {
                // ISO8601 format expected
                $query['modified_after'] = $modified_after;
            }
            $res = $this->request( 'portofolio', $query );
            if ( $res['error'] ) {
                break;
            }
            $items = $res['items'];
            $all = array_merge( $all, $items );
            $page++;
        } while ( ! empty( $items ) && count( $items ) === 100 );
        return $all;
    }

    public function get_last_error() {
        return $this->last_error;
    }

    /**
     * Extract featured image URL from embedded response
     */
    public static function extract_featured_image_url( $item ) {
        if ( isset( $item['_embedded']['wp:featuredmedia'][0]['source_url'] ) ) {
            return $item['_embedded']['wp:featuredmedia'][0]['source_url'];
        }
        return '';
    }

    /**
     * Extract taxonomy slugs for 'jenis-web' from embedded terms
     */
    public static function extract_jenis_web_slugs( $item ) {
        $slugs = array();
        if ( isset( $item['_embedded']['wp:term'] ) && is_array( $item['_embedded']['wp:term'] ) ) {
            foreach ( $item['_embedded']['wp:term'] as $term_group ) {
                foreach ( $term_group as $term ) {
                    if ( isset( $term['taxonomy'] ) && $term['taxonomy'] === 'jenis-web' && isset( $term['slug'] ) ) {
                        $slugs[] = $term['slug'];
                    }
                }
            }
        }
        return array_values( array_unique( $slugs ) );
    }
}