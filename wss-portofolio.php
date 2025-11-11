<?php
/**
 * Plugin Name: WSS Portofolio
 * Description: Import portofolio dan kategori (jenis-web) dari API WSS ke situs WordPress lokal.
 * Version: 1.0.0
 * Author: Nur Dita Damayanti
 * URI: https://github.com/dhytalu/websweet-portofolio
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WSSP_PLUGIN_VERSION', '1.0.0' );
define( 'WSSP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WSSP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WSSP_API_BASE', 'https://my.websweetstudio.com/wp-json/wp/v2' );
define( 'WSSP_OPTION_KEY', 'wssp_access_key' );
define( 'WSSP_LAST_SYNC_OPTION', 'wssp_last_sync' );
define( 'WSSP_PER_PAGE_OPTION', 'wssp_per_page' );
define( 'WSSP_CACHE_TTL_OPTION', 'wssp_cache_ttl' );
define( 'WSSP_CACHE_INDEX_OPTION', 'wssp_cache_index' );
define( 'WSSP_REMOTE_META_KEY', '_wssp_remote_id' );

require_once WSSP_PLUGIN_PATH . 'includes/class-wss-client.php';
require_once WSSP_PLUGIN_PATH . 'includes/class-wss-importer.php';

/**
 * Register CPT: portofolio
 */
function wssp_register_cpt() {
    $labels = array(
        'name'               => 'Portofolio',
        'singular_name'      => 'Portofolio',
        'add_new'            => 'Tambah Baru',
        'add_new_item'       => 'Tambah Portofolio',
        'edit_item'          => 'Edit Portofolio',
        'new_item'           => 'Portofolio Baru',
        'view_item'          => 'Lihat Portofolio',
        'search_items'       => 'Cari Portofolio',
        'not_found'          => 'Tidak ada portofolio ditemukan',
        'not_found_in_trash' => 'Tidak ada portofolio di tong sampah',
        'menu_name'          => 'Portofolio',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'portofolio' ),
        'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-portfolio',
        'menu_position'      => 5,
    );

    register_post_type( 'portofolio', $args );
}
add_action( 'init', 'wssp_register_cpt' );

/**
 * Register taxonomy: jenis-web
 */
function wssp_register_taxonomy() {
    $labels = array(
        'name'              => 'Jenis Web',
        'singular_name'     => 'Jenis Web',
        'search_items'      => 'Cari Jenis Web',
        'all_items'         => 'Semua Jenis Web',
        'edit_item'         => 'Edit Jenis Web',
        'update_item'       => 'Update Jenis Web',
        'add_new_item'      => 'Tambah Jenis Web',
        'new_item_name'     => 'Nama Jenis Web Baru',
        'menu_name'         => 'Jenis Web',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'jenis-web' ),
    );

    register_taxonomy( 'jenis-web', array( 'portofolio' ), $args );
}
add_action( 'init', 'wssp_register_taxonomy' );

/**
 * Admin menu: Tools -> WSS Portofolio
 */
function wssp_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=portofolio',
        'WSS Portofolio',
        'WSS Portofolio',
        'manage_options',
        'wssp-portofolio',
        'wssp_admin_page_render'
    );
}
add_action( 'admin_menu', 'wssp_admin_menu' );

/**
 * Save settings and handle import actions
 */
function wssp_handle_post_actions() {
    if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( isset( $_POST['wssp_action'] ) ) {
        check_admin_referer( 'wssp_nonce_action', 'wssp_nonce_field' );
        $action = sanitize_text_field( wp_unslash( $_POST['wssp_action'] ) );
        $client = new WSSP_Client();
        $importer = new WSSP_Importer();

        if ( 'save_settings' === $action ) {
            $key = isset( $_POST['wssp_access_key'] ) ? sanitize_text_field( wp_unslash( $_POST['wssp_access_key'] ) ) : '';
            update_option( WSSP_OPTION_KEY, $key );

            $per_page = isset( $_POST['wssp_per_page'] ) ? intval( $_POST['wssp_per_page'] ) : 12;
            if ( $per_page < 1 ) { $per_page = 1; }
            if ( $per_page > 100 ) { $per_page = 100; }
            update_option( WSSP_PER_PAGE_OPTION, $per_page );

            $cache_ttl = isset( $_POST['wssp_cache_ttl'] ) ? intval( $_POST['wssp_cache_ttl'] ) : 900;
            if ( $cache_ttl < 0 ) { $cache_ttl = 0; }
            update_option( WSSP_CACHE_TTL_OPTION, $cache_ttl );

            add_settings_error( 'wssp_messages', 'wssp_saved', 'Pengaturan disimpan', 'updated' );
        }

        if ( 'import_categories' === $action ) {
            $terms = $client->fetch_all_terms();
            $result = $importer->import_categories( $terms );
            add_settings_error( 'wssp_messages', 'wssp_import_terms', sprintf( 'Kategori diimpor: %d dibuat, %d diperbarui', $result['created'], $result['updated'] ), 'updated' );
        }

        if ( 'import_posts' === $action ) {
            $last_sync = get_option( WSSP_LAST_SYNC_OPTION, '' );
            $force_full = ! empty( $_POST['wssp_force_full'] );
            if ( $force_full ) {
                $last_sync = '';
            }
            $posts = $client->fetch_all_portofolio( $last_sync ?: null );

            // Jika kosong tapi ada last_sync, coba fallback full import
            $fallback_used = false;
            if ( empty( $posts ) && ! empty( $last_sync ) ) {
                $posts = $client->fetch_all_portofolio( null );
                $fallback_used = true;
            }

            // Jika error, tampilkan notice
            $last_error = method_exists( $client, 'get_last_error' ) ? $client->get_last_error() : null;
            if ( $last_error ) {
                add_settings_error( 'wssp_messages', 'wssp_import_error', 'Gagal mengambil data portofolio: ' . esc_html( $last_error ), 'error' );
            }

            $result = $importer->import_posts( $posts );

            // Update last_sync hanya jika ada perubahan
            if ( ( $result['created'] + $result['updated'] ) > 0 ) {
                update_option( WSSP_LAST_SYNC_OPTION, gmdate( 'c' ) );
            }

            $message = sprintf( 'Post diimpor: %d dibuat, %d diperbarui', $result['created'], $result['updated'] );
            if ( $fallback_used ) {
                $message .= ' (Fallback full import karena tidak ada perubahan setelah last_sync)';
            }
            add_settings_error( 'wssp_messages', 'wssp_import_posts', $message, 'updated' );
        }

        if ( 'clear_cache' === $action ) {
            // Hapus cache berdasarkan index yang disimpan
            $index = get_option( WSSP_CACHE_INDEX_OPTION, array() );
            if ( is_array( $index ) ) {
                foreach ( $index as $key ) {
                    delete_transient( $key );
                }
            }
            update_option( WSSP_CACHE_INDEX_OPTION, array() );

            // Fallback: bersihkan transients di options table dengan prefix
            global $wpdb;
            $prefix = esc_sql( '_transient_wssp_cache_' );
            $timeout_prefix = esc_sql( '_transient_timeout_wssp_cache_' );
            $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '{$prefix}%' OR option_name LIKE '{$timeout_prefix}%'" );

            add_settings_error( 'wssp_messages', 'wssp_cache_cleared', 'Cache API dibersihkan', 'updated' );
        }
    }
}
add_action( 'admin_init', 'wssp_handle_post_actions' );

/**
 * Admin page render
 */
function wssp_admin_page_render() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    settings_errors( 'wssp_messages' );
    $saved_key = get_option( WSSP_OPTION_KEY, '' );
    $saved_per_page = intval( get_option( WSSP_PER_PAGE_OPTION, 12 ) );
    $saved_cache_ttl = intval( get_option( WSSP_CACHE_TTL_OPTION, 900 ) );
    ?>
    <div class="wrap">
        <h1>WSS Portofolio Importer</h1>

        <h2>Pengaturan</h2>
        <form method="post">
            <?php wp_nonce_field( 'wssp_nonce_action', 'wssp_nonce_field' ); ?>
            <input type="hidden" name="wssp_action" value="save_settings" />
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="wssp_access_key">Access Key</label></th>
                    <td>
                        <input name="wssp_access_key" id="wssp_access_key" type="text" class="regular-text" value="<?php echo esc_attr( $saved_key ); ?>" />
                        <p class="description">Masukkan token access_key API WSS kamu.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="wssp_per_page">Per Page (import portofolio)</label></th>
                    <td>
                        <input name="wssp_per_page" id="wssp_per_page" type="number" min="1" max="100" class="small-text" value="<?php echo esc_attr( $saved_per_page ); ?>" />
                        <p class="description">Jumlah item per halaman saat fetch portofolio (default 12).</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="wssp_cache_ttl">Cache TTL (detik)</label></th>
                    <td>
                        <input name="wssp_cache_ttl" id="wssp_cache_ttl" type="number" min="0" class="small-text" value="<?php echo esc_attr( $saved_cache_ttl ); ?>" />
                        <p class="description">Lama cache response API disimpan. Set 0 untuk menonaktifkan (misal 900 = 15 menit).</p>
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Simpan Pengaturan' ); ?>
        </form>

        <h2>Import</h2>
        <form method="post" style="margin-bottom:16px;">
            <?php wp_nonce_field( 'wssp_nonce_action', 'wssp_nonce_field' ); ?>
            <input type="hidden" name="wssp_action" value="import_categories" />
            <?php submit_button( 'Import Kategori (jenis-web)', 'secondary' ); ?>
        </form>

        <form method="post">
            <?php wp_nonce_field( 'wssp_nonce_action', 'wssp_nonce_field' ); ?>
            <input type="hidden" name="wssp_action" value="import_posts" />
            <?php submit_button( 'Import Portofolio', 'primary' ); ?>
            <p>
                <label>
                    <input type="checkbox" name="wssp_force_full" value="1" />
                    Full import (abaikan last_sync)
                </label>
            </p>
            <p class="description">Import incremental berdasarkan perubahan terbaru. Jika pertama kali, semua post akan diambil.</p>
        </form>

        <h2>Cache</h2>
        <form method="post">
            <?php wp_nonce_field( 'wssp_nonce_action', 'wssp_nonce_field' ); ?>
            <input type="hidden" name="wssp_action" value="clear_cache" />
            <?php submit_button( 'Clear Cache', 'secondary' ); ?>
            <p class="description">Menghapus semua cache API yang disimpan untuk menghemat request saat debugging atau setelah perubahan pengaturan.</p>
        </form>
    </div>
    <?php
}