/**
 * Premium Interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Sticky Navbar
    const navbar = document.querySelector('.landing-navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 30) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // 2. Scroll Reveals
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
        const targetAttr = el.getAttribute('data-target');
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

    // 3. FAQ Accordion
    const faqCards = document.querySelectorAll('.faq-card');
    faqCards.forEach(card => {
        const header = card.querySelector('.faq-header');
        header.addEventListener('click', () => {
            faqCards.forEach(c => {
                if (c !== card) c.classList.remove('active');
            });
            card.classList.toggle('active');
        });
    });

    // 4. Duplicate Carousels for Seamless Scroll
    const testiTrack = document.querySelector('.testimonial-track');
    if (testiTrack) testiTrack.innerHTML += testiTrack.innerHTML;

    const partnerTrack = document.querySelector('.partner-track');
    if (partnerTrack) partnerTrack.innerHTML += partnerTrack.innerHTML;

    // 5. Mouse Parallax for Hero Elements
    const heroScene = document;
    if (heroScene) {
        const parallaxElements = document.querySelectorAll('[data-depth]');
        
        heroScene.addEventListener('mousemove', (e) => {
            // Calculate mouse position relative to center
            const x = (e.clientX - window.innerWidth / 2) / 100;
            const y = (e.clientY - window.innerHeight / 2) / 100;
            
            requestAnimationFrame(() => {
                parallaxElements.forEach(el => {
                    const depth = parseFloat(el.getAttribute('data-depth'));
                    const translateX = x * depth * -100;
                    const translateY = y * depth * -100;
                    
                    // Keep existing transforms if element is animated
                    el.style.transform = `translate3d(${translateX}px, ${translateY}px, 0)`;
                });
            });
        });
        
        // Reset on mouse leave
        heroScene.addEventListener('mouseleave', () => {
            requestAnimationFrame(() => {
                parallaxElements.forEach(el => {
                    el.style.transform = `translate3d(0, 0, 0)`;
                });
            });
        });
    }

    // Trigger counters for premium glass cards on load since they are in hero
    setTimeout(() => {
        document.querySelectorAll('.premium-glass-card .counter').forEach(counter => {
            animateCounter(counter);
        });
    }, 500);
});

