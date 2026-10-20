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
    | 5. Ambient Eye-Comfortable Aura & Scroll Progress Tracker
    |--------------------------------------------------------------------------
    */
    const aura = document.getElementById('nexusAura');
    if (aura && !prefersReducedMotion) {
        let currentX = -1000, currentY = -1000;
        let targetX = -1000, targetY = -1000;

        window.addEventListener('mousemove', (e) => {
            targetX = e.clientX;
            targetY = e.clientY;
        }, { passive: true });

        const updateAura = () => {
            currentX += (targetX - currentX) * 0.1;
            currentY += (targetY - currentY) * 0.1;
            aura.style.transform = `translate3d(${currentX}px, ${currentY}px, 0)`;
            requestAnimationFrame(updateAura);
        };
        updateAura();
    }

    const progressBar = document.getElementById('scrollProgressBar');
    if (progressBar) {
        window.addEventListener('scroll', () => {
            const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
            const progress = maxScroll > 0 ? (window.scrollY / maxScroll) * 100 : 0;
            progressBar.style.width = `${progress}%`;
        }, { passive: true });
    }

    /*
    |--------------------------------------------------------------------------
    | 6. Ultra-Smooth 3D Card Tilt Physics ([data-tilt])
    |--------------------------------------------------------------------------
    */
    if (!prefersReducedMotion) {
        document.querySelectorAll('[data-tilt]').forEach((card) => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = ((y - centerY) / centerY) * -6;
                const rotateY = ((x - centerX) / centerX) * 6;
                card.style.transform = `perspective(1000px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) scale3d(1.015, 1.015, 1.015)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
            });
        });
    }


    /*
    |--------------------------------------------------------------------------
    | 8. Cosmic Celestial Particle Canvas with Gravity Well & Ripple Physics
    |--------------------------------------------------------------------------
    */
    const canvas = document.getElementById('cosmicCanvas');
    if (canvas && !prefersReducedMotion) {
        const ctx = canvas.getContext('2d');
        let width, height;
        let particles = [];
        let shockwaves = [];
        let mouseX = -1000, mouseY = -1000;

        const resize = () => {
            width = canvas.parentElement.offsetWidth;
            height = canvas.parentElement.offsetHeight;
            canvas.width = width;
            canvas.height = height;
        };

        resize();
        window.addEventListener('resize', resize, { passive: true });

        canvas.parentElement.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            mouseX = e.clientX - rect.left;
            mouseY = e.clientY - rect.top;
        }, { passive: true });

        canvas.parentElement.addEventListener('mouseleave', () => {
            mouseX = -1000;
            mouseY = -1000;
        });

        // Click creates a soft cosmic shockwave
        canvas.parentElement.addEventListener('click', (e) => {
            const rect = canvas.getBoundingClientRect();
            shockwaves.push({
                x: e.clientX - rect.left,
                y: e.clientY - rect.top,
                radius: 5,
                maxRadius: 160,
                alpha: 0.5
            });
        });

        const particleCount = Math.min(55, Math.floor((width * height) / 16000));

        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - 0.5) * 0.4,
                vy: (Math.random() - 0.5) * 0.4,
                radius: Math.random() * 1.8 + 0.6,
                color: Math.random() > 0.4 ? 'rgba(56, 189, 248, ' : 'rgba(168, 85, 247, ',
                alpha: Math.random() * 0.5 + 0.2,
                alphaSpeed: (Math.random() * 0.012 + 0.004) * (Math.random() > 0.5 ? 1 : -1)
            });
        }

        const render = () => {
            ctx.clearRect(0, 0, width, height);

            // Render shockwaves
            for (let s = shockwaves.length - 1; s >= 0; s--) {
                const sw = shockwaves[s];
                sw.radius += 3.5;
                sw.alpha *= 0.94;

                ctx.beginPath();
                ctx.arc(sw.x, sw.y, sw.radius, 0, Math.PI * 2);
                ctx.strokeStyle = `rgba(56, 189, 248, ${sw.alpha * 0.4})`;
                ctx.lineWidth = 2;
                ctx.stroke();

                if (sw.alpha < 0.02 || sw.radius > sw.maxRadius) {
                    shockwaves.splice(s, 1);
                }
            }

            for (let i = 0; i < particles.length; i++) {
                const p = particles[i];

                // Mouse Gravity Well (soft pull towards cursor, comfortable on eyes)
                if (mouseX > 0 && mouseY > 0) {
                    const dx = mouseX - p.x;
                    const dy = mouseY - p.y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < 130 && dist > 10) {
                        const force = (1 - dist / 130) * 0.35;
                        p.x += (dx / dist) * force;
                        p.y += (dy / dist) * force;
                    }
                }

                p.x += p.vx;
                p.y += p.vy;

                if (p.x < 0) p.x = width;
                if (p.x > width) p.x = 0;
                if (p.y < 0) p.y = height;
                if (p.y > height) p.y = 0;

                p.alpha += p.alphaSpeed;
                if (p.alpha > 0.8 || p.alpha < 0.15) {
                    p.alphaSpeed = -p.alphaSpeed;
                }

                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                ctx.fillStyle = p.color + p.alpha + ')';
                ctx.fill();

                // Gentle constellation connection lines
                for (let j = i + 1; j < particles.length; j++) {
                    const p2 = particles[j];
                    const dx = p.x - p2.x;
                    const dy = p.y - p2.y;
                    const dist = Math.sqrt(dx * dx + dy * dy);

                    if (dist < 110) {
                        ctx.beginPath();
                        ctx.moveTo(p.x, p.y);
                        ctx.lineTo(p2.x, p2.y);
                        ctx.strokeStyle = `rgba(56, 189, 248, ${(1 - dist / 110) * 0.16})`;
                        ctx.lineWidth = 0.75;
                        ctx.stroke();
                    }
                }
            }

            requestAnimationFrame(render);
        };

        render();
    }

    /*
    |--------------------------------------------------------------------------
    | 9. Interactive Skill-Compatibility Simulator (Sandbox)
    |--------------------------------------------------------------------------
    */
    const sandboxChips = document.querySelectorAll('.sandbox-chip');
    const sandboxPercentEl = document.getElementById('sandboxPercent');
    const sandboxProgressRing = document.getElementById('sandboxProgressRing');
    const sandboxFeedbackEl = document.getElementById('sandboxFeedback');

    if (sandboxChips.length > 0 && sandboxPercentEl && sandboxProgressRing) {
        const targetProjectSkills = ['python', 'machine learning', 'c++', 'robotics'];

        const updateSandbox = () => {
            const activeSkills = Array.from(document.querySelectorAll('.sandbox-chip.active'))
                .map(chip => chip.getAttribute('data-skill').toLowerCase());

            const matchedSkills = targetProjectSkills.filter(s => activeSkills.includes(s));
            const matchPercent = Math.round((matchedSkills.length / targetProjectSkills.length) * 100);

            const circumference = 251.3;
            const offset = circumference - (matchPercent / 100) * circumference;
            sandboxProgressRing.style.strokeDashoffset = offset;
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
