<?php
if (!defined('ABSPATH')) {
    exit;
}

class WPCS_Admin_Interface {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_filter('manage_content_blocks_posts_columns', array($this, 'add_custom_columns'));
        add_action('manage_content_blocks_posts_custom_column', array($this, 'render_custom_columns'), 10, 2);
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=content_blocks',
            'Content Block Settings',
            'Settings',
            'manage_options',
            'content-block-settings',
            array($this, 'render_settings_page')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'post.php' || $hook === 'post-new.php') {
            global $post_type;
            if ($post_type === 'content_blocks') {
                wp_enqueue_style(
                    'wpcs-admin-style',
                    WPCS_CPT_PLUGIN_URL . 'assets/css/admin-style.css',
                    array(),
                    '1.0.0'
                );
                
                wp_enqueue_script(
                    'wpcs-admin-script',
                    WPCS_CPT_PLUGIN_URL . 'assets/js/admin-script.js',
                    array('jquery'),
                    '1.0.0',
                    true
                );
            }
        }
    }
    
    public function add_custom_columns($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ($key === 'title') {
                $new_columns['block_type'] = 'Block Type';
                $new_columns['shortcode'] = 'Shortcode';
            }
        }
        
        return $new_columns;
    }
    
    public function render_custom_columns($column, $post_id) {
        switch ($column) {
            case 'block_type':
                $block_type = get_post_meta($post_id, '_block_type', true);
                echo esc_html($block_type ? ucfirst($block_type) : 'Text');
                break;
                
            case 'shortcode':
                echo '<code>[content_block id="' . $post_id . '"]</code>';
                break;
        }
    }
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Handle form submission
        if (isset($_POST['submit'])) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'wpcs_settings_nonce')) {
                wp_die(__('Security check failed.'));
            }
            
            // Save settings
            update_option('wpcs_enable_css', isset($_POST['enable_css']) ? 1 : 0);
            update_option('wpcs_enable_js', isset($_POST['enable_js']) ? 1 : 0);
            
            echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
        }
        
        $enable_css = get_option('wpcs_enable_css', 1);
        $enable_js = get_option('wpcs_enable_js', 1);
        
        ?>
        <div class="wrap">
            <h1>Content Block Settings</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('wpcs_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable CSS</th>
                        <td>
                            <label for="enable_css">
                                <input type="checkbox" id="enable_css" name="enable_css" value="1" <?php checked($enable_css, 1); ?>>
                                Load default CSS styles
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Enable JavaScript</th>
                        <td>
                            <label for="enable_js">
                                <input type="checkbox" id="enable_js" name="enable_js" value="1" <?php checked($enable_js, 1); ?>>
                                Load default JavaScript
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <h2>Usage Instructions</h2>
            <p>1. Create a new Content Block from the admin menu</p>
            <p>2. Add your content and select the block type</p>
            <p>3. Copy the generated shortcode and paste it into any post or page</p>
            <p>4. The shortcode format is: <code>[content_block id="123"]</code></p>
        </div>
        <?php
    }
}