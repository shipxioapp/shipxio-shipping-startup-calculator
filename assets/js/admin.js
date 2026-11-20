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

    /**
     * Item reordering. Rows carry hidden _order inputs, so moving a row in the
     * DOM changes the order that is saved. Without JavaScript the buttons stay
     * hidden and the saved order is kept.
     */
    function initReorder() {
        const table = document.querySelector('.shipxio-startup-admin .form-table');
        const status = document.querySelector('.shipxio-startup-admin [data-shipxio-move-status]');

        if (!table) {
            return;
        }

        const rows = () => Array.from(table.querySelectorAll('tr.shipxio-startup-item-row'));

        const refresh = () => {
            const list = rows();
            list.forEach((row, index) => {
                const up = row.querySelector('[data-shipxio-move="up"]');
                const down = row.querySelector('[data-shipxio-move="down"]');
                if (up) {
                    up.disabled = index === 0;
                }
                if (down) {
                    down.disabled = index === list.length - 1;
                }
            });
        };

        rows().forEach((row) => {
            const controls = row.querySelector('.shipxio-startup-move');
            if (controls) {
                controls.hidden = false;
            }
        });

        table.addEventListener('click', (event) => {
            const button = event.target.closest('[data-shipxio-move]');
            if (!button) {
                return;
            }

            const row = button.closest('tr.shipxio-startup-item-row');
            const list = rows();
            const index = list.indexOf(row);
            const up = button.dataset.shipxioMove === 'up';
            const target = list[index + (up ? -1 : 1)];

            if (!row || !target) {
                return;
            }

            target.insertAdjacentElement(up ? 'beforebegin' : 'afterend', row);
            refresh();

            // Keep focus on the moved row; fall back to its other button at an edge.
            const other = row.querySelector(`[data-shipxio-move="${up ? 'down' : 'up'}"]`);
            (button.disabled ? other : button).focus();

            if (status) {
                const name = row.querySelector('th')?.textContent.trim() || '';
                status.textContent = `${name}: ${rows().indexOf(row) + 1} / ${rows().length}`;
            }
        });

        refresh();
    }

    initCopyButtons();
    initReorder();
})();
