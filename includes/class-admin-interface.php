<?php
/**
 * Admin Interface Handler
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CCB_AdminInterface {
    
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdminAssets'));
        add_action('admin_notices', array($this, 'displayAdminNotices'));
        add_action('wp_ajax_ccb_copy_shortcode', array($this, 'ajaxCopyShortcode'));
        add_filter('post_row_actions', array($this, 'addRowActions'), 10, 2);
        add_action('admin_menu', array($this, 'addAdminMenu'));
    }
    
    public function enqueueAdminAssets($hook) {
        global $post_type;
        
        // Only load on content_blocks pages
        if ($post_type === 'content_blocks' || $hook === 'toplevel_page_content-blocks-help') {
            wp_enqueue_script(
                'ccb-admin-js',
                CCB_PLUGIN_URL . 'assets/js/admin-script.js',
                array('jquery'),
                CCB_VERSION,
                true
            );
            
            wp_localize_script('ccb-admin-js', 'ccb_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('ccb_nonce'),
                'messages' => array(
                    'copied'      => __('Shortcode copied to clipboard!', 'custom-content-blocks'),
                    'copy_failed' => __('Failed to copy. Please select and copy manually.', 'custom-content-blocks')
                )
            ));
            
            wp_enqueue_style(
                'ccb-admin-css',
                CCB_PLUGIN_URL . 'assets/css/admin-style.css',
                array(),
                CCB_VERSION
            );
        }
    }
    
    public function displayAdminNotices() {
        global $post_type, $pagenow;
        
        if ($post_type === 'content_blocks' && $pagenow === 'post-new.php') {
            echo '<div class="notice notice-info"><p>';
            echo __('After publishing this content block, you\'ll get a shortcode to use anywhere on your site.', 'custom-content-blocks');
            echo '</p></div>';
        }
    }
    
    public function ajaxCopyShortcode() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ccb_nonce')) {
            wp_die('Security check failed');
        }
        
        $shortcode = sanitize_text_field($_POST['shortcode']);
        
        // Return success (actual copying happens on frontend)
        wp_send_json_success(array(
            'shortcode' => $shortcode,
            'message'   => __('Shortcode ready to copy', 'custom-content-blocks')
        ));
    }
    
    public function addRowActions($actions, $post) {
        if ($post->post_type === 'content_blocks') {
            $shortcode_id = '[content_block id="' . $post->ID . '"]';
            
            $actions['copy_shortcode'] = sprintf(
                '<a href="#" class="ccb-copy-shortcode-link" data-shortcode="%s">%s</a>',
                esc_attr($shortcode_id),
                __('Copy Shortcode', 'custom-content-blocks')
            );
        }
        
        return $actions;
    }
    
    public function addAdminMenu() {
        add_submenu_page(
            'edit.php?post_type=content_blocks',
            __('Content Blocks Help', 'custom-content-blocks'),
            __('Help & Usage', 'custom-content-blocks'),
            'manage_options',
            'content-blocks-help',
            array($this, 'displayHelpPage')
        );
    }
    
    public function displayHelpPage() {
        ?>
        <div class="wrap">
            <h1><?php _e('Content Blocks Help & Usage', 'custom-content-blocks'); ?></h1>
            
            <div class="ccb-help-container">
                <div class="ccb-help-section">
                    <h2><?php _e('How to Use Content Blocks', 'custom-content-blocks'); ?></h2>
                    <ol>
                        <li><?php _e('Create a new Content Block from the left menu', 'custom-content-blocks'); ?></li>
                        <li><?php _e('Add your title and content (just like a regular post)', 'custom-content-blocks'); ?></li>
                        <li><?php _e('Publish the content block', 'custom-content-blocks'); ?></li>
                        <li><?php _e('Copy the generated shortcode from the sidebar', 'custom-content-blocks'); ?></li>
                        <li><?php _e('Paste the shortcode anywhere on your site!', 'custom-content-blocks'); ?></li>
                    </ol>
                </div>
                
                <div class="ccb-help-section">
                    <h2><?php _e('Shortcode Examples', 'custom-content-blocks'); ?></h2>
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th><?php _e('Shortcode', 'custom-content-blocks'); ?></th>
                                <th><?php _e('Description', 'custom-content-blocks'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>[content_block id="123"]</code></td>
                                <td><?php _e('Display content block by ID', 'custom-content-blocks'); ?></td>
                            </tr>
                            <tr>
                                <td><code>[content_block slug="my-block"]</code></td>
                                <td><?php _e('Display content block by slug', 'custom-content-blocks'); ?></td>
                            </tr>
                            <tr>
                                <td><code>[content_block id="123" class="custom-style"]</code></td>
                                <td><?php _e('Add custom CSS class', 'custom-content-blocks'); ?></td>
                            </tr>
                            <tr>
                                <td><code>[content_block id="123" wrapper="span"]</code></td>
                                <td><?php _e('Use different wrapper element', 'custom-content-blocks'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="ccb-help-section">
                    <h2><?php _e('Where Can I Use Shortcodes?', 'custom-content-blocks'); ?></h2>
                    <ul>
                        <li><?php _e('Posts and Pages content', 'custom-content-blocks'); ?></li>
                        <li><?php _e('Text widgets', 'custom-content-blocks'); ?></li>
                        <li><?php _e('Custom fields (if they support shortcodes)', 'custom-content-blocks'); ?></li>
                        <li><?php _e('Theme template files using do_shortcode()', 'custom-content-blocks'); ?></li>
                    </ul>
                </div>
                
                <div class="ccb-help-section">
                    <h2><?php _e('Tips & Best Practices', 'custom-content-blocks'); ?></h2>
                    <ul>
                        <li><?php _e('Use descriptive titles for your content blocks', 'custom-content-blocks'); ?></li>
                        <li><?php _e('Keep content blocks focused on a single purpose', 'custom-content-blocks'); ?></li>
                        <li><?php _e('Test your shortcodes before using them extensively', 'custom-content-blocks'); ?></li>
                        <li><?php _e('Use the slug-based shortcode if you might change the content later', 'custom-content-blocks'); ?></li>
                    </ul>
                </div>
                
                <?php if (current_user_can('manage_options')): ?>
                <div class="ccb-help-section">
                    <h2><?php _e('All Content Blocks', 'custom-content-blocks'); ?></h2>
                    <?php $this->displayAllContentBlocks(); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    private function displayAllContentBlocks() {
        $blocks = CCB_ShortcodeHandler::getAllContentBlocks();
        
        if (empty($blocks)) {
            echo '<p>' . __('No content blocks found. Create your first one!', 'custom-content-blocks') . '</p>';
            return;
        }
        
        echo '<div class="ccb-shortcode-list">';
        foreach ($blocks as $block) {
            $shortcode_id = '[content_block id="' . $block->ID . '"]';
            $shortcode_slug = '[content_block slug="' . $block->post_name . '"]';
            
            printf(
                '<strong>%s</strong><br>ID: <code>%s</code><br>Slug: <code>%s</code><br><br>',
                esc_html($block->post_title),
                esc_html($shortcode_id),
                esc_html($shortcode_slug)
            );
        }
        echo '</div>';
    }
}