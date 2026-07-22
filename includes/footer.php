<?php
if (!isset($basePath)) {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $baseDir = dirname($scriptName);
    $baseDir = str_replace('\\', '/', $baseDir);
    $basePath = rtrim($baseDir, '/') . '/';
}
?>
<!-- Footer Section -->
<footer class="footer bg-dark text-white pt-5 pb-4">
    <div class="container">
        <div class="row g-4 mb-4">
            <!-- Brand Column -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand mb-3">
                    <a href="<?php echo $basePath; ?>" class="d-flex align-items-center text-decoration-none mb-3">
                        <img src="<?php echo $basePath; ?>assets/images/logo.png" alt="Arohan Foundation Logo" style="height: 48px; width: auto; object-fit: contain;" class="me-2" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="logo-icon-fallback d-none align-items-center justify-content-center bg-primary text-white rounded-circle me-2" style="width: 40px; height: 40px;">
                            <i class="fa-solid fa-hand-holding-heart"></i>
                        </div>
                        <span class="fs-4 fw-bold text-white">Arohan<span class="text-success">Foundation</span></span>
                    </a>
                    <p class="text-light-muted fs-6 mb-3" style="line-height: 1.6; color: #94a3b8;">
                        Arohan Foundation is a dedicated non-profit organization focused on empowering vulnerable communities through quality education, healthcare outreach, emergency relief, and sustainable development.
                    </p>
                    <div class="social-links d-flex gap-2">
                        <a href="https://facebook.com" target="_blank" class="social-icon-btn" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="https://twitter.com" target="_blank" class="social-icon-btn" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="https://instagram.com" target="_blank" class="social-icon-btn" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                        <a href="https://linkedin.com" target="_blank" class="social-icon-btn" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="https://youtube.com" target="_blank" class="social-icon-btn" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>
            </div>

            <!-- Quick Navigation -->
            <div class="col-lg-2 col-md-6 col-6">
                <h5 class="fw-bold mb-3 text-white border-bottom border-success border-2 pb-2 d-inline-block">Quick Links</h5>
                <ul class="list-unstyled footer-menu-links">
                    <li class="mb-2"><a href="<?php echo $basePath; ?>#home" class="text-light-muted text-decoration-none hover-white"><i class="fa-solid fa-chevron-right me-2 text-success small"></i>Home</a></li>
                    <li class="mb-2"><a href="<?php echo $basePath; ?>#about" class="text-light-muted text-decoration-none hover-white"><i class="fa-solid fa-chevron-right me-2 text-success small"></i>About Us</a></li>
                    <li class="mb-2"><a href="<?php echo $basePath; ?>#services" class="text-light-muted text-decoration-none hover-white"><i class="fa-solid fa-chevron-right me-2 text-success small"></i>Services</a></li>
                    <li class="mb-2"><a href="<?php echo $basePath; ?>#donate" class="text-light-muted text-decoration-none hover-white"><i class="fa-solid fa-chevron-right me-2 text-success small"></i>Donate Now</a></li>
                    <li class="mb-2"><a href="<?php echo $basePath; ?>#volunteers" class="text-light-muted text-decoration-none hover-white"><i class="fa-solid fa-chevron-right me-2 text-success small"></i>Volunteers</a></li>
                    <li class="mb-2"><a href="<?php echo $basePath; ?>#contact" class="text-light-muted text-decoration-none hover-white"><i class="fa-solid fa-chevron-right me-2 text-success small"></i>Contact Us</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-3 col-md-6 col-6">
                <h5 class="fw-bold mb-3 text-white border-bottom border-success border-2 pb-2 d-inline-block">Contact Info</h5>
                <ul class="list-unstyled text-light-muted fs-6">
                    <li class="d-flex align-items-start mb-3" style="color: #94a3b8;">
                        <i class="fa-solid fa-location-dot text-primary mt-1 me-3 fs-5"></i>
                        <span>123 Arohan Towers, Main Boulevard, City Center</span>
                    </li>
                    <li class="d-flex align-items-center mb-3" style="color: #94a3b8;">
                        <i class="fa-solid fa-phone text-success me-3 fs-5"></i>
                        <span>+1 (800) 555-AROHAN</span>
                    </li>
                    <li class="d-flex align-items-center mb-3" style="color: #94a3b8;">
                        <i class="fa-solid fa-envelope text-info me-3 fs-5"></i>
                        <span>contact@arohanfoundation.org</span>
                    </li>
                    <li class="d-flex align-items-center" style="color: #94a3b8;">
                        <i class="fa-solid fa-clock text-warning me-3 fs-5"></i>
                        <span>Mon - Sat: 8:00 AM - 6:00 PM</span>
                    </li>
                </ul>
            </div>

            <!-- Newsletter Subscription -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-3 text-white border-bottom border-success border-2 pb-2 d-inline-block">Newsletter</h5>
                <p class="text-light-muted small mb-3" style="color: #94a3b8;">
                    Subscribe to receive real-time updates on our campaigns, transparent financial reports, and impact stories.
                </p>
                <form id="footerNewsletterForm" class="mb-3" onsubmit="handleFooterNewsletter(event)">
                    <div class="input-group">
                        <input type="email" id="footerNewsletterEmail" class="form-control bg-dark border-secondary text-white shadow-none" placeholder="Enter your email" required>
                        <button class="btn btn-success text-white px-3" type="submit" aria-label="Subscribe">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
                <div id="footerNewsletterAlert" class="alert alert-success d-none py-2 px-3 small" role="alert"></div>
            </div>
        </div>

        <!-- Divider & Bottom Credits -->
        <hr class="border-secondary my-4 opacity-50">
        
        <div class="row align-items-center text-center text-md-start">
            <div class="col-md-6 mb-2 mb-md-0">
                <p class="mb-0 text-light-muted small" style="color: #94a3b8;">
                    &copy; <?php echo date('Y'); ?> Arohan Foundation Management System. All Rights Reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <ul class="list-inline mb-0 small">
                    <li class="list-inline-item"><a href="#" class="text-light-muted text-decoration-none">Privacy Policy</a></li>
                    <li class="list-inline-item ms-3"><a href="#" class="text-light-muted text-decoration-none">Terms of Service</a></li>
                    <li class="list-inline-item ms-3"><a href="#" class="text-light-muted text-decoration-none">Annual Financials</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/bootstrap.bundle.min.js"></script>

<!-- AOS (Animate On Scroll) JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Main Interactive JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize AOS Animations
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    }

    // 2. Navbar Scroll Behavior (Sticky Shadow & Glassmorphism)
    const header = document.getElementById('mainHeader');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 40) {
            header.classList.add('shadow-sm', 'bg-white', 'header-scrolled');
        } else {
            header.classList.remove('shadow-sm', 'header-scrolled');
        }
    });

    // 3. Impact Statistics Counter Animation
    const counters = document.querySelectorAll('.stat-counter');
    let countersTriggered = false;

    function startCounterAnimation() {
        const statsSection = document.getElementById('impact-stats');
        if (!statsSection || countersTriggered) return;

        const rect = statsSection.getBoundingClientRect();
        if (rect.top <= window.innerHeight - 100 && rect.bottom >= 0) {
            countersTriggered = true;
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const speed = 40; // lower is faster
                const increment = Math.ceil(target / (2000 / speed));

                let count = 0;
                const updateCount = () => {
                    count += increment;
                    if (count >= target) {
                        counter.innerText = target.toLocaleString() + '+';
                    } else {
                        counter.innerText = count.toLocaleString() + '+';
                        setTimeout(updateCount, speed);
                    }
                };
                updateCount();
            });
        }
    }

    window.addEventListener('scroll', startCounterAnimation);
    startCounterAnimation(); // Run check on initial load
});

// Newsletter Handler
function handleFooterNewsletter(e) {
    e.preventDefault();
    const email = document.getElementById('footerNewsletterEmail').value;
    const alert = document.getElementById('footerNewsletterAlert');
    if (email.trim()) {
        alert.innerText = "Thank you for subscribing to Arohan Foundation updates!";
        alert.classList.remove('d-none');
        document.getElementById('footerNewsletterEmail').value = '';
        setTimeout(() => {
            alert.classList.add('d-none');
        }, 5000);
    }
}
</script>
</body>
</html>

