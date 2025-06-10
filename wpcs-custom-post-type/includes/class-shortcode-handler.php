<?php
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
}