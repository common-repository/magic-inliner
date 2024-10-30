<?php
// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}
 
$option_name = 'plugin_option_name';
 
delete_option( 'mi_registered_css_styles' );
delete_option( 'mi_registered_js_scripts' );
delete_option( 'mi_registered_css_handles' );
delete_option( 'mi_registered_js_handles' );
 
// For site options in Multisite
delete_site_option( 'mi_registered_css_styles' );  
delete_site_option( 'mi_registered_js_scripts' );  
delete_site_option( 'mi_registered_css_handles' );  
delete_site_option( 'mi_registered_js_handles' );  
?>