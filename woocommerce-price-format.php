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

// СПЕЦИАЛЬНЫЕ ФИЛЬТРЫ ДЛЯ БЛОКОВ WOOCOMMERCE (Gutenberg)
// Эти фильтры нужны для новых блоков корзины и оформления заказа

// Форматирование цен в блоках корзины
function format_woocommerce_block_prices($price_data) {
    if (is_array($price_data)) {
        // Если это массив данных о цене
        if (isset($price_data['price'])) {
            $price_data['price'] = format_price_with_spaces($price_data['price']);
        }
        if (isset($price_data['regular_price'])) {
            $price_data['regular_price'] = format_price_with_spaces($price_data['regular_price']);
        }
        if (isset($price_data['sale_price'])) {
            $price_data['sale_price'] = format_price_with_spaces($price_data['sale_price']);
        }
    } else {
        // Если это строка
        $price_data = preg_replace_callback('/\b(\d{4,})\b/', function($matches) {
            return format_price_with_spaces($matches[1]);
        }, $price_data);
    }
    
    return $price_data;
}

// Фильтры для блоков WooCommerce
add_filter('woocommerce_blocks_cart_item_price', 'format_woocommerce_block_prices', 99);
add_filter('woocommerce_blocks_cart_item_subtotal', 'format_woocommerce_block_prices', 99);
add_filter('woocommerce_blocks_cart_total', 'format_woocommerce_block_prices', 99);
add_filter('woocommerce_blocks_cart_subtotal', 'format_woocommerce_block_prices', 99);

// JavaScript для форматирования цен в блоках после загрузки
function add_block_price_formatting_script() {
    if (is_cart() || is_checkout()) {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Функция для форматирования чисел с пробелами
            function formatNumberWithSpaces(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
            }
            
            // Форматируем цены в блоках при загрузке и изменениях
            function formatBlockPrices() {
                $('.wc-block-formatted-money-amount, .wc-block-components-formatted-money-amount').each(function() {
                    var $this = $(this);
                    var text = $this.text();
                    
                    // Ищем числа от 1000 и заменяем их
                    var newText = text.replace(/\b(\d{4,})\b/g, function(match) {
                        return formatNumberWithSpaces(match);
                    });
                    
                    if (newText !== text) {
                        $this.text(newText);
                    }
                });
            }
            
            // Запускаем при загрузке
            formatBlockPrices();
            
            // Запускаем при изменениях в корзине
            $(document).on('updated_cart_totals updated_checkout', formatBlockPrices);
            
            // Наблюдаем за изменениями в DOM для блоков
            if (window.MutationObserver) {
                var observer = new MutationObserver(function(mutations) {
                    var shouldFormat = false;
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'childList') {
                            $(mutation.addedNodes).find('.wc-block-formatted-money-amount, .wc-block-components-formatted-money-amount').each(function() {
                                shouldFormat = true;
                            });
                        }
                    });
                    
                    if (shouldFormat) {
                        setTimeout(formatBlockPrices, 100);
                    }
                });
                
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }
        });
        </script>
        <?php
    }
}
add_action('wp_footer', 'add_block_price_formatting_script');
?>