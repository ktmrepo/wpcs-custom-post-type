<?php
/**
 * Shortcode Handler Class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CCB_ShortcodeHandler {
    
    public function __construct() {
        add_action('init', array($this, 'registerShortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueStyles'));
    }
    
    public function registerShortcode() {
        add_shortcode('content_block', array($this, 'handleShortcode'));
    }
    
    public function handleShortcode($atts, $content = null) {
        // Define default attributes
        $defaults = array(
            'id'      => '',
            'slug'    => '',
            'class'   => '',
            'wrapper' => 'div'
        );
        
        // Parse attributes
        $atts = shortcode_atts($defaults, $atts, 'content_block');
        
        // Sanitize attributes
        $id = intval($atts['id']);
        $slug = sanitize_text_field($atts['slug']);
        $class = sanitize_html_class($atts['class']);
        $wrapper = sanitize_html_class($atts['wrapper']);
        
        // Validate wrapper tag
        $allowed_wrappers = array('div', 'span', 'section', 'article', 'aside');
        if (!in_array($wrapper, $allowed_wrappers)) {
            $wrapper = 'div';
        }
        
        // Get the post
        $post = $this->getContentBlock($id, $slug);
        
        if (!$post) {
            return $this->getErrorMessage('Content block not found.');
        }
        
        // Check if post is published
        if ($post->post_status !== 'publish') {
            return $this->getErrorMessage('Content block is not published.');
        }
        
        // Get the content
        $content = $this->formatContent($post, $class, $wrapper);
        
        return $content;
    }
    
    private function getContentBlock($id, $slug) {
        $post = null;
        
        if (!empty($id)) {
            // Get by ID
            $post = get_post($id);
            
            // Verify it's the correct post type
            if ($post && $post->post_type !== 'content_blocks') {
                return null;
            }
            
        } elseif (!empty($slug)) {
            // Get by slug
            $posts = get_posts(array(
                'name'        => $slug,
                'post_type'   => 'content_blocks',
                'post_status' => 'publish',
                'numberposts' => 1
            ));
            
            if (!empty($posts)) {
                $post = $posts[0];
            }
        }
        
        return $post;
    }
    
    private function formatContent($post, $custom_class = '', $wrapper = 'div') {
        // Setup post data for proper content filtering
        setup_postdata($post);
        
        // Get the content and apply WordPress filters
        $content = apply_filters('the_content', $post->post_content);
        
        // Build CSS classes
        $classes = array(
            'content-block',
            'content-block-' . $post->ID
        );
        
        if (!empty($custom_class)) {
            $classes[] = $custom_class;
        }
        
        $class_string = implode(' ', array_map('sanitize_html_class', $classes));
        
        // Wrap the content
        $output = sprintf(
            '<%s class="%s" data-content-block-id="%d">%s</%s>',
            esc_html($wrapper),
            esc_attr($class_string),
            intval($post->ID),
            $content,
            esc_html($wrapper)
        );
        
        // Reset post data
        wp_reset_postdata();
        
        // Apply custom filter for developers
        return apply_filters('ccb_shortcode_output', $output, $post, $custom_class, $wrapper);
    }
    
    private function getErrorMessage($message) {
        // Only show error messages to logged-in users with appropriate capabilities
        if (!current_user_can('edit_posts')) {
            return '';
        }
        
        return sprintf(
            '<div class="content-block-error" style="background: #ffeaa7; border: 1px solid #fdcb6e; padding: 10px; border-radius: 3px; margin: 10px 0;"><strong>Content Block Error:</strong> %s</div>',
            esc_html($message)
        );
    }
    
    public function enqueueStyles() {
        // Only enqueue if shortcode is being used on the page
        global $post;
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'content_block')) {
            $this->addInlineStyles();
        }
    }
    
    private function addInlineStyles() {
        $css = '
        .content-block {
            margin: 1em 0;
            clear: both;
        }
        
        .content-block p:first-child {
            margin-top: 0;
        }
        
        .content-block p:last-child {
            margin-bottom: 0;
        }
        
        .content-block img {
            max-width: 100%;
            height: auto;
        }
        
        .content-block-error {
            background: #ffeaa7 !important;
            border: 1px solid #fdcb6e !important;
            padding: 10px !important;
            border-radius: 3px !important;
            margin: 10px 0 !important;
            color: #2d3436 !important;
        }
        ';
        
        wp_add_inline_style('wp-block-library', $css);
    }
    
    /**
     * Helper function to check if a shortcode exists in content
     */
    public static function hasContentBlockShortcode($content) {
        return has_shortcode($content, 'content_block');
    }
    
    /**
     * Get all content blocks for admin purposes
     */
    public static function getAllContentBlocks() {
        return get_posts(array(
            'post_type'   => 'content_blocks',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC'
        ));
    }
}