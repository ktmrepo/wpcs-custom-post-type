<?php
/**
 * Plugin Name: Custom Content Blocks
 * Plugin URI: https://yoursite.com
 * Description: Create custom content blocks with automatic shortcode generation for easy embedding across your site.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: custom-content-blocks
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CCB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CCB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CCB_VERSION', '1.0.0');

class CustomContentBlocks {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Include required files early
        $this->includeFiles();
        
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'loadTextDomain'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Initialize components
        new CCB_PostType();
        new CCB_ShortcodeHandler();
        new CCB_AdminInterface();
    }
    
    private function includeFiles() {
        require_once CCB_PLUGIN_PATH . 'includes/class-post-type.php';
        require_once CCB_PLUGIN_PATH . 'includes/class-shortcode-handler.php';
        require_once CCB_PLUGIN_PATH . 'includes/class-admin-interface.php';
    }
    
    public function loadTextDomain() {
        load_plugin_textdomain('custom-content-blocks', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function activate() {
        // Files are already included in constructor, so we can safely use the classes
        $postType = new CCB_PostType();
        $postType->registerPostType();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize the plugin
CustomContentBlocks::getInstance();