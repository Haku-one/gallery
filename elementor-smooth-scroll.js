document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, что мы на десктопе
    const isDesktop = window.innerWidth >= 1025;
    
    if (!isDesktop) {
        return; // Выходим, если это не десктоп
    }
    
    let sections = [];
    let currentSectionIndex = 0;
    let isScrolling = false;
    let touchStartY = 0;
    
    // Инициализация
    function init() {
        // Находим все секции с классом full-height-section или Elementor секции
        sections = document.querySelectorAll('.full-height-section, .elementor-section');
        
        if (sections.length === 0) {
            console.warn('Секции для скролла не найдены');
            return;
        }
        
        // Создаем контейнер если его нет
        let container = document.querySelector('.sections-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'sections-container';
            document.body.appendChild(container);
            
            // Перемещаем все секции в контейнер
            sections.forEach(section => {
                container.appendChild(section);
            });
        }
        
        // Добавляем класс full-height-section ко всем секциям
        sections.forEach((section, index) => {
            section.classList.add('full-height-section');
            section.style.transform = `translateY(${index * 100}vh)`;
            if (index === 0) {
                section.classList.add('active');
            }
        });
        
        // Создаем навигацию
        createNavigation();
        
        // Добавляем обработчики событий
        addEventListeners();
        
        // Устанавливаем первую секцию
        goToSection(0);
    }
    
    // Создание навигации
    function createNavigation() {
        // Создаем индикатор прогресса
        const indicator = document.createElement('div');
        indicator.className = 'scroll-indicator';
        
        const ul = document.createElement('ul');
        sections.forEach((section, index) => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = '#';
            a.dataset.section = index;
            if (index === 0) a.classList.add('active');
            
            a.addEventListener('click', (e) => {
                e.preventDefault();
                goToSection(index);
            });
            
            li.appendChild(a);
            ul.appendChild(li);
        });
        
        indicator.appendChild(ul);
        document.body.appendChild(indicator);
        
        // Создаем стрелки навигации
        const arrows = document.createElement('div');
        arrows.className = 'scroll-arrows';
        
        const upArrow = document.createElement('a');
        upArrow.className = 'scroll-arrow scroll-up';
        upArrow.href = '#';
        upArrow.innerHTML = '↑';
        upArrow.addEventListener('click', (e) => {
            e.preventDefault();
            scrollUp();
        });
        
        const downArrow = document.createElement('a');
        downArrow.className = 'scroll-arrow scroll-down';
        downArrow.href = '#';
        downArrow.innerHTML = '↓';
        downArrow.addEventListener('click', (e) => {
            e.preventDefault();
            scrollDown();
        });
        
        arrows.appendChild(upArrow);
        arrows.appendChild(downArrow);
        document.body.appendChild(arrows);
    }
    
    // Добавление обработчиков событий
    function addEventListeners() {
        // Обработка колеса мыши
        document.addEventListener('wheel', handleWheel, { passive: false });
        
        // Обработка клавиатуры
        document.addEventListener('keydown', handleKeyboard);
        
        // Обработка touch событий для трекпада
        document.addEventListener('touchstart', handleTouchStart, { passive: false });
        document.addEventListener('touchmove', handleTouchMove, { passive: false });
        
        // Обработка изменения размера окна
        window.addEventListener('resize', handleResize);
    }
    
    // Обработка колеса мыши
    function handleWheel(e) {
        e.preventDefault();
        
        if (isScrolling) return;
        
        const delta = e.deltaY;
        
        if (delta > 0) {
            scrollDown();
        } else {
            scrollUp();
        }
    }
    
    // Обработка клавиатуры
    function handleKeyboard(e) {
        if (isScrolling) return;
        
        switch(e.key) {
            case 'ArrowDown':
            case 'PageDown':
            case ' ': // Пробел
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
    }
    
    // Обработка touch событий
    function handleTouchStart(e) {
        touchStartY = e.touches[0].clientY;
    }
    
    function handleTouchMove(e) {
        if (isScrolling) return;
        
        e.preventDefault();
        
        const touchY = e.touches[0].clientY;
        const diff = touchStartY - touchY;
        
        if (Math.abs(diff) > 50) { // Минимальное расстояние для срабатывания
            if (diff > 0) {
                scrollDown();
            } else {
                scrollUp();
            }
        }
    }
    
    // Обработка изменения размера окна
    function handleResize() {
        const newIsDesktop = window.innerWidth >= 1025;
        
        if (newIsDesktop !== isDesktop) {
            // Если изменился тип устройства, перезагружаем страницу
            location.reload();
        }
    }
    
    // Скролл вниз
    function scrollDown() {
        if (currentSectionIndex < sections.length - 1) {
            goToSection(currentSectionIndex + 1);
        }
    }
    
    // Скролл вверх
    function scrollUp() {
        if (currentSectionIndex > 0) {
            goToSection(currentSectionIndex - 1);
        }
    }
    
    // Переход к конкретной секции
    function goToSection(index) {
        if (index < 0 || index >= sections.length || isScrolling) return;
        
        isScrolling = true;
        currentSectionIndex = index;
        
        // Обновляем позиции секций
        sections.forEach((section, i) => {
            section.classList.remove('active');
            const translateY = (i - currentSectionIndex) * 100;
            section.style.transform = `translateY(${translateY}vh)`;
        });
        
        // Добавляем активный класс
        sections[currentSectionIndex].classList.add('active');
        
        // Обновляем навигацию
        updateNavigation();
        
        // Снимаем блокировку скролла
        setTimeout(() => {
            isScrolling = false;
        }, 800);
    }
    
    // Обновление навигации
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
    
    // Запуск инициализации
    init();
    
    // Публичный API
    window.ElementorSmoothScroll = {
        goToSection: goToSection,
        scrollUp: scrollUp,
        scrollDown: scrollDown,
        getCurrentSection: () => currentSectionIndex,
        getSectionsCount: () => sections.length
    };
});

// Дополнительная проверка для Elementor
if (typeof elementorFrontend !== 'undefined') {
    elementorFrontend.hooks.addAction('frontend/element_ready/global', function() {
        // Пере-инициализация если Elementor загрузился после нашего скрипта
        setTimeout(() => {
            if (window.innerWidth >= 1025) {
                location.reload();
            }
        }, 1000);
    });
}