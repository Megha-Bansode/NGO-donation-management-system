/**
 * AROHAN FOUNDATION - PREMIUM DASHBOARD FOUNDATION
 * Core JavaScript logic for dashboard interactivity
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Sidebar Toggle Logic
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    // Check local storage for desktop state
    const isMobile = window.innerWidth <= 992;
    const sidebarState = localStorage.getItem('sidebar_collapsed');
    
    if (!isMobile && sidebarState === 'true' && sidebar) {
        sidebar.classList.add('collapsed');
    }

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            if (window.innerWidth <= 992) {
                // Mobile behavior: slide in/out
                sidebar.classList.toggle('mobile-open');
                if (overlay) overlay.classList.toggle('show');
            } else {
                // Desktop behavior: collapse/expand
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
            }
        });
    }

    // Close mobile sidebar on overlay click
    if (overlay && sidebar) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('show');
        });
    }

    // 2. Dropdown Logic
    const dropdownToggles = document.querySelectorAll('[data-toggle="dropdown"]');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            // Allow clicks inside the dropdown menu (like links) to function normally
            if (e.target.closest('.dropdown-menu')) {
                return;
            }
            
            e.preventDefault();
            e.stopPropagation();
            
            const targetId = toggle.getAttribute('data-target');
            const targetMenu = document.getElementById(targetId);
            
            // Close all other dropdowns first
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                if (menu.id !== targetId) {
                    menu.classList.remove('show');
                }
            });

            if (targetMenu) {
                targetMenu.classList.toggle('show');
            }
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-toggle="dropdown"]') && !e.target.closest('.dropdown-menu')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // 3. KPI Counter Animation (Performance Optimized)
    const animateCounters = () => {
        const counters = document.querySelectorAll('.counter-animate');
        
        counters.forEach(counter => {
            const targetStr = counter.getAttribute('data-target');
            // Remove non-numeric characters for counting
            const targetNum = parseInt(targetStr.replace(/[^0-9]/g, ''));
            const suffix = targetStr.replace(/[0-9]/g, '');
            
            if (isNaN(targetNum)) return;
            
            const duration = 2000; // 2 seconds
            const startTime = performance.now();
            
            const updateCounter = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // easeOutExpo
                const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                const currentNum = Math.floor(easeProgress * targetNum);
                
                counter.textContent = currentNum.toLocaleString() + suffix;
                
                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = targetStr; // Ensure final exact string
                }
            };
            
            requestAnimationFrame(updateCounter);
            
            // Prevent re-animation
            counter.classList.remove('counter-animate');
        });
    };

    // Run counters after a short delay for entrance animations
    setTimeout(animateCounters, 600);
});
