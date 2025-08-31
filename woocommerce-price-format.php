<?php
/**
 * Форматирование цен WooCommerce с пробелами
 * Преобразует 462431 в 462 431
 * 
 * Добавьте этот код в functions.php вашей темы или подключите как отдельный файл
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

// Устанавливаем пробел как разделитель тысяч в WooCommerce
function modify_woocommerce_price_thousand_separator() {
    return ' '; // Пробел как разделитель тысяч
}
add_filter('woocommerce_price_thousand_sep', 'modify_woocommerce_price_thousand_separator');

// Убираем десятичные знаки для целых чисел
function modify_woocommerce_price_decimals($decimals) {
    return 0; // Без десятичных знаков
}
add_filter('woocommerce_price_num_decimals', 'modify_woocommerce_price_decimals');
?>