<?php
if (!defined('ABSPATH')) {
    exit;
}

class WPCS_Post_Type {
    
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_box_data'));
    }
    
    public function register_post_type() {
        $args = array(
            'labels' => array(
                'name' => 'Content Blocks',
                'singular_name' => 'Content Block',
                'add_new' => 'Add New Block',
                'add_new_item' => 'Add New Content Block',
                'edit_item' => 'Edit Content Block',
                'new_item' => 'New Content Block',
                'view_item' => 'View Content Block',
                'search_items' => 'Search Content Blocks',
                'not_found' => 'No content blocks found',
                'not_found_in_trash' => 'No content blocks found in trash',
                'parent_item_colon' => 'Parent Content Block:',
                'menu_name' => 'Content Blocks',
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'content-blocks'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-layout',
            'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
            'show_in_rest' => true,
        );
        
        register_post_type('content_blocks', $args);
    }
    
    public function add_meta_boxes() {
        add_meta_box(
            'content_block_settings',
            'Block Settings',
            array($this, 'render_meta_box'),
            'content_blocks',
            'normal',
            'high'
        );
    }
    
    public function render_meta_box($post) {
        wp_nonce_field('save_content_block_meta', 'content_block_meta_nonce');
        
        $block_type = get_post_meta($post->ID, '_block_type', true);
        $block_shortcode = get_post_meta($post->ID, '_block_shortcode', true);
        
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="block_type">Block Type:</label></th>';
        echo '<td>';
        echo '<select name="block_type" id="block_type">';
        echo '<option value="text"' . selected($block_type, 'text', false) . '>Text Block</option>';
        echo '<option value="image"' . selected($block_type, 'image', false) . '>Image Block</option>';
        echo '<option value="video"' . selected($block_type, 'video', false) . '>Video Block</option>';
        echo '<option value="custom"' . selected($block_type, 'custom', false) . '>Custom Block</option>';
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="block_shortcode">Shortcode:</label></th>';
        echo '<td>';
        echo '<input type="text" name="block_shortcode" id="block_shortcode" value="' . esc_attr($block_shortcode) . '" class="regular-text" readonly>';
        echo '<p class="description">This shortcode is auto-generated. Use it to display this block.</p>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
    }
    
    public function save_meta_box_data($post_id) {
        if (!isset($_POST['content_block_meta_nonce']) || !wp_verify_nonce($_POST['content_block_meta_nonce'], 'save_content_block_meta')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (isset($_POST['block_type'])) {
            update_post_meta($post_id, '_block_type', sanitize_text_field($_POST['block_type']));
        }
        
        // Generate shortcode
        $shortcode = '[content_block id="' . $post_id . '"]';
        update_post_meta($post_id, '_block_shortcode', $shortcode);
    }
}