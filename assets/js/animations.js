/**
 * Purpose: JavaScript based animations and effects utilities
 * Author: Senior Software Architect
 * Project Name: NGO Donation & Volunteer Management System
 * Module: Assets
 * Version: 1.1.0
 */

const AnimationEngine = {
    init() {
        this.initScrollReveal();
        this.initLazyReveal();
        this.initCounterAnimation();
        this.initProgressAnimation();
        this.initSmoothScrollHelper();
        this.initCardHoverEffects();
    },

    /* ==========================================================================
       SCROLL REVEAL (FADE UP)
       ========================================================================== */
    initScrollReveal() {
        const revealElements = document.querySelectorAll('.reveal-on-scroll');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('anim-fade-up');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: "0px 0px -50px 0px" });

        revealElements.forEach(el => {
            el.style.opacity = '0'; // Ensure it's hidden before animation
            observer.observe(el);
        });
    },

    /* ==========================================================================
       LAZY REVEAL (STAGGERED)
       ========================================================================== */
    initLazyReveal() {
        const revealGroups = document.querySelectorAll('.lazy-reveal-group');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const children = entry.target.querySelectorAll('.lazy-item');
                    this.staggerAnimation(children, 'anim-fade-up', 100);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        revealGroups.forEach(group => {
            const children = group.querySelectorAll('.lazy-item');
            children.forEach(c => c.style.opacity = '0');
            observer.observe(group);
        });
    },

    staggerAnimation(elements, animationClass, delay = 100) {
        elements.forEach((el, index) => {
            setTimeout(() => {
                el.classList.add(animationClass);
            }, index * delay);
        });
    },

    /* ==========================================================================
       COUNTER ANIMATION
       ========================================================================== */
    initCounterAnimation() {
        const counters = document.querySelectorAll('.counter-anim');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateValue(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(counter => observer.observe(counter));
    },

    animateValue(obj) {
        const start = 0;
        const end = parseInt(obj.getAttribute('data-target'), 10) || parseInt(obj.innerText.replace(/,/g, ''), 10);
        const duration = 2000;
        let startTimestamp = null;
        
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            obj.innerHTML = Math.floor(progress * (end - start) + start).toLocaleString();
            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                obj.innerHTML = end.toLocaleString();
            }
        };
        window.requestAnimationFrame(step);
    },

    /* ==========================================================================
       PROGRESS BAR ANIMATION
       ========================================================================== */
    initProgressAnimation() {
        const progressBars = document.querySelectorAll('.progress-fill');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const targetWidth = entry.target.getAttribute('data-width');
                    if(targetWidth) {
                        entry.target.style.width = targetWidth + '%';
                    }
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        progressBars.forEach(bar => {
            bar.style.width = '0%';
            observer.observe(bar);
        });
    },

    /* ==========================================================================
       SMOOTH SCROLL HELPER
       ========================================================================== */
    initSmoothScrollHelper() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const targetId = this.getAttribute('href');
                if(targetId === '#' || !targetId) return;
                
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
    },

    /* ==========================================================================
       CARD HOVER EFFECTS (3D Tilt / Glare)
       ========================================================================== */
    initCardHoverEffects() {
        // Subtle tilt effect for premium cards (optional usage via .tilt-card class)
        const tiltCards = document.querySelectorAll('.tilt-card');
        tiltCards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = ((y - centerY) / centerY) * -5; // max 5deg
                const rotateY = ((x - centerX) / centerX) * 5;
                
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = `perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)`;
                card.style.transition = 'transform 0.5s ease';
            });
            
            card.addEventListener('mouseenter', () => {
                card.style.transition = 'none';
            });
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    AnimationEngine.init();
});
