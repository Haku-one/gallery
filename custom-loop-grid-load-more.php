<?php
/**
 * Кастомная кнопка "Показать ещё" для всех Loop Grid
 * Показывает по 8 элементов за раз, скрывая остальные
 */

// Добавляем CSS стили для кастомной кнопки
function add_custom_load_more_styles() {
    ?>
    <style>
    /* Уникальные стили для кастомной кнопки показать ещё */
    .custom-loop-load-more-wrapper {
        display: flex;
        justify-content: end;
        margin-top: 20px;
        width: 100%;
    }
    
    .custom-loop-load-more-button {
        background-color: #02010100 !important;
        font-size: 16px !important;
        font-weight: 400 !important;
        fill: #989898 !important;
        color: #989898 !important;
        border-radius: 0px !important;
        padding: 0px !important;
        border: none !important;
        cursor: pointer !important;
        position: relative !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center !important;
    }
    
    .custom-loop-load-more-button:hover {
        color: #666666 !important;
        fill: #666666 !important;
    }
    
    .custom-load-more-content-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .custom-load-more-text {
        font-size: 16px;
        font-weight: 400;
    }
    
    .custom-load-more-spinner {
        display: none;
        width: 16px;
        height: 16px;
        animation: custom-spinner-rotate 1s linear infinite;
    }
    
    .custom-load-more-spinner.loading {
        display: inline-block;
    }
    
    .custom-load-more-spinner svg {
        width: 100%;
        height: 100%;
        fill: currentColor;
    }
    
    @keyframes custom-spinner-rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Скрываем элементы после 8-го */
    .custom-loop-grid-container .elementor-loop-container > *:nth-child(n+9) {
        display: none !important;
    }
    
    .custom-loop-grid-container.show-more-8 .elementor-loop-container > *:nth-child(n+17) {
        display: none !important;
    }
    
    .custom-loop-grid-container.show-more-16 .elementor-loop-container > *:nth-child(n+25) {
        display: none !important;
    }
    
    .custom-loop-grid-container.show-more-24 .elementor-loop-container > *:nth-child(n+33) {
        display: none !important;
    }
    
    /* Показываем элементы при раскрытии */
    .custom-loop-grid-container.show-more-8 .elementor-loop-container > *:nth-child(-n+16) {
        display: block !important;
    }
    
    .custom-loop-grid-container.show-more-16 .elementor-loop-container > *:nth-child(-n+24) {
        display: block !important;
    }
    
    .custom-loop-grid-container.show-more-24 .elementor-loop-container > *:nth-child(-n+32) {
        display: block !important;
    }
    
    /* Скрываем кнопку когда все элементы показаны */
    .custom-loop-load-more-wrapper.all-shown {
        display: none !important;
    }
    </style>
    <?php
}
add_action('wp_head', 'add_custom_load_more_styles');

// JavaScript для функционала кастомной кнопки
function add_custom_load_more_script() {
    ?>
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Инициализация кастомных loop grid
        function initCustomLoopGrids() {
            const loopGrids = document.querySelectorAll('[data-widget_type="loop-grid"], .elementor-widget-loop-grid');
            
            loopGrids.forEach(function(grid, index) {
                // Добавляем уникальный класс
                grid.classList.add('custom-loop-grid-container');
                grid.setAttribute('data-custom-grid-id', 'custom-grid-' + index);
                
                const loopContainer = grid.querySelector('.elementor-loop-container');
                if (!loopContainer) return;
                
                const allItems = loopContainer.children;
                const totalItems = allItems.length;
                
                // Если элементов больше 8, добавляем кнопку
                if (totalItems > 8) {
                    // Проверяем, что кнопка ещё не добавлена
                    if (!grid.querySelector('.custom-loop-load-more-wrapper')) {
                        const buttonWrapper = document.createElement('div');
                        buttonWrapper.className = 'custom-loop-load-more-wrapper';
                        buttonWrapper.innerHTML = `
                            <a href="#" class="custom-loop-load-more-button" role="button" data-grid-id="custom-grid-${index}">
                                <span class="custom-load-more-content-wrapper">
                                    <span class="custom-load-more-text">Показать ещё...</span>
                                    <span class="custom-load-more-spinner">
                                        <svg aria-hidden="true" class="custom-spinner-icon" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M304 48c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-48 368c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zm208-208c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zM96 256c0-26.51-21.49-48-48-48S0 229.49 0 256s21.49 48 48 48 48-21.49 48-48zm12.922 99.078c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zm294.156 0c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.49-48-48-48zM108.922 60.922c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.491-48-48-48z"></path>
                                        </svg>
                                    </span>
                                </span>
                            </a>
                        `;
                        
                        // Добавляем кнопку после loop container
                        grid.appendChild(buttonWrapper);
                        
                        // Добавляем обработчик клика
                        const button = buttonWrapper.querySelector('.custom-loop-load-more-button');
                        let currentlyShown = 8;
                        
                        button.addEventListener('click', function(e) {
                            e.preventDefault();
                            
                            const spinner = this.querySelector('.custom-load-more-spinner');
                            const textElement = this.querySelector('.custom-load-more-text');
                            
                            // Показываем спиннер
                            spinner.classList.add('loading');
                            textElement.textContent = 'Загрузка...';
                            
                            // Имитируем задержку загрузки
                            setTimeout(function() {
                                // Показываем следующие 8 элементов
                                currentlyShown += 8;
                                
                                // Обновляем классы для показа элементов
                                grid.className = grid.className.replace(/show-more-\d+/g, '');
                                
                                if (currentlyShown <= 16) {
                                    grid.classList.add('show-more-8');
                                } else if (currentlyShown <= 24) {
                                    grid.classList.add('show-more-16');
                                } else if (currentlyShown <= 32) {
                                    grid.classList.add('show-more-24');
                                }
                                
                                // Показываем элементы
                                for (let i = 0; i < Math.min(currentlyShown, totalItems); i++) {
                                    if (allItems[i]) {
                                        allItems[i].style.display = 'block';
                                    }
                                }
                                
                                // Скрываем спиннер
                                spinner.classList.remove('loading');
                                
                                // Проверяем, все ли элементы показаны
                                if (currentlyShown >= totalItems) {
                                    textElement.textContent = 'Все товары показаны';
                                    button.style.pointerEvents = 'none';
                                    button.style.opacity = '0.5';
                                    
                                    // Скрываем кнопку через 2 секунды
                                    setTimeout(function() {
                                        buttonWrapper.classList.add('all-shown');
                                    }, 2000);
                                } else {
                                    textElement.textContent = 'Показать ещё...';
                                }
                            }, 800); // Задержка для имитации загрузки
                        });
                    }
                }
            });
        }
        
        // Запускаем инициализацию
        initCustomLoopGrids();
        
        // Переинициализируем после AJAX запросов
        jQuery(document).ajaxComplete(function() {
            setTimeout(initCustomLoopGrids, 500);
        });
        
        // Наблюдаем за новыми элементами
        if (window.MutationObserver) {
            const observer = new MutationObserver(function(mutations) {
                let shouldReinit = false;
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1 && (
                                node.querySelector && node.querySelector('[data-widget_type="loop-grid"]') ||
                                node.classList && node.classList.contains('elementor-widget-loop-grid')
                            )) {
                                shouldReinit = true;
                            }
                        });
                    }
                });
                
                if (shouldReinit) {
                    setTimeout(initCustomLoopGrids, 100);
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
add_action('wp_footer', 'add_custom_load_more_script', 999);

// Скрываем оригинальные кнопки load-more
function hide_original_load_more_buttons() {
    ?>
    <style>
    /* Скрываем оригинальные кнопки load-more */
    .e-load-more-anchor,
    .elementor-button-wrapper .e-load-more-anchor,
    .e-loop__load-more {
        display: none !important;
    }
    </style>
    <?php
}
add_action('wp_head', 'hide_original_load_more_buttons', 999);

// Функция для автоматического ограничения количества элементов в loop grid
function limit_loop_grid_items($query_args, $widget_settings) {
    // Если в настройках loop grid указано 1000, ограничиваем до реального количества
    if (isset($query_args['posts_per_page']) && $query_args['posts_per_page'] == 1000) {
        // Оставляем 1000 для получения всех элементов, но скрываем через CSS/JS
        return $query_args;
    }
    
    return $query_args;
}
add_filter('elementor/query/query_args', 'limit_loop_grid_items', 10, 2);
?>