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

    /*
    |--------------------------------------------------------------------------
    | 4. Global Command Palette / Quick Search (Ctrl + K)
    |--------------------------------------------------------------------------
    */
    const cmdPalette = document.getElementById('cmdPalette');
    const openCmdBtn = document.getElementById('openCommandPalette');
    const cmdInput = document.getElementById('cmdPaletteInput');
    const cmdResults = document.getElementById('cmdPaletteResults');

    if (cmdPalette && cmdInput && cmdResults) {
        const openPalette = () => {
            cmdPalette.style.display = 'flex';
            cmdInput.value = '';
            filterCmdItems('');
            setTimeout(() => cmdInput.focus(), 50);
        };

        const closePalette = () => {
            cmdPalette.style.display = 'none';
        };

        if (openCmdBtn) {
            openCmdBtn.addEventListener('click', openPalette);
        }

        // Global Keyboard Shortcut: Ctrl + K or Cmd + K
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                if (cmdPalette.style.display === 'none' || !cmdPalette.style.display) {
                    openPalette();
                } else {
                    closePalette();
                }
            } else if (e.key === 'Escape' && cmdPalette.style.display !== 'none') {
                closePalette();
            }
        });

        // Close on backdrop click
        cmdPalette.addEventListener('click', (e) => {
            if (e.target === cmdPalette) {
                closePalette();
            }
        });

        // Filter items
        const filterCmdItems = (query) => {
            const cleanQuery = query.toLowerCase().trim();
            const items = cmdResults.querySelectorAll('.cmd-item');
            let hasVisible = false;

            items.forEach((item) => {
                const text = item.textContent.toLowerCase();
                if (cleanQuery === '' || text.includes(cleanQuery)) {
                    item.style.display = 'flex';
                    hasVisible = true;
                } else {
                    item.style.display = 'none';
                }
            });

            // Handle empty search state
            let noResultMsg = cmdResults.querySelector('.cmd-no-results');
            if (!hasVisible) {
                if (!noResultMsg) {
                    noResultMsg = document.createElement('div');
                    noResultMsg.className = 'cmd-no-results';
                    noResultMsg.style.padding = '24px';
                    noResultMsg.style.textAlign = 'center';
                    noResultMsg.style.color = 'var(--muted)';
                    noResultMsg.innerHTML = '🔍 No matching destinations found. Try searching <em>projects</em>, <em>skills</em>, <em>ideas</em>, or <em>events</em>.';
                    cmdResults.appendChild(noResultMsg);
                }
                noResultMsg.style.display = 'block';
            } else if (noResultMsg) {
                noResultMsg.style.display = 'none';
            }
        };

        cmdInput.addEventListener('input', (e) => {
            filterCmdItems(e.target.value);
        });

        // Arrow Key Selection in Palette
        cmdInput.addEventListener('keydown', (e) => {
            const visibleItems = Array.from(cmdResults.querySelectorAll('.cmd-item')).filter(
                (item) => item.style.display !== 'none'
            );
            if (visibleItems.length === 0) return;

            let currentIndex = visibleItems.findIndex((item) => item.classList.contains('selected'));

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (currentIndex >= 0) visibleItems[currentIndex].classList.remove('selected');
                const nextIndex = (currentIndex + 1) % visibleItems.length;
                visibleItems[nextIndex].classList.add('selected');
                visibleItems[nextIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (currentIndex >= 0) visibleItems[currentIndex].classList.remove('selected');
                const prevIndex = (currentIndex - 1 + visibleItems.length) % visibleItems.length;
                visibleItems[prevIndex].classList.add('selected');
                visibleItems[prevIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const selected = visibleItems.find((item) => item.classList.contains('selected')) || visibleItems[0];
                if (selected) {
                    window.location.href = selected.getAttribute('href');
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Cosmic Celestial Particle Canvas
    |--------------------------------------------------------------------------
    */
    const canvas = document.getElementById('cosmicCanvas');
    if (canvas && !prefersReducedMotion) {
        const ctx = canvas.getContext('2d');
        let width, height;
        let particles = [];
        let mouseX = -1000, mouseY = -1000;

        const resize = () => {
            width = canvas.parentElement.offsetWidth;
            height = canvas.parentElement.offsetHeight;
            canvas.width = width;
            canvas.height = height;
        };

        resize();
        window.addEventListener('resize', resize);

        canvas.parentElement.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            mouseX = e.clientX - rect.left;
            mouseY = e.clientY - rect.top;
        });

        canvas.parentElement.addEventListener('mouseleave', () => {
            mouseX = -1000;
            mouseY = -1000;
        });

        const particleCount = Math.min(50, Math.floor((width * height) / 18000));

        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - 0.5) * 0.45,
                vy: (Math.random() - 0.5) * 0.45,
                radius: Math.random() * 1.8 + 0.6,
                color: Math.random() > 0.4 ? 'rgba(56, 189, 248, ' : 'rgba(168, 85, 247, ',
                alpha: Math.random() * 0.6 + 0.2,
                alphaSpeed: (Math.random() * 0.015 + 0.005) * (Math.random() > 0.5 ? 1 : -1)
            });
        }

        let animationFrameId;
        const render = () => {
            ctx.clearRect(0, 0, width, height);

            for (let i = 0; i < particles.length; i++) {
                const p = particles[i];

                p.x += p.vx;
                p.y += p.vy;

                if (p.x < 0) p.x = width;
                if (p.x > width) p.x = 0;
                if (p.y < 0) p.y = height;
                if (p.y > height) p.y = 0;

                p.alpha += p.alphaSpeed;
                if (p.alpha > 0.85 || p.alpha < 0.15) {
                    p.alphaSpeed = -p.alphaSpeed;
                }

                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                ctx.fillStyle = p.color + p.alpha + ')';
                ctx.fill();

                // Gentle connection lines
                for (let j = i + 1; j < particles.length; j++) {
                    const p2 = particles[j];
                    const dx = p.x - p2.x;
                    const dy = p.y - p2.y;
                    const dist = Math.sqrt(dx * dx + dy * dy);

                    if (dist < 100) {
                        ctx.beginPath();
                        ctx.moveTo(p.x, p.y);
                        ctx.lineTo(p2.x, p2.y);
                        ctx.strokeStyle = `rgba(56, 189, 248, ${(1 - dist / 100) * 0.14})`;
                        ctx.lineWidth = 0.75;
                        ctx.stroke();
                    }
                }
            }

            animationFrameId = requestAnimationFrame(render);
        };

        render();
    }

    /*
    |--------------------------------------------------------------------------
    | 6. Interactive Skill-Compatibility Simulator (Sandbox)
    |--------------------------------------------------------------------------
    */
    const sandboxChips = document.querySelectorAll('.sandbox-chip');
    const sandboxPercentEl = document.getElementById('sandboxPercent');
    const sandboxProgressRing = document.getElementById('sandboxProgressRing');
    const sandboxFeedbackEl = document.getElementById('sandboxFeedback');

    if (sandboxChips.length > 0 && sandboxPercentEl && sandboxProgressRing) {
        // Target project requires: Python, Machine Learning, ROS2, C++
        const targetProjectSkills = ['python', 'machine learning', 'c++', 'robotics'];

        const updateSandbox = () => {
            const activeSkills = Array.from(document.querySelectorAll('.sandbox-chip.active'))
                .map(chip => chip.getAttribute('data-skill').toLowerCase());

            const matchedSkills = targetProjectSkills.filter(s => activeSkills.includes(s));
            const matchPercent = Math.round((matchedSkills.length / targetProjectSkills.length) * 100);

            // Animate stroke dashoffset (circumference: 2 * PI * 40 = 251.3)
            const circumference = 251.3;
            const offset = circumference - (matchPercent / 100) * circumference;
            sandboxProgressRing.style.strokeDashoffset = offset;

            // Animate number count-up
            sandboxPercentEl.textContent = `${matchPercent}%`;

            if (matchPercent === 100) {
                sandboxFeedbackEl.innerHTML = '🌟 <strong>Perfect Match!</strong> You possess 100% of the core competencies for the Autonomous Rover project.';
                sandboxProgressRing.style.stroke = '#10b981';
            } else if (matchPercent >= 75) {
                sandboxFeedbackEl.innerHTML = '🚀 <strong>Strong Match!</strong> Highly competitive candidate. The team would eagerly welcome you.';
                sandboxProgressRing.style.stroke = 'var(--primary)';
            } else if (matchPercent >= 50) {
                sandboxFeedbackEl.innerHTML = '🤝 <strong>Good Foundation!</strong> You match 2 of 4 skills. Great opportunity to learn on the team.';
                sandboxProgressRing.style.stroke = 'var(--peach-500)';
            } else {
                sandboxFeedbackEl.innerHTML = '🌱 <strong>Learning Opportunity!</strong> Click additional skills like Python or Robotics to see compatibility climb.';
                sandboxProgressRing.style.stroke = '#94a3b8';
            }
        };

        sandboxChips.forEach(chip => {
            chip.addEventListener('click', () => {
                chip.classList.toggle('active');
                updateSandbox();
            });
        });

        updateSandbox();
    }

});
