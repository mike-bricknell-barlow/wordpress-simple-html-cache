<?php

// Don't trigger output buffering in admin area
if ( is_admin() || strpos( $_SERVER['REQUEST_URI'], 'wp-login' ) !== FALSE ) {
    return;
}

ob_start();

add_action( 'shutdown', function() {
    $final = '';

    $levels = ob_get_level();

    for ($i = 0; $i < $levels; $i++) {
        $final .= ob_get_clean();
    }

    // Apply any filters to the final output
    echo apply_filters( 'shc_final_output', $final );
}, 0 );