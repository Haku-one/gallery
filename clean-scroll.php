<?php
/**
 * ЧИСТЫЙ СКРОЛЛ БЕЗ ОТЛАДКИ - для финальной версии сайта
 */

function add_clean_scroll() {
    if (is_admin() || isset($_GET['elementor-preview'])) {
        return;
    }
    ?>
    <style>
        /* Стили для десктопа - плавный скролл по виртуальным секциям */
        @media (min-width: 1025px) {
            body {
                overflow: hidden;
                height: 100vh;
            }
            
            .elementor-section,
            .elementor-container,
            .elementor[data-elementor-type] {
                transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }
        }

        /* Стили для планшетов и мобильных - обычный скролл */
        @media (max-width: 1024px) {
            body {
                overflow-y: auto;
                overflow-x: hidden;
            }
        }

        /* Навигационные индикаторы */
        .scroll-navigation {
            position: fixed;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1000;
            display: none;
        }

        @media (min-width: 1025px) {
            .scroll-navigation {
                display: block;
            }
        }

        .scroll-nav-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .scroll-nav-dot.active {
            background: #00E5FF;
            transform: scale(1.2);
        }
    </style>

    <div class="scroll-navigation" id="scrollNav"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isDesktop = window.innerWidth >= 1025;
            
            if (!isDesktop) {
                return; // Выходим на мобильных
            }

            // Пробуем найти главный контейнер Elementor
            let container = null;
            const selectors = [
                '.elementor[data-elementor-type="wp-page"]',
                '.elementor-section',
                '.elementor-container',
                'main',
                'body'
            ];

            for (let selector of selectors) {
                const element = document.querySelector(selector);
                if (element) {
                    container = element;
                    break;
                }
            }

            if (!container) {
                return; // Выходим если контейнер не найден
            }

            // Параметры для виртуальных секций
            const VIRTUAL_SECTIONS_COUNT = 4; // Количество виртуальных секций
            let currentSection = 0;
            let isScrolling = false;

            // Создаем навигацию
            createNavigation();

            // Добавляем обработчики событий
            addEventListeners();

            function createNavigation() {
                const nav = document.getElementById('scrollNav');
                for (let i = 0; i < VIRTUAL_SECTIONS_COUNT; i++) {
                    const dot = document.createElement('div');
                    dot.className = 'scroll-nav-dot';
                    if (i === 0) dot.classList.add('active');
                    
                    dot.addEventListener('click', () => {
                        scrollToSection(i);
                    });
                    
                    nav.appendChild(dot);
                }
            }

            function scrollToSection(index) {
                if (isScrolling || index < 0 || index >= VIRTUAL_SECTIONS_COUNT) return;
                
                isScrolling = true;
                currentSection = index;
                
                // Вычисляем позицию для скролла
                const scrollPosition = -index * 100; // В процентах от высоты экрана
                container.style.transform = `translateY(${scrollPosition}vh)`;
                
                // Обновляем навигацию
                updateNavigation();
                
                // Разблокируем через время анимации
                setTimeout(() => {
                    isScrolling = false;
                }, 800);
            }

            function updateNavigation() {
                const dots = document.querySelectorAll('.scroll-nav-dot');
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === currentSection);
                });
            }

            function addEventListeners() {
                // Обработка колеса мыши
                let wheelTimeout;
                document.addEventListener('wheel', function(e) {
                    e.preventDefault();
                    
                    if (isScrolling) return;
                    
                    clearTimeout(wheelTimeout);
                    wheelTimeout = setTimeout(() => {
                        const delta = e.deltaY;
                        
                        if (delta > 0) {
                            // Скролл вниз
                            if (currentSection < VIRTUAL_SECTIONS_COUNT - 1) {
                                scrollToSection(currentSection + 1);
                            }
                        } else {
                            // Скролл вверх
                            if (currentSection > 0) {
                                scrollToSection(currentSection - 1);
                            }
                        }
                    }, 50);
                }, { passive: false });

                // Обработка клавиатуры
                document.addEventListener('keydown', function(e) {
                    if (isScrolling) return;
                    
                    switch(e.key) {
                        case 'ArrowDown':
                        case 'PageDown':
                            e.preventDefault();
                            if (currentSection < VIRTUAL_SECTIONS_COUNT - 1) {
                                scrollToSection(currentSection + 1);
                            }
                            break;
                        case 'ArrowUp':
                        case 'PageUp':
                            e.preventDefault();
                            if (currentSection > 0) {
                                scrollToSection(currentSection - 1);
                            }
                            break;
                        case 'Home':
                            e.preventDefault();
                            scrollToSection(0);
                            break;
                        case 'End':
                            e.preventDefault();
                            scrollToSection(VIRTUAL_SECTIONS_COUNT - 1);
                            break;
                    }
                });

                // Touch события
                let touchStartY = 0;
                document.addEventListener('touchstart', function(e) {
                    touchStartY = e.touches[0].clientY;
                }, { passive: true });

                document.addEventListener('touchend', function(e) {
                    if (isScrolling) return;
                    
                    const touchEndY = e.changedTouches[0].clientY;
                    const diff = touchStartY - touchEndY;
                    
                    if (Math.abs(diff) > 50) {
                        if (diff > 0) {
                            if (currentSection < VIRTUAL_SECTIONS_COUNT - 1) {
                                scrollToSection(currentSection + 1);
                            }
                        } else {
                            if (currentSection > 0) {
                                scrollToSection(currentSection - 1);
                            }
                        }
                    }
                }, { passive: true });
            }
        });
    </script>
    <?php
}
add_action('wp_head', 'add_clean_scroll');
?>