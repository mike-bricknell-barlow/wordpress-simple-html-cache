<?php
/**
 * Plugin Name:       Simple HTML Cache
 * Plugin URI:        
 * Description:       Just a super simple HTML cache for WordPress. When a page or post is generated, the HTML file is stored and served in all future requests, until purged.
 * Version:           1.0.0
 * Requires at least: 5.0.0
 * Requires PHP:      7.0
 * Author:            Mike Bricknell-Barlow
 * Author URI:        https://bricknellbarlow.co.uk
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       simple-html-cache
*/

define( 'SIMPLE_HTML_CACHE_VERSION', '1.2.1' );
define( 'SIMPLE_HTML_CACHE_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'SIMPLE_HTML_CACHE_PLUGIN_DIR_PATH', dirname( __FILE__ ) );

require_once 'includes' . DIRECTORY_SEPARATOR . 'class-simple-html-cache.php';

new SimpleHTMLCache();

function shc_activation () {
    if ( ! file_exists ( WPMU_PLUGIN_DIR ) ) {
        mkdir ( WPMU_PLUGIN_DIR, 0777, true );
    }
    
    copy ( 
        SIMPLE_HTML_CACHE_PLUGIN_DIR_PATH . DIRECTORY_SEPARATOR . 'mu-plugins' . DIRECTORY_SEPARATOR . 'shc_output_buffering.php',
        WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'shc_output_buffering.php'
    );
}
register_activation_hook( __FILE__, 'shc_activation' );

function shc_deactivation () {
    unlink ( WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'shc_output_buffering.php' );
}
register_deactivation_hook( __FILE__, 'shc_deactivation' );