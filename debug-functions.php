<?php
/**
 * ОТЛАДОЧНАЯ версия плавного скролла - с детальным логированием
 * Замените временно ваш код на этот для диагностики
 */

function add_elementor_smooth_scroll_debug() {
    if (is_admin() || isset($_GET['elementor-preview'])) {
        return;
    }
    ?>
    <style>
        /* Стили для блоков по 100vh */
.full-height-section {
    height: 100vh;
    position: relative;
    overflow: hidden;
}

/* Основной контейнер */
body {
    margin: 0;
    padding: 0;
}

/* Стили для десктопа - отключаем обычный скролл */
@media (min-width: 1025px) {
    body {
        overflow: hidden;
        height: 100vh;
    }
    
    .sections-container {
        height: 100vh;
        overflow: hidden;
        position: relative;
    }
    
    .full-height-section {
        height: 100vh;
        position: absolute;
        width: 100%;
        top: 0;
        left: 0;
        transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        z-index: 1;
    }
    
    .full-height-section.active {
        z-index: 2;
    }
    
    /* Скрываем скроллбар полностью на десктопе */
    html {
        overflow: hidden;
    }
}

/* Стили для планшетов и мобильных устройств - обычный скролл */
@media (max-width: 1024px) {
    body {
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    html {
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    .sections-container {
        height: auto;
        overflow: visible;
    }
    
    .full-height-section {
        height: 100vh;
        position: relative;
        transform: none !important;
        transition: none !important;
    }
}

/* Стили для индикатора навигации */
.scroll-indicator {
    position: fixed;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1000;
    display: none;
}

@media (min-width: 1025px) {
    .scroll-indicator {
        display: block;
    }
}

.scroll-indicator ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.scroll-indicator li {
    margin: 10px 0;
}

.scroll-indicator a {
    display: block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.7);
    transition: all 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.scroll-indicator a.active,
.scroll-indicator a:hover {
    background: #fff;
    border-color: #fff;
    transform: scale(1.2);
}

/* Стили для кнопок навигации */
.scroll-arrows {
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 1000;
    display: none;
}

@media (min-width: 1025px) {
    .scroll-arrows {
        display: block;
    }
}

.scroll-arrow {
    display: block;
    width: 50px;
    height: 50px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    text-align: center;
    line-height: 50px;
    margin: 5px 0;
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 50%;
    text-decoration: none;
    font-size: 20px;
    font-weight: bold;
}

.scroll-arrow:hover {
    background: rgba(0, 0, 0, 1);
    transform: scale(1.1);
    color: white;
    text-decoration: none;
}

.scroll-arrow.disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

/* Отладочные стили */
.debug-info {
    position: fixed;
    top: 10px;
    left: 10px;
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 10px;
    font-size: 12px;
    z-index: 9999;
    border-radius: 5px;
    max-width: 300px;
}
    </style>
    
    <script>
console.log('🚀 Отладочный скрипт запущен');

document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM загружен');
    
    // Проверяем размер экрана
    const screenWidth = window.innerWidth;
    const isDesktop = screenWidth >= 1025;
    
    console.log(`📺 Размер экрана: ${screenWidth}px, Десктоп: ${isDesktop}`);
    
    if (!isDesktop) {
        console.log('📱 Мобильное устройство - скрипт остановлен');
        return;
    }
    
    let sections = [];
    let currentSectionIndex = 0;
    let isScrolling = false;
    let touchStartY = 0;
    let isInitialized = false;
    
    // Создаем отладочную панель
    function createDebugPanel() {
        const debugDiv = document.createElement('div');
        debugDiv.className = 'debug-info';
        debugDiv.id = 'debug-panel';
        document.body.appendChild(debugDiv);
        return debugDiv;
    }
    
    function updateDebugInfo(message) {
        const debugPanel = document.getElementById('debug-panel');
        if (debugPanel) {
            debugPanel.innerHTML = `
                <strong>Отладка скролла:</strong><br>
                Экран: ${window.innerWidth}px<br>
                Секций найдено: ${sections.length}<br>
                Текущая секция: ${currentSectionIndex + 1}<br>
                Скроллинг: ${isScrolling}<br>
                Инициализирован: ${isInitialized}<br>
                <em>${message}</em>
            `;
        }
    }
    
    // Функция поиска секций
    function findSections() {
        // Ищем разные типы секций
        const elementorSections = document.querySelectorAll('.elementor-section');
        const customSections = document.querySelectorAll('.full-height-section');
        const allDivs = document.querySelectorAll('div[class*="section"], section, .elementor-top-section');
        
        console.log(`🔍 Найдено .elementor-section: ${elementorSections.length}`);
        console.log(`🔍 Найдено .full-height-section: ${customSections.length}`);
        console.log(`🔍 Найдено div с section в классе: ${allDivs.length}`);
        
        // Объединяем все найденные секции
        const allSections = new Set();
        
        elementorSections.forEach(section => allSections.add(section));
        customSections.forEach(section => allSections.add(section));
        
        return Array.from(allSections);
    }
    
    // Функция ожидания секций
    function waitForSections(callback) {
        let attempts = 0;
        const maxAttempts = 100; // 10 секунд
        
        function checkSections() {
            attempts++;
            console.log(`🔄 Попытка ${attempts}: поиск секций...`);
            
            const foundSections = findSections();
            
            if (foundSections.length > 0) {
                console.log(`✅ Найдено ${foundSections.length} секций!`);
                callback(foundSections);
                return;
            }
            
            if (attempts < maxAttempts) {
                setTimeout(checkSections, 100);
            } else {
                console.error('❌ Секции не найдены за 10 секунд');
                updateDebugInfo('Секции не найдены!');
            }
        }
        
        checkSections();
    }
    
    // Инициализация
    function init(foundSections) {
        if (isInitialized) {
            console.log('⚠️ Уже инициализирован');
            return;
        }
        
        sections = foundSections;
        console.log(`🎯 Инициализация с ${sections.length} секциями`);
        
        // Создаем отладочную панель
        createDebugPanel();
        updateDebugInfo('Инициализация...');
        
        // Принудительно устанавливаем высоту
        sections.forEach((section, index) => {
            section.style.height = '100vh';
            section.style.minHeight = '100vh';
            section.classList.add('full-height-section');
            console.log(`📏 Секция ${index + 1}: установлена высота 100vh`);
        });
        
        // Создаем контейнер
        setupContainer();
        
        // Позиционируем секции
        positionSections();
        
        // Создаем навигацию
        createNavigation();
        
        // Добавляем обработчики
        addEventListeners();
        
        // Переходим к первой секции
        goToSection(0);
        
        isInitialized = true;
        updateDebugInfo('Готов к работе!');
        console.log('🎉 Инициализация завершена');
    }
    
    function setupContainer() {
        let container = document.querySelector('.sections-container');
        if (!container && sections.length > 0) {
            container = document.createElement('div');
            container.className = 'sections-container';
            
            const firstSection = sections[0];
            const parent = firstSection.parentNode;
            
            parent.insertBefore(container, firstSection);
            
            sections.forEach(section => {
                container.appendChild(section);
            });
            
            console.log('📦 Контейнер создан и секции перемещены');
        }
    }
    
    function positionSections() {
        sections.forEach((section, index) => {
            section.style.position = 'absolute';
            section.style.width = '100%';
            section.style.top = '0';
            section.style.left = '0';
            section.style.transform = `translateY(${index * 100}vh)`;
            section.style.zIndex = index === 0 ? '2' : '1';
            
            if (index === 0) {
                section.classList.add('active');
            }
            
            console.log(`📐 Секция ${index + 1}: positioned at ${index * 100}vh`);
        });
    }
    
    function createNavigation() {
        // Удаляем старую навигацию
        const oldIndicator = document.querySelector('.scroll-indicator');
        const oldArrows = document.querySelector('.scroll-arrows');
        if (oldIndicator) oldIndicator.remove();
        if (oldArrows) oldArrows.remove();
        
        // Индикаторы
        const indicator = document.createElement('div');
        indicator.className = 'scroll-indicator';
        
        const ul = document.createElement('ul');
        sections.forEach((section, index) => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = '#';
            a.dataset.section = index;
            a.title = `Секция ${index + 1}`;
            if (index === 0) a.classList.add('active');
            
            a.addEventListener('click', (e) => {
                e.preventDefault();
                console.log(`🖱️ Клик по индикатору секции ${index + 1}`);
                goToSection(index);
            });
            
            li.appendChild(a);
            ul.appendChild(li);
        });
        
        indicator.appendChild(ul);
        document.body.appendChild(indicator);
        
        // Стрелки
        const arrows = document.createElement('div');
        arrows.className = 'scroll-arrows';
        
        const upArrow = document.createElement('a');
        upArrow.className = 'scroll-arrow scroll-up';
        upArrow.href = '#';
        upArrow.innerHTML = '↑';
        upArrow.title = 'Предыдущая секция';
        upArrow.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('🖱️ Клик: вверх');
            scrollUp();
        });
        
        const downArrow = document.createElement('a');
        downArrow.className = 'scroll-arrow scroll-down';
        downArrow.href = '#';
        downArrow.innerHTML = '↓';
        downArrow.title = 'Следующая секция';
        downArrow.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('🖱️ Клик: вниз');
            scrollDown();
        });
        
        arrows.appendChild(upArrow);
        arrows.appendChild(downArrow);
        document.body.appendChild(arrows);
        
        console.log('🧭 Навигация создана');
    }
    
    function addEventListeners() {
        // Колесо мыши
        document.addEventListener('wheel', function(e) {
            e.preventDefault();
            
            if (isScrolling) {
                console.log('⏳ Скроллинг уже идет, игнорируем');
                return;
            }
            
            const delta = e.deltaY;
            console.log(`🖱️ Колесо мыши: delta=${delta}`);
            
            if (delta > 0) {
                console.log('⬇️ Скролл вниз');
                scrollDown();
            } else {
                console.log('⬆️ Скролл вверх');
                scrollUp();
            }
        }, { passive: false });
        
        // Клавиатура
        document.addEventListener('keydown', function(e) {
            if (isScrolling) return;
            
            console.log(`⌨️ Нажата клавиша: ${e.key}`);
            
            switch(e.key) {
                case 'ArrowDown':
                case 'PageDown':
                case ' ':
                    e.preventDefault();
                    scrollDown();
                    break;
                case 'ArrowUp':
                case 'PageUp':
                    e.preventDefault();
                    scrollUp();
                    break;
                case 'Home':
                    e.preventDefault();
                    goToSection(0);
                    break;
                case 'End':
                    e.preventDefault();
                    goToSection(sections.length - 1);
                    break;
            }
        });
        
        console.log('👂 Обработчики событий добавлены');
    }
    
    function scrollDown() {
        if (currentSectionIndex < sections.length - 1) {
            goToSection(currentSectionIndex + 1);
        } else {
            console.log('📍 Уже последняя секция');
            updateDebugInfo('Уже последняя секция');
        }
    }
    
    function scrollUp() {
        if (currentSectionIndex > 0) {
            goToSection(currentSectionIndex - 1);
        } else {
            console.log('📍 Уже первая секция');
            updateDebugInfo('Уже первая секция');
        }
    }
    
    function goToSection(index) {
        if (index < 0 || index >= sections.length || isScrolling) {
            console.log(`❌ Переход невозможен: index=${index}, isScrolling=${isScrolling}`);
            return;
        }
        
        isScrolling = true;
        const oldIndex = currentSectionIndex;
        currentSectionIndex = index;
        
        console.log(`🎯 Переход с секции ${oldIndex + 1} на секцию ${index + 1}`);
        
        // Обновляем позиции
        sections.forEach((section, i) => {
            section.classList.remove('active');
            const translateY = (i - currentSectionIndex) * 100;
            section.style.transform = `translateY(${translateY}vh)`;
            section.style.zIndex = i === currentSectionIndex ? '2' : '1';
            
            console.log(`📐 Секция ${i + 1}: translateY(${translateY}vh)`);
        });
        
        sections[currentSectionIndex].classList.add('active');
        
        updateNavigation();
        updateDebugInfo(`Переход на секцию ${index + 1}`);
        
        setTimeout(() => {
            isScrolling = false;
            console.log('✅ Переход завершен');
            updateDebugInfo(`Секция ${index + 1} активна`);
        }, 800);
    }
    
    function updateNavigation() {
        // Обновляем индикаторы
        const indicators = document.querySelectorAll('.scroll-indicator a');
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === currentSectionIndex);
        });
        
        // Обновляем стрелки
        const upArrow = document.querySelector('.scroll-up');
        const downArrow = document.querySelector('.scroll-down');
        
        if (upArrow) {
            upArrow.classList.toggle('disabled', currentSectionIndex === 0);
        }
        
        if (downArrow) {
            downArrow.classList.toggle('disabled', currentSectionIndex === sections.length - 1);
        }
    }
    
    // Запускаем поиск секций
    waitForSections(init);
});
    </script>
    <?php
}

add_action('wp_head', 'add_elementor_smooth_scroll_debug');
?>