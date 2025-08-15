<?php
/**
 * Плавный скролл по блокам для Elementor
 * Добавьте этот код в functions.php вашей темы
 */

// Подключение CSS для плавного скролла
function enqueue_elementor_smooth_scroll_styles() {
    // Проверяем, что мы не в админке
    if (!is_admin()) {
        wp_enqueue_style(
            'elementor-smooth-scroll-css', 
            get_template_directory_uri() . '/css/elementor-smooth-scroll.css',
            array(),
            '1.0.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_elementor_smooth_scroll_styles');

// Подключение JS для плавного скролла
function enqueue_elementor_smooth_scroll_scripts() {
    // Проверяем, что мы не в админке
    if (!is_admin()) {
        wp_enqueue_script(
            'elementor-smooth-scroll-js', 
            get_template_directory_uri() . '/js/elementor-smooth-scroll.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_elementor_smooth_scroll_scripts');

// Альтернативный способ - инлайн стили и скрипты
function add_elementor_smooth_scroll_inline() {
    // Проверяем, что мы не в админке и не в редакторе Elementor
    if (is_admin() || isset($_GET['elementor-preview'])) {
        return;
    }
    
    // Добавляем CSS в head
    ?>
    <style>
        /* Вставьте сюда CSS из файла elementor-smooth-scroll.css */
    </style>
    
    <script>
        /* Вставьте сюда JavaScript из файла elementor-smooth-scroll.js */
    </script>
    <?php
}
// Раскомментируйте следующую строку, если хотите использовать инлайн способ
// add_action('wp_head', 'add_elementor_smooth_scroll_inline');

// Добавление пользовательского CSS через админку WordPress
function add_smooth_scroll_customizer($wp_customize) {
    // Добавляем секцию
    $wp_customize->add_section('smooth_scroll_section', array(
        'title' => 'Плавный скролл',
        'priority' => 120,
    ));
    
    // Добавляем настройку для включения/выключения
    $wp_customize->add_setting('enable_smooth_scroll', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    
    // Добавляем контрол
    $wp_customize->add_control('enable_smooth_scroll', array(
        'label' => 'Включить плавный скролл',
        'section' => 'smooth_scroll_section',
        'type' => 'checkbox',
    ));
    
    // Добавляем настройку для скорости анимации
    $wp_customize->add_setting('scroll_animation_speed', array(
        'default' => '0.8',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('scroll_animation_speed', array(
        'label' => 'Скорость анимации (в секундах)',
        'section' => 'smooth_scroll_section',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 0.1,
            'max' => 3,
            'step' => 0.1,
        ),
    ));
}
add_action('customize_register', 'add_smooth_scroll_customizer');

// Функция для вывода настроек в CSS
function smooth_scroll_custom_css() {
    $enable_smooth_scroll = get_theme_mod('enable_smooth_scroll', true);
    $animation_speed = get_theme_mod('scroll_animation_speed', '0.8');
    
    if (!$enable_smooth_scroll) {
        return;
    }
    
    ?>
    <style type="text/css">
        @media (min-width: 1025px) {
            .full-height-section {
                transition: transform <?php echo esc_attr($animation_speed); ?>s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'smooth_scroll_custom_css');

// Отключение конфликтующих скриптов
function disable_conflicting_smooth_scroll() {
    // Список известных конфликтующих плагинов/скриптов
    $conflicting_handles = array(
        'smoothscroll',
        'smooth-scroll',
        'fullpage',
        'aos-script',
    );
    
    foreach ($conflicting_handles as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
}
add_action('wp_enqueue_scripts', 'disable_conflicting_smooth_scroll', 100);

// Добавление класса к body для определения типа устройства
function add_device_body_class($classes) {
    if (wp_is_mobile()) {
        $classes[] = 'is-mobile-device';
    } else {
        $classes[] = 'is-desktop-device';
    }
    
    return $classes;
}
add_filter('body_class', 'add_device_body_class');

// Хук для инициализации после загрузки Elementor
function elementor_smooth_scroll_init() {
    // Добавляем JavaScript для совместимости с Elementor
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ждем полной загрузки Elementor
            if (typeof elementorFrontend !== 'undefined') {
                elementorFrontend.hooks.addAction('frontend/element_ready/global', function() {
                    // Повторно инициализируем скролл если нужно
                    if (window.ElementorSmoothScroll && typeof window.ElementorSmoothScroll.reinit === 'function') {
                        window.ElementorSmoothScroll.reinit();
                    }
                });
            }
        });
    </script>
    <?php
}
add_action('wp_footer', 'elementor_smooth_scroll_init');

// Добавление мета-данных для отключения скролла на определенных страницах
function add_smooth_scroll_meta_box() {
    add_meta_box(
        'smooth_scroll_settings',
        'Настройки плавного скролла',
        'smooth_scroll_meta_box_callback',
        'page',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_smooth_scroll_meta_box');

function smooth_scroll_meta_box_callback($post) {
    wp_nonce_field('smooth_scroll_nonce', 'smooth_scroll_nonce');
    $disable_smooth_scroll = get_post_meta($post->ID, '_disable_smooth_scroll', true);
    ?>
    <label>
        <input type="checkbox" name="disable_smooth_scroll" value="1" <?php checked($disable_smooth_scroll, 1); ?>>
        Отключить плавный скролл на этой странице
    </label>
    <?php
}

function save_smooth_scroll_meta($post_id) {
    if (!isset($_POST['smooth_scroll_nonce']) || !wp_verify_nonce($_POST['smooth_scroll_nonce'], 'smooth_scroll_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['disable_smooth_scroll'])) {
        update_post_meta($post_id, '_disable_smooth_scroll', 1);
    } else {
        delete_post_meta($post_id, '_disable_smooth_scroll');
    }
}
add_action('save_post', 'save_smooth_scroll_meta');

// Условное подключение скриптов в зависимости от настроек страницы
function conditional_smooth_scroll_enqueue() {
    if (is_singular()) {
        $disable_smooth_scroll = get_post_meta(get_the_ID(), '_disable_smooth_scroll', true);
        if ($disable_smooth_scroll) {
            wp_dequeue_style('elementor-smooth-scroll-css');
            wp_dequeue_script('elementor-smooth-scroll-js');
        }
    }
}
add_action('wp_enqueue_scripts', 'conditional_smooth_scroll_enqueue', 20);
?>