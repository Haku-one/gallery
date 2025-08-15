<?php
/**
 * Плавный скролл по блокам для Elementor - ИСПРАВЛЕННАЯ ВЕРСИЯ
 * Добавьте этот код в functions.php вашей темы
 */

// Альтернативный способ - инлайн стили и скрипты
function add_elementor_smooth_scroll_inline() {
    // Проверяем, что мы не в админке и не в редакторе Elementor
    if (is_admin() || isset($_GET['elementor-preview'])) {
        return;
    }
    
    // Добавляем CSS в head
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

/* Стили для индикатора навигации (опционально) */
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
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.scroll-indicator a.active,
.scroll-indicator a:hover {
    background: #fff;
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
    width: 40px;
    height: 40px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    text-align: center;
    line-height: 40px;
    margin: 5px 0;
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 50%;
    text-decoration: none;
}

.scroll-arrow:hover {
    background: rgba(0, 0, 0, 0.9);
    transform: scale(1.1);
}

.scroll-arrow.disabled {
    opacity: 0.3;
    cursor: not-allowed;
}
    </style>
    
    <script>
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
    let isInitialized = false;
    
    // Функция для ожидания загрузки Elementor
    function waitForElementor(callback) {
        let attempts = 0;
        const maxAttempts = 50; // 5 секунд
        
        function checkElementor() {
            attempts++;
            
            // Проверяем наличие секций
            const elementorSections = document.querySelectorAll('.elementor-section');
            const customSections = document.querySelectorAll('.full-height-section');
            
            if ((elementorSections.length > 0 || customSections.length > 0) && !isInitialized) {
                callback();
                return;
            }
            
            if (attempts < maxAttempts) {
                setTimeout(checkElementor, 100);
            }
        }
        
        checkElementor();
    }
    
    // Инициализация
    function init() {
        if (isInitialized) return;
        
        // Находим все секции с классом full-height-section или Elementor секции
        sections = document.querySelectorAll('.full-height-section, .elementor-section');
        
        if (sections.length === 0) {
            console.warn('Секции для скролла не найдены');
            return;
        }
        
        console.log('Найдено секций:', sections.length);
        
        // Создаем контейнер если его нет
        let container = document.querySelector('.sections-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'sections-container';
            
            // Получаем родительский элемент первой секции
            const firstSection = sections[0];
            const parent = firstSection.parentNode;
            
            // Вставляем контейнер перед первой секцией
            parent.insertBefore(container, firstSection);
            
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
        
        isInitialized = true;
        console.log('Плавный скролл инициализирован');
    }
    
    // Создание навигации
    function createNavigation() {
        // Удаляем существующую навигацию если есть
        const existingIndicator = document.querySelector('.scroll-indicator');
        const existingArrows = document.querySelector('.scroll-arrows');
        if (existingIndicator) existingIndicator.remove();
        if (existingArrows) existingArrows.remove();
        
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
    
    // Публичный API
    window.ElementorSmoothScroll = {
        goToSection: goToSection,
        scrollUp: scrollUp,
        scrollDown: scrollDown,
        getCurrentSection: () => currentSectionIndex,
        getSectionsCount: () => sections.length,
        reinit: function() {
            isInitialized = false;
            waitForElementor(init);
        }
    };
    
    // Запуск инициализации с ожиданием Elementor
    waitForElementor(init);
});
    </script>
    <?php
}
// ВКЛЮЧИТЕ эту строку для активации
add_action('wp_head', 'add_elementor_smooth_scroll_inline');

// Добавление пользовательского CSS через админку WordPress
function add_smooth_scroll_customizer($wp_customize) {
    // Добавляем секцию
    $wp_customize->add_section('smooth_scroll_section', array(
        'title' => 'Плавный скролл',
        'priority' => 120,
    ));
    
    // Добавляем настройку для включения/выключения
    $wp_customize->add_setting('enable_smooth_scroll', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    
    // Добавляем контрол
    $wp_customize->add_control('enable_smooth_scroll', array(
        'label' => 'Включить плавный скролл',
        'section' => 'smooth_scroll_section',
        'type' => 'checkbox',
    ));
    
    // Добавляем настройку для скорости анимации
    $wp_customize->add_setting('scroll_animation_speed', array(
        'default' => '0.8',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('scroll_animation_speed', array(
        'label' => 'Скорость анимации (в секундах)',
        'section' => 'smooth_scroll_section',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 0.1,
            'max' => 3,
            'step' => 0.1,
        ),
    ));
}
add_action('customize_register', 'add_smooth_scroll_customizer');

// Функция для вывода настроек в CSS
function smooth_scroll_custom_css() {
    $enable_smooth_scroll = get_theme_mod('enable_smooth_scroll', true);
    $animation_speed = get_theme_mod('scroll_animation_speed', '0.8');
    
    if (!$enable_smooth_scroll) {
        return;
    }
    
    ?>
    <style type="text/css">
        @media (min-width: 1025px) {
            .full-height-section {
                transition: transform <?php echo esc_attr($animation_speed); ?>s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'smooth_scroll_custom_css');

// Отключение конфликтующих скриптов
function disable_conflicting_smooth_scroll() {
    // Список известных конфликтующих плагинов/скриптов
    $conflicting_handles = array(
        'smoothscroll',
        'smooth-scroll',
        'fullpage',
        'aos-script',
    );
    
    foreach ($conflicting_handles as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
}
add_action('wp_enqueue_scripts', 'disable_conflicting_smooth_scroll', 100);

// Добавление класса к body для определения типа устройства
function add_device_body_class($classes) {
    if (wp_is_mobile()) {
        $classes[] = 'is-mobile-device';
    } else {
        $classes[] = 'is-desktop-device';
    }
    
    return $classes;
}
add_filter('body_class', 'add_device_body_class');

// УДАЛЕН проблемный код для Elementor hooks

// Добавление мета-данных для отключения скролла на определенных страницах
function add_smooth_scroll_meta_box() {
    add_meta_box(
        'smooth_scroll_settings',
        'Настройки плавного скролла',
        'smooth_scroll_meta_box_callback',
        'page',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_smooth_scroll_meta_box');

function smooth_scroll_meta_box_callback($post) {
    wp_nonce_field('smooth_scroll_nonce', 'smooth_scroll_nonce');
    $disable_smooth_scroll = get_post_meta($post->ID, '_disable_smooth_scroll', true);
    ?>
    <label>
        <input type="checkbox" name="disable_smooth_scroll" value="1" <?php checked($disable_smooth_scroll, 1); ?>>
        Отключить плавный скролл на этой странице
    </label>
    <?php
}

function save_smooth_scroll_meta($post_id) {
    if (!isset($_POST['smooth_scroll_nonce']) || !wp_verify_nonce($_POST['smooth_scroll_nonce'], 'smooth_scroll_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['disable_smooth_scroll'])) {
        update_post_meta($post_id, '_disable_smooth_scroll', 1);
    } else {
        delete_post_meta($post_id, '_disable_smooth_scroll');
    }
}
add_action('save_post', 'save_smooth_scroll_meta');

// Условное подключение скриптов в зависимости от настроек страницы
function conditional_smooth_scroll_enqueue() {
    if (is_singular()) {
        $disable_smooth_scroll = get_post_meta(get_the_ID(), '_disable_smooth_scroll', true);
        if ($disable_smooth_scroll) {
            // Если скролл отключен на этой странице, удаляем функцию
            remove_action('wp_head', 'add_elementor_smooth_scroll_inline');
        }
    }
}
add_action('init', 'conditional_smooth_scroll_enqueue');
?>