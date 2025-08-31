<?php
/**
 * Исправление проблем с Loop Grid и JavaScript ошибками
 * Решает проблемы с множественными loop grid на одной странице
 */

// Исправление ошибки lightbox-controls.js 404
function fix_missing_lightbox_controls() {
    // Проверяем, что файл не существует и убираем его из очереди
    wp_dequeue_script('lightbox-controls');
    wp_deregister_script('lightbox-controls');
}
add_action('wp_enqueue_scripts', 'fix_missing_lightbox_controls', 999);

// Исправление проблемы с множественными Loop Grid
function fix_multiple_loop_grids() {
    ?>
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Исправляем проблему с getAttribute для множественных loop grid
        
        // Находим все контейнеры с loop grid
        const loopGridContainers = document.querySelectorAll('[class*="loop"], [data-widget_type*="loop"]');
        
        loopGridContainers.forEach(function(container, index) {
            // Добавляем уникальные ID если их нет
            if (!container.id) {
                container.id = 'loop-grid-' + index;
            }
            
            // Исправляем load-more кнопки
            const loadMoreButtons = container.querySelectorAll('.load-more-button, [class*="load-more"]');
            loadMoreButtons.forEach(function(button, btnIndex) {
                // Добавляем уникальные атрибуты
                if (!button.getAttribute('data-container-id')) {
                    button.setAttribute('data-container-id', container.id);
                }
                
                if (!button.getAttribute('data-page')) {
                    button.setAttribute('data-page', '1');
                }
                
                if (!button.getAttribute('data-max-pages')) {
                    button.setAttribute('data-max-pages', '10');
                }
            });
        });
        
        // Переопределяем LoopLoadMore если он существует
        if (typeof window.LoopLoadMore !== 'undefined') {
            const originalHandleSuccessFetch = window.LoopLoadMore.prototype.handleSuccessFetch;
            
            window.LoopLoadMore.prototype.handleSuccessFetch = function(response, button) {
                try {
                    // Проверяем наличие необходимых элементов
                    if (!button || typeof button.getAttribute !== 'function') {
                        console.warn('LoopLoadMore: Invalid button element');
                        return;
                    }
                    
                    const containerId = button.getAttribute('data-container-id');
                    if (!containerId) {
                        console.warn('LoopLoadMore: Missing container ID');
                        return;
                    }
                    
                    const container = document.getElementById(containerId);
                    if (!container) {
                        console.warn('LoopLoadMore: Container not found');
                        return;
                    }
                    
                    // Вызываем оригинальную функцию если все проверки прошли
                    if (originalHandleSuccessFetch) {
                        return originalHandleSuccessFetch.call(this, response, button);
                    }
                } catch (error) {
                    console.error('LoopLoadMore error:', error);
                }
            };
        }
        
        // Дополнительная обработка для Elementor Loop Grid
        const elementorLoopGrids = document.querySelectorAll('[data-widget_type="loop-grid"]');
        elementorLoopGrids.forEach(function(grid, index) {
            const loadMoreBtn = grid.querySelector('.e-load-more-anchor');
            if (loadMoreBtn && !loadMoreBtn.getAttribute('data-page')) {
                loadMoreBtn.setAttribute('data-page', '1');
                loadMoreBtn.setAttribute('data-max-pages', '999');
                loadMoreBtn.setAttribute('data-grid-id', 'elementor-loop-' + index);
            }
        });
        
        // Обработка AJAX запросов для множественных grid
        jQuery(document).ajaxComplete(function(event, xhr, settings) {
            // После AJAX запросов переформатируем цены
            if (typeof formatBlockPrices === 'function') {
                setTimeout(formatBlockPrices, 500);
            }
            
            // Переинициализируем loop grid элементы
            setTimeout(function() {
                const newLoopElements = document.querySelectorAll('[class*="loop"]:not([data-initialized])');
                newLoopElements.forEach(function(element, index) {
                    element.setAttribute('data-initialized', 'true');
                    
                    const loadMoreBtn = element.querySelector('.load-more-button, [class*="load-more"]');
                    if (loadMoreBtn && !loadMoreBtn.getAttribute('data-container-id')) {
                        loadMoreBtn.setAttribute('data-container-id', element.id || 'loop-dynamic-' + index);
                    }
                });
            }, 100);
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'fix_multiple_loop_grids', 999);

// Дополнительная функция для исправления Elementor Loop Grid
function fix_elementor_loop_grid_scripts() {
    ?>
    <script type="text/javascript">
    // Исправление для Elementor Loop Grid
    document.addEventListener('DOMContentLoaded', function() {
        // Ждем загрузки Elementor
        function waitForElementor() {
            if (typeof elementorFrontend !== 'undefined') {
                // Переопределяем обработчик load-more для множественных grid
                elementorFrontend.hooks.addAction('frontend/element_ready/loop-grid.default', function($scope) {
                    const loadMoreButton = $scope.find('.e-load-more-anchor')[0];
                    if (loadMoreButton) {
                        // Добавляем уникальные атрибуты
                        const gridId = $scope.attr('data-id') || 'grid-' + Math.random().toString(36).substr(2, 9);
                        loadMoreButton.setAttribute('data-grid-id', gridId);
                        
                        // Исправляем getAttribute ошибку
                        if (!loadMoreButton.getAttribute('data-page')) {
                            loadMoreButton.setAttribute('data-page', '1');
                        }
                        if (!loadMoreButton.getAttribute('data-max-pages')) {
                            loadMoreButton.setAttribute('data-max-pages', '999');
                        }
                    }
                });
            } else {
                setTimeout(waitForElementor, 100);
            }
        }
        
        waitForElementor();
    });
    </script>
    <?php
}
add_action('wp_footer', 'fix_elementor_loop_grid_scripts', 998);

// Удаляем проблемные скрипты
function remove_problematic_scripts() {
    // Проверяем и удаляем несуществующие скрипты
    wp_dequeue_script('lightbox-controls');
    wp_deregister_script('lightbox-controls');
    
    // Проверяем load-more скрипт
    global $wp_scripts;
    if (isset($wp_scripts->registered['load-more'])) {
        $load_more_script = $wp_scripts->registered['load-more'];
        if ($load_more_script && !file_exists(str_replace(home_url(), ABSPATH, $load_more_script->src))) {
            wp_dequeue_script('load-more');
        }
    }
}
add_action('wp_enqueue_scripts', 'remove_problematic_scripts', 999);
?>