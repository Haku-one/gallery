// ИСПРАВЛЕННЫЙ КОД ДЛЯ ELEMENTOR ФОРМ
// Исправляет проблему с ложными срабатываниями модального окна успешной отправки

// Добавьте этот код в начало вашего скрипта
const metaViewport = document.querySelector('meta[name="viewport"]');
if (metaViewport) {
    metaViewport.setAttribute('content', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no');
} else {
    const newMeta = document.createElement('meta');
    newMeta.setAttribute('name', 'viewport');
    newMeta.setAttribute('content', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no');
    document.head.appendChild(newMeta);
}

// Также добавьте CSS для предотвращения увеличения на iOS
const styleElement = document.createElement('style');
styleElement.textContent = `
    input[type="text"],
    input[type="email"],
    input[type="tel"],
    textarea {
        font-size: 16px !important; /* iOS не увеличивает поля с размером шрифта 16px и больше */
        -webkit-text-size-adjust: 100%;
    }
`;
document.head.appendChild(styleElement);

document.addEventListener('DOMContentLoaded', function() {
    // ИСПРАВЛЕНО: Переменные для отслеживания отправки формы
    let formSubmissionInProgress = false;
    let formSubmittedSuccessfully = false;

    // Стилизация чекбоксов и радио-кнопок
    const checkboxStyleElement = document.createElement('style');
    checkboxStyleElement.textContent = `
        .elementor-field-option {
            position: relative;
            display: flex;
            align-items: center;
        }

        span.elementor-field-option {
            padding-inline-end: 0px !important;
        }

        .elementor-field-subgroup{
            gap: 15px;
        }

        /* Скрываем оригинальные чекбоксы и радио */
        .elementor-field-option input[type="checkbox"],
        .elementor-field-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* Стили для кастомных чекбоксов */
        .elementor-field-option input[type="checkbox"] + label::before {
            content: "";
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 1px solid #FD9459;
            border-radius: 5px;
            margin-right: 10px;
            vertical-align: middle;
            transition: all 0.2s ease;
        }

        /* Стили для кастомных радио-кнопок */
        .elementor-field-option input[type="radio"] + label::before {
            content: "";
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 1px solid #FD9459;
            border-radius: 12.5px;
            margin-right: 10px;
            vertical-align: middle;
            transition: all 0.2s ease;
        }

        /* Стили при наведении */
        .elementor-field-option input[type="checkbox"] + label:hover::before,
        .elementor-field-option input[type="radio"] + label:hover::before {
            background: rgba(253, 148, 89, 0.5);
        }

        /* Стили для активного чекбокса */
        .elementor-field-option input[type="checkbox"]:checked + label::before {
            background-image: url("data:image/svg+xml,%3Csvg width='11' height='10' viewBox='0 0 11 10' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0.75 6.5L3.75 8.75L10.5 0.5' stroke='%23FD9459' stroke-width='1.2'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
        }

        /* Стили для активной радио-кнопки */
        .elementor-field-option input[type="radio"]:checked + label::before {
            background: #FD9459;
            box-shadow: inset 0 0 0 4px #FD9459, inset 0 0 0 9px white;
        }

        /* Стили для кастомного модального окна успешной отправки */
        .custom-success-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex !important;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease;
        }

        .custom-success-popup.active {
            opacity: 1 !important;
            visibility: visible !important;
        }

        .custom-success-popup-content {
            position: relative;
            width: 600px;
            max-width: 90%;
            padding: 60px 25px;
            background: #2A2A2A;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .custom-success-popup-close {
            position: absolute;
            width: 40px;
            height: 40px;
            right: 20px;
            top: 20px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .custom-success-popup-close:hover {
            transform: rotate(90deg);
        }

        .custom-success-popup-title {
            font-family: 'Mont', sans-serif;
            font-style: normal;
            font-weight: 900;
            font-size: 32px;
            line-height: 1.2em;
            color: #F9F9F9;
            text-align: center;
            margin-bottom: 30px;
        }

        .custom-success-popup-subtitle {
            font-family: 'Montserrat', sans-serif;
            font-style: normal;
            font-weight: 300;
            font-size: 25px;
            line-height: 1.3em;
            text-align: center;
            color: rgba(249, 249, 249, 0.5);
            max-width: 343px;
        }

        /* Адаптивные стили */
        @media (max-width: 768px) {
            .elementor-field-type-checkbox .elementor-field-subgroup,
            .elementor-field-type-radio .elementor-field-subgroup {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .custom-success-popup-content {
                padding: 40px 20px;
            }
            
            .custom-success-popup-close {
                right: 15px;
                top: 15px;
                width: 30px;
                height: 30px;
            }
            
            .custom-success-popup-title {
                font-size: 32px;
            }
            
            .custom-success-popup-subtitle {
                font-size: 24px;
                line-height: 30px;
            }
        }

        @media (max-width: 480px) {
            .elementor-field-type-checkbox .elementor-field-subgroup,
            .elementor-field-type-radio .elementor-field-subgroup {
                grid-template-columns: 1fr;
            }
            
            .custom-success-popup-title {
                font-size: 22px;
            }
            
            .custom-success-popup-subtitle {
                font-size: 16px;
                line-height: 1.3em;
            }

            .custom-success-popup-close svg{
                width: 30px;
                height: 30px;
            }
        }

        /* Скрываем стандартное сообщение об успешной отправке Elementor */
        .elementor-message-success {
            display: none !important;
        }

        /* Стили для телефонного поля */
        .custom-phone-input {
            position: relative;
            display: flex;
            width: 100%;
        }

        .country-select {
            position: relative;
            min-width: 90px;
            border: 1px solid #ddd;
            border-radius: 6px 0px 0px 6px;
            border-width: 1px 0px 1px 1px;
        }

        .selected-flag {
            display: flex;
            align-items: center;
            padding: 0 5px;
            height: 100%;
            cursor: pointer;
            background-color: #333;
            border-radius: 5px 0 0 5px;
            border-right: none;
        }

        .flag-icon {
            width: 24px;
            height: 16px;
            margin-right: 5px;
            background-size: cover;
            background-position: center;
        }

        .ru-flag { background-image: url('/wp-content/uploads/2025/06/ru.svg'); }
        .us-flag { background-image: url('/wp-content/uploads/2025/06/us.svg'); }
        .gb-flag { background-image: url('/wp-content/uploads/2025/06/gb.svg'); }
        .de-flag { background-image: url('/wp-content/uploads/2025/06/de.svg'); }
        .fr-flag { background-image: url('/wp-content/uploads/2025/06/fr.svg'); }
        .it-flag { background-image: url('/wp-content/uploads/2025/06/it.svg'); }
        .es-flag { background-image: url('/wp-content/uploads/2025/06/es.svg'); }
        .cn-flag { background-image: url('/wp-content/uploads/2025/06/cn.svg'); }

        .country-code {
            margin-right: 5px;
            font-size: 14px;
            color: #fff;
        }

        .arrow {
            font-size: 10px;
            margin-left: 5px;
            color: #fff;
        }

        .country-list {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 100;
            display: none;
            width: 250px;
            max-height: 200px;
            overflow-y: auto;
            background-color: #333;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.2);
            margin-top: 5px;
        }

        .country-list.active {
            display: block;
        }

        .country-item {
            display: flex;
            align-items: center;
            padding: 8px 10px;
            cursor: pointer;
            transition: background-color 0.2s;
            color: #fff;
        }

        .country-item:hover {
            background-color: #00000090;
        }

        .country-name {
            flex-grow: 1;
            margin: 0 10px;
        }

        .country-dial-code {
            color: #fff;
        }

        .custom-phone-input input {
            flex-grow: 1;
            border-radius: 0 5px 5px 0 !important;
            padding-left: 10px !important;
        }

        .phone-error {
            color: #d9534f;
            font-size: 12px;
            margin-top: 5px;
        }
    `;
    document.head.appendChild(checkboxStyleElement);

    // Создаем кастомное модальное окно для сообщения об успешной отправке
    const successPopup = document.createElement('div');
    successPopup.className = 'custom-success-popup';
    successPopup.innerHTML = `
        <div class="custom-success-popup-content">
            <div class="custom-success-popup-close">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M30 10L10 30" stroke="url(#paint0_linear_301_3855)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M30 10L10 30" stroke="url(#paint1_radial_301_3855)" stroke-opacity="0.7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M30 10L10 30" stroke="url(#paint2_radial_301_3855)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 10L30 30" stroke="url(#paint3_linear_301_3855)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 10L30 30" stroke="url(#paint4_radial_301_3855)" stroke-opacity="0.7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 10L30 30" stroke="url(#paint5_radial_301_3855)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <defs>
                        <linearGradient id="paint0_linear_301_3855" x1="37.8743" y1="45.4055" x2="5.22489" y2="43.9293" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#FC0403"/>
                            <stop offset="0.715" stop-color="#FD7113"/>
                            <stop offset="1" stop-color="#FD9C19"/>
                        </linearGradient>
                        <radialGradient id="paint1_radial_301_3855" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(15.2778 14.1667) rotate(51.0441) scale(16.7889 16.7889)">
                            <stop stop-color="white" stop-opacity="0.59"/>
                            <stop offset="0.697917" stop-color="white" stop-opacity="0"/>
                            <stop offset="1" stop-color="white" stop-opacity="0"/>
                        </radialGradient>
                        <radialGradient id="paint2_radial_301_3855" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(27.2603 27.5) rotate(-93.6705) scale(17.1184 19.0792)">
                            <stop stop-opacity="0.23"/>
                            <stop offset="0.861815" stop-opacity="0"/>
                        </radialGradient>
                        <linearGradient id="paint3_linear_301_3855" x1="2.1257" y1="45.4055" x2="34.7751" y2="43.9293" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#FC0403"/>
                            <stop offset="0.715" stop-color="#FD7113"/>
                            <stop offset="1" stop-color="#FD9C19"/>
                        </linearGradient>
                        <radialGradient id="paint4_radial_301_3855" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(24.7222 14.1667) rotate(128.956) scale(16.7889 16.7889)">
                            <stop stop-color="white" stop-opacity="0.59"/>
                            <stop offset="0.697917" stop-color="white" stop-opacity="0"/>
                            <stop offset="1" stop-color="white" stop-opacity="0"/>
                        </radialGradient>
                        <radialGradient id="paint5_radial_301_3855" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(12.7397 27.5) rotate(-86.3295) scale(17.1184 19.0792)">
                            <stop stop-opacity="0.23"/>
                            <stop offset="0.861815" stop-opacity="0"/>
                        </radialGradient>
                    </defs>
                </svg>
            </div>
            <h2 class="custom-success-popup-title">Ваша заявка отправлена</h2>
            <p class="custom-success-popup-subtitle">В ближайшее время<br> мы с вами свяжемся</p>
        </div>
    `;
    document.body.appendChild(successPopup);

    // Обработчик закрытия модального окна
    const closeButton = successPopup.querySelector('.custom-success-popup-close');
    closeButton.addEventListener('click', function() {
        successPopup.classList.remove('active');
    });

    // Также закрываем по клику на фон
    successPopup.addEventListener('click', function(e) {
        if (e.target === successPopup) {
            successPopup.classList.remove('active');
        }
    });

    // ИСПРАВЛЕНО: Функция для показа модального окна успеха
    function showSuccessPopup() {
        if (!formSubmittedSuccessfully) return; // Показываем только если форма была отправлена
        
        console.log('Показываем модальное окно успеха');
        
        // Закрываем модальное окно Elementor
        const elementorPopup = document.querySelector('.dialog-widget.elementor-popup-modal');
        if (elementorPopup) {
            const closeButton = elementorPopup.querySelector('.dialog-close-button');
            if (closeButton) closeButton.click();
        }
        
        // Показываем наше кастомное окно
        successPopup.classList.add('active');
        
        // Автоматически закрываем через 4 секунды
        setTimeout(function() {
            successPopup.classList.remove('active');
        }, 4000);
        
        // Сбрасываем флаг
        formSubmittedSuccessfully = false;
    }

    // ИСПРАВЛЕНО: Находим все Elementor формы и добавляем обработчики
    function initializeForms() {
        const forms = document.querySelectorAll('.elementor-form');
        
        forms.forEach(form => {
            // Проверяем, не инициализирована ли уже эта форма
            if (form.hasAttribute('data-custom-initialized')) return;
            form.setAttribute('data-custom-initialized', 'true');

            console.log('Инициализируем форму:', form);

            // Обработчик отправки формы
            form.addEventListener('submit', function(e) {
                console.log('Форма отправляется');
                formSubmissionInProgress = true;
                
                // Проверяем все обязательные поля
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    // Если поле пустое
                    if (field.value.trim() === '') {
                        field.classList.add('elementor-field-invalid');
                        isValid = false;
                        
                        // Если у поля нет сообщения об ошибке, создаем его
                        let errorMsg = field.parentNode.querySelector('.field-error');
                        if (!errorMsg) {
                            errorMsg = document.createElement('div');
                            errorMsg.className = 'field-error';
                            errorMsg.style.color = '#d9534f';
                            errorMsg.style.fontSize = '12px';
                            errorMsg.style.marginTop = '5px';
                            field.parentNode.appendChild(errorMsg);
                        }
                        
                        errorMsg.textContent = 'Это поле обязательно для заполнения';
                        errorMsg.style.display = 'block';
                    } else {
                        field.classList.remove('elementor-field-invalid');
                        const errorMsg = field.parentNode.querySelector('.field-error');
                        if (errorMsg) {
                            errorMsg.style.display = 'none';
                        }
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    e.stopPropagation();
                    formSubmissionInProgress = false;
                    return;
                }
            });
        });
    }

    // ИСПРАВЛЕНО: обработчик для отслеживания AJAX-запросов Elementor
    const originalXHR = window.XMLHttpRequest;
    window.XMLHttpRequest = function() {
        const xhr = new originalXHR();
        
        xhr.addEventListener('load', function() {
            // Проверяем только если форма была отправлена
            if (formSubmissionInProgress) {
                console.log('AJAX запрос завершен:', this.responseText);
                
                // Более точная проверка успешной отправки Elementor формы
                if (this.responseText && 
                    (this.responseText.includes('"success":true') || 
                     this.responseText.includes('success') && this.responseText.includes('data'))) {
                    
                    console.log('Elementor форма успешно отправлена');
                    formSubmissionInProgress = false;
                    formSubmittedSuccessfully = true;
                    
                    setTimeout(function() {
                        showSuccessPopup();
                    }, 500);
                }
            }
        });

        return xhr;
    };

    // ИСПРАВЛЕНО: MutationObserver - более точные условия
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                // Проверяем добавленные узлы только если форма была отправлена
                mutation.addedNodes.forEach(node => {
                    if (formSubmissionInProgress && 
                        node.nodeType === 1 && 
                        node.classList && 
                        node.classList.contains('elementor-message-success')) {
                        
                        console.log('Обнаружено сообщение об успехе');
                        formSubmissionInProgress = false;
                        formSubmittedSuccessfully = true;
                        
                        // Скрываем стандартное сообщение
                        node.style.display = 'none';
                        
                        setTimeout(function() {
                            showSuccessPopup();
                        }, 100);
                    }
                });
            }
        });
    });

    // Инициализируем формы при загрузке
    initializeForms();

    // Начинаем наблюдение только за формами
    const formContainers = document.querySelectorAll('.elementor-form');
    formContainers.forEach(form => {
        observer.observe(form, {
            childList: true,
            subtree: true
        });
    });

    // Обработчик для динамически добавляемых форм
    const formObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                const hasNewForms = Array.from(mutation.addedNodes).some(node =>
                    node.nodeType === 1 && (
                        node.querySelector('.elementor-form') ||
                        node.classList.contains('elementor-form')
                    )
                );

                if (hasNewForms) {
                    setTimeout(function() {
                        initializeForms();
                        // Добавляем наблюдение за новыми формами
                        const newForms = document.querySelectorAll('.elementor-form:not([data-custom-initialized])');
                        newForms.forEach(form => {
                            observer.observe(form, {
                                childList: true,
                                subtree: true
                            });
                        });
                    }, 100);
                }
            }
        });
    });

    // Наблюдаем за изменениями в документе для новых форм
    formObserver.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Обработчик для модальных окон Elementor
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('elementor/popup/show', function() {
            setTimeout(function() {
                initializeForms();
                initPhoneField();
            }, 100);
        });
    }

    // ТЕЛЕФОННЫЕ ПОЛЯ - весь ваш существующий код
    // Добавляем функцию валидации телефона
    function validatePhone(input) {
        const selectedFlag = input.closest('.custom-phone-input').querySelector('.selected-flag');
        const dialCode = selectedFlag.querySelector('.country-code').textContent;
        const countryCode = selectedFlag.querySelector('.flag-icon').className.split(' ')[1].split('-')[0];

        // Получаем только цифры после кода страны
        const digits = input.value.substring(dialCode.length).replace(/\D/g, '');

        // Минимальное количество цифр для разных стран
        const minLengths = {
            'ru': 10,
            'us': 10,
            'gb': 10,
            'de': 10,
            'fr': 9,
            'it': 10,
            'es': 9,
            'cn': 11
        };

        const minLength = minLengths[countryCode] || 10;

        // Проверяем длину
        return digits.length >= minLength;
    }

    // Функция инициализации телефонного поля
    function initPhoneField() {
        const phoneInputs = document.querySelectorAll('#form-field-tel');

        phoneInputs.forEach(phoneInput => {
            // Проверяем, не обработано ли уже это поле или его родитель
            if (!phoneInput ||
                phoneInput.closest('.custom-phone-input') ||
                phoneInput.getAttribute('data-phone-initialized') === 'true' ||
                phoneInput.style.display === 'none') {
                return;
            }

            // Помечаем поле как обработанное
            phoneInput.setAttribute('data-phone-initialized', 'true');

            const parent = phoneInput.parentNode;
            const wrapper = document.createElement('div');
            wrapper.className = 'custom-phone-input';

            // Создаем новый инпут (копируем атрибуты из старого)
            const newPhoneInput = document.createElement('input');
            newPhoneInput.type = 'tel';
            newPhoneInput.name = phoneInput.name;
            newPhoneInput.id = phoneInput.id + '_custom';
            newPhoneInput.className = phoneInput.className;
            newPhoneInput.placeholder = 'Телефон*';
            newPhoneInput.required = true;
            newPhoneInput.autocomplete = "tel";

            // Скрываем оригинальное поле, но оставляем его в DOM для работы с Elementor
            phoneInput.style.display = 'none';

            // Создаем выпадающий список стран
            const countrySelect = document.createElement('div');
            countrySelect.className = 'country-select';

            const selectedFlag = document.createElement('div');
            selectedFlag.className = 'selected-flag';
            selectedFlag.innerHTML = `
                <div class="flag-icon ru-flag"></div>
                <span class="country-code">+7</span>
                <span class="arrow">▼</span>
            `;

            const countryList = document.createElement('div');
            countryList.className = 'country-list';

            // Список популярных стран
            const countries = [
                { code: 'ru', name: 'Россия', dialCode: '+7' },
                { code: 'us', name: 'США', dialCode: '+1' },
                { code: 'gb', name: 'Великобрит....', dialCode: '+44' },
                { code: 'de', name: 'Германия', dialCode: '+49' },
                { code: 'fr', name: 'Франция', dialCode: '+33' },
                { code: 'it', name: 'Италия', dialCode: '+39' },
                { code: 'es', name: 'Испания', dialCode: '+34' },
                { code: 'cn', name: 'Китай', dialCode: '+86' }
            ];

            // Добавляем страны в выпадающий список
            countries.forEach(country => {
                const countryItem = document.createElement('div');
                countryItem.className = 'country-item';
                countryItem.dataset.code = country.code;
                countryItem.dataset.dialCode = country.dialCode;
                countryItem.innerHTML = `
                    <div class="flag-icon ${country.code}-flag"></div>
                    <span class="country-name">${country.name}</span>
                    <span class="country-dial-code">${country.dialCode}</span>
                `;
                countryList.appendChild(countryItem);
            });

            // Создаем элемент для сообщения об ошибке
            const errorMsg = document.createElement('div');
            errorMsg.className = 'phone-error';
            errorMsg.textContent = 'Пожалуйста, введите корректный номер телефона';
            errorMsg.style.display = 'none';

            // Собираем всё вместе
            countrySelect.appendChild(selectedFlag);
            countrySelect.appendChild(countryList);
            wrapper.appendChild(countrySelect);
            wrapper.appendChild(newPhoneInput);

            // Вставляем наш кастомный элемент после скрытого оригинального поля
            parent.insertBefore(wrapper, phoneInput.nextSibling);
            parent.appendChild(errorMsg);

            // Устанавливаем начальное значение
            newPhoneInput.value = '+7 ';
            phoneInput.value = '+7 ';

            // Добавляем обработчик для синхронизации значений
            newPhoneInput.addEventListener('input', function() {
                phoneInput.value = newPhoneInput.value;
                
                // Проверяем валидность и показываем/скрываем ошибку
                if (validatePhone(newPhoneInput)) {
                    errorMsg.style.display = 'none';
                    newPhoneInput.classList.remove('elementor-field-invalid');
                    phoneInput.classList.remove('elementor-field-invalid');
                    phoneInput.setCustomValidity('');
                } else {
                    errorMsg.style.display = 'block';
                    newPhoneInput.classList.add('elementor-field-invalid');
                    phoneInput.classList.add('elementor-field-invalid');
                    phoneInput.setCustomValidity('Пожалуйста, введите корректный номер телефона');
                }
            });

            // Добавляем обработчик на событие invalid для оригинального поля
            phoneInput.addEventListener('invalid', function(e) {
                if (!validatePhone(newPhoneInput)) {
                    e.preventDefault();
                    errorMsg.style.display = 'block';
                    newPhoneInput.classList.add('elementor-field-invalid');
                }
            });

            // Добавляем обработчик для формы
            const form = phoneInput.closest('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Если телефон не валиден, предотвращаем отправку
                    if (!validatePhone(newPhoneInput)) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        errorMsg.style.display = 'block';
                        newPhoneInput.classList.add('elementor-field-invalid');
                        
                        // Прокручиваем к полю с ошибкой
                        newPhoneInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        return false;
                    }
                    
                    // Синхронизируем значение с оригинальным полем перед отправкой
                    phoneInput.value = newPhoneInput.value;
                }, true); // Используем capturing для перехвата события до обработчиков Elementor
            }
        });
    }

    // Обработчик клика для открытия/закрытия списка стран
    document.addEventListener('click', function(e) {
        // Если клик был по флагу или стрелке
        if (e.target.closest('.selected-flag')) {
            const countryList = e.target.closest('.country-select').querySelector('.country-list');
            countryList.classList.toggle('active');
            e.stopPropagation();
        }
        // Если клик был по элементу страны
        else if (e.target.closest('.country-item')) {
            const countryItem = e.target.closest('.country-item');
            const countryList = countryItem.closest('.country-list');
            const selectedFlag = countryList.previousElementSibling;
            const newPhoneInput = countryItem.closest('.custom-phone-input').querySelector('input');

            const code = countryItem.dataset.code;
            const dialCode = countryItem.dataset.dialCode;

            // Обновляем выбранный флаг
            selectedFlag.innerHTML = `
                <div class="flag-icon ${code}-flag"></div>
                <span class="country-code">${dialCode}</span>
                <span class="arrow">▼</span>
            `;

            // Сбрасываем поле ввода при смене страны
            newPhoneInput.value = dialCode + ' ';

            // Закрываем список
            countryList.classList.remove('active');

            // Фокусируемся на поле ввода и ставим курсор в конец
            newPhoneInput.focus();
            setTimeout(() => {
                newPhoneInput.selectionStart = newPhoneInput.value.length;
                newPhoneInput.selectionEnd = newPhoneInput.value.length;
            }, 0);

            // Синхронизируем с оригинальным полем
            const originalInput = newPhoneInput.closest('.elementor-field-group').querySelector('#form-field-tel');
            if (originalInput) {
                originalInput.value = newPhoneInput.value;
            }

            // Проверяем валидность
            const errorMsg = newPhoneInput.closest('.elementor-field-group').querySelector('.phone-error');
            if (validatePhone(newPhoneInput)) {
                errorMsg.style.display = 'none';
                newPhoneInput.classList.remove('elementor-field-invalid');
                if (originalInput) originalInput.classList.remove('elementor-field-invalid');
            } else {
                errorMsg.style.display = 'block';
                newPhoneInput.classList.add('elementor-field-invalid');
                if (originalInput) originalInput.classList.add('elementor-field-invalid');
            }
        }
        // Закрываем все открытые списки при клике вне
        else {
            document.querySelectorAll('.country-list.active').forEach(list => {
                list.classList.remove('active');
            });
        }
    });

    // Упрощенный обработчик ввода для телефона
    document.addEventListener('input', function(e) {
        if (e.target.closest('.custom-phone-input input')) {
            const input = e.target;
            const cursorPos = input.selectionStart;

            // Получаем код страны из выбранного флага
            const selectedFlag = input.closest('.custom-phone-input').querySelector('.selected-flag');
            const dialCode = selectedFlag.querySelector('.country-code').textContent;
            const countryCode = selectedFlag.querySelector('.flag-icon').className.split(' ')[1].split('-')[0];

            // Проверяем, начинается ли значение с кода страны
            if (!input.value.startsWith(dialCode)) {
                input.value = dialCode + ' ' + input.value.replace(dialCode, '');
            }

            // Получаем только цифры после кода страны
            let digits = input.value.substring(dialCode.length).replace(/\D/g, '');

            // Ограничиваем длину в зависимости от страны
            const maxLengths = {
                'ru': 10,
                'us': 10,
                'gb': 10,
                'de': 10,
                'fr': 9,
                'it': 10,
                'es': 9,
                'cn': 11
            };
            const maxLength = maxLengths[countryCode] || 10;
            digits = digits.substring(0, maxLength);

            // Форматируем номер
            let formattedNumber = '';

            if (countryCode === 'ru') {
                // Формат для России: +7 (XXX) XXX-XX-XX
                if (digits.length > 0) {
                    formattedNumber += ' (';
                    formattedNumber += digits.substring(0, Math.min(3, digits.length));
                    
                    if (digits.length > 3) {
                        formattedNumber += ') ';
                        formattedNumber += digits.substring(3, Math.min(6, digits.length));
                        
                        if (digits.length > 6) {
                            formattedNumber += '-';
                            formattedNumber += digits.substring(6, Math.min(8, digits.length));
                            
                            if (digits.length > 8) {
                                formattedNumber += '-';
                                formattedNumber += digits.substring(8);
                            }
                        }
                    }
                }
            } else if (countryCode === 'us') {
                // Формат для США: +1 (XXX) XXX-XXXX
                if (digits.length > 0) {
                    formattedNumber += ' (';
                    formattedNumber += digits.substring(0, Math.min(3, digits.length));
                    
                    if (digits.length > 3) {
                        formattedNumber += ') ';
                        formattedNumber += digits.substring(3, Math.min(6, digits.length));
                        
                        if (digits.length > 6) {
                            formattedNumber += '-';
                            formattedNumber += digits.substring(6);
                        }
                    }
                }
            } else {
                // Общий формат для других стран: группы по 3 цифры
                if (digits.length > 0) {
                    formattedNumber += ' ';
                    for (let i = 0; i < digits.length; i += 3) {
                        if (i > 0) formattedNumber += ' ';
                        formattedNumber += digits.substring(i, Math.min(i + 3, digits.length));
                    }
                }
            }

            // Сохраняем текущее положение курсора относительно цифр
            const digitPos = getDigitPosition(input.value.substring(0, cursorPos), dialCode);

            // Устанавливаем новое значение
            input.value = dialCode + formattedNumber;

            // Восстанавливаем положение курсора
            const newCursorPos = getPositionFromDigit(input.value, digitPos, dialCode);
            setTimeout(() => {
                input.selectionStart = newCursorPos;
                input.selectionEnd = newCursorPos;
            }, 0);

            // Синхронизируем с оригинальным полем
            const originalInput = input.closest('.elementor-field-group').querySelector('#form-field-tel');
            if (originalInput) {
                originalInput.value = input.value;
            }

            // Проверяем валидность
            const errorMsg = input.closest('.elementor-field-group').querySelector('.phone-error');
            if (validatePhone(input)) {
                errorMsg.style.display = 'none';
                input.classList.remove('elementor-field-invalid');
                if (originalInput) originalInput.classList.remove('elementor-field-invalid');
            } else {
                errorMsg.style.display = 'block';
                input.classList.add('elementor-field-invalid');
                if (originalInput) originalInput.classList.add('elementor-field-invalid');
            }
        }
    });

    // Функция для получения позиции в цифрах
    function getDigitPosition(value, dialCode) {
        // Удаляем код страны
        const withoutCode = value.replace(dialCode, '');
        // Считаем только цифры
        return withoutCode.replace(/\D/g, '').length;
    }

    // Функция для получения позиции курсора из позиции в цифрах
    function getPositionFromDigit(value, digitPos, dialCode) {
        let count = 0;
        let pos = dialCode.length;

        // Проходим по символам после кода страны
        for (let i = dialCode.length; i < value.length && count < digitPos; i++) {
            if (/\d/.test(value[i])) {
                count++;
            }
            pos++;
        }

        return pos;
    }

    // Предотвращаем удаление кода страны
    document.addEventListener('keydown', function(e) {
        if (e.target.closest('.custom-phone-input input')) {
            const input = e.target;
            const selectedFlag = input.closest('.custom-phone-input').querySelector('.selected-flag');
            const dialCode = selectedFlag.querySelector('.country-code').textContent;

            // Если пытаемся удалить код страны
            if ((e.key === 'Backspace' || e.key === 'Delete') && 
                (input.selectionStart <= dialCode.length)) {
                
                // Если выделен весь текст, разрешаем удаление, но восстанавливаем код страны
                if (input.selectionStart === 0 && input.selectionEnd === input.value.length) {
                    setTimeout(() => {
                        input.value = dialCode + ' ';
                        input.selectionStart = input.value.length;
                        input.selectionEnd = input.value.length;
                        
                        // Синхронизируем с оригинальным полем
                        const originalInput = input.closest('.elementor-field-group').querySelector('#form-field-tel');
                        if (originalInput) {
                            originalInput.value = input.value;
                        }
                    }, 0);
                    return;
                }
                
                // Иначе предотвращаем удаление кода страны
                e.preventDefault();
            }
        }
    });

    // Функция для очистки и повторной инициализации телефонных полей
    function reinitPhoneFields() {
        // Удаляем все кастомные поля телефонов
        document.querySelectorAll('.custom-phone-input').forEach(el => {
            el.remove();
        });

        document.querySelectorAll('.phone-error').forEach(el => {
            el.remove();
        });

        // Сбрасываем флаги инициализации
        document.querySelectorAll('#form-field-tel').forEach(el => {
            el.removeAttribute('data-phone-initialized');
            el.style.display = '';
        });

        // Заново инициализируем поля
        initPhoneField();
    }

    // Инициализируем телефонные поля при загрузке страницы
    initPhoneField();

    // Добавляем обработчик для случаев, когда форма обновляется через AJAX
    document.addEventListener('elementor/frontend/init', function() {
        if (window.elementorFrontend && window.elementorFrontend.hooks) {
            window.elementorFrontend.hooks.addAction('frontend/element_ready/form.default', function() {
                setTimeout(function() {
                    reinitPhoneFields();
                }, 100);
            });
        }
    });
});