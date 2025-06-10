<?php
/**
 * Plugin Name: WPCS Custom Post Type
 * Description: Custom post type plugin for content blocks
 * Version: 1.0
 * Author: Your Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPCS_CPT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPCS_CPT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once WPCS_CPT_PLUGIN_DIR . 'includes/class-post-type.php';
require_once WPCS_CPT_PLUGIN_DIR . 'includes/class-shortcode-handler.php';
require_once WPCS_CPT_PLUGIN_DIR . 'includes/class-admin-interface.php';

// Initialize plugin
function wpcs_cpt_init() {
    $post_type = new WPCS_Post_Type();
    $shortcode_handler = new WPCS_Shortcode_Handler();
    $admin_interface = new WPCS_Admin_Interface();
}
add_action('init', 'wpcs_cpt_init');

// Activation hook
register_activation_hook(__FILE__, 'wpcs_cpt_activate');
function wpcs_cpt_activate() {
    wpcs_cpt_init();
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'wpcs_cpt_deactivate');
function wpcs_cpt_deactivate() {
    flush_rewrite_rules();
}