const fs = require('fs');
const path = require('path');

// Extract and create the plugin files from the provided ZIP content
const pluginFiles = {
    'wpcs-custom-post-type/includes/class-post-type.php': `<?php
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
}`,

    'wpcs-custom-post-type/includes/class-shortcode-handler.php': `<?php
if (!defined('ABSPATH')) {
    exit;
}

class WPCS_Shortcode_Handler {
    
    public function __construct() {
        add_shortcode('content_block', array($this, 'render_content_block'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function render_content_block($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'class' => '',
            'style' => ''
        ), $atts, 'content_block');
        
        if (empty($atts['id'])) {
            return '<p>Error: Content block ID is required.</p>';
        }
        
        $post = get_post($atts['id']);
        
        if (!$post || $post->post_type !== 'content_blocks') {
            return '<p>Error: Content block not found.</p>';
        }
        
        if ($post->post_status !== 'publish') {
            return '<p>Error: Content block is not published.</p>';
        }
        
        $block_type = get_post_meta($post->ID, '_block_type', true);
        $additional_class = !empty($atts['class']) ? ' ' . esc_attr($atts['class']) : '';
        $inline_style = !empty($atts['style']) ? ' style="' . esc_attr($atts['style']) . '"' : '';
        
        $output = '<div class="wpcs-content-block wpcs-block-' . esc_attr($block_type) . $additional_class . '"' . $inline_style . '>';
        
        // Add title if exists
        if (!empty($post->post_title)) {
            $output .= '<h3 class="wpcs-block-title">' . esc_html($post->post_title) . '</h3>';
        }
        
        // Add content
        $content = apply_filters('the_content', $post->post_content);
        $output .= '<div class="wpcs-block-content">' . $content . '</div>';
        
        // Add excerpt if exists
        if (!empty($post->post_excerpt)) {
            $output .= '<div class="wpcs-block-excerpt">' . esc_html($post->post_excerpt) . '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style(
            'wpcs-content-blocks-style',
            WPCS_CPT_PLUGIN_URL . 'assets/css/frontend-style.css',
            array(),
            '1.0.0'
        );
    }
}`,

    'wpcs-custom-post-type/includes/class-admin-interface.php': `<?php
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
}`
};

// Create the plugin files
Object.entries(pluginFiles).forEach(([filePath, content]) => {
    const dir = path.dirname(filePath);
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
    }
    fs.writeFileSync(filePath, content);
});

console.log('Plugin files extracted and ready for debugging!');