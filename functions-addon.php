<?php
/**
 * Плавный скролл по блокам для Elementor
 * Добавьте этот код в functions.php вашей темы
 */

/**
 * Форматирование цен WooCommerce с пробелами
 * Преобразует 462431 в 462 431
 */

// Функция для форматирования числа с пробелами
function format_price_with_spaces($price) {
    // Убираем все пробелы и форматируем число
    $clean_price = preg_replace('/\s+/', '', $price);
    
    // Проверяем, что это число
    if (!is_numeric($clean_price)) {
        return $price;
    }
    
    // Форматируем число с пробелами каждые 3 цифры
    return number_format((float)$clean_price, 0, ',', ' ');
}

// Фильтр для форматирования цены в WooCommerce
function custom_woocommerce_price_format($price, $args) {
    // Получаем символ валюты
    $currency_symbol = get_woocommerce_currency_symbol($args['currency']);
    
    // Извлекаем числовое значение из цены
    $numeric_price = preg_replace('/[^\d\.,]/', '', $price);
    $numeric_price = str_replace(',', '.', $numeric_price);
    
    // Форматируем с пробелами
    $formatted_price = format_price_with_spaces($numeric_price);
    
    // Возвращаем отформатированную цену с символом валюты
    switch ($args['currency_pos']) {
        case 'left':
            return $currency_symbol . $formatted_price;
        case 'right':
            return $formatted_price . $currency_symbol;
        case 'left_space':
            return $currency_symbol . ' ' . $formatted_price;
        case 'right_space':
            return $formatted_price . ' ' . $currency_symbol;
        default:
            return $currency_symbol . $formatted_price;
    }
}

// Применяем фильтр к ценам WooCommerce
add_filter('wc_price', 'custom_woocommerce_price_format', 10, 2);

// Дополнительный фильтр для форматирования цен в разных местах
function format_woocommerce_price_display($price_html, $price, $args) {
    // Проверяем, что цена содержит числа
    if (preg_match('/\d/', $price_html)) {
        // Ищем числа в HTML и заменяем их отформатированными
        $price_html = preg_replace_callback('/(\d{4,})/', function($matches) {
            return format_price_with_spaces($matches[1]);
        }, $price_html);
    }
    
    return $price_html;
}

// Применяем к различным местам отображения цен
add_filter('woocommerce_format_price_range', 'format_woocommerce_price_display', 10, 3);
add_filter('woocommerce_get_price_html', 'format_woocommerce_price_display', 10, 3);

// Форматирование цен в корзине и оформлении заказа
function format_cart_item_price($price_html, $cart_item, $cart_item_key) {
    return preg_replace_callback('/(\d{4,})/', function($matches) {
        return format_price_with_spaces($matches[1]);
    }, $price_html);
}
add_filter('woocommerce_cart_item_price', 'format_cart_item_price', 10, 3);

// Форматирование итоговых сумм
function format_cart_totals($price_html) {
    return preg_replace_callback('/(\d{4,})/', function($matches) {
        return format_price_with_spaces($matches[1]);
    }, $price_html);
}
add_filter('woocommerce_cart_item_subtotal', 'format_cart_totals', 10, 1);
add_filter('woocommerce_cart_subtotal', 'format_cart_totals', 10, 1);
add_filter('woocommerce_cart_total', 'format_cart_totals', 10, 1);

// Дополнительные фильтры для полного покрытия всех мест отображения цен
add_filter('woocommerce_price_format', 'format_cart_totals', 10, 1);
add_filter('woocommerce_order_formatted_line_subtotal', 'format_cart_totals', 10, 1);
add_filter('woocommerce_get_formatted_order_total', 'format_cart_totals', 10, 1);

// Форматирование цен в виджетах и shortcodes
function format_widget_prices($price_html) {
    return preg_replace_callback('/(\d{4,})/', function($matches) {
        return format_price_with_spaces($matches[1]);
    }, $price_html);
}
add_filter('woocommerce_widget_cart_item_quantity', 'format_widget_prices', 10, 1);

// Форматирование цен в мини-корзине
function format_mini_cart_prices($price_html, $cart_item, $cart_item_key) {
    return preg_replace_callback('/(\d{4,})/', function($matches) {
        return format_price_with_spaces($matches[1]);
    }, $price_html);
}
add_filter('woocommerce_widget_cart_item_price', 'format_mini_cart_prices', 10, 3);

// Альтернативный метод через изменение настроек WooCommerce
function modify_woocommerce_price_thousand_separator() {
    return ' '; // Устанавливаем пробел как разделитель тысяч
}
add_filter('woocommerce_price_thousand_sep', 'modify_woocommerce_price_thousand_separator');

// Убираем десятичные знаки для целых чисел
function modify_woocommerce_price_decimals($decimals) {
    return 0; // Убираем десятичные знаки
}
add_filter('woocommerce_price_num_decimals', 'modify_woocommerce_price_decimals');

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