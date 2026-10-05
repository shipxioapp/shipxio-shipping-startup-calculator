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
        }

        form.addEventListener('change', (event) => {
            if (event.target.matches('.calculator-checkbox')) {
                calculateTotals();
            }
        });

        calculateTotals();
    }

/**
 * Educational disclosures.
 *
 * The rows use native <details>/<summary>. Browsers do not transition them,
 * so the open and close are driven here from the measured content height.
 * The disclosure sits outside the row <label>, so none of this touches the
 * checkbox.
 */
    function initializeDisclosures(root) {
        const DURATION = 240;

        const EASING_OPEN = 'cubic-bezier(0.22, 0.75, 0.3, 1)';
        const EASING_CLOSE = 'cubic-bezier(0.4, 0, 0.7, 1)';

        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

        const supportsAnimation = typeof Element.prototype.animate === 'function';

        if (!supportsAnimation) {
            return;
        }

        const disclosures = Array.from(
            root.querySelectorAll('.calculator-details')
        );

        disclosures.forEach((details) => {
            const summary = details.querySelector('summary');
            const body = details.querySelector('.calculator-details-body');

            if (!summary || !body) {
                return;
            }

            let animation = null;
            let expanded = details.open;

            function settle() {
                const finished = animation;

                animation = null;

                details.open = expanded;
                details.classList.remove('is-closing');

                body.classList.remove('is-animating');
                body.style.height = '';

                if (finished) {
                    finished.cancel();
                }
            }

            function toggle(opening) {
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

                expanded = opening;

                // Keep the content rendered while closing so it can animate out.
                details.open = true;
                details.classList.toggle('is-closing', !opening);

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

                animation.onfinish = settle;
            }

            summary.addEventListener('click', (event) => {
                if (reducedMotion.matches) {
                    if (animation) {
                        settle();
                    }
                    expanded = !details.open;
                    return;
                }

                event.preventDefault();

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
