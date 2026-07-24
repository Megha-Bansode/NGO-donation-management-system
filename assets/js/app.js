/**
 * Purpose: Main application scripts, global interactions, and UI engines
 * Author: Senior Software Architect
 * Project Name: NGO Donation & Volunteer Management System
 * Module: Assets
 * Version: 1.1.0
 */

document.addEventListener('DOMContentLoaded', () => {
    App.init();
});

const App = {
    init() {
        this.initRippleEffectEngine();
        this.initDropdownEngine();
        this.initSidebarToggle();
        this.initMobileMenuToggle();
        this.initTabsEngine();
        this.initAccordionEngine();
        this.initTooltipEngine();
        this.initNavbarScrollDetection();
        this.initScrollProgressUtility();
        this.initBackToTopUtility();
        this.initClickOutsideDetection();
        this.initLoaderController();
        this.initLayoutResizeHandler();
    },

    initRippleEffectEngine() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn');
            if (!btn || btn.disabled || btn.classList.contains('disabled') || btn.classList.contains('btn-loading')) return;
            
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const ripple = document.createElement('span');
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            ripple.classList.add('anim-ripple');
            ripple.style.position = 'absolute';
            ripple.style.background = btn.classList.contains('btn-outline') || btn.classList.contains('btn-ghost') ? 'rgba(37,99,235,0.2)' : 'rgba(255,255,255,0.4)';
            ripple.style.borderRadius = '50%';
            ripple.style.transform = 'translate(-50%, -50%)';
            ripple.style.pointerEvents = 'none';
            
            btn.style.position = 'relative';
            btn.style.overflow = 'hidden';
            btn.appendChild(ripple);
            
            setTimeout(() => { ripple.remove(); }, 600);
        });
    },

    initDropdownEngine() {
        document.addEventListener('click', (e) => {
            const toggle = e.target.closest('.dropdown-toggle');
            
            if (!toggle && !e.target.closest('.dropdown-menu')) {
                document.querySelectorAll('.dropdown.active').forEach(el => el.classList.remove('active'));
                return;
            }

            if (toggle) {
                e.preventDefault();
                const dropdown = toggle.closest('.dropdown');
                const isActive = dropdown.classList.contains('active');
                
                document.querySelectorAll('.dropdown.active').forEach(el => el.classList.remove('active'));
                
                if (!isActive) dropdown.classList.add('active');
            }
        });
    },

    initSidebarToggle() {
        const toggleBtn = document.querySelector('.sidebar-toggle-btn');
        const sidebar = document.querySelector('.sidebar');
        const mainContainer = document.querySelector('.dashboard-page-container');
        
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                if (window.innerWidth > 992) {
                    sidebar.classList.toggle('sidebar-collapsed');
                    if (mainContainer) mainContainer.classList.toggle('sidebar-collapsed-main');
                } else {
                    sidebar.classList.toggle('mobile-open');
                    this.toggleOverlay(sidebar.classList.contains('mobile-open'));
                }
            });
        }
    },

    initMobileMenuToggle() {
        const toggle = document.querySelector('.mobile-menu-toggle');
        const collapse = document.querySelector('.navbar-collapse');
        if (toggle && collapse) {
            toggle.addEventListener('click', () => {
                collapse.classList.toggle('show');
            });
        }
    },

    toggleOverlay(show) {
        let overlay = document.querySelector('.layout-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'layout-overlay';
            document.body.appendChild(overlay);
            overlay.addEventListener('click', () => {
                const sidebar = document.querySelector('.sidebar');
                if (sidebar) sidebar.classList.remove('mobile-open');
                this.toggleOverlay(false);
            });
        }
        if (show) {
            overlay.classList.add('active');
        } else {
            overlay.classList.remove('active');
        }
    },

    initTabsEngine() {
        document.addEventListener('click', (e) => {
            const tabBtn = e.target.closest('.tab-btn');
            if (!tabBtn) return;
            
            const targetId = tabBtn.getAttribute('data-tab-target');
            if (!targetId) return;

            const tabsContainer = tabBtn.closest('.tabs');
            const contentContainer = document.querySelector(targetId).parentElement;

            tabsContainer.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            contentContainer.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            tabBtn.classList.add('active');
            document.querySelector(targetId).classList.add('active');
        });
    },

    initAccordionEngine() {
        document.addEventListener('click', (e) => {
            const header = e.target.closest('.accordion-header');
            if (!header) return;

            const item = header.closest('.accordion-item');
            item.classList.toggle('active');
        });
    },

    initTooltipEngine() {
        // CSS handles hover tooltips via .tooltip-wrapper
    },

    initNavbarScrollDetection() {
        const navbar = document.querySelector('.header') || document.querySelector('.navbar');
        if (navbar) {
            window.addEventListener('scroll', Utils.throttle(() => {
                if (window.scrollY > 10) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }, 100));
        }
    },

    initScrollProgressUtility() {
        const bar = document.querySelector('.scroll-progress-bar');
        if (bar) {
            window.addEventListener('scroll', Utils.throttle(() => {
                const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const scrolled = (winScroll / height) * 100;
                bar.style.width = scrolled + '%';
            }, 50));
        }
    },

    initBackToTopUtility() {
        const btn = document.querySelector('.back-to-top');
        if (btn) {
            window.addEventListener('scroll', Utils.throttle(() => {
                if (window.scrollY > 300) {
                    btn.classList.add('visible');
                } else {
                    btn.classList.remove('visible');
                }
            }, 100));

            btn.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    },

    initClickOutsideDetection() {
        // Used in Dropdown Engine and Overlays
    },

    initLoaderController() {
        const loader = document.getElementById('global-loader');
        if (loader) {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    loader.classList.add('hidden');
                    setTimeout(() => loader.style.display = 'none', 500);
                }, 500);
            });
        }
    },

    initLayoutResizeHandler() {
        window.addEventListener('resize', Utils.debounce(() => {
            if (window.innerWidth > 992) {
                const sidebar = document.querySelector('.sidebar');
                if (sidebar && sidebar.classList.contains('mobile-open')) {
                    sidebar.classList.remove('mobile-open');
                    this.toggleOverlay(false);
                }
            }
        }, 200));
    }
};

window.ModalEngine = {
    open(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            this.trapFocus(modal);
        }
    },
    close(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    },
    init() {
        document.addEventListener('click', (e) => {
            if (e.target.hasAttribute('data-modal-target')) {
                e.preventDefault();
                this.open(e.target.getAttribute('data-modal-target'));
            }
            if (e.target.hasAttribute('data-modal-close') || e.target.classList.contains('modal-backdrop')) {
                const modal = e.target.closest('.modal-backdrop');
                if (modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
        });
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-backdrop.active').forEach(modal => {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }
        });
    },
    trapFocus(modal) {
        const focusableElements = modal.querySelectorAll('a[href], button:not([disabled]), textarea, input, select');
        if(focusableElements.length) focusableElements[0].focus();
    }
};
document.addEventListener('DOMContentLoaded', () => ModalEngine.init());

window.ToastEngine = {
    containerTopRight: null,
    
    init() {
        if(!this.containerTopRight) {
            this.containerTopRight = document.createElement('div');
            this.containerTopRight.className = 'toast-container toast-top-right';
            document.body.appendChild(this.containerTopRight);
        }
    },
    
    show(options) {
        this.init();
        const { type = 'info', title = '', message = '', duration = 4000 } = options;
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        let iconClass = 'fa-info-circle';
        if(type === 'success') iconClass = 'fa-check-circle';
        if(type === 'error') iconClass = 'fa-times-circle';
        if(type === 'warning') iconClass = 'fa-exclamation-triangle';

        toast.innerHTML = `
            <div class="toast-body">
                <i class="fas ${iconClass} toast-icon"></i>
                <div class="toast-content">
                    ${title ? `<span class="toast-title">${title}</span>` : ''}
                    <span class="toast-message">${message}</span>
                </div>
                <button class="toast-close">&times;</button>
            </div>
            <div class="toast-progress"><div class="toast-progress-bar"></div></div>
        `;

        this.containerTopRight.appendChild(toast);
        
        const progressBar = toast.querySelector('.toast-progress-bar');
        setTimeout(() => { progressBar.style.width = '0%'; progressBar.style.transitionDuration = `${duration}ms`; }, 50);

        const closeToast = () => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        };

        toast.querySelector('.toast-close').addEventListener('click', closeToast);
        
        if (duration > 0) {
            setTimeout(closeToast, duration);
        }
    }
};

const Utils = {
    debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    },
    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
};
