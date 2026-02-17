/**
 * User Dashboard - Centralized JavaScript
 * Handles sidebar navigation, animations, and interactive features
 */

(function() {
    'use strict';

    // AOS removed from dashboard for improved scroll performance

    /**
     * Sidebar Toggle Functionality
     */
    function initSidebar() {
        const sidebar = document.getElementById('dashboard-sidebar');
        const overlay = document.getElementById('dashboard-sidebar-overlay');
        const openButtons = document.querySelectorAll('[data-open-sidebar]');
        const closeButtons = document.querySelectorAll('[data-close-sidebar]');

        if (!sidebar) return;

        function openSidebar() {
            sidebar.classList.add('active');
            if (overlay) {
                overlay.classList.add('active');
                overlay.setAttribute('aria-hidden', 'false');
            }
            openButtons.forEach(btn => btn.setAttribute('aria-expanded', 'true'));
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            if (overlay) {
                overlay.classList.remove('active');
                overlay.setAttribute('aria-hidden', 'true');
            }
            openButtons.forEach(btn => btn.setAttribute('aria-expanded', 'false'));
            document.body.style.overflow = '';
        }

        // Event listeners
        openButtons.forEach(btn => btn.addEventListener('click', openSidebar));
        closeButtons.forEach(btn => btn.addEventListener('click', closeSidebar));
        if (overlay) overlay.addEventListener('click', closeSidebar);
        
        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeSidebar();
        });

        // Close sidebar on window resize to desktop
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (window.innerWidth >= 1024) {
                    closeSidebar();
                }
            }, 250);
        });
    }

    /**
     * Smooth scroll to anchor links
     */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href === '#') return;
                
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    /**
     * Auto-hide alerts after a delay
     */
    function initAlerts() {
        const alerts = document.querySelectorAll('.ud-alert[data-auto-dismiss]');
        alerts.forEach(alert => {
            const delay = parseInt(alert.getAttribute('data-auto-dismiss')) || 5000;
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            }, delay);
        });
    }

    /**
     * Form validation helpers
     */
    function initFormValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
                
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('error');
                        
                        // Add error message if not exists
                        if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('ud-form-error')) {
                            const error = document.createElement('span');
                            error.className = 'ud-form-error';
                            error.textContent = 'Field ini wajib diisi';
                            input.parentNode.insertBefore(error, input.nextSibling);
                        }
                    } else {
                        input.classList.remove('error');
                        const error = input.nextElementSibling;
                        if (error && error.classList.contains('ud-form-error')) {
                            error.remove();
                        }
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
            
            // Remove error on input
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.classList.remove('error');
                    const error = this.nextElementSibling;
                    if (error && error.classList.contains('ud-form-error')) {
                        error.remove();
                    }
                });
            });
        });
    }

    /**
     * Lazy load images
     */
    function initLazyLoad() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                        }
                        observer.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    /**
     * Add loading state to buttons
     * Note: This is disabled for settings page forms and logout forms
     */
    function initButtonLoading() {
        // Skip button loading for settings page forms to avoid conflicts with custom validation
        const settingsForms = document.querySelectorAll('#profile-form, #password-form');
        if (settingsForms.length > 0) {
            console.log('Settings page detected, skipping button loading initialization');
            return; // Don't add loading state to settings page buttons
        }

        // Apply loading state on form submission to avoid cancelling native submit
        const forms = document.querySelectorAll('form:not([data-no-loading])');
        forms.forEach(form => {
            // Skip logout forms entirely
            if (form.action && form.action.includes('/logout')) return;

            form.addEventListener('submit', function(e) {
                // If some other handler prevented submission (e.g., validation), do nothing
                if (e.defaultPrevented) return;

                // Determine the submitter button (supported in modern browsers)
                const submitter = e.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
                if (!submitter) return;

                // Respect opt-out on the submitter
                if (submitter.matches('[data-no-loading]')) return;

                // Add loading state without preventing default form submission
                submitter.classList.add('loading');
                submitter.disabled = true;
                const originalText = submitter.innerHTML;
                submitter.setAttribute('data-original-html', originalText);
                submitter.innerHTML = '<svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Loading...';

                // Reset after 10 seconds as fallback if navigation did not happen
                setTimeout(() => {
                    if (!document.body.contains(submitter)) return; // likely navigated
                    submitter.classList.remove('loading');
                    submitter.disabled = false;
                    submitter.innerHTML = submitter.getAttribute('data-original-html') || originalText;
                }, 10000);
            });
        });

        // Also support anchor elements with explicit loading behavior
        const loadingLinks = document.querySelectorAll('a[data-loading]');
        loadingLinks.forEach(link => {
            link.addEventListener('click', function() {
                this.classList.add('loading');
            });
        });
    }

    /**
     * Initialize tooltips
     */
    function initTooltips() {
        const tooltips = document.querySelectorAll('[data-tooltip]');
        
        tooltips.forEach(element => {
            element.addEventListener('mouseenter', function() {
                const text = this.getAttribute('data-tooltip');
                const tooltip = document.createElement('div');
                tooltip.className = 'ud-tooltip';
                tooltip.textContent = text;
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.top = `${rect.top - tooltip.offsetHeight - 8}px`;
                tooltip.style.left = `${rect.left + (rect.width - tooltip.offsetWidth) / 2}px`;
                
                this._tooltip = tooltip;
            });
            
            element.addEventListener('mouseleave', function() {
                if (this._tooltip) {
                    this._tooltip.remove();
                    this._tooltip = null;
                }
            });
        });
    }

    /**
     * Initialize all features when DOM is ready
     */
    function init() {
        initSidebar();
        initSmoothScroll();
        initAlerts();
        initFormValidation();
        initLazyLoad();
        initButtonLoading();
        initTooltips();
        
        // Log initialization
        console.log('User Dashboard initialized successfully');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();

