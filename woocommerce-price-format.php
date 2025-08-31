<?php
/**
 * Форматирование цен WooCommerce с пробелами
 * Преобразует любые числа от 1000: 4261 → 4 261, 1000 → 1 000, 461643 → 461 643
 * 
 * Добавьте этот код в functions.php вашей темы или подключите как отдельный файл
 */

// Функция для форматирования числа с пробелами (работает с любыми числами от 1000)
function format_price_with_spaces($price) {
    // Убираем все лишние символы, оставляем только цифры и точки/запятые
    $clean_price = preg_replace('/[^\d\.,]/', '', $price);
    $clean_price = str_replace(',', '.', $clean_price);
    
    // Проверяем, что это число
    if (!is_numeric($clean_price)) {
        return $price;
    }
    
    // Форматируем число с пробелами каждые 3 цифры (от 1000 и выше)
    return number_format((float)$clean_price, 0, ',', ' ');
}

// Основной фильтр для всех цен WooCommerce
function format_all_woocommerce_prices($price_html) {
    // Ищем все числа от 4 цифр и заменяем их отформатированными
    $formatted_html = preg_replace_callback('/\b(\d{4,})\b/', function($matches) {
        return format_price_with_spaces($matches[1]);
    }, $price_html);
    
    // Также обрабатываем числа от 1000 до 9999
    $formatted_html = preg_replace_callback('/\b(1\d{3}|[2-9]\d{3})\b/', function($matches) {
        return format_price_with_spaces($matches[1]);
    }, $formatted_html);
    
    return $formatted_html;
}

// Применяем ко ВСЕМ местам отображения цен в WooCommerce
add_filter('woocommerce_get_price_html', 'format_all_woocommerce_prices', 99);
add_filter('woocommerce_cart_item_price', 'format_all_woocommerce_prices', 99);
add_filter('woocommerce_cart_item_subtotal', 'format_all_woocommerce_prices', 99);
add_filter('woocommerce_cart_subtotal', 'format_all_woocommerce_prices', 99);
add_filter('woocommerce_cart_total', 'format_all_woocommerce_prices', 99);
add_filter('woocommerce_order_formatted_line_subtotal', 'format_all_woocommerce_prices', 99);
add_filter('woocommerce_get_formatted_order_total', 'format_all_woocommerce_prices', 99);
add_filter('woocommerce_widget_cart_item_price', 'format_all_woocommerce_prices', 99);

// Устанавливаем настройки WooCommerce для правильного форматирования
function set_woocommerce_price_format() {
    return ' '; // Пробел как разделитель тысяч
}
add_filter('woocommerce_price_thousand_sep', 'set_woocommerce_price_format');

// Убираем десятичные знаки
function remove_woocommerce_decimals($decimals) {
    return 0;
}
add_filter('woocommerce_price_num_decimals', 'remove_woocommerce_decimals');

// Дополнительная обработка для сложных случаев
function format_complex_prices($formatted_price, $price, $args) {
    // Обрабатываем любые числа в тексте
    return preg_replace_callback('/\b(\d{4,})\b/', function($matches) {
        return number_format((int)$matches[1], 0, '', ' ');
    }, $formatted_price);
}
add_filter('wc_price', 'format_complex_prices', 99, 3);
?>