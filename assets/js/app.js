/* ============================================
   Jester Scale Models - UI Enhancements
   ============================================ */

(function() {
    'use strict';

    document.documentElement.classList.add('js');

    /* --- Quantity stepper (cart) --- */
    function initCartStepper() {
        var forms = document.querySelectorAll('[data-cart-qty]');
        forms.forEach(function(form) {
            var input = form.querySelector('.qty-input');
            if (!input) return;

            form.querySelectorAll('.qty-step').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var dir = parseInt(btn.getAttribute('data-dir'), 10);
                    var val = parseInt(input.value, 10);
                    if (isNaN(val)) val = 1;
                    val += dir;
                    if (val < 1) val = 1;
                    if (val > 999) val = 999;
                    input.value = val;
                    form.submit();
                });
            });
        });
    }

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

    /* --- Show toast for add-to-cart success (non-AJAX forms) --- */
    function initCartToast() {
        var addToCartForms = document.querySelectorAll('form[action="cart.php"]:not([data-ajax-add])');
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

    /* --- Set cart badge count in nav --- */
    function setCartBadge(count) {
        var link = document.querySelector('.nav-cart');
        var badge = link ? link.querySelector('.cart-badge') : null;
        if (count > 0) {
            if (!badge && link) {
                badge = document.createElement('span');
                badge.className = 'cart-badge';
                link.appendChild(badge);
            }
            if (badge) badge.textContent = count;
        } else if (badge) {
            badge.remove();
        }
    }

    /* --- AJAX add-to-cart (home cards: toast only, no redirect) --- */
    function initAjaxAddToCart() {
        var forms = document.querySelectorAll('form[data-ajax-add]');
        forms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                var card = form.closest('.product-card');
                var title = '';
                if (card) {
                    var titleEl = card.querySelector('.product-title');
                    if (titleEl) title = titleEl.textContent.trim();
                }

                var btn = form.querySelector('button[type="submit"]');
                if (btn) btn.disabled = true;

                fetch(form.getAttribute('action'), {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form)
                }).then(function(res) {
                    if (!res.ok) throw new Error('Request failed');
                    return res.json().catch(function() { return null; });
                }).then(function(data) {
                    if (data && data.success) {
                        showToast(title ? title + ' added to cart' : 'Added to cart', 'success');
                        if (typeof data.cart_count === 'number') setCartBadge(data.cart_count);
                    } else {
                        showToast('Could not add to cart. Please try again.', 'error');
                    }
                }).catch(function() {
                    showToast('Could not add to cart. Please try again.', 'error');
                }).then(function() {
                    if (btn) btn.disabled = false;
                });
            });
        });
    }

    /* --- Animate elements on scroll --- */
    function initScrollAnimations() {
        var animateElements = document.querySelectorAll(
            '.product-card, .order-card, .metric-card, .detail-card, .summary-card, .status-container, .auth-card, .empty-state'
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
            if (el.classList.contains('metric-card')) {
                el.classList.add('animate-in-zoom');
            } else if (el.classList.contains('detail-card')) {
                el.classList.add('animate-in-left');
            } else if (el.classList.contains('summary-card')) {
                el.classList.add('animate-in-right');
            } else {
                el.classList.add('animate-in');
            }
            var parent = el.parentElement;
            var siblings = parent ? Array.prototype.slice.call(parent.children) : [el];
            var idx = siblings.indexOf(el);
            if (idx > 0) el.style.animationDelay = Math.min(idx * 0.07, 0.6) + 's';
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

    /* --- Theme toggle --- */
    function initThemeToggle() {
        var btn = document.querySelector('[data-theme-toggle]');
        if (!btn) return;

        btn.addEventListener('click', function() {
            var root = document.documentElement;
            var next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            root.setAttribute('data-theme', next);
            btn.setAttribute('aria-pressed', next === 'light');
            try {
                localStorage.setItem('theme', next);
            } catch (e) {}
        });
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
        initThemeToggle();
        initCartToast();
        initAjaxAddToCart();
        initCartStepper();
        initScrollAnimations();
        initQtyValidation();
        initDeleteConfirm();
        initPaymentCards();
        initGridStagger();

        // Expose showToast globally for manual use
        window.showToast = showToast;
    }
})();
