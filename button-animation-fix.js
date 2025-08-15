// Автоматическое разбиение текста кнопки на символы для анимации
document.addEventListener('DOMContentLoaded', function() {
    // Находим все кнопки с классом bntfull
    const buttons = document.querySelectorAll('.bntfull .elementor-button');
    
    buttons.forEach(button => {
        const buttonText = button.querySelector('.elementor-button-text');
        
        if (buttonText) {
            const text = buttonText.textContent;
            
            // Очищаем текст и создаем span для каждого символа
            buttonText.innerHTML = '';
            
            // Разбиваем текст на символы и оборачиваем каждый в span
            text.split('').forEach((char, index) => {
                const span = document.createElement('span');
                span.textContent = char === ' ' ? '\u00A0' : char; // Заменяем пробелы на неразрывные
                span.style.display = 'inline-block';
                buttonText.appendChild(span);
            });
        }
    });
});

// Альтернативный вариант для динамически добавляемых кнопок
function initButtonAnimation(selector = '.bntfull .elementor-button') {
    const buttons = document.querySelectorAll(selector);
    
    buttons.forEach(button => {
        const buttonText = button.querySelector('.elementor-button-text');
        
        if (buttonText && !buttonText.hasAttribute('data-animated')) {
            const text = buttonText.textContent.trim();
            buttonText.innerHTML = '';
            
            text.split('').forEach((char, index) => {
                const span = document.createElement('span');
                span.textContent = char === ' ' ? '\u00A0' : char;
                span.style.display = 'inline-block';
                buttonText.appendChild(span);
            });
            
            // Помечаем как обработанную
            buttonText.setAttribute('data-animated', 'true');
        }
    });
}

// Вызов функции для уже существующих элементов
document.addEventListener('DOMContentLoaded', () => initButtonAnimation());

// Наблюдатель для динамически добавляемых элементов
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.type === 'childList') {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1) { // Element node
                    // Проверяем сам элемент
                    if (node.matches('.bntfull .elementor-button')) {
                        initButtonAnimation();
                    }
                    // Проверяем дочерние элементы
                    const buttons = node.querySelectorAll('.bntfull .elementor-button');
                    if (buttons.length > 0) {
                        initButtonAnimation();
                    }
                }
            });
        }
    });
});

// Начинаем наблюдение
observer.observe(document.body, {
    childList: true,
    subtree: true
});