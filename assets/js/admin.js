/** Shipxio Shipping Startup Calculator settings: shortcode copy buttons only. */
(function () {
    'use strict';

    function initCopyButtons() {
        const buttons = document.querySelectorAll('.shipxio-startup-admin .shipxio-startup-copy');

        if (buttons.length === 0) {
            return;
        }

        /** Older browsers and insecure origins have no clipboard API. */
        const copy = (text) => {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            }

            const field = document.createElement('textarea');
            field.value = text;
            field.setAttribute('readonly', 'readonly');
            field.style.position = 'fixed';
            field.style.opacity = '0';
            document.body.appendChild(field);
            field.select();

            try {
                return document.execCommand('copy')
                    ? Promise.resolve()
                    : Promise.reject(new Error('Copy failed'));
            } catch (error) {
                return Promise.reject(error);
            } finally {
                document.body.removeChild(field);
            }
        };

        buttons.forEach((button) => {
            const label = button.querySelector('.shipxio-startup-copy-label');
            const original = label ? label.textContent : '';

            button.addEventListener('click', () => {
                copy(button.dataset.shipxioCopy || '').then(
                    () => {
                        if (!label) {
                            return;
                        }

                        button.classList.add('is-copied');
                        label.textContent = button.dataset.shipxioCopiedLabel || 'Copied';

                        window.setTimeout(() => {
                            button.classList.remove('is-copied');
                            label.textContent = original;
                        }, 1600);
                    },
                    () => {},
                );
            });
        });
    }

    initCopyButtons();
})();
