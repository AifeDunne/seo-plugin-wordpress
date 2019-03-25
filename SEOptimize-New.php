<?php
/*
  Plugin Name: SEOptimize New
  Description: Analyzes SEO traits both site-wide and for individual pages.
  Version: 1.0
  Author: Aife Dunne
 */

if (is_admin() === true) {
    
function hook_install() {
    $pURL = plugin_dir_path( __FILE__ );
    $resource_path = $pURL . "resources/";
    require_once($resource_path.'class.seoinstall.php');
    $SEOInstall = new SEOInstall(); 
    $content = $SEOInstall->run_install(); }
    
register_activation_hook( __FILE__,  'hook_install');
    
add_action( 'current_screen', 'load_plugin' );

function load_plugin() {
    $site_root = get_site_url(); 
    $admin_url = $site_root . "/wp-admin/";
    $index_url = $admin_url."index.php";
    $pURL = plugin_dir_path( __FILE__ );
    $resource_path = $pURL . "resources/";
    $request_src = $_SERVER['REQUEST_URI'];
    $current_url = $site_root . $request_src;
    $screen = get_current_screen();
    $is_edit = false;
    if ($screen->base === 'post') { 
        $search_var = strpos($request_src,'edit');
        if ($search_var !== false) { $is_edit = true; }
    }
    require_once($resource_path.'class.seoload.php');
    $SEOLoad = new SEOLoad();
    if ($current_url === $admin_url || $current_url === $index_url) { $SEOLoad->SEOptimize_Dashboard_Widgets(); }
    else if ( $is_edit === true ) { $SEOLoad->SEOptimize_Page_Widget(); }
}

add_action( 'deleted_post', 'change_lists' );
function change_lists($pid) {
    $pURL = plugin_dir_path( __FILE__ );
    $resource_path = $pURL . "resources/";
    require_once($resource_path.'class.seoptimize.php');
    $SEODelete = new SEOptimize();
    $SEODelete->remove_page($pid);
    }
}