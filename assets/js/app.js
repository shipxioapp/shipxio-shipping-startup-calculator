/**
 * Shipxio Shipping Startup Calculator
 * wordpress/shipxio-shipping-startup-calculator/assets/js/app.js
 */

(function () {
    'use strict';

    const widgetSelector = '.shipxio-shipping-startup-calculator';
    const initialized = new WeakSet();

    function initializeWidgets(scope) {
        const widgets = Array.from(scope.querySelectorAll(widgetSelector));
        if (scope.matches?.(widgetSelector)) {
            widgets.unshift(scope);
        }
        widgets.forEach((root) => {
            if (initialized.has(root)) {
                return;
            }
            initialized.add(root);
            initializeCalculator(root);
            initializeDisclosures(root);
        });
    }

    function initializeCalculator(root) {
        const form = root.querySelector('.calculator-form');

        if (!form) {
            return;
        }

        const startupTotal = root.querySelector('[data-ssc-id="startup-total"]');

        const recurringCosts = root.querySelector('[data-ssc-id="recurring-costs"]');

        const monthlyRow = root.querySelector('[data-ssc-id="monthly-cost-row"]');
        const monthlyTotal = root.querySelector('[data-ssc-id="monthly-total"]');

        const yearlyRow = root.querySelector('[data-ssc-id="yearly-cost-row"]');
        const yearlyTotal = root.querySelector('[data-ssc-id="yearly-total"]');

        const checkboxes = Array.from(
            form.querySelectorAll('.calculator-checkbox')
        );

        function formatJmd(value) {
            return `J$${value.toLocaleString('en-JM', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            })}`;
        }

        function formatUsd(value) {
            return `US$${value.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2,
            })}`;
        }

        function calculateTotals() {
            let oneTimeJmd = 0;
            let monthlyUsd = 0;
            let yearlyUsd = 0;

            checkboxes.forEach((checkbox) => {
                if (!checkbox.checked) {
                    return;
                }

                const cost = Number(checkbox.dataset.cost);

                if (!Number.isFinite(cost) || cost < 0) {
                    return;
                }

                const currency = checkbox.dataset.currency;
                const billingPeriod = checkbox.dataset.billingPeriod;

                if (
                    currency === 'JMD'
                    && billingPeriod === 'one_time'
                ) {
                    oneTimeJmd += cost;

                    return;
                }

                if (
                    currency === 'USD'
                    && billingPeriod === 'month'
                ) {
                    monthlyUsd += cost;

                    return;
                }

                if (
                    currency === 'USD'
                    && billingPeriod === 'year'
                ) {
                    yearlyUsd += cost;
                }
            });

            startupTotal.textContent = formatJmd(oneTimeJmd);

            monthlyTotal.textContent = `${formatUsd(monthlyUsd)}/month`;
            yearlyTotal.textContent = `${formatUsd(yearlyUsd)}/year`;

            monthlyRow.hidden = monthlyUsd === 0;
            yearlyRow.hidden = yearlyUsd === 0;

            recurringCosts.hidden = monthlyUsd === 0 && yearlyUsd === 0;

            updateSelectionState();
        }

        // Presentation only: the count and bulk label follow the checkboxes.
        const selectedCount = root.querySelector('[data-ssc-id="selected-count"]');
        const bulkToggle = root.querySelector('[data-ssc-id="bulk-toggle"]');

        function allSelected() {
            return checkboxes.every((checkbox) => checkbox.checked);
        }

        function updateSelectionState() {
            const checked = checkboxes.filter((checkbox) => checkbox.checked).length;

            if (selectedCount) {
                const format = selectedCount.dataset.format || '%1$d of %2$d selected';
                selectedCount.textContent = format
                    .replace('%1$d', String(checked))
                    .replace('%2$d', String(checkboxes.length));
            }

            if (bulkToggle) {
                bulkToggle.textContent = allSelected()
                    ? bulkToggle.dataset.clearLabel
                    : bulkToggle.dataset.selectLabel;
            }
        }

        form.addEventListener('change', (event) => {
            if (event.target.matches('.calculator-checkbox')) {
                calculateTotals();
            }
        });

        if (bulkToggle) {
            bulkToggle.addEventListener('click', () => {
                const select = !allSelected();
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = select;
                });
                calculateTotals();
            });
        }

        // The narrow-layout breakdown; wide layouts always show it via CSS.
        const breakdownToggle = root.querySelector('[data-ssc-id="breakdown-toggle"]');
        if (breakdownToggle) {
            breakdownToggle.addEventListener('click', () => {
                const expanded = breakdownToggle.getAttribute('aria-expanded') !== 'true';
                breakdownToggle.setAttribute('aria-expanded', String(expanded));
            });
        }

        calculateTotals();
    }

/**
 * Educational disclosures.
 *
 * Each row has an info <button aria-expanded aria-controls> and a sibling
 * body region with the hidden attribute (the standard disclosure pattern).
 * The button is outside the row <label>, so none of this touches the
 * checkbox. Open and close animate from the measured content height.
 */
    function initializeDisclosures(root) {
        const DURATION = 240;

        const EASING_OPEN = 'cubic-bezier(0.22, 0.75, 0.3, 1)';
        const EASING_CLOSE = 'cubic-bezier(0.4, 0, 0.7, 1)';

        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

        const supportsAnimation = typeof Element.prototype.animate === 'function';

        const buttons = Array.from(root.querySelectorAll('.calculator-info'));

        buttons.forEach((button) => {
            const targetId = button.getAttribute('aria-controls');
            const body = targetId ? root.querySelector(`[id="${targetId}"]`) : null;

            if (!body) {
                return;
            }

            let animation = null;
            let expanded = button.getAttribute('aria-expanded') === 'true';

            function settle() {
                const finished = animation;

                animation = null;

                body.hidden = !expanded;

                body.classList.remove('is-animating');
                body.style.height = '';

                if (finished) {
                    finished.cancel();
                }
            }

            function toggle(opening) {
                expanded = opening;
                button.setAttribute('aria-expanded', String(opening));

                if (!supportsAnimation || reducedMotion.matches) {
                    settle();
                    return;
                }

                const startHeight = animation
                    ? body.getBoundingClientRect().height
                    : (opening ? 0 : body.getBoundingClientRect().height);

                const startOpacity = animation
                    ? Number(window.getComputedStyle(body).opacity)
                    : (opening ? 0 : 1);

                if (animation) {
                    animation.cancel();

                    animation = null;
                }

                // Keep the content rendered while closing so it can animate out.
                body.hidden = false;

                body.classList.add('is-animating');
                body.style.height = '';

                const fullHeight = body.offsetHeight;

                animation = body.animate(
                    {
                        height: [`${startHeight}px`, `${opening ? fullHeight : 0}px`],
                        opacity: [startOpacity, opening ? 1 : 0],
                        transform: [
                            `translateY(${opening ? -4 : 0}px)`,
                            `translateY(${opening ? 0 : -4}px)`,
                        ],
                    },
                    {
                        duration: DURATION,
                        easing: opening ? EASING_OPEN : EASING_CLOSE,
                        fill: 'both',
                    }
                );

                // Settle only if this animation is still the current one; a
                // cancelled animation (rapid clicks) rejects and is ignored.
                const current = animation;
                current.finished.then(() => {
                    if (animation === current) {
                        settle();
                    }
                }, () => {});
            }

            button.addEventListener('click', () => {
                toggle(!expanded);
            });
        });
    }

    // Reuse Connect's standard Shortcode widget lifecycle; no custom widget.
    let elementorHookRegistered = false;
    function registerElementorHook() {
        const hooks = window.elementorFrontend?.hooks;
        if (elementorHookRegistered || typeof hooks?.addAction !== 'function') {
            return;
        }
        elementorHookRegistered = true;
        hooks.addAction('frontend/element_ready/shortcode.default', ($scope) => {
            const scope = $scope?.[0] || $scope;
            if (scope?.querySelectorAll) {
                initializeWidgets(scope);
            }
        });
    }

    initializeWidgets(document);
    registerElementorHook();
    window.addEventListener('elementor/frontend/init', registerElementorHook);
    if (window.jQuery) {
        window.jQuery(window).on('elementor/frontend/init', registerElementorHook);
    }
})();
