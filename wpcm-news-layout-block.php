<?php
/**
 * Plugin Name:       WPCM News Layout Block
 * Plugin URI:        https://rd5.com/dev
 * Description:       Plugin otimizado para exibir postagens em layout de jornal com imagens laterais e slideshow.
 * Version:           2.2
 * Author:            Daniel Oliveira da Paixao (Refatorado por IA)
 * Author URI:        https://rd5.com/dev
 * Text Domain:       wpcm-news-layout-block
 * Domain Path:       /languages
 * Requires at least: 5.5
 * Tested up to:      6.5
 * Requires PHP:      7.4
 * Network:           false
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

final class WPCM_News_Layout_Plugin {

    private static $instance;
    private $load_scripts = false;
    private $slideshow_interval = 2000;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Ações e filtros principais
        add_action('init', [$this, 'register_block']);
        add_shortcode('wpcm_news_layout', [$this, 'render_shortcode']);
        add_action('add_meta_boxes', [$this, 'add_subtitle_meta_box']);
        add_action('save_post', [$this, 'save_subtitle_meta']);
        add_action('save_post_post', [$this, 'clear_transient_cache']);
        add_action('wp_footer', [$this, 'enqueue_assets']);

        // Filtro para remover a tag [resumoXXX] do conteúdo exibido publicamente
        add_filter('the_content', [$this, 'strip_resumo_tag_from_content']);
    }
    
    public function strip_resumo_tag_from_content($content) {
        return preg_replace('/\[resumo\d+\]/i', '', $content);
    }
    
    public function register_block() {
        register_block_type(__DIR__ . '/build', ['render_callback' => [$this, 'render_block_callback']]);
    }

    public function render_shortcode($atts) {
        $atts = shortcode_atts([
            'category'          => '',
            'min_height'        => 400,
            'interval'          => 2000,
            'background_color'  => '',
            'show_border'       => 'true',
        ], $atts, 'wpcm_news_layout');
        return $this->render_block_callback($atts);
    }
    
    private function get_post_images($post_id, $min_height = 400) {
        $images = [];
        $unique_ids = [];
        if (has_post_thumbnail($post_id)) {
            $thumb_id = get_post_thumbnail_id($post_id);
            $meta = wp_get_attachment_metadata($thumb_id);
            if ($meta && isset($meta['height']) && $meta['height'] >= $min_height) {
                $images[] = ['url' => get_the_post_thumbnail_url($post_id, 'full'), 'alt' => get_post_meta($thumb_id, '_wp_attachment_image_alt', true)];
                $unique_ids[$thumb_id] = true;
            }
        }
        $attachments = get_attached_media('image', $post_id);
        foreach ($attachments as $attachment) {
            if (isset($unique_ids[$attachment->ID])) continue;
            $meta = wp_get_attachment_metadata($attachment->ID);
            if ($meta && isset($meta['height']) && $meta['height'] >= $min_height) {
                $images[] = ['url' => wp_get_attachment_url($attachment->ID), 'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true)];
            }
        }
        return $images;
    }

    private function get_custom_excerpt($post_id) {
        $content = get_post_field('post_content', $post_id);
        preg_match('/\[resumo(\d+)\]/i', $content, $matches);
        if (isset($matches[1]) && is_numeric($matches[1])) {
            $limit = (int) $matches[1];
            $cleaned_content = wp_strip_all_tags(strip_shortcodes($content));
            if (mb_strlen($cleaned_content) > $limit) {
                return mb_substr($cleaned_content, 0, $limit) . '...';
            }
            return $cleaned_content;
        }
        return get_the_excerpt($post_id);
    }

    public function render_block_callback($attributes) {
        $category = isset($attributes['category']) ? sanitize_text_field($attributes['category']) : '';
        $min_height = isset($attributes['min_height']) ? absint($attributes['min_height']) : 400;
        $this->slideshow_interval = isset($attributes['interval']) ? absint($attributes['interval']) : 2000;
        $background_color = isset($attributes['background_color']) ? sanitize_hex_color($attributes['background_color']) : '';
        $show_border = isset($attributes['show_border']) ? filter_var($attributes['show_border'], FILTER_VALIDATE_BOOLEAN) : true;

        $transient_key = 'wpcm_news_layout_' . md5(serialize($attributes));
        $cached_html = get_transient($transient_key);
        if (false !== $cached_html) {
            $this->load_scripts = true;
            return $cached_html;
        }
        
        $args = ['post_type' => 'post', 'posts_per_page' => 1, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC'];
        if (!empty($category)) {
            $args['category_name'] = $category;
        }
        $query = new WP_Query($args);

        $output = '';
        if ($query->have_posts()) {
            $this->load_scripts = true;
            ob_start();

            $unique_id = 'wpcm-block-' . uniqid();
            $inline_styles = '';
            if (!empty($background_color)) {
                $inline_styles .= "#{$unique_id} { background-color: " . esc_attr($background_color) . "; }";
            }
            if (!$show_border) {
                $inline_styles .= "#{$unique_id} { border: none; padding: 22px; }";
            }
            if (!empty($inline_styles)) {
                echo '<style>' . $inline_styles . '</style>';
            }

            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $post_images = $this->get_post_images($post_id, $min_height);
                ?>
                <div id="<?php echo esc_attr($unique_id); ?>" class="wpcm-news-layout">
                    <article class="news-article">
                        <div class="news-container">
                            <div class="news-content">
                                <h1 class="news-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                                <?php
                                $subtitle = get_post_meta($post_id, 'subtitle', true);
                                if (!empty($subtitle)) {
                                    echo '<h2 class="news-subtitle">' . esc_html($subtitle) . '</h2>';
                                }
                                ?>
                                <div class="news-excerpt"><?php echo $this->get_custom_excerpt($post_id); ?></div>
                                <div class="news-read-more">
                                    <a href="<?php the_permalink(); ?>" class="news-read-more-btn">Continuar Lendo →</a>
                                </div>
                            </div>
                            <?php if (!empty($post_images)) : ?>
                                <div class="news-images">
                                    <?php if (count($post_images) > 1) : ?>
                                        <div class="news-image-slideshow">
                                            <?php foreach ($post_images as $index => $image) : ?>
                                                <div class="news-image-slide<?php echo $index === 0 ? ' active' : ''; ?>">
                                                    <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" class="news-image" loading="lazy" />
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="slideshow-dots">
                                            <?php for ($i = 0; $i < count($post_images); $i++) : ?>
                                                <span class="dot<?php echo $i === 0 ? ' active' : ''; ?>" data-slide-to="<?php echo $i; ?>"></span>
                                            <?php endfor; ?>
                                        </div>
                                    <?php else : ?>
                                        <div class="news-image-container">
                                            <img src="<?php echo esc_url($post_images[0]['url']); ?>" alt="<?php echo esc_attr($post_images[0]['alt']); ?>" class="news-image" loading="lazy" />
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>
                <?php
            }
            $output = ob_get_clean();
        } else {
            $output = '<div class="wpcm-news-layout no-posts-message"><p>' . __('Nenhuma postagem encontrada.', 'wpcm-news-layout-block') . '</p></div>';
        }

        wp_reset_postdata();
        set_transient($transient_key, $output, HOUR_IN_SECONDS);
        return $output;
    }
    
    public function enqueue_assets() {
        if ($this->load_scripts) {
            wp_enqueue_style('wpcm-news-layout-style', plugin_dir_url(__FILE__) . 'assets/css/main.css', [], '2.2');
            wp_enqueue_script('wpcm-news-layout-script', plugin_dir_url(__FILE__) . 'assets/js/main.js', [], '2.2', true);
            wp_localize_script('wpcm-news-layout-script', 'wpcm_news_params', ['interval' => $this->slideshow_interval]);
        }
    }

    public function clear_transient_cache($post_id) {
        global $wpdb;
        $prefix = '_transient_wpcm_news_layout_';
        $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE %s", $wpdb->esc_like($prefix) . '%'));
    }

    public function add_subtitle_meta_box() {
        add_meta_box('wpcm_news_subtitle', 'Subtítulo da Notícia', [$this, 'render_subtitle_meta_box'], 'post', 'normal', 'high');
    }

    public function render_subtitle_meta_box($post) {
        wp_nonce_field('wpcm_news_subtitle_nonce', 'wpcm_news_subtitle_nonce');
        $subtitle = get_post_meta($post->ID, 'subtitle', true);
        echo '<input type="text" name="wpcm_news_subtitle" value="' . esc_attr($subtitle) . '" style="width: 100%;" placeholder="Digite o subtítulo da notícia (opcional)" />';
    }

    public function save_subtitle_meta($post_id) {
        if (!isset($_POST['wpcm_news_subtitle_nonce']) || !wp_verify_nonce($_POST['wpcm_news_subtitle_nonce'], 'wpcm_news_subtitle_nonce')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if (isset($_POST['wpcm_news_subtitle'])) {
            update_post_meta($post_id, 'subtitle', sanitize_text_field($_POST['wpcm_news_subtitle']));
        }
    }
}

WPCM_News_Layout_Plugin::get_instance();
