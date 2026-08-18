/* ============================================
   Apex Scale Models - UI Enhancements
   ============================================ */

(function() {
    'use strict';

    /* --- Mobile Navigation Toggle --- */
    function initMobileNav() {
        const toggle = document.querySelector('.nav-toggle');
        const nav = document.querySelector('.main-nav');
        if (!toggle || !nav) return;

        toggle.addEventListener('click', function() {
            nav.classList.toggle('open');
            this.textContent = nav.classList.contains('open') ? '\u2715' : '\u2630';
        });

        document.addEventListener('click', function(e) {
            if (!nav.contains(e.target) && !toggle.contains(e.target)) {
                nav.classList.remove('open');
                toggle.textContent = '\u2630';
            }
        });
    }

    /* --- Toast Notifications --- */
    function showToast(message, type) {
        type = type || 'success';
        var existing = document.querySelector('.toast');
        if (existing) existing.remove();

        var toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        var icon = type === 'success' ? '\u2713' : '\u2717';
        toast.innerHTML = '<span>' + icon + '</span> ' + message;
        document.body.appendChild(toast);

        requestAnimationFrame(function() {
            toast.classList.add('show');
        });

        setTimeout(function() {
            toast.classList.remove('show');
            setTimeout(function() { toast.remove(); }, 300);
        }, 3000);
    }

    /* --- Show toast for add-to-cart success --- */
    function initCartToast() {
        var addToCartForms = document.querySelectorAll('form[action="cart.php"]');
        addToCartForms.forEach(function(form) {
            var actionInput = form.querySelector('input[name="action"]');
            if (actionInput && actionInput.value === 'add') {
                form.addEventListener('submit', function() {
                    var card = form.closest('.product-card');
                    var title = '';
                    if (card) {
                        var titleEl = card.querySelector('.product-title');
                        if (titleEl) title = titleEl.textContent.trim();
                    }
                    if (title) {
                        showToast(title + ' added to cart', 'success');
                    }
                });
            }
        });
    }

    /* --- Animate elements on scroll --- */
    function initScrollAnimations() {
        var animateElements = document.querySelectorAll(
            '.product-card, .order-card, .metric-card, .detail-card, .summary-card, .status-container, .auth-card'
        );
        if (!animateElements.length) return;

        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        animateElements.forEach(function(el) {
            observer.observe(el);
        });
    }

    /* --- Quantity input validation --- */
    function initQtyValidation() {
        var qtyInputs = document.querySelectorAll('.qty-input');
        qtyInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                var val = parseInt(this.value, 10);
                if (isNaN(val) || val < 1) this.value = 1;
                if (val > 999) this.value = 999;
            });
        });
    }

    /* --- Smooth confirmation for delete actions --- */
    function initDeleteConfirm() {
        var deleteLinks = document.querySelectorAll('[onclick*="confirm"]');
        deleteLinks.forEach(function(link) {
            link.style.transition = 'opacity 0.2s';
            link.addEventListener('mouseenter', function() { this.style.opacity = '0.7'; });
            link.addEventListener('mouseleave', function() { this.style.opacity = '1'; });
        });
    }

    /* --- Payment method card highlight --- */
    function initPaymentCards() {
        var paymentCards = document.querySelectorAll('.payment-card');
        paymentCards.forEach(function(card) {
            var radio = card.querySelector('input[type="radio"]');
            if (!radio) return;

            radio.addEventListener('change', function() {
                paymentCards.forEach(function(c) { c.style.borderColor = ''; });
                if (radio.checked) {
                    card.style.borderColor = 'var(--accent)';
                }
            });

            if (radio.checked) {
                card.style.borderColor = 'var(--accent)';
            }
        });
    }

    /* --- Stagger animation for product grid --- */
    function initGridStagger() {
        var cards = document.querySelectorAll('.product-card');
        cards.forEach(function(card, i) {
            card.style.animationDelay = (i * 0.05) + 's';
        });
    }

    /* --- Categories dropdown (click toggle, mobile friendly) --- */
    function initNavDropdown() {
        var dropdowns = document.querySelectorAll('.nav-dropdown');
        dropdowns.forEach(function(dd) {
            var toggle = dd.querySelector('.nav-dropdown-toggle');
            if (!toggle) return;

            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropdowns.forEach(function(other) {
                    if (other !== dd) other.classList.remove('open');
                });
                dd.classList.toggle('open');
                toggle.setAttribute('aria-expanded', dd.classList.contains('open'));
            });
        });

        document.addEventListener('click', function(e) {
            dropdowns.forEach(function(dd) {
                if (!dd.contains(e.target)) {
                    dd.classList.remove('open');
                    var t = dd.querySelector('.nav-dropdown-toggle');
                    if (t) t.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }

    /* --- Header shadow on scroll --- */
    function initHeaderScroll() {
        var header = document.querySelector('.site-header');
        if (!header) return;

        function onScroll() {
            header.classList.toggle('scrolled', window.scrollY > 8);
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* --- Initialize everything on DOM ready --- */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        initMobileNav();
        initNavDropdown();
        initHeaderScroll();
        initCartToast();
        initScrollAnimations();
        initQtyValidation();
        initDeleteConfirm();
        initPaymentCards();
        initGridStagger();

        // Expose showToast globally for manual use
        window.showToast = showToast;
    }
})();
