<?php
/**
 * ПЛАВНЫЙ СКРОЛЛ С ПОДДЕРЖКОЙ МОДАЛЬНЫХ ОКОН И СКРЫТИЕМ ХЕДЕРА
 */

function add_scroll_with_normal_header() {
    if (is_admin() || isset($_GET['elementor-preview'])) {
        return;
    }
    ?>
    <style>
        /* Убираем sticky/fixed позиционирование header */
        header,
        .site-header,
        .header,
        .elementor-location-header,
        #masthead,
        .main-header,
        .navbar,
        .top-header,
        .site-branding {
            position: static !important;
            position: relative !important;
            top: auto !important;
            z-index: auto !important;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        /* Скрытие хедера при скролле (только для десктопа) */
        @media (min-width: 1025px) {
            .header-hidden {
                transform: translateY(-100%) !important;
                opacity: 0 !important;
            }
        }

        /* Стили для десктопа - плавный скролл */
        @media (min-width: 1025px) {
            body:not(.modal-open) {
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

        /* Модальные окна - отключаем скролл-эффекты */
        body.modal-open {
            overflow: hidden !important;
        }

        body.modal-open .elementor-section,
        body.modal-open .elementor-container,
        body.modal-open .elementor[data-elementor-type] {
            transition: none !important;
        }

        /* Навигационные индикаторы */
        .scroll-navigation {
            position: fixed;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1000;
            display: none;
            transition: opacity 0.3s ease;
        }

        body.modal-open .scroll-navigation {
            opacity: 0;
            pointer-events: none;
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

        /* Стили для модального окна видео */
        .rutube-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .rutube-modal.show {
            opacity: 1;
            visibility: visible;
        }

        .rutube-modal-content {
            position: relative;
            width: 90%;
            max-width: 1200px;
            height: 80%;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
        }

        .rutube-modal-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .rutube-modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            background: none;
            border: none;
            color: white;
            font-size: 30px;
            cursor: pointer;
            z-index: 10001;
            padding: 5px;
        }

        .rutube-modal-close:hover {
            opacity: 0.7;
        }
    </style>

    <div class="scroll-navigation" id="scrollNav"></div>

    <!-- Модальное окно для видео -->
    <div class="rutube-modal" id="rutube-modal">
        <div class="rutube-modal-content">
            <button class="rutube-modal-close" id="rutube-modal-close">&times;</button>
            <iframe class="rutube-modal-iframe" id="rutube-modal-iframe" src="" allowfullscreen></iframe>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isDesktop = window.innerWidth >= 1025;
            
            // Убираем sticky/fixed позиционирование header через JavaScript
            const headerSelectors = [
                'header',
                '.site-header',
                '.header', 
                '.elementor-location-header',
                '#masthead',
                '.main-header',
                '.navbar',
                '.top-header',
                '.site-branding'
            ];

            let headerElements = [];
            headerSelectors.forEach(selector => {
                const elements = document.querySelectorAll(selector);
                elements.forEach(el => {
                    el.style.position = 'relative';
                    el.style.top = 'auto';
                    el.style.zIndex = 'auto';
                    headerElements.push(el);
                });
            });
            
            // Инициализация модального окна
            initVideoModal();
            
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
            let isModalOpen = false;

            // Создаем навигацию
            createNavigation();

            // Добавляем обработчики событий
            addEventListeners();

            // Функция для управления хедером
            function toggleHeader(show) {
                headerElements.forEach(header => {
                    if (show) {
                        header.classList.remove('header-hidden');
                    } else {
                        header.classList.add('header-hidden');
                    }
                });
            }

            function initVideoModal() {
                const videoItems = document.querySelectorAll('.rutube-video-item');
                const modal = document.getElementById('rutube-modal');
                const modalIframe = document.getElementById('rutube-modal-iframe');
                const closeBtn = document.getElementById('rutube-modal-close');
                
                if (!modal || !modalIframe || !closeBtn) {
                    return; // Выходим если элементы модального окна не найдены
                }

                // Обработчик клика на видео
                videoItems.forEach(item => {
                    item.addEventListener('click', function() {
                        const autoplayUrl = this.getAttribute('data-autoplay-url');
                        if (autoplayUrl) {
                            openModal(autoplayUrl);
                        }
                    });
                });
                
                // Закрытие модального окна
                closeBtn.addEventListener('click', closeModal);
                
                // Закрытие по клику вне модального окна
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
                
                // Закрытие по ESC
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && modal.classList.contains('show')) {
                        closeModal();
                    }
                });

                function openModal(url) {
                    isModalOpen = true;
                    document.body.classList.add('modal-open');
                    modalIframe.src = url;
                    modal.classList.add('show');
                    
                    // Показываем хедер когда открывается модальное окно
                    if (isDesktop) {
                        toggleHeader(true);
                    }
                }

                function closeModal() {
                    isModalOpen = false;
                    document.body.classList.remove('modal-open');
                    modal.classList.remove('show');
                    modalIframe.src = '';
                    
                    // Скрываем хедер если мы не на первой секции
                    if (isDesktop && currentSection > 0) {
                        toggleHeader(false);
                    }
                }
            }

            function createNavigation() {
                const nav = document.getElementById('scrollNav');
                for (let i = 0; i < VIRTUAL_SECTIONS_COUNT; i++) {
                    const dot = document.createElement('div');
                    dot.className = 'scroll-nav-dot';
                    if (i === 0) dot.classList.add('active');
                    
                    dot.addEventListener('click', () => {
                        if (!isModalOpen) {
                            scrollToSection(i);
                        }
                    });
                    
                    nav.appendChild(dot);
                }
            }

            function scrollToSection(index) {
                if (isScrolling || index < 0 || index >= VIRTUAL_SECTIONS_COUNT || isModalOpen) return;
                
                isScrolling = true;
                currentSection = index;
                
                // Вычисляем позицию для скролла
                const scrollPosition = -index * 100; // В процентах от высоты экрана
                container.style.transform = `translateY(${scrollPosition}vh)`;
                
                // Управляем видимостью хедера
                if (index === 0) {
                    toggleHeader(true); // Показываем хедер на первой секции
                } else {
                    toggleHeader(false); // Скрываем хедер на остальных секциях
                }
                
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
                    if (isModalOpen) return; // Не обрабатываем скролл в модальном окне
                    
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
                    if (isScrolling || isModalOpen) return;
                    
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
                    if (isModalOpen) return;
                    touchStartY = e.touches[0].clientY;
                }, { passive: true });

                document.addEventListener('touchend', function(e) {
                    if (isScrolling || isModalOpen) return;
                    
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
add_action('wp_head', 'add_scroll_with_normal_header');
?>