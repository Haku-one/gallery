/**
 * ОТДЕЛЬНАЯ ЛОГИКА ДЛЯ МОДАЛЬНОГО ОКНА ВИДЕО
 * Можно использовать независимо от скролл-эффекта
 */

document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, есть ли уже модальное окно
    let modal = document.getElementById('rutube-modal');
    
    // Если модального окна нет, создаем его
    if (!modal) {
        createModal();
        modal = document.getElementById('rutube-modal');
    }
    
    initVideoModal();
    
    function createModal() {
        const modalHTML = `
            <div class="rutube-modal" id="rutube-modal">
                <div class="rutube-modal-content">
                    <button class="rutube-modal-close" id="rutube-modal-close">&times;</button>
                    <iframe class="rutube-modal-iframe" id="rutube-modal-iframe" src="" allowfullscreen></iframe>
                </div>
            </div>
        `;
        
        // Добавляем стили если их нет
        if (!document.querySelector('#rutube-modal-styles')) {
            const styles = document.createElement('style');
            styles.id = 'rutube-modal-styles';
            styles.textContent = `
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

                /* Предотвращаем скролл страницы когда модальное окно открыто */
                body.video-modal-open {
                    overflow: hidden !important;
                }
            `;
            document.head.appendChild(styles);
        }
        
        // Добавляем модальное окно в конец body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
    
    function initVideoModal() {
        const videoItems = document.querySelectorAll('.rutube-video-item');
        const modal = document.getElementById('rutube-modal');
        const modalIframe = document.getElementById('rutube-modal-iframe');
        const closeBtn = document.getElementById('rutube-modal-close');
        
        if (!modal || !modalIframe || !closeBtn) {
            console.error('Rutube modal elements not found');
            return;
        }

        // Обработчик клика на видео
        videoItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
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
            modalIframe.src = url;
            modal.classList.add('show');
            document.body.classList.add('video-modal-open');
            
            // Уведомляем другие скрипты о том, что модальное окно открыто
            window.dispatchEvent(new CustomEvent('videoModalOpen'));
        }

        function closeModal() {
            modal.classList.remove('show');
            modalIframe.src = '';
            document.body.classList.remove('video-modal-open');
            
            // Уведомляем другие скрипты о том, что модальное окно закрыто
            window.dispatchEvent(new CustomEvent('videoModalClose'));
        }
    }
});