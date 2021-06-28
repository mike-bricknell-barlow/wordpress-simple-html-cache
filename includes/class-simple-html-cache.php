<?php

class SimpleHTMLCache {
    function __construct() {
        add_filter( 'shc_final_output', [ $this, 'save_cache_file' ] );
        add_filter( 'final_output', [ $this, 'save_cache_file' ] );
        add_action( 'template_redirect', [ $this, 'serve_from_cache' ] );
        add_action( 'admin_bar_menu', [ $this, 'add_clear_cache_button' ], 500 );
        add_action( 'admin_init', [ $this, 'purge_cache' ] );
        add_action( 'admin_notices', [ $this, 'show_cache_cleared_message' ] );
    }

    protected function get_file_name() {
        $post_id = get_the_id();
        $post_type = get_post_type();

        $file_name = false;

        switch( true ) {
            case is_category(): 
                $file_name = sprintf(
                    '%s/cache/%s-%s-%s.html',
                    SIMPLE_HTML_CACHE_PLUGIN_DIR_PATH,
                    $post_type,
                    'category',
                    get_the_category()[0]->slug
                );
                break;

            case is_tag(): 
                $file_name = sprintf(
                    '%s/cache/%s-%s-%s.html',
                    SIMPLE_HTML_CACHE_PLUGIN_DIR_PATH,
                    $post_type,
                    'tag',
                    single_tag_title( '', false )
                );
                break;

            case is_date(): 
                $file_name = sprintf(
                    '%s/cache/%s-%s-%s.html',
                    SIMPLE_HTML_CACHE_PLUGIN_DIR_PATH,
                    $post_type,
                    'date-archive',
                    get_query_var('year') . '-' . get_query_var('monthnum') . '-' . get_query_var('day')
                );
                break;

            case is_author(): 
                $file_name = sprintf(
                    '%s/cache/%s-%s.html',
                    SIMPLE_HTML_CACHE_PLUGIN_DIR_PATH,
                    'author',
                    get_query_var( 'author_name' )
                );
                break;

            case is_tax(): 
                global $wp_query;
                $tax = $wp_query->get_queried_object();
                $file_name = sprintf(
                    '%s/cache/%s-%s-%s.html',
                    SIMPLE_HTML_CACHE_PLUGIN_DIR_PATH,
                    $post_type,
                    'taxonomy',
                    $tax->slug
                );
                break;

            case ( is_archive() || is_home() ): 
                $file_name = sprintf(
                    '%s/cache/%s-%s.html',
                    SIMPLE_HTML_CACHE_PLUGIN_DIR_PATH,
                    $post_type,
                    'archive'
                );
                break;

            case is_404(): 
                $file_name = sprintf(
                    '%s/cache/%s.html',
                    SIMPLE_HTML_CACHE_PLUGIN_DIR_PATH,
                    '404'
                );
                break;

            default: 
                $file_name = sprintf(
                    '%s/cache/%s-%s.html',
                    SIMPLE_HTML_CACHE_PLUGIN_DIR_PATH,
                    $post_type,
                    $post_id
                );
                break;
        }

        return $file_name;
    }

    public function serve_from_cache() {
        if( is_user_logged_in() ) {
            // Don't serve from cache if user logged-in
            return;
        }

        if( is_admin() || $GLOBALS['pagenow'] === 'wp-login.php' ) {
            // Don't serve from cache in admin area
            return;
        }

        if( ! file_exists( $this->get_file_name() ) ) {
            return;
        }

        header( 'X-cache: Hit' );
        echo file_get_contents( $this->get_file_name() );
        die();
    }

    public function save_cache_file( $html ) {
        if( is_user_logged_in() ) {
            // Don't save to cache if user logged-in
            return $html;
        }

        if( is_admin() || $GLOBALS['pagenow'] === 'wp-login.php' ) {
            // Don't save to cache in admin area
            return $html;
        }

        if( is_plugin_active( 'simple-webp-images/simple-webp-images.php' ) && current_filter() !== 'final_output' ) {
            // If Simple Webp Images is installed, hook into it's final_output filter instead
            return $html;
        }
        
        file_put_contents(
            $this->get_file_name(),
            $html
        );

        return $html;
    }

    public function add_clear_cache_button( $admin_bar ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $admin_bar->add_menu( array(
            'id'    => 'shc_clear_cache',
            'parent' => null,
            'group'  => null,
            'title' => 'Purge cache',
            'href'  => admin_url('admin.php?shc=clear-cache'),
            'meta' => [
                'title' => __( 'Purge the Simple HTML Cache', 'simple-html-cache' ), 
            ]
        ) );
    }

    public function purge_cache() {
        if( ! isset( $_GET['shc'] ) || $_GET['shc'] != 'clear-cache' ) {
            return;
        }

        $cache_files = glob(
            sprintf(
                '%s/cache/*',
                SIMPLE_HTML_CACHE_PLUGIN_DIR_PATH
            )
        ); 

        foreach( $cache_files as $cache_file ) {
            unlink( $cache_file );
        }

        wp_redirect( admin_url( '/?shc-cleared=1' ) );
        exit();
    }

    public function show_cache_cleared_message() {
        if( ! isset( $_GET['shc-cleared'] ) ) {
            return;
        }
        
        echo '<div class="notice notice-success is-dismissible">
            <p>Simple HTML Cache cleared successfully.</p>
        </div>';
    }
}