/**
 * JavaScript FrontOffice
 * Guerre Iran - Site d'information
 */

document.addEventListener('DOMContentLoaded', function () {
    const header = document.querySelector('.site-header');

    // Menu mobile toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    const navBackdrop = document.querySelector('.nav-backdrop');

    if (menuToggle && mainNav) {
        const closeMobileMenu = function () {
            mainNav.classList.remove('open');
            menuToggle.classList.remove('open');
            menuToggle.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('no-scroll');
            if (header) {
                header.classList.remove('menu-open');
            }
        };

        menuToggle.addEventListener('click', function () {
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', String(!expanded));
            this.classList.toggle('open');
            mainNav.classList.toggle('open');
            document.body.classList.toggle('no-scroll');
            if (header) {
                header.classList.toggle('menu-open');
            }
        });

        // Fermer le menu en cliquant à l'extérieur
        document.addEventListener('click', function (e) {
            if (!mainNav.contains(e.target) && !menuToggle.contains(e.target)) {
                closeMobileMenu();
            }
        });

        if (navBackdrop) {
            navBackdrop.addEventListener('click', closeMobileMenu);
        }

        // Fermer le menu avec Echap
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        });

        // Fermer le menu après clic sur un lien en mobile
        mainNav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                closeMobileMenu();
            });
        });
    }

    // Lazy loading des images (fallback pour anciens navigateurs)
    if ('loading' in HTMLImageElement.prototype) {
        // Browser supports native lazy loading
    } else {
        // Fallback pour les anciens navigateurs
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');

        const lazyImageObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    const lazyImage = entry.target;
                    lazyImage.src = lazyImage.dataset.src || lazyImage.src;
                    lazyImageObserver.unobserve(lazyImage);
                }
            });
        });

        lazyImages.forEach(function (lazyImage) {
            lazyImageObserver.observe(lazyImage);
        });
    }

    // Smooth scroll pour les ancres
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Header sticky avec effet au scroll
    window.addEventListener('scroll', function () {
        const currentScroll = window.pageYOffset;

        if (header && currentScroll > 40) {
            header.classList.add('scrolled');
        } else if (header) {
            header.classList.remove('scrolled');
        }
    });

    // Lien actif de navigation selon l'URL courante
    const navLinks = document.querySelectorAll('.main-nav .nav-link');
    if (navLinks.length) {
        const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';

        navLinks.forEach(function (link) {
            const url = new URL(link.href, window.location.origin);
            const linkPath = url.pathname.replace(/\/+$/, '') || '/';

            if (currentPath === linkPath) {
                link.classList.add('active');
                link.setAttribute('aria-current', 'page');
            }
        });
    }

});
