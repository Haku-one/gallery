<?php
/**
 * Галерея WooCommerce в стиле Wildberries с поддержкой вариативных плиток
 * Version: 2.1 - Изображения меняются только при смене упаковки
 */

// Регистрируем скрипты и стили для кастомной галереи
function dt_woocommerce_gallery_scripts() {
    // Подключаем только на странице товара
    if (!is_product()) {
        return;
    }

    // Стили галереи
    wp_register_style('dt-variation-gallery', false);
    wp_enqueue_style('dt-variation-gallery');
    
    wp_add_inline_style('dt-variation-gallery', '
    /* Кастомная галерея WooCommerce в стиле Wildberries */
    .dt-product-gallery {
        display: flex;
        flex-direction: row;
        gap: 15px;
        margin-bottom: 30px;
    }

    /* Миниатюры СЛЕВА (как на Wildberries) */
    .dt-thumbnails-container {
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 100px;
         max-height: 595px;
        overflow-y: auto;
        padding-right: 5px;
        order: 1; /* Порядок слева */
		aspect-ratio: 1 / 1;
		
    }

    /* Главное изображение СПРАВА */
    .dt-main-image-container {
        flex: 1;
        position: relative;
        order: 2; /* Порядок справа */
    }

    .dt-main-image {
        width: 100%;
        height: auto;
        border-radius: 8px!important;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1)!important;
        cursor: zoom-in;
            max-width: 100%!important;
    aspect-ratio: 1 / 1.2!important;
    object-fit: cover !important;
    }

    .dt-thumbnail {
        width: 90px!important;
        height: 90px!important;
        object-fit: cover!important;
        border-radius: 5px !important;
        cursor: pointer!important;
        border: 2px solid #eee !important;
        transition: all 0.2s ease!important;
    }

    .dt-thumbnail:hover {
        border-color: #aaa;
        transform: translateY(-2px);
    }

    .dt-thumbnail.active {
        border-color: #01923F;
    }

    /* Модальное окно для зума */
    .dt-zoom-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        z-index: 999999;
        cursor: zoom-out;
    }

    .dt-zoom-modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dt-zoom-image {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
    }

    .dt-zoom-close {
        position: absolute;
        top: 20px;
        right: 30px;
        color: white;
        font-size: 40px;
        cursor: pointer;
        z-index: 1000000;
    }

    .dt-zoom-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        color: white;
        font-size: 40px;
        cursor: pointer;
        background: rgba(0,0,0,0.3);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        z-index: 1000000;
    }

    .dt-zoom-nav:hover {
        background: rgba(0,0,0,0.6);
    }

    .dt-zoom-prev {
        left: 30px;
    }

    .dt-zoom-next {
        right: 30px;
    }

    /* Индикатор загрузки */
    .dt-variation-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255,255,255,0.8);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .dt-variation-loading.active {
        display: flex;
    }

    .dt-loading-spinner {
        width: 30px;
        height: 30px;
        border: 3px solid rgba(1, 146, 63, 0.2);
        border-top: 3px solid #01923F;
        border-radius: 50%;
        animation: dt-spin 1s linear infinite;
    }

    @keyframes dt-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Мобильная адаптация */
    @media (max-width: 768px) {
        .dt-product-gallery {
            flex-direction: column;
        }
        
        .dt-thumbnails-container {
            flex-direction: row;
            width: 100%;
            max-height: none;
            overflow-x: auto;
            overflow-y: hidden;
            padding-right: 0;
            padding-bottom: 10px;
            order: 2; /* На мобильных миниатюры внизу */
			        height: 90px;
        }
        
        .dt-main-image-container {
            order: 1; /* На мобильных изображение вверху */
        }
        
        .dt-thumbnail {
            min-width: 70px;
            width: 70px;
            height: 70px;
        }
		
		dt-thumbnails-container::-webkit-scrollbar {
        display: none;
        width: 0;
        height: 0;
    }
    
    /* Для Firefox */
    .dt-thumbnails-container {
        scrollbar-width: none;
    }
    
    /* Для IE/Edge */
    .dt-thumbnails-container {
        -ms-overflow-style: none;
    }
	
	.dt-product-gallery {
   
    margin-bottom: 0px;
}
		
		
		
		
    }

    /* Стиль для вариаций */
    .dt-variation-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #01923F;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        z-index: 2;
    }

    /* Стилизация скроллбара */
    .dt-thumbnails-container::-webkit-scrollbar {
        width: 6px;
    }

    .dt-thumbnails-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .dt-thumbnails-container::-webkit-scrollbar-thumb {
        background: #01923F;
        border-radius: 3px;
		
    }
    
    /* Стили для кастомной плитки вариаций */
    .variation-tiles {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 15px;
    }
    
    .variation-tile {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .variation-tile:hover {
        border-color: #01923F;
    }
    
    .variation-tile.selected {
        background: #01923F;
        color: white;
        border-color: #01923F;
    }
    ');

    // JavaScript для галереи
    wp_enqueue_script('jquery');
    wp_register_script('dt-variation-gallery', false, array('jquery'), '2.1', true);
    wp_enqueue_script('dt-variation-gallery');
    
    // Получаем все данные о вариациях продукта
    $product_id = get_the_ID();
    $product = wc_get_product($product_id);
    
    $gallery_data = array();
    $base_gallery = array();
    $variation_attributes = array();
    $variation_image_map = array();
    $upakovka_image_map = array(); // Специальная карта только для упаковки
    
    // Собираем базовую галерею, только если продукт существует
    if ($product && is_object($product)) {
        $gallery_ids = $product->get_gallery_image_ids();
        $featured_image = $product->get_image_id();
        
        $base_images = array();
        if ($featured_image) {
            $base_images[] = $featured_image;
        }
        if (!empty($gallery_ids)) {
            $base_images = array_merge($base_images, $gallery_ids);
        }
        
        // Создаем массив с информацией об изображениях
        foreach ($base_images as $image_id) {
            if ($image_id) {
                $base_gallery[] = array(
                    'id' => $image_id,
                    'url' => wp_get_attachment_image_url($image_id, 'full'),
                    'thumb' => wp_get_attachment_image_url($image_id, 'thumbnail'),
                    'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: get_the_title($product_id)
                );
            }
        }
        
        // Если это вариативный товар, получаем все данные о вариациях
        if ($product->is_type('variable')) {
            $variations = $product->get_available_variations();
            
            foreach ($variations as $variation) {
                $variation_id = $variation['variation_id'];
                $variation_obj = wc_get_product($variation_id);
                
                if ($variation_obj) {
                    // Собираем атрибуты вариации для поиска соответствий
                    $attributes = array();
                    foreach ($variation['attributes'] as $attr_name => $attr_value) {
                        if ($attr_value) {
                            $attributes[$attr_name] = $attr_value;
                        }
                    }
                    
                    // Сохраняем соответствие атрибутов и ID вариации
                    $attribute_key = json_encode($attributes);
                    $variation_attributes[$attribute_key] = $variation_id;
                    
                    // Собираем изображения вариации
                    $variation_gallery = array();
                    
                    // Главное изображение вариации
                    $variation_image_id = $variation['image_id'];
                    if ($variation_image_id) {
                        $variation_gallery[] = array(
                            'id' => $variation_image_id,
                            'url' => wp_get_attachment_image_url($variation_image_id, 'full'),
                            'thumb' => wp_get_attachment_image_url($variation_image_id, 'thumbnail'),
                            'alt' => get_post_meta($variation_image_id, '_wp_attachment_image_alt', true) ?: $variation_obj->get_name()
                        );
                        
                        // Создаем карту соответствий ТОЛЬКО для атрибута упаковки
                        foreach ($attributes as $attr_name => $attr_value) {
                            // Проверяем, что это именно атрибут упаковки
                            if ($attr_name === 'attribute_pa_upakovka') {
                                if (!isset($upakovka_image_map[$attr_name])) {
                                    $upakovka_image_map[$attr_name] = array();
                                }
                                $upakovka_image_map[$attr_name][$attr_value] = $variation_image_id;
                            }
                        }
                    }
                    
                    // Дополнительные изображения из галереи вариации
                    $variation_gallery_ids = $variation_obj->get_gallery_image_ids();
                    foreach ($variation_gallery_ids as $image_id) {
                        if ($image_id) {
                            $variation_gallery[] = array(
                                'id' => $image_id,
                                'url' => wp_get_attachment_image_url($image_id, 'full'),
                                'thumb' => wp_get_attachment_image_url($image_id, 'thumbnail'),
                                'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $variation_obj->get_name()
                            );
                        }
                    }
                    
                    // Если у вариации нет своих изображений, используем базовые
                    if (empty($variation_gallery)) {
                        $variation_gallery = $base_gallery;
                    }
                    
                    // Сохраняем галерею для этой вариации
                    $gallery_data[$variation_id] = $variation_gallery;
                }
            }
        }
    }
    
    // Передаем данные в JavaScript
    wp_localize_script('dt-variation-gallery', 'dt_gallery_data', array(
        'variations' => $gallery_data,
        'base_gallery' => $base_gallery,
        'variation_attributes' => $variation_attributes,
        'upakovka_image_map' => $upakovka_image_map, // Карта только для упаковки
        'debug' => WP_DEBUG
    ));
    
    // Скрипт галереи
    wp_add_inline_script('dt-variation-gallery', '
    jQuery(document).ready(function($) {
        // Текущая галерея и индекс
        var currentGallery = dt_gallery_data.base_gallery;
        var currentIndex = 0;
        var selectedAttributes = {};
        
        function log(message, data) {
            if (dt_gallery_data.debug) {
                console.log("DT Gallery: " + message, data || "");
            }
        }
        
        // Инициализация галереи
        function initGallery() {
            log("Initializing gallery");
            
            if (currentGallery && currentGallery.length > 0) {
                updateMainImage(0);
                initThumbnailClicks();
                initZoomFeature();
                initCustomVariationTiles();
            } else {
                log("No images found in gallery");
            }
        }
        
        // Обновление главного изображения
        function updateMainImage(index) {
            if (!currentGallery || !currentGallery[index]) {
                log("Invalid gallery or index", { gallery: currentGallery, index: index });
                return;
            }
            
            log("Updating main image to index " + index);
            var mainImage = $(".dt-main-image");
            
            if (mainImage.length === 0) {
                log("Main image element not found");
                return;
            }
            
            // Показываем индикатор загрузки
            $(".dt-variation-loading").addClass("active");
            
            // Предварительно загружаем изображение
            var newImg = new Image();
            newImg.onload = function() {
                mainImage.attr("src", currentGallery[index].url);
                mainImage.attr("alt", currentGallery[index].alt || "");
                currentIndex = index;
                
                // Обновляем активный thumbnail
                $(".dt-thumbnail").removeClass("active");
                $(".dt-thumbnail[data-index=\'" + index + "\']").addClass("active");
                
                // Скрываем индикатор загрузки
                $(".dt-variation-loading").removeClass("active");
            };
            
            newImg.onerror = function() {
                log("Error loading image", currentGallery[index].url);
                $(".dt-variation-loading").removeClass("active");
            };
            
            newImg.src = currentGallery[index].url;
        }
        
        // Инициализация событий для миниатюр
        function initThumbnailClicks() {
            log("Setting up thumbnail click events");
            
            // Удаляем предыдущие обработчики
            $(document).off("click", ".dt-thumbnail");
            
            // Добавляем новые
            $(document).on("click", ".dt-thumbnail", function(e) {
                e.preventDefault();
                var index = $(this).data("index");
                log("Thumbnail clicked, index: " + index);
                updateMainImage(index);
                return false;
            });
        }
        
        // Инициализация функции увеличения
        function initZoomFeature() {
            log("Setting up zoom feature");
            
            // Клик по главному изображению - открываем зум
            $(document).off("click", ".dt-main-image");
            $(document).on("click", ".dt-main-image", function(e) {
                e.preventDefault();
                openZoom(currentIndex);
                return false;
            });
            
            // Закрытие модального окна
            $(document).off("click", ".dt-zoom-modal, .dt-zoom-close");
            $(document).on("click", ".dt-zoom-modal, .dt-zoom-close", function(e) {
                if (e.target === this || $(e.target).hasClass("dt-zoom-close")) {
                    closeZoom();
                    return false;
                }
            });
            
            // Навигация в модальном окне
            $(document).off("click", ".dt-zoom-prev");
            $(document).on("click", ".dt-zoom-prev", function(e) {
                e.stopPropagation();
                var prevIndex = currentIndex > 0 ? currentIndex - 1 : currentGallery.length - 1;
                openZoom(prevIndex);
                return false;
            });
            
            $(document).off("click", ".dt-zoom-next");
            $(document).on("click", ".dt-zoom-next", function(e) {
                e.stopPropagation();
                var nextIndex = currentIndex < currentGallery.length - 1 ? currentIndex + 1 : 0;
                openZoom(nextIndex);
                return false;
            });
            
            // Клавиатурная навигация
            $(document).off("keydown.gallery");
            $(document).on("keydown.gallery", function(e) {
                if ($(".dt-zoom-modal").hasClass("active")) {
                    if (e.key === "Escape") {
                        closeZoom();
                    } else if (e.key === "ArrowLeft") {
                        $(".dt-zoom-prev").click();
                    } else if (e.key === "ArrowRight") {
                        $(".dt-zoom-next").click();
                    }
                }
            });
        }
        
        // Открытие модального окна с увеличенным изображением
        function openZoom(index) {
            if (!currentGallery || !currentGallery[index]) return;
            
            log("Opening zoom, index: " + index);
            var modal = $(".dt-zoom-modal");
            var zoomImage = $(".dt-zoom-image");
            
            // Обновляем индекс и изображение
            currentIndex = index;
            zoomImage.attr("src", currentGallery[index].url);
            zoomImage.attr("alt", currentGallery[index].alt || "");
            
            // Показываем модальное окно
            modal.addClass("active");
            $("body").addClass("overflow-hidden");
            
            // Обновляем миниатюры
            updateMainImage(index);
        }
        
        // Закрытие модального окна
        function closeZoom() {
            log("Closing zoom");
            $(".dt-zoom-modal").removeClass("active");
            $("body").removeClass("overflow-hidden");
        }
        
        // Инициализация кастомных плиток вариаций
        function initCustomVariationTiles() {
            log("Setting up custom variation tiles");
            
            // Клик по кастомной плитке вариации
            $(document).on("click", ".variation-tile", function() {
                var $this = $(this);
                var attributeName = $this.data("attribute");
                var attributeValue = $this.data("value");
                
                log("Variation tile clicked", { attribute: attributeName, value: attributeValue });
                
                // Визуально выделяем выбранную плитку
                $this.siblings().removeClass("selected");
                $this.addClass("selected");
                
                // Обновляем скрытое поле
                var hiddenInput = $("input[name=\'" + attributeName + "\']");
                if (hiddenInput.length) {
                    hiddenInput.val(attributeValue).trigger("change");
                }
                
                // Сохраняем выбранный атрибут
                selectedAttributes[attributeName] = attributeValue;
                
                // ВАЖНО: Проверяем изображения ТОЛЬКО для атрибута упаковки
                if (attributeName === "attribute_pa_upakovka") {
                    log("Upakovka attribute changed, checking for image", { value: attributeValue });
                    checkForUpakovkaImage(attributeName, attributeValue);
                } else {
                    log("Non-upakovka attribute changed, ignoring image change", { attribute: attributeName });
                }
                
                // Обновляем бейдж вариации
                showVariationBadge();
            });
            
            // Восстанавливаем состояние вариаций при инициализации
            $(".variation-tile.selected").each(function() {
                var $this = $(this);
                var attributeName = $this.data("attribute");
                var attributeValue = $this.data("value");
                
                selectedAttributes[attributeName] = attributeValue;
            });
            
            // Обработка reset_data события
            $(".reset_variations").on("click", function() {
                selectedAttributes = {};
                currentGallery = dt_gallery_data.base_gallery;
                updateMainImage(0);
                updateThumbnails();
                $(".dt-variation-badge").hide();
                $(".variation-tile").removeClass("selected");
            });
        }
        
        // Проверка изображения только для атрибута упаковки
        function checkForUpakovkaImage(attributeName, attributeValue) {
            if (!dt_gallery_data.upakovka_image_map) {
                log("No upakovka image map found");
                return;
            }
            
            if (dt_gallery_data.upakovka_image_map[attributeName] && 
                dt_gallery_data.upakovka_image_map[attributeName][attributeValue]) {
                
                var imageId = dt_gallery_data.upakovka_image_map[attributeName][attributeValue];
                log("Found image for upakovka", { attribute: attributeName, value: attributeValue, imageId: imageId });
                
                // Находим этот image_id в текущей галерее
                for (var i = 0; i < currentGallery.length; i++) {
                    if (currentGallery[i].id == imageId) {
                        log("Switching to image index " + i + " for upakovka");
                        updateMainImage(i);
                        break;
                    }
                }
            } else {
                log("No specific image found for upakovka value", { attribute: attributeName, value: attributeValue });
            }
        }
        
        // Обновление миниатюр
        function updateThumbnails() {
            log("Updating thumbnails");
            
            var container = $(".dt-thumbnails-container");
            if (container.length === 0) {
                log("Thumbnails container not found");
                return;
            }
            
            // Очищаем контейнер
            container.empty();
            
            // Если в галерее только одно изображение
            if (currentGallery.length <= 1) {
                container.hide();
            } else {
                container.show();
                
                // Добавляем все миниатюры
                $.each(currentGallery, function(index, image) {
                    var activeClass = (index === currentIndex) ? "active" : "";
                    
                    var thumbnail = $("<img>", {
                        "class": "dt-thumbnail " + activeClass,
                        "src": image.thumb,
                        "alt": image.alt || "",
                        "data-index": index
                    });
                    
                    container.append(thumbnail);
                });
            }
        }
        
        // Показ бейджа вариации
        function showVariationBadge() {
            var badge = $(".dt-variation-badge");
            var container = $(".dt-main-image-container");
            
            // Создаем бейдж, если его нет
            if (badge.length === 0 && container.length > 0) {
                badge = $("<span>", {"class": "dt-variation-badge"});
                container.prepend(badge);
            }
            
            // Формируем текст только из упаковки (игнорируем вес)
            var badgeText = "";
            $.each(selectedAttributes, function(attrName, attrValue) {
                // Показываем в бейдже только упаковку
                if (attrName === "attribute_pa_upakovka" && attrValue && attrValue !== "") {
                    var attrLabel = attrValue.charAt(0).toUpperCase() + attrValue.slice(1);
                    badgeText = attrLabel;
                }
            });
            
            // Устанавливаем текст и показываем бейдж
            if (badgeText) {
                badge.text(badgeText);
                badge.show();
            } else {
                badge.hide();
            }
        }
        
        // Запускаем инициализацию галереи с небольшой задержкой
        setTimeout(function() {
            initGallery();
        }, 100);
        
        // Отлавливаем событие woocommerce_variation_has_changed
        $(document).on("woocommerce_variation_has_changed", function() {
            log("WooCommerce variation changed event triggered");
            
            // Собираем выбранные атрибуты из формы
            var form = $("form.variations_form");
            if (form.length) {
                var previousUpakovka = selectedAttributes["attribute_pa_upakovka"];
                selectedAttributes = {};
                
                form.find("select[name^=\'attribute_\']").each(function() {
                    var name = $(this).attr("name");
                    var value = $(this).val();
                    if (value) {
                        selectedAttributes[name] = value;
                    }
                });
                
                // Добавляем атрибуты из кастомных плиток
                $(".variation-row input[type=\'hidden\'][name^=\'attribute_\']").each(function() {
                    var name = $(this).attr("name");
                    var value = $(this).val();
                    if (value) {
                        selectedAttributes[name] = value;
                    }
                });
                
                log("Collected attributes from form", selectedAttributes);
                
                // Проверяем изображения ТОЛЬКО если изменилась упаковка
                var currentUpakovka = selectedAttributes["attribute_pa_upakovka"];
                if (currentUpakovka && currentUpakovka !== previousUpakovka) {
                    log("Upakovka changed from " + previousUpakovka + " to " + currentUpakovka);
                    checkForUpakovkaImage("attribute_pa_upakovka", currentUpakovka);
                } else {
                    log("Upakovka not changed, keeping current image");
                }
                
                // Обновляем бейдж
                showVariationBadge();
            }
        });
    });
    ');
}
add_action('wp_enqueue_scripts', 'dt_woocommerce_gallery_scripts');

// Функция рендеринга галереи
function dt_render_product_gallery() {
    global $product;
    
    // Проверяем, есть ли объект товара
    if (!$product) {
        $product_id = get_the_ID();
        $product = wc_get_product($product_id);
    }
    
    if (!$product) {
        return '<div class="dt-gallery-error">Товар не найден</div>';
    }
    
    // Получаем изображения товара
    $gallery_ids = $product->get_gallery_image_ids();
    $featured_image = $product->get_image_id();
    
    // Объединяем все изображения
    $images = array();
    if ($featured_image) {
        $images[] = $featured_image;
    }
    if (!empty($gallery_ids)) {
        $images = array_merge($images, $gallery_ids);
    }
    
    // Если нет изображений, выводим сообщение
    if (empty($images)) {
        return '<div class="dt-gallery-error">У товара нет изображений</div>';
    }
    
    // Формируем HTML галереи
    ob_start();
    ?>
    <div class="dt-product-gallery">
        <!-- Миниатюры (слева) -->
        <div class="dt-thumbnails-container">
            <?php foreach ($images as $index => $image_id): ?>
                <img class="dt-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                     src="<?php echo esc_url(wp_get_attachment_image_url($image_id, 'thumbnail')); ?>" 
                     alt="<?php echo esc_attr(get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: get_the_title()); ?>"
                     data-index="<?php echo esc_attr($index); ?>"
                     loading="eager">
            <?php endforeach; ?>
        </div>
        
        <!-- Основное изображение (справа) -->
        <div class="dt-main-image-container">
            <img class="dt-main-image" 
                 src="<?php echo esc_url(wp_get_attachment_image_url($images[0], 'large')); ?>" 
                 alt="<?php echo esc_attr(get_post_meta($images[0], '_wp_attachment_image_alt', true) ?: get_the_title()); ?>"
                 loading="eager">
            
            <!-- Индикатор загрузки -->
            <div class="dt-variation-loading">
                <div class="dt-loading-spinner"></div>
            </div>
        </div>
    </div>
    
    <!-- Модальное окно для увеличения -->
    <div class="dt-zoom-modal">
        <span class="dt-zoom-close">&times;</span>
        <?php if (count($images) > 1): ?>
            <span class="dt-zoom-nav dt-zoom-prev">&lsaquo;</span>
            <span class="dt-zoom-nav dt-zoom-next">&rsaquo;</span>
        <?php endif; ?>
        <img class="dt-zoom-image" src="" alt="">
    </div>
    <?php
    
    return ob_get_clean();
}

// Регистрация шорткода для галереи
function dt_product_gallery_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0
    ), $atts);
    
    // Если задан ID продукта, используем его
    if (!empty($atts['id'])) {
        global $product;
        $saved_product = $product;
        $product = wc_get_product($atts['id']);
        
        $output = dt_render_product_gallery();
        
        // Восстанавливаем глобальный объект продукта
        $product = $saved_product;
        
        return $output;
    }
    
    return dt_render_product_gallery();
}
add_shortcode('dt_product_gallery', 'dt_product_gallery_shortcode');

// Добавляем поддержку для кастомных плиток вариаций
function dt_tile_variations_support() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Делаем кастомные плитки интерактивными
        $(document).on('click', '.variation-tile', function() {
            var $tile = $(this);
            var attribute = $tile.data('attribute');
            var value = $tile.data('value');
            
            // Выделяем выбранную плитку
            $tile.siblings('.variation-tile').removeClass('selected');
            $tile.addClass('selected');
            
            // Обновляем скрытое поле
            $('input[name="' + attribute + '"]').val(value).trigger('change');
            
            // Запускаем обновление формы вариаций только если это упаковка
            if (attribute === 'attribute_pa_upakovka') {
                console.log('Upakovka changed, triggering variation update');
                $('form.variations_form').trigger('woocommerce_variation_has_changed');
            } else {
                console.log('Weight changed, not triggering image update');
            }
        });
        
        // Сброс вариаций
        $(document).on('reset_data', '.variations_form', function() {
            $('.variation-tile').removeClass('selected');
            $('.variation-row input[type="hidden"]').val('');
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'dt_tile_variations_support');
?>