<?php
/**
 * Plugin Name:       WPCM News Layout Block
 * Plugin URI:        https://rd5.com/dev
 * Description:       Plugin otimizado para exibir postagens em layout de jornal com imagens laterais e slideshow.
 * Version:           3.6
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
    private $option_name = 'wpcm_news_settings';
    
    private $color_map = [
        'preto' => '#000000', 'branco' => '#ffffff', 'vermelho' => '#dc3545', 'verde' => '#28a745',
        'azul' => '#007bff', 'amarelo' => '#ffc107', 'cinza' => '#6c757d', 'cinzaclaro' => '#f8f9fa',
    ];

    public static function get_instance() {
        if (null === self::$instance) { self::$instance = new self(); }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register_block']);
        add_shortcode('wpcm_news_layout', [$this, 'render_shortcode']);
        add_action('add_meta_boxes', [$this, 'add_subtitle_meta_box']);
        add_action('save_post', [$this, 'save_subtitle_meta']);
        add_action('save_post_post', [$this, 'clear_all_plugin_transients']);
        add_action('wp_footer', [$this, 'enqueue_assets']);
        add_filter('the_content', [$this, 'strip_magic_tag_from_content'], 99);
        add_action('admin_menu', [$this, 'create_admin_menu']);
        add_action('admin_init', [$this, 'register_plugin_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('update_option_' . $this->option_name, [$this, 'clear_all_plugin_transients'], 10, 0);
    }

    // --- PAINEL DE CONFIGURAÇÕES ---

    public function create_admin_menu() {
        add_menu_page( 'Configurações do Layout de Notícia', 'Layout de Notícia', 'manage_options', 'wpcm_news_layout_settings', [$this, 'render_settings_page'], 'dashicons-text-page', 3 );
    }

    public function enqueue_admin_assets($hook) {
        if ($hook != 'toplevel_page_wpcm_news_layout_settings') { return; }
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        add_action('admin_footer', function() {
            echo '<script type="text/javascript">jQuery(document).ready(function($){$(".wp-color-picker-field").wpColorPicker();});</script>';
        });
    }

    public function register_plugin_settings() {
        register_setting($this->option_name, $this->option_name);
        add_settings_section('wpcm_content_section', 'Configurações de Conteúdo Padrão', null, $this->option_name);
        add_settings_field('default_category', 'Categoria Padrão (Slug)', [$this, 'render_field'], $this->option_name, 'wpcm_content_section', ['type' => 'text', 'id' => 'default_category', 'default' => 'manchete', 'placeholder' => 'manchete']);
        add_settings_field('excerpt_length', 'Tamanho do Resumo (caracteres)', [$this, 'render_field'], $this->option_name, 'wpcm_content_section', ['type' => 'number', 'id' => 'excerpt_length', 'default' => 400]);
        add_settings_section('wpcm_style_section', 'Estilos Padrão', null, $this->option_name);
        add_settings_field('title_font_size', 'Tamanho da Fonte do Título (px)', [$this, 'render_field'], $this->option_name, 'wpcm_style_section', ['type' => 'number', 'id' => 'title_font_size', 'default' => 62]);
        add_settings_field('title_font_family', 'Família da Fonte do Título', [$this, 'render_field'], $this->option_name, 'wpcm_style_section', ['type' => 'text', 'id' => 'title_font_family', 'default' => 'Merriweather, Georgia, serif', 'placeholder' => 'Merriweather, serif']);
        add_settings_field('bg_color', 'Cor de Fundo do Bloco', [$this, 'render_field'], $this->option_name, 'wpcm_style_section', ['type' => 'color', 'id' => 'bg_color', 'default' => '#ffffff']);
        add_settings_field('text_color', 'Cor do Texto', [$this, 'render_field'], $this->option_name, 'wpcm_style_section', ['type' => 'color', 'id' => 'text_color', 'default' => '#000000']);
        add_settings_field('btn_bg_color', 'Cor de Fundo do Botão', [$this, 'render_field'], $this->option_name, 'wpcm_style_section', ['type' => 'color', 'id' => 'btn_bg_color', 'default' => '#000000']);
        add_settings_field('btn_text_color', 'Cor do Texto do Botão', [$this, 'render_field'], $this->option_name, 'wpcm_style_section', ['type' => 'color', 'id' => 'btn_text_color', 'default' => '#ffffff']);
    }

    public function render_field($args) {
        $options = get_option($this->option_name, []);
        $value = isset($options[$args['id']]) ? esc_attr($options[$args['id']]) : $args['default'];
        $class = ($args['type'] === 'color') ? 'wp-color-picker-field' : '';
        $placeholder = isset($args['placeholder']) ? 'placeholder="' . $args['placeholder'] . '"' : '';
        echo "<input type='{$args['type']}' id='{$args['id']}' name='{$this->option_name}[{$args['id']}]' value='{$value}' class='{$class}' {$placeholder} />";
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Configurações - Layout de Notícia</h1>
            <p>Defina aqui os valores padrão para todos os blocos de notícias. Lembre-se que a "Magic Tag" em um post individual sempre terá prioridade sobre estas configurações.</p>
            <form action="options.php" method="post">
                <?php
                settings_fields($this->option_name);
                do_settings_sections($this->option_name);
                submit_button('Salvar Configurações');
                ?>
            </form>
        </div>
        <?php
    }

    // --- LÓGICA PRINCIPAL DO PLUGIN ---

    public function register_block() {
        if (function_exists('register_block_type')) {
            register_block_type(__DIR__ . '/build', ['render_callback' => [$this, 'render_block_callback']]);
        }
    }
    
    public function render_shortcode($atts) {
        $atts = shortcode_atts(['category' => '', 'min_height' => 400, 'interval' => 2000, 'show_border' => 'true'], $atts, 'wpcm_news_layout');
        return $this->render_block_callback($atts);
    }
    
    public function strip_magic_tag_from_content($content) {
        return preg_replace('/\[(resumo|fundo|texto|botaofundo|botaotexto|fonte|negrito)[^\]]*\]/i', '', $content);
    }
    
    private function get_attributes_from_magic_tag($content) {
        $attributes = [];
        if (preg_match('/\[((resumo|fundo|texto|botaofundo|botaotexto|fonte|negrito)[^\]]*)\]/i', $content, $matches)) {
            $attributes['full_tag'] = $matches[0];
            $parts = explode('-', $matches[1]);
            foreach ($parts as $part) {
                if (strpos($part, 'resumo') === 0) $attributes['resumo'] = (int) preg_replace('/\D/', '', $part);
                elseif (strpos($part, 'fundo') === 0) $attributes['fundo'] = $this->translate_color(str_replace('fundo', '', $part));
                elseif (strpos($part, 'texto') === 0) $attributes['texto'] = $this->translate_color(str_replace('texto', '', $part));
                elseif (strpos($part, 'botaofundo') === 0) $attributes['botaofundo'] = $this->translate_color(str_replace('botaofundo', '', $part));
                elseif (strpos($part, 'botaotexto') === 0) $attributes['botaotexto'] = $this->translate_color(str_replace('botaotexto', '', $part));
                elseif (strpos($part, 'fonte') === 0) $attributes['fonte'] = (int) preg_replace('/\D/', '', $part);
                elseif ($part === 'negrito') $attributes['negrito'] = true;
            }
        }
        return $attributes;
    }

    public function render_block_callback($attributes) {
        $global_options = get_option($this->option_name, []);
        $query_category = !empty($attributes['category']) ? sanitize_text_field($attributes['category']) : ($global_options['default_category'] ?? '');
        $show_border = isset($attributes['show_border']) ? filter_var($attributes['show_border'], FILTER_VALIDATE_BOOLEAN) : true;
        $this->slideshow_interval = isset($attributes['interval']) ? absint($attributes['interval']) : 2000;
        $min_height = isset($attributes['min_height']) ? absint($attributes['min_height']) : 400;

        $transient_key = 'wpcm_news_layout_v3.6_' . md5(serialize($attributes) . $query_category);
        $cached_html = get_transient($transient_key);
        if (false !== $cached_html) { $this->load_scripts = true; return $cached_html; }
        
        $args = ['post_type' => 'post', 'posts_per_page' => 1, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC'];
        if (!empty($query_category)) { $args['category_name'] = $query_category; }
        
        $query = new WP_Query($args);
        $output = '';
        if ($query->have_posts()) {
            $this->load_scripts = true;
            ob_start();
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $post_images = $this->get_post_images($post_id, $min_height);
                $tag_attributes = $this->get_attributes_from_magic_tag(get_post_field('post_content', $post_id));
                
                $final_title_size = !empty($tag_attributes['fonte']) ? $tag_attributes['fonte'] : ($global_options['title_font_size'] ?? 62);
                $final_title_font = $global_options['title_font_family'] ?? 'Merriweather, Georgia, serif';
                $is_bold = !empty($tag_attributes['negrito']);
                $final_bg = !empty($tag_attributes['fundo']) ? $tag_attributes['fundo'] : ($global_options['bg_color'] ?? '#ffffff');
                $final_text = !empty($tag_attributes['texto']) ? $tag_attributes['texto'] : ($global_options['text_color'] ?? '#000000');
                $final_btn_bg = !empty($tag_attributes['botaofundo']) ? $tag_attributes['botaofundo'] : ($global_options['btn_bg_color'] ?? '#000000');
                $final_btn_text = !empty($tag_attributes['botaotexto']) ? $tag_attributes['botaotexto'] : ($global_options['btn_text_color'] ?? '#ffffff');

                $unique_id = 'wpcm-block-' . uniqid();
                $title_style = "font-family: {$final_title_font}; font-size: {$final_title_size}px;";
                if ($is_bold) { $title_style .= " font-weight: bold !important;"; }
                
                $inline_styles = "#{$unique_id} .news-title a { {$title_style} }";
                if (!$show_border) $inline_styles .= "#{$unique_id} { border: none; padding: 30px; }";
                $inline_styles .= "#{$unique_id} { background-color: " . esc_attr($final_bg) . "; }";
                $inline_styles .= "#{$unique_id} .news-title, #{$unique_id} .news-subtitle, #{$unique_id} .news-excerpt { color: " . esc_attr($final_text) . "; }";
                $inline_styles .= "#{$unique_id} .news-read-more-btn { background-color: " . esc_attr($final_btn_bg) . "; color: " . esc_attr($final_btn_text) . "; border-color: " . esc_attr($final_btn_bg) . "; }";
                
                echo '<style>' . $inline_styles . '</style>';
                ?>
                <div id="<?php echo esc_attr($unique_id); ?>" class="wpcm-news-layout">
                    <h1 class="news-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                    
                    <div class="news-content">
                        <?php $subtitle = get_post_meta($post_id, 'subtitle', true); if (!empty($subtitle)) { echo '<h2 class="news-subtitle">' . esc_html($subtitle) . '</h2>'; } ?>
                        <div class="news-excerpt"><?php echo $this->get_custom_excerpt($post_id, $tag_attributes, $global_options); ?></div>
                        <div class="news-read-more"> <a href="<?php the_permalink(); ?>" class="news-read-more-btn">Continuar Lendo →</a> </div>
                    </div>

                    <?php if (!empty($post_images)) : ?>
                        <div class="news-images">
                            <?php if (count($post_images) > 1) : ?>
                                <div class="news-image-slideshow"> <?php foreach ($post_images as $index => $image) : ?><div class="news-image-slide<?php echo $index === 0 ? ' active' : ''; ?>"><img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" class="news-image" loading="lazy" /></div><?php endforeach; ?> </div>
                                <div class="slideshow-dots"> <?php for ($i = 0; $i < count($post_images); $i++) : ?><span class="dot<?php echo $i === 0 ? ' active' : ''; ?>" data-slide-to="<?php echo $i; ?>"></span><?php endfor; ?> </div>
                            <?php else : ?>
                                <div class="news-image-container"><img src="<?php echo esc_url($post_images[0]['url']); ?>" alt="<?php echo esc_attr($post_images[0]['alt']); ?>" class="news-image" loading="lazy" /></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
            }
            $output = ob_get_clean();
        } else { $output = '<!-- WPCM News: Nenhuma postagem encontrada para os critérios definidos. -->'; }
        
        wp_reset_postdata();
        set_transient($transient_key, $output, HOUR_IN_SECONDS);
        return $output;
    }
    
    // --- FUNÇÕES AUXILIARES COMPLETAS ---

    public function clear_all_plugin_transients() {
        global $wpdb;
        $prefix = '_transient_wpcm_news_layout_v3.6_';
        $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE %s", $wpdb->esc_like($prefix) . '%'));
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

    public function enqueue_assets() {
        if ($this->load_scripts) {
            wp_enqueue_style('wpcm-news-layout-style', plugin_dir_url(__FILE__) . 'assets/css/main.css', [], '3.6');
            wp_enqueue_script('wpcm-news-layout-script', plugin_dir_url(__FILE__) . 'assets/js/main.js', [], '3.6', true);
            wp_localize_script('wpcm-news-layout-script', 'wpcm_news_params', ['interval' => $this->slideshow_interval]);
        }
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
    
    private function get_custom_excerpt($post_id, $tag_attributes, $global_options) {
        $limit = !empty($tag_attributes['resumo']) ? $tag_attributes['resumo'] : ($global_options['excerpt_length'] ?? 400);
        $content = get_post_field('post_content', $post_id);
        $content_without_tag = $this->strip_magic_tag_from_content($content);
        $cleaned_content = wp_strip_all_tags(strip_shortcodes($content_without_tag));
        if (mb_strlen($cleaned_content) > $limit) {
            return mb_substr($cleaned_content, 0, $limit) . '...';
        }
        return $cleaned_content;
    }

    private function translate_color($color_key) {
        if (isset($this->color_map[$color_key])) {
            return $this->color_map[$color_key];
        }
        if (strpos($color_key, '#') === 0 && preg_match('/^#([a-f0-9]{6}|[a-f0-9]{3})$/i', $color_key)) {
            return sanitize_hex_color($color_key);
        }
        return null;
    }
}

WPCM_News_Layout_Plugin::get_instance();

assets/css/main.css
/* WPCM News Layout Block Styles v3.6 */

/* --- LAYOUT PARA DESKTOP (GRID) --- */
.wpcm-news-layout {
    display: grid;
    max-width: 1200px;
    margin: 20px auto;
    padding: 30px;
    background: #ffffff;
    border: 2px solid #000;
    border-radius: 8px;
    box-sizing: border-box;
    gap: 20px 30px; /* Espaçamento entre linhas e colunas */
    
    /* Define a grade: título na primeira linha, conteúdo e imagens na segunda */
    grid-template-areas:
        "title title"
        "content images";
    
    /* Define a largura das colunas: conteúdo flexível, imagens com 40% */
    grid-template-columns: 1fr 40%;
}

/* Associa cada elemento à sua área na grade */
.wpcm-news-layout .news-title { grid-area: title; }
.wpcm-news-layout .news-content { grid-area: content; min-width: 0; }
.wpcm-news-layout .news-images { grid-area: images; position: relative; }

/* Estilos gerais dos elementos */
.wpcm-news-layout .news-title {
    margin: 0;
    padding: 0;
}
.wpcm-news-layout .news-title a {
    color: inherit;
    text-decoration: none;
    font-weight: bold;
    line-height: 1.15;
}
.wpcm-news-layout .news-title a:hover { text-decoration: underline; }

.news-subtitle {
    font-size: 1.4rem;
    font-weight: normal;
    line-height: 1.3;
    margin-top: 0;
    margin-bottom: 20px;
    font-style: italic;
}

.news-excerpt {
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 20px;
    text-align: justify;
}

.news-read-more {
    overflow: hidden; /* Clearfix para o botão flutuante */
    margin-top: 15px;
}
.news-read-more-btn {
    display: inline-block;
    padding: 10px 20px;
    border: 1px solid transparent;
    border-radius: 4px;
    font-size: 0.95rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    text-decoration: none;
    transition: opacity 0.3s ease;
    cursor: pointer;
    float: right;
}
.news-read-more-btn:hover { opacity: 0.85; }

/* Slideshow de imagens */
.news-image-slideshow, .news-image-container {
    position: relative;
    width: 100%;
    height: 400px;
    overflow: hidden;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.news-image-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.7s ease-in-out;
    visibility: hidden;
}

.news-image-slide.active {
    opacity: 1;
    visibility: visible;
}

.news-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}

.slideshow-dots {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 15px;
}
.dot {
    width: 12px;
    height: 12px;
    background: #ccc;
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.3s ease;
}
.dot.active { background: #333; }


/* --- LAYOUT PARA CELULAR (FLEXBOX + ORDER) --- */
@media (max-width: 968px) {
    /* Muda o layout para flexível e em coluna */
    .wpcm-news-layout {
        display: flex;
        flex-direction: column;
        grid-template-areas: none; /* Desativa as áreas do grid */
        grid-template-columns: 100%; /* Uma única coluna */
    }

    /* Reordena os elementos para a hierarquia móvel */
    .wpcm-news-layout .news-title { order: 1; margin-bottom: 20px; }
    .wpcm-news-layout .news-images { order: 2; margin-bottom: 20px; }
    .wpcm-news-layout .news-content { order: 3; }

    /* Ajuste responsivo do tamanho da fonte do título */
    .wpcm-news-layout .news-title a { font-size: 42px !important; }
}

@media (max-width: 480px) {
    .wpcm-news-layout { padding: 20px; }
    .wpcm-news-layout .news-title a { font-size: 32px !important; }
}

assets/js/main.js

document.addEventListener('DOMContentLoaded', function () {
  // Encontra todos os slideshows na página
  const slideshows = document.querySelectorAll('.news-image-slideshow');

  // Pega o intervalo definido no PHP (com um padrão de 2 segundos)
  const intervalTime = window.wpcm_news_params?.interval || 2000;

  slideshows.forEach((slideshow) => {
    const slides = slideshow.querySelectorAll('.news-image-slide');
    const dotsContainer = slideshow.nextElementSibling;
    const dots = dotsContainer ? dotsContainer.querySelectorAll('.dot') : [];
    let currentSlide = 0;

    if (slides.length <= 1) {
      return; // Não faz nada se tiver 1 ou 0 imagens
    }

    function showSlide(index) {
      // Esconde o slide atual
      slides[currentSlide].classList.remove('active');
      if (dots[currentSlide]) {
        dots[currentSlide].classList.remove('active');
      }

      // Define o novo slide
      currentSlide = index;

      // Mostra o novo slide
      slides[currentSlide].classList.add('active');
      if (dots[currentSlide]) {
        dots[currentSlide].classList.add('active');
      }
    }

    // Inicia a rotação automática
    let slideInterval = setInterval(() => {
      const nextSlide = (currentSlide + 1) % slides.length;
      showSlide(nextSlide);
    }, intervalTime);

    // Adiciona funcionalidade de clique nos pontinhos
    dots.forEach((dot) => {
      dot.addEventListener('click', (e) => {
        const slideIndex = parseInt(e.target.dataset.slideTo, 10);
        showSlide(slideIndex);
        
        // Reinicia o intervalo para não trocar de slide imediatamente após o clique
        clearInterval(slideInterval);
        slideInterval = setInterval(() => {
          const nextSlide = (currentSlide + 1) % slides.length;
          showSlide(nextSlide);
        }, intervalTime);
      });
    });
  });
});
