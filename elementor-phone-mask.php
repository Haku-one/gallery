<?php
/**
 * Маска телефона для форм Elementor с кастомной валидацией
 * - Применяет маску через Numbered.js (если подключен на сайте)
 * - Поддерживает формы в поп-апах (ID поля: #form-field-tel_custom)
 * - Скрывает стандартные ошибки Elementor и показывает свою
 */

function add_elementor_phone_mask_inline() {
	if (is_admin() || isset($_GET['elementor-preview'])) {
		return;
	}
	?>
	<style>
		/* Скрыть стандартные сообщения ошибок Elementor */
		.elementor-message.elementor-message-danger,
		.elementor-field .elementor-error,
		.elementor-field-group .elementor-error {
			display: none !important;
		}

		/* Наши стили ошибок */
		.mask-phone-error {
			display: block;
			margin-top: 6px;
			font-size: 0.875em;
			line-height: 1.2;
			color: #ff3b30;
		}

		.elementor-form input.mask-phone-invalid,
		input.mask-phone-invalid {
			border-color: #ff3b30 !important;
		}

		.elementor-form input.mask-phone-invalid::placeholder,
		input.mask-phone-invalid::placeholder {
			color: #ff3b30 !important;
		}
	</style>

	<script>
	(function() {
		// Конфигурация маски телефона (Россия)
		var PHONE_MASK_PATTERN = '+7 (###) ###-##-##';
		var PHONE_REGEX = /^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/;
		var ERROR_TEXT = '\u041D\u0435\u043F\u0440\u0430\u0432\u0438\u043B\u044C\u043D\u044B\u0439 \u0444\u043E\u0440\u043C\u0430\u0442 \u0442\u0435\u043B\u0435\u0444\u043E\u043D\u0430'; // Неправильный формат телефона

		function uniqueByElement(arr) {
			var seen = new Set();
			var result = [];
			for (var i = 0; i < arr.length; i++) {
				var el = arr[i];
				if (!el || seen.has(el)) continue;
				seen.add(el);
				result.push(el);
			}
			return result;
		}

		function findPhoneInputs(root) {
			var scope = root || document;
			var a = Array.prototype.slice.call(scope.querySelectorAll('.elementor-form input[type="tel"]'));
			var b = Array.prototype.slice.call(scope.querySelectorAll('#form-field-tel_custom'));
			return uniqueByElement(a.concat(b));
		}

		function isValidPhone(value) {
			return PHONE_REGEX.test((value || '').trim());
		}

		function showError(input) {
			if (!input) return;
			input.classList.add('mask-phone-invalid');
			if (!input.dataset.originalPlaceholder) {
				input.dataset.originalPlaceholder = input.getAttribute('placeholder') || '';
			}
			// Меняем плейсхолдер и очищаем значение, чтобы он стал виден красным
			input.value = '';
			input.setAttribute('placeholder', ERROR_TEXT);

			var wrapper = input.closest('.elementor-field-group') || input.parentElement;
			if (!wrapper) wrapper = input;
			var prev = wrapper.querySelector('.mask-phone-error');
			if (!prev) {
				var span = document.createElement('span');
				span.className = 'mask-phone-error';
				span.textContent = ERROR_TEXT;
				wrapper.appendChild(span);
			}
		}

		function clearError(input) {
			if (!input) return;
			input.classList.remove('mask-phone-invalid');
			var original = input.dataset.originalPlaceholder;
			if (typeof original !== 'undefined') {
				input.setAttribute('placeholder', original);
			}
			var wrapper = input.closest('.elementor-field-group') || input.parentElement;
			if (!wrapper) wrapper = input;
			var prev = wrapper.querySelector('.mask-phone-error');
			if (prev) prev.remove();
		}

		function formatLocalDigits(localDigits) {
			var a = localDigits.slice(0, 3);
			var b = localDigits.slice(3, 6);
			var c = localDigits.slice(6, 8);
			var d = localDigits.slice(8, 10);
			var res = '+7 ';
			if (a.length) {
				res += '(' + a + (a.length === 3 ? ')' : '');
			}
			if (b.length) {
				res += (a.length ? ' ' : '') + b;
			}
			if (c.length) {
				res += '-' + c;
			}
			if (d.length) {
				res += '-' + d;
			}
			return res;
		}

		function applyFallbackMask(input) {
			if (input.dataset.maskFallbackInit) return;
			input.dataset.maskFallbackInit = '1';
			input.addEventListener('input', function() {
				var digits = (input.value || '').replace(/\D+/g, '').slice(0, 11);
				var local = digits;
				if (digits.length > 0 && (digits.charAt(0) === '7' || digits.charAt(0) === '8')) {
					local = digits.slice(1);
				}
				input.value = formatLocalDigits(local);
			});
		}

		function applyNumberedMask(input) {
			try {
				if (window.Numbered) {
					if (!input._numberedInstance) {
						// Пытаемся инициализировать Numbered.js
						input._numberedInstance = new window.Numbered(input, {
							mask: PHONE_MASK_PATTERN
						});
					}
					return true;
				}
			} catch (e) {
				// no-op
			}
			return false;
		}

		function initInput(input) {
			if (!input || input.dataset.maskPhoneInitialized) return;
			input.dataset.maskPhoneInitialized = '1';

			// Пробуем Numbered.js, иначе включаем простой fallback
			var ok = applyNumberedMask(input);
			if (!ok) {
				applyFallbackMask(input);
			}

			// Валидация на blur и по вводу
			input.addEventListener('blur', function() {
				if (!isValidPhone(input.value)) {
					showError(input);
				} else {
					clearError(input);
				}
			});
			input.addEventListener('input', function() {
				if (isValidPhone(input.value)) {
					clearError(input);
				}
			});
		}

		function scanAndInit(root) {
			var inputs = findPhoneInputs(root);
			for (var i = 0; i < inputs.length; i++) {
				initInput(inputs[i]);
			}
		}

		function interceptFormValidation(form) {
			if (!form || form.dataset.maskPhoneFormInit) return;
			form.dataset.maskPhoneFormInit = '1';
			form.addEventListener('submit', function(e) {
				var inputs = findPhoneInputs(form);
				var hasInvalid = false;
				for (var i = 0; i < inputs.length; i++) {
					var input = inputs[i];
					if (!isValidPhone(input.value)) {
						hasInvalid = true;
						showError(input);
					}
				}
				if (hasInvalid) {
					e.preventDefault();
					e.stopImmediatePropagation();
					e.stopPropagation();
					return false;
				}
			}, true);
		}

		function initAllForms(root) {
			var scope = root || document;
			var forms = Array.prototype.slice.call(scope.querySelectorAll('.elementor-form'));
			for (var i = 0; i < forms.length; i++) {
				interceptFormValidation(forms[i]);
			}
		}

		function init() {
			scanAndInit(document);
			initAllForms(document);
		}

		// Запуск после загрузки DOM
		document.addEventListener('DOMContentLoaded', init);

		// Поддержка Elementor Popup
		if (typeof jQuery !== 'undefined') {
			jQuery(document).on('elementor/popup/show', function(event, id, instance) {
				var popup = document.querySelector('.elementor-popup-modal');
				if (popup) {
					scanAndInit(popup);
					initAllForms(popup);
				}
			});
		}

		// На всякий случай отслеживаем динамические изменения DOM
		try {
			var observer = new MutationObserver(function(mutations) {
				for (var i = 0; i < mutations.length; i++) {
					var m = mutations[i];
					if (m.addedNodes && m.addedNodes.length) {
						for (var j = 0; j < m.addedNodes.length; j++) {
							var node = m.addedNodes[j];
							if (!(node instanceof Element)) continue;
							scanAndInit(node);
							initAllForms(node);
						}
					}
				}
			});
			observer.observe(document.documentElement, { childList: true, subtree: true });
		} catch (e) {
			// silent
		}
	})();
	</script>
	<?php
}

// ВКЛЮЧИТЕ эту строку для активации (инлайн JS/CSS в футере)
add_action('wp_footer', 'add_elementor_phone_mask_inline');
?>

