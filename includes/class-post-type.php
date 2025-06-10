<?php
/**
 * Custom Post Type Handler
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CCB_PostType {
    
    public function __construct() {
        add_action('init', array($this, 'registerPostType'));
        add_action('add_meta_boxes', array($this, 'addMetaBoxes'));
        add_action('save_post', array($this, 'savePost'));
        add_filter('manage_content_blocks_posts_columns', array($this, 'addAdminColumns'));
        add_action('manage_content_blocks_posts_custom_column', array($this, 'populateAdminColumns'), 10, 2);
    }
    
    public function registerPostType() {
        $labels = array(
            'name'                  => __('Content Blocks', 'custom-content-blocks'),
            'singular_name'         => __('Content Block', 'custom-content-blocks'),
            'menu_name'             => __('Content Blocks', 'custom-content-blocks'),
            'name_admin_bar'        => __('Content Block', 'custom-content-blocks'),
            'add_new'               => __('Add New', 'custom-content-blocks'),
            'add_new_item'          => __('Add New Content Block', 'custom-content-blocks'),
            'new_item'              => __('New Content Block', 'custom-content-blocks'),
            'edit_item'             => __('Edit Content Block', 'custom-content-blocks'),
            'view_item'             => __('View Content Block', 'custom-content-blocks'),
            'all_items'             => __('All Content Blocks', 'custom-content-blocks'),
            'search_items'          => __('Search Content Blocks', 'custom-content-blocks'),
            'parent_item_colon'     => __('Parent Content Blocks:', 'custom-content-blocks'),
            'not_found'             => __('No content blocks found.', 'custom-content-blocks'),
            'not_found_in_trash'    => __('No content blocks found in Trash.', 'custom-content-blocks')
        );
        
        $args = array(
            'labels'             => $labels,
            'description'        => __('Custom content blocks for shortcode embedding', 'custom-content-blocks'),
            'public'             => true,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'content-blocks'),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-layout',
            'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
            'exclude_from_search' => true,
            'show_in_rest'       => true,
        );
        
        register_post_type('content_blocks', $args);
    }
    
    public function addMetaBoxes() {
        add_meta_box(
            'ccb_shortcode_display',
            __('Generated Shortcode', 'custom-content-blocks'),
            array($this, 'shortcodeMetaBoxCallback'),
            'content_blocks',
            'side',
            'high'
        );
    }
    
    public function shortcodeMetaBoxCallback($post) {
        $shortcode_id = '[content_block id="' . $post->ID . '"]';
        $shortcode_slug = '[content_block slug="' . $post->post_name . '"]';
        
        echo '<div class="ccb-shortcode-meta">';
        echo '<h4>' . __('By ID:', 'custom-content-blocks') . '</h4>';
        echo '<input type="text" readonly value="' . esc_attr($shortcode_id) . '" onclick="this.select();" style="width: 100%; margin-bottom: 10px;">';
        echo '<button type="button" class="button ccb-copy-shortcode" data-shortcode="' . esc_attr($shortcode_id) . '">' . __('Copy ID Shortcode', 'custom-content-blocks') . '</button>';
        
        if (!empty($post->post_name)) {
            echo '<h4 style="margin-top: 15px;">' . __('By Slug:', 'custom-content-blocks') . '</h4>';
            echo '<input type="text" readonly value="' . esc_attr($shortcode_slug) . '" onclick="this.select();" style="width: 100%; margin-bottom: 10px;">';
            echo '<button type="button" class="button ccb-copy-shortcode" data-shortcode="' . esc_attr($shortcode_slug) . '">' . __('Copy Slug Shortcode', 'custom-content-blocks') . '</button>';
        }
        
        echo '<p style="margin-top: 15px;"><em>' . __('Use these shortcodes anywhere on your site to display this content block.', 'custom-content-blocks') . '</em></p>';
        echo '</div>';
        
        // Add some inline CSS for better styling
        echo '<style>
            .ccb-shortcode-meta input[readonly] {
                background-color: #f9f9f9;
                font-family: monospace;
                font-size: 12px;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 3px;
            }
            .ccb-shortcode-meta .button {
                width: 100%;
                margin-bottom: 10px;
            }
            .ccb-shortcode-meta h4 {
                margin-bottom: 5px;
                color: #333;
            }
        </style>';
    }
    
    public function savePost($post_id) {
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Only process content_blocks post type
        if (get_post_type($post_id) !== 'content_blocks') {
            return;
        }
        
        // Generate and save shortcode information
        $this->generateShortcodeInfo($post_id);
    }
    
    private function generateShortcodeInfo($post_id) {
        $post = get_post($post_id);
        
        if (!$post) {
            return;
        }
        
        // Store shortcode info as post meta
        update_post_meta($post_id, '_ccb_shortcode_id', '[content_block id="' . $post_id . '"]');
        update_post_meta($post_id, '_ccb_shortcode_slug', '[content_block slug="' . $post->post_name . '"]');
        update_post_meta($post_id, '_ccb_created_date', current_time('mysql'));
    }
    
    public function addAdminColumns($columns) {
        $new_columns = array();
        
        // Add shortcode column after title
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['shortcode'] = __('Shortcode', 'custom-content-blocks');
            }
        }
        
        return $new_columns;
    }
    
    public function populateAdminColumns($column, $post_id) {
        if ($column === 'shortcode') {
            $shortcode_id = '[content_block id="' . $post_id . '"]';
            echo '<code style="font-size: 11px; background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">' . esc_html($shortcode_id) . '</code>';
            echo '<br><small><a href="#" class="ccb-copy-shortcode-link" data-shortcode="' . esc_attr($shortcode_id) . '">' . __('Copy', 'custom-content-blocks') . '</a></small>';
        }
    }
}