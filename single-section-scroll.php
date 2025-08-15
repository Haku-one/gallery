<?php
/**
 * СКРОЛЛ ДЛЯ ОДНОЙ СЕКЦИИ - разделение на виртуальные блоки
 * Используйте этот код если у вас одна большая секция
 */

function add_single_section_scroll() {
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
            
            .scroll-sections-container {
                height: 100vh;
                overflow: hidden;
                scroll-behavior: smooth;
                transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }
            
            .virtual-section {
                height: 100vh;
                width: 100%;
                position: relative;
                overflow: hidden;
            }
        }

        /* Стили для планшетов и мобильных - обычный скролл */
        @media (max-width: 1024px) {
            body {
                overflow-y: auto;
                overflow-x: hidden;
            }
            
            .scroll-sections-container {
                height: auto;
                overflow: visible;
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

        /* Отладочная панель */
        .debug-panel {
            position: fixed;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            z-index: 10000;
            display: none;
        }

        @media (min-width: 1025px) {
            .debug-panel {
                display: block;
            }
        }
    </style>
    
    <div class="debug-panel">
        <div>Режим: <span id="debug-mode">Загрузка...</span></div>
        <div>Секция: <span id="debug-section">-/-</span></div>
        <div>Скролл: <span id="debug-scroll">Готов</span></div>
    </div>

    <div class="scroll-navigation" id="scrollNav"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Скрипт для одной секции запущен');
            
            const isDesktop = window.innerWidth >= 1025;
            document.getElementById('debug-mode').textContent = isDesktop ? 'Десктоп' : 'Мобильный';
            
            if (!isDesktop) {
                console.log('📱 Мобильное устройство - обычный скролл');
                document.querySelector('.debug-panel').style.display = 'none';
                return;
            }

            // Параметры для виртуальных секций
            const VIRTUAL_SECTIONS_COUNT = 4; // Количество виртуальных секций
            let currentSection = 0;
            let isScrolling = false;
            
            // Находим основной контейнер
            const container = document.querySelector('.scroll-sections-container');
            if (!container) {
                console.log('❌ Контейнер не найден');
                return;
            }

            console.log('✅ Контейнер найден, создаем виртуальные секции');

            // Создаем навигацию
            createNavigation();
            
            // Обновляем отладочную информацию
            updateDebugInfo();

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
                console.log('🧭 Навигация создана');
            }

            function scrollToSection(index) {
                if (isScrolling || index < 0 || index >= VIRTUAL_SECTIONS_COUNT) return;
                
                isScrolling = true;
                currentSection = index;
                
                console.log(`🎯 Переход на виртуальную секцию ${index + 1}`);
                
                // Вычисляем позицию для скролла
                const scrollPosition = -index * 100; // В процентах от высоты экрана
                container.style.transform = `translateY(${scrollPosition}vh)`;
                
                // Обновляем навигацию
                updateNavigation();
                updateDebugInfo();
                
                // Разблокируем через время анимации
                setTimeout(() => {
                    isScrolling = false;
                    console.log('✅ Переход завершен');
                }, 800);
            }

            function updateNavigation() {
                const dots = document.querySelectorAll('.scroll-nav-dot');
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === currentSection);
                });
            }

            function updateDebugInfo() {
                document.getElementById('debug-section').textContent = 
                    `${currentSection + 1}/${VIRTUAL_SECTIONS_COUNT}`;
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
                        console.log(`🖱️ Колесо мыши: delta=${delta}`);
                        
                        if (delta > 0) {
                            // Скролл вниз
                            if (currentSection < VIRTUAL_SECTIONS_COUNT - 1) {
                                scrollToSection(currentSection + 1);
                            } else {
                                console.log('📍 Уже последняя секция');
                            }
                        } else {
                            // Скролл вверх
                            if (currentSection > 0) {
                                scrollToSection(currentSection - 1);
                            } else {
                                console.log('📍 Уже первая секция');
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

                // Touch события для мобильных в десктопном режиме
                let touchStartY = 0;
                document.addEventListener('touchstart', function(e) {
                    touchStartY = e.touches[0].clientY;
                }, { passive: true });

                document.addEventListener('touchend', function(e) {
                    if (isScrolling) return;
                    
                    const touchEndY = e.changedTouches[0].clientY;
                    const diff = touchStartY - touchEndY;
                    
                    if (Math.abs(diff) > 50) { // Минимальное расстояние для свайпа
                        if (diff > 0) {
                            // Свайп вверх - следующая секция
                            if (currentSection < VIRTUAL_SECTIONS_COUNT - 1) {
                                scrollToSection(currentSection + 1);
                            }
                        } else {
                            // Свайп вниз - предыдущая секция
                            if (currentSection > 0) {
                                scrollToSection(currentSection - 1);
                            }
                        }
                    }
                }, { passive: true });

                console.log('👂 Обработчики событий добавлены');
            }

            console.log('🎉 Инициализация завершена');
            document.getElementById('debug-scroll').textContent = 'Активен';
        });
    </script>
    <?php
}
add_action('wp_head', 'add_single_section_scroll');
?>