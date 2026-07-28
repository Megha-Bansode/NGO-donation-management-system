/**
 * Premium Interactions & Polished Landing Page Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Sticky Navbar & Active Link Highlight (Scrollspy)
    const navbar = document.querySelector('.landing-navbar');
    const sections = document.querySelectorAll('header[id], section[id]');
    const navLinks = document.querySelectorAll('.nav-links .nav-link');

    function handleScroll() {
        if (navbar) {
            if (window.scrollY > 30) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }

        let currentSection = '';
        const scrollPosition = window.pageYOffset + 120;

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                currentSection = section.getAttribute('id');
            }
        });

        if (currentSection) {
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${currentSection}`) {
                    link.classList.add('active');
                }
            });
        }
    }

    window.addEventListener('scroll', handleScroll);
    handleScroll();

    // 2. Mobile Menu Toggle
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('.navbar-menu');
    if (mobileBtn && navMenu) {
        mobileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            navMenu.classList.toggle('active');
            const icon = mobileBtn.querySelector('i');
            if (icon) {
                if (navMenu.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });

        document.addEventListener('click', (e) => {
            if (!navMenu.contains(e.target) && !mobileBtn.contains(e.target)) {
                navMenu.classList.remove('active');
                const icon = mobileBtn.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });

        navMenu.querySelectorAll('.nav-link, .btn').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                const icon = mobileBtn.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        });
    }

    // 3. Hero Dots Interactivity
    const heroDots = document.querySelectorAll('.hero-dot');
    heroDots.forEach((dot) => {
        dot.addEventListener('click', () => {
            heroDots.forEach(d => d.classList.remove('active'));
            dot.classList.add('active');
        });
    });

    // 4. Scroll Reveals & Animated Counters
    const animatedElements = document.querySelectorAll('[data-animate]');
    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                obs.unobserve(entry.target);
                
                // Counters
                if (entry.target.classList.contains('impact-card')) {
                    const counter = entry.target.querySelector('.counter');
                    if (counter) animateCounter(counter);
                }
                
                // Progress bars
                if (entry.target.classList.contains('campaign-card')) {
                    const bar = entry.target.querySelector('.fund-bar-fill');
                    if (bar) {
                        const percent = bar.getAttribute('data-percent');
                        bar.style.width = percent + '%';
                    }
                }
            }
        });
    }, { threshold: 0.15, rootMargin: "0px 0px -50px 0px" });

    animatedElements.forEach(el => observer.observe(el));

    function animateCounter(el) {
        if (el.dataset.animated) return;
        el.dataset.animated = "true";
        const targetAttr = el.getAttribute('data-target');
        if (!targetAttr) return;
        const isFloat = targetAttr.includes('.');
        const target = parseFloat(targetAttr);
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;

        const update = () => {
            current += step;
            if (current < target) {
                el.innerText = isFloat ? current.toFixed(1) : Math.ceil(current).toLocaleString();
                requestAnimationFrame(update);
            } else {
                el.innerText = isFloat ? target.toFixed(1) : target.toLocaleString();
            }
        };
        update();
    }

    // 5. FAQ Accordion
    const faqCards = document.querySelectorAll('.faq-card');
    faqCards.forEach(card => {
        const header = card.querySelector('.faq-header');
        if (header) {
            header.addEventListener('click', () => {
                faqCards.forEach(c => {
                    if (c !== card) c.classList.remove('active');
                });
                card.classList.toggle('active');
            });
        }
    });

    // 6. Duplicate Carousels for Seamless Continuous Scroll
    const testiTrack = document.querySelector('.testimonial-track');
    if (testiTrack && !testiTrack.dataset.duplicated) {
        testiTrack.dataset.duplicated = "true";
        testiTrack.innerHTML += testiTrack.innerHTML;
    }

    const partnerTrack = document.querySelector('.partner-track');
    if (partnerTrack && !partnerTrack.dataset.duplicated) {
        partnerTrack.dataset.duplicated = "true";
        partnerTrack.innerHTML += partnerTrack.innerHTML;
    }

    // 7. Mouse Parallax for Hero Elements (Desktop / Non-touch only)
    const isDesktop = window.innerWidth > 1024 && matchMedia('(hover: hover)').matches;
    if (isDesktop) {
        const parallaxElements = document.querySelectorAll('[data-depth]:not(.premium-glass-card)');
        
        window.addEventListener('mousemove', (e) => {
            const x = (e.clientX - window.innerWidth / 2) / 100;
            const y = (e.clientY - window.innerHeight / 2) / 100;
            
            requestAnimationFrame(() => {
                parallaxElements.forEach(el => {
                    const depth = parseFloat(el.getAttribute('data-depth'));
                    const translateX = x * depth * -100;
                    const translateY = y * depth * -100;
                    el.style.transform = `translate3d(${translateX}px, ${translateY}px, 0)`;
                });
            });
        });
        
        window.addEventListener('mouseleave', () => {
            requestAnimationFrame(() => {
                parallaxElements.forEach(el => {
                    el.style.transform = `translate3d(0, 0, 0)`;
                });
            });
        });
    }

    // 8. Contact & Newsletter Form Interactivity Feedback
    const nlForm = document.querySelector('.nl-form form');
    if (nlForm) {
        nlForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const btn = nlForm.querySelector('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = `<i class="fas fa-check"></i> Subscribed!`;
            btn.style.background = '#10B981';
            nlForm.reset();
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '';
            }, 3000);
        });
    }

    const contactForm = document.querySelector('.contact-form form');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const btn = contactForm.querySelector('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = `<i class="fas fa-check-circle"></i> Message Sent!`;
            btn.style.background = '#10B981';
            contactForm.reset();
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '';
            }, 3000);
        });
    }

    // Trigger counters for premium glass cards on load
    setTimeout(() => {
        document.querySelectorAll('.premium-glass-card .counter').forEach(counter => {
            animateCounter(counter);
        });
    }, 500);
});
