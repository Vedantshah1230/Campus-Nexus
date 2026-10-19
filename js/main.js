/**
 * Campus Nexus – Master Vanilla JavaScript
 * Lightweight, accessible, no third-party dependencies.
 */
document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | 1. Mobile Navigation Drawer Toggle
    |--------------------------------------------------------------------------
    */
    const navToggle = document.getElementById('navToggle');
    const primaryNav = document.getElementById('primaryNav');

    if (navToggle && primaryNav) {
        navToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = primaryNav.classList.contains('is-open');
            const nextState = !isOpen;

            primaryNav.classList.toggle('is-open', nextState);
            navToggle.classList.toggle('is-active', nextState);
            navToggle.setAttribute('aria-expanded', String(nextState));
        });

        // Close when pressing Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && primaryNav.classList.contains('is-open')) {
                primaryNav.classList.remove('is-open');
                navToggle.classList.remove('is-active');
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.focus();
            }
        });

        // Close when clicking outside of the navigation
        document.addEventListener('click', (e) => {
            if (
                primaryNav.classList.contains('is-open') &&
                !primaryNav.contains(e.target) &&
                !navToggle.contains(e.target)
            ) {
                primaryNav.classList.remove('is-open');
                navToggle.classList.remove('is-active');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Close menu when a link inside mobile nav is clicked
        primaryNav.querySelectorAll('.nav-link, .btn').forEach((link) => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 880) {
                    primaryNav.classList.remove('is-open');
                    navToggle.classList.remove('is-active');
                    navToggle.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Alert Dismissal with Smooth Transition
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('.alert-close').forEach((button) => {
        button.addEventListener('click', () => {
            const alertBox = button.closest('.alert');
            if (alertBox) {
                alertBox.style.opacity = '0';
                alertBox.style.transform = 'translateY(-6px)';
                setTimeout(() => {
                    alertBox.remove();
                }, 200);
            }
        });
    });

    /*
    |--------------------------------------------------------------------------
    | 3. Motion-Safe Scroll Reveal Utility
    |--------------------------------------------------------------------------
    */
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!prefersReducedMotion && 'IntersectionObserver' in window) {
        const revealTargets = document.querySelectorAll('.reveal-on-scroll');

        if (revealTargets.length > 0) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('reveal-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.12,
                rootMargin: '0px 0px -40px 0px'
            });

            revealTargets.forEach((target) => {
                target.classList.add('reveal-init');
                observer.observe(target);
            });
        }
    }

});
